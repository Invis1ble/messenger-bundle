<?php

declare(strict_types=1);

namespace Invis1ble\MessengerBundle\Tests;

use Invis1ble\Messenger\Command\CommandBus;
use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Event\EventBus;
use Invis1ble\Messenger\Event\EventBusInterface;
use Invis1ble\Messenger\Query\QueryBus;
use Invis1ble\Messenger\Query\QueryBusInterface;
use Invis1ble\MessengerBundle\DependencyInjection\RegisterMessageHandlersPass;
use Invis1ble\MessengerBundle\Invis1bleMessengerBundle;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class Invis1bleMessengerBundleTest extends AbstractExtensionTestCase
{
    public function testContainerHasRegisterMessageHandlersPass(): void
    {
        $bundle = $this->createBundle();
        $bundle->build($this->container);

        $passes = $this->container->getCompilerPassConfig()
            ->getPasses();

        $found = false;

        foreach ($passes as $pass) {
            if ($pass instanceof RegisterMessageHandlersPass) {
                $found = true;

                break;
            }
        }

        $this->assertTrue(
            $found,
            sprintf('%s is not added to the container.', RegisterMessageHandlersPass::class),
        );
    }

    #[DataProvider('provideBus')]
    public function testContainerContainsBus(string $serviceFqn, string $aliasFqn, string $busName): void
    {
        $this->load();
        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            serviceId: $serviceFqn,
            argumentIndex: 0,
            expectedValue: new Reference($busName),
        );

        $this->assertContainerBuilderHasService($serviceFqn, $serviceFqn);
        $this->assertContainerBuilderHasAlias($aliasFqn, $serviceFqn);
    }

    /**
     * @return \Generator<string[]>
     */
    public static function provideBus(): iterable
    {
        yield [CommandBus::class, CommandBusInterface::class, 'messenger.bus.command'];
        yield [QueryBus::class, QueryBusInterface::class, 'messenger.bus.query'];
        yield [EventBus::class, EventBusInterface::class, 'messenger.bus.event.async'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setParameter('kernel.environment', 'test');
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
