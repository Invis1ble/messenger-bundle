<?php

declare(strict_types=1);

namespace DependencyInjection;

use Invis1ble\Messenger\Command\CommandBus;
use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Event\EventBus;
use Invis1ble\Messenger\Event\EventBusInterface;
use Invis1ble\Messenger\Query\QueryBus;
use Invis1ble\Messenger\Query\QueryBusInterface;
use Invis1ble\MessengerBundle\DependencyInjection\Invis1bleMessengerExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Reference;

class Invis1bleMessengerExtensionTest extends AbstractExtensionTestCase
{
    public function testAfterLoadingBusesExists(): void
    {
        $this->setParameter('kernel.environment', 'test');
        $this->setParameter('kernel.build_dir', __DIR__);
        $this->load();
        $this->compile();

        $commandMessageBus = new Reference('messenger.bus.command');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            serviceId: CommandBus::class,
            argumentIndex: 0,
            expectedValue: $commandMessageBus,
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            serviceId: CommandBusInterface::class,
            argumentIndex: 0,
            expectedValue: $commandMessageBus,
        );
        $this->assertContainerBuilderHasService(CommandBus::class, CommandBus::class);
        $this->assertContainerBuilderHasAlias(CommandBusInterface::class, CommandBus::class);

        $eventMessageBus = new Reference('messenger.bus.event.async');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            serviceId: EventBus::class,
            argumentIndex: 0,
            expectedValue: $eventMessageBus,
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            serviceId: EventBusInterface::class,
            argumentIndex: 0,
            expectedValue: $eventMessageBus,
        );
        $this->assertContainerBuilderHasService(EventBus::class, EventBus::class);
        $this->assertContainerBuilderHasAlias(EventBusInterface::class, EventBus::class);

        $queryMessageBus = new Reference('messenger.bus.query');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            serviceId: QueryBus::class,
            argumentIndex: 0,
            expectedValue: $queryMessageBus,
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            serviceId: QueryBusInterface::class,
            argumentIndex: 0,
            expectedValue: $queryMessageBus,
        );
        $this->assertContainerBuilderHasService(QueryBus::class, QueryBus::class);
        $this->assertContainerBuilderHasAlias(QueryBusInterface::class, QueryBus::class);
    }

    protected function getContainerExtensions(): array
    {
        return [
            new Invis1bleMessengerExtension(),
        ];
    }
}
