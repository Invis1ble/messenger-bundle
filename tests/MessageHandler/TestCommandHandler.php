<?php

declare(strict_types=1);

namespace Invis1ble\MessengerBundle\Tests\MessageHandler;

use Invis1ble\Messenger\Command\CommandHandlerInterface;
use Invis1ble\Messenger\Command\CommandInterface;

class TestCommandHandler implements CommandHandlerInterface
{
    public function __invoke(CommandInterface $command): void
    {
        // do nothing
    }
}
