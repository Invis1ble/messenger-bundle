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

    // Декораторы шин: autowire подставляет декорируемую шину (.inner) в
    // конструктор Traceable*Bus, поэтому явный аргумент не нужен — как в XML.
    $services->set(TraceableCommandBus::class)
        ->decorate(CommandBus::class);
    // Переопределяем алиас интерфейса на декоратор (поверх services.php).
    $services->alias(CommandBusInterface::class, TraceableCommandBus::class);

    $services->set(TraceableEventBus::class)
        ->decorate(EventBus::class);
    $services->alias(EventBusInterface::class, TraceableEventBus::class);

    $services->set(TraceableQueryBus::class)
        ->decorate(QueryBus::class);
    $services->alias(QueryBusInterface::class, TraceableQueryBus::class);
};
