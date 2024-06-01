<?php

declare(strict_types=1);

namespace Invis1ble\MessengerBundle\Tests\MessageHandler;

use Invis1ble\Messenger\Event\EventHandlerInterface;
use Invis1ble\Messenger\Event\EventInterface;

class TestEventHandler implements EventHandlerInterface
{
    public function __invoke(EventInterface $event): void
    {
        // do nothing
    }
}
