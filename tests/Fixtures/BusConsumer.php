<?php

declare(strict_types=1);

namespace Invis1ble\MessengerBundle\Tests\Fixtures;

use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Event\EventBusInterface;
use Invis1ble\Messenger\Query\QueryBusInterface;

class BusConsumer
{
    public function __construct(
        public readonly CommandBusInterface $commandBus,
        public readonly QueryBusInterface $queryBus,
        public readonly EventBusInterface $eventBus,
    ) {
    }
}
