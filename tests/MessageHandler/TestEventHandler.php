<?php

declare(strict_types=1);

namespace Invis1ble\MessengerBundle\Tests\MessageHandler;

use Invis1ble\Messenger\Event\EventHandlerInterface;
use Invis1ble\Messenger\Event\EventInterface;

class TestEventHandler implements EventHandlerInterface
{
    /**
     * @var EventInterface[]
     */
    public array $messages = [];

    public function __invoke(EventInterface $event): void
    {
        $this->messages[] = $event;
    }
}
