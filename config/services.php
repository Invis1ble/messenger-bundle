<?php

declare(strict_types=1);

use Invis1ble\Messenger\Command\CommandBus;
use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Event\EventBus;
use Invis1ble\Messenger\Event\EventBusInterface;
use Invis1ble\Messenger\Query\QueryBus;
use Invis1ble\Messenger\Query\QueryBusInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(CommandBus::class)
        ->args([service('messenger.bus.command')]);
    $services->alias(CommandBusInterface::class, CommandBus::class);

    $services->set(EventBus::class)
        ->args([service('messenger.bus.event.async')]);
    $services->alias(EventBusInterface::class, EventBus::class);

    $services->set(QueryBus::class)
        ->args([service('messenger.bus.query')]);
    $services->alias(QueryBusInterface::class, QueryBus::class);
};
