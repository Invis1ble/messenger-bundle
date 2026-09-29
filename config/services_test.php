<?php

declare(strict_types=1);

use Invis1ble\Messenger\Command\CommandBus;
use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Command\TraceableCommandBus;
use Invis1ble\Messenger\Event\EventBus;
use Invis1ble\Messenger\Event\EventBusInterface;
use Invis1ble\Messenger\Event\TraceableEventBus;
use Invis1ble\Messenger\Query\QueryBus;
use Invis1ble\Messenger\Query\QueryBusInterface;
use Invis1ble\Messenger\Query\TraceableQueryBus;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Autowiring injects the decorated bus into each traceable bus.
    $services->set(TraceableCommandBus::class)
        ->decorate(CommandBus::class);
    // Expose the traceable decorator through the bus interface in tests.
    $services->alias(CommandBusInterface::class, TraceableCommandBus::class);

    $services->set(TraceableEventBus::class)
        ->decorate(EventBus::class);
    $services->alias(EventBusInterface::class, TraceableEventBus::class);

    $services->set(TraceableQueryBus::class)
        ->decorate(QueryBus::class);
    $services->alias(QueryBusInterface::class, TraceableQueryBus::class);
};
