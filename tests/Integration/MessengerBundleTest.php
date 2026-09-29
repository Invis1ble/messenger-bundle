<?php

declare(strict_types=1);

namespace Invis1ble\MessengerBundle\Tests\Integration;

use Invis1ble\Messenger\Command\CommandBus;
use Invis1ble\Messenger\Command\TraceableCommandBus;
use Invis1ble\Messenger\Event\EventBus;
use Invis1ble\Messenger\Event\TraceableEventBus;
use Invis1ble\Messenger\Query\QueryBus;
use Invis1ble\Messenger\Query\TraceableQueryBus;
use Invis1ble\MessengerBundle\Tests\Fixtures\BusConsumer;
use Invis1ble\MessengerBundle\Tests\Fixtures\TestCommand;
use Invis1ble\MessengerBundle\Tests\Fixtures\TestEvent;
use Invis1ble\MessengerBundle\Tests\Fixtures\TestKernel;
use Invis1ble\MessengerBundle\Tests\Fixtures\TestQuery;
use Invis1ble\MessengerBundle\Tests\MessageHandler\TestCommandHandler;
use Invis1ble\MessengerBundle\Tests\MessageHandler\TestEventHandler;
use Invis1ble\MessengerBundle\Tests\MessageHandler\TestQueryHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;

class MessengerBundleTest extends TestCase
{
    private ?TestKernel $kernel = null;

    #[DataProvider('provideEnvironment')]
    public function testContainerPassesLint(string $environment): void
    {
        $this->bootKernel($environment);
        $application = new Application($this->kernel);
        $command = $application->find('lint:container');
        $tester = new CommandTester($command);
        $tester->execute($command->getDefinition()->hasOption('resolve-env-vars') ? ['--resolve-env-vars' => true] : []);

        $this->assertSame(0, $tester->getStatusCode(), $tester->getDisplay());
    }

    #[DataProvider('provideEnvironment')]
    public function testBusesAreAutowiredAndHandleMessagesOnce(string $environment): void
    {
        $container = $this->bootKernel($environment);
        $consumer = $container->get(BusConsumer::class);
        $traced = 'test' === $environment;

        $this->assertInstanceOf($traced ? TraceableCommandBus::class : CommandBus::class, $consumer->commandBus);
        $this->assertInstanceOf($traced ? TraceableQueryBus::class : QueryBus::class, $consumer->queryBus);
        $this->assertInstanceOf($traced ? TraceableEventBus::class : EventBus::class, $consumer->eventBus);
        $this->assertSame($container->get('test.event'), $container->get('test.default'));

        $command = new TestCommand();
        $query = new TestQuery();
        $consumer->commandBus->dispatch($command);

        $this->assertSame('query result', $consumer->queryBus->ask($query));
        $this->assertSame([$command], $container->get(TestCommandHandler::class)->messages);
        $this->assertSame([$query], $container->get(TestQueryHandler::class)->messages);
        $this->assertSame([], $container->get(TestEventHandler::class)->messages);

        if ($traced) {
            $this->assertCount(1, $consumer->commandBus->getDispatchedCommands());
            $this->assertCount(1, $consumer->queryBus->getAskedQueries());
        }
    }

    public function testEventIsHandledSynchronouslyOnceInTestEnvironment(): void
    {
        $container = $this->bootKernel('test');
        $bus = $container->get(BusConsumer::class)->eventBus;
        $event = new TestEvent();
        $bus->dispatch($event);

        $this->assertInstanceOf(SyncTransport::class, $container->get('test.transport'));
        $this->assertSame([$event], $container->get(TestEventHandler::class)->messages);
        $this->assertSame([], $container->get(TestCommandHandler::class)->messages);
        $this->assertSame([], $container->get(TestQueryHandler::class)->messages);
        $this->assertCount(1, $bus->getDispatchedEvents());
    }

    public function testEventIsSentToTransportAndHandledOnceOnReceipt(): void
    {
        $container = $this->bootKernel('prod');
        $event = new TestEvent();
        $container->get(BusConsumer::class)->eventBus->dispatch($event);
        $transport = $container->get('test.transport');

        $this->assertInstanceOf(InMemoryTransport::class, $transport);
        $this->assertSame([], $container->get(TestEventHandler::class)->messages);
        $this->assertCount(1, $transport->getSent());
        $envelope = $transport->getSent()[0];
        $this->assertSame($event, $envelope->getMessage());
        $this->assertSame('messenger.bus.event.async', $envelope->last(BusNameStamp::class)->getBusName());

        $container->get('test.routable')->dispatch($envelope->with(new ReceivedStamp('async')));

        $this->assertSame([$event], $container->get(TestEventHandler::class)->messages);
        $this->assertCount(1, $transport->getSent());
        $this->assertSame([], $container->get(TestCommandHandler::class)->messages);
        $this->assertSame([], $container->get(TestQueryHandler::class)->messages);
    }

    #[DataProvider('provideMessageOnWrongBus')]
    public function testHandlersAreRestrictedToTheirBus(string $bus, object $message): void
    {
        $container = $this->bootKernel('test');

        if ('event' !== $bus) {
            $this->expectException(NoHandlerForMessageException::class);
        }

        $container->get('test.' . $bus)->dispatch($message);

        $this->assertSame([], $container->get(TestCommandHandler::class)->messages);
        $this->assertSame([], $container->get(TestQueryHandler::class)->messages);
        $this->assertSame([], $container->get(TestEventHandler::class)->messages);
    }

    /**
     * @return iterable<string[]>
     */
    public static function provideEnvironment(): iterable
    {
        yield 'production' => ['prod'];
        yield 'test' => ['test'];
    }

    /**
     * @return iterable<array{string, object}>
     */
    public static function provideMessageOnWrongBus(): iterable
    {
        yield ['command', new TestQuery()];
        yield ['command', new TestEvent()];
        yield ['query', new TestCommand()];
        yield ['query', new TestEvent()];
        yield ['event', new TestCommand()];
        yield ['event', new TestQuery()];
    }

    protected function tearDown(): void
    {
        if (null !== $this->kernel) {
            $this->kernel->shutdown();
            (new Filesystem())->remove($this->kernel->getCacheDir());
        }

        parent::tearDown();
    }

    private function bootKernel(string $environment): ContainerInterface
    {
        $this->kernel = new TestKernel($environment);
        $this->kernel->boot();

        return $this->kernel->getContainer();
    }
}
