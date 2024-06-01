<?php

declare(strict_types=1);

namespace Invis1ble\MessengerBundle\Tests\MessageHandler;

use Invis1ble\Messenger\Query\QueryHandlerInterface;
use Invis1ble\Messenger\Query\QueryInterface;

class TestQueryHandler implements QueryHandlerInterface
{
    public function __invoke(QueryInterface $query): void
    {
        // do nothing
    }
}
