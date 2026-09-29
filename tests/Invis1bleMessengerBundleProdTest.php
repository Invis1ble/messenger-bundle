<?php

declare(strict_types=1);

namespace Invis1ble\MessengerBundle\Tests;

use Invis1ble\Messenger\Command\CommandBus;
use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Command\TraceableCommandBus;
use Invis1ble\Messenger\Event\EventBus;
use Invis1ble\Messenger\Event\EventBusInterface;
use Invis1ble\Messenger\Event\TraceableEventBus;
use Invis1ble\Messenger\Query\QueryBus;
use Invis1ble\Messenger\Query\QueryBusInterface;
use Invis1ble\Messenger\Query\TraceableQueryBus;
use Invis1ble\MessengerBundle\Invis1bleMessengerBundle;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Production loads the regular buses without the test-only traceable decorators.
 */
class Invis1bleMessengerBundleProdTest extends AbstractExtensionTestCase
{
    #[DataProvider('provideBus')]
    public function testContainerContainsUndecoratedBus(
        string $busFqn,
        string $busAliasFqn,
        string $traceableBusFqn,
        string $busName,
    ): void {
        $this->load();
        $this->compile();

        // Each bus wraps its corresponding Symfony Messenger bus.
        $this->assertContainerBuilderHasService($busFqn);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            serviceId: $busFqn,
            argumentIndex: 0,
            expectedValue: new Reference($busName),
        );

        // The interface resolves to the regular bus in production.
        $this->assertContainerBuilderHasAlias($busAliasFqn, $busFqn);

        // Traceable decorators are only available in the test environment.
        $this->assertContainerBuilderNotHasService($traceableBusFqn);
    }

    /**
     * @return \Generator<string[]>
     */
    public static function provideBus(): iterable
    {
        yield [
            CommandBus::class,
            CommandBusInterface::class,
            TraceableCommandBus::class,
            'messenger.bus.command',
        ];
        yield [
            QueryBus::class,
            QueryBusInterface::class,
            TraceableQueryBus::class,
            'messenger.bus.query',
        ];
        yield [
            EventBus::class,
            EventBusInterface::class,
            TraceableEventBus::class,
            'messenger.bus.event.async',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setParameter('kernel.environment', 'prod');
        $this->setParameter('kernel.build_dir', __DIR__);
    }

    protected function getContainerExtensions(): array
    {
        return [
            $this->createBundle()->getContainerExtension(),
        ];
    }

    private function createBundle(): AbstractBundle
    {
        return new Invis1bleMessengerBundle();
    }
}
