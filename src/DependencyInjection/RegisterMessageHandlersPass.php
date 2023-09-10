<?php

declare(strict_types=1);

namespace Invis1ble\MessengerBundle\DependencyInjection;

use Invis1ble\Messenger\Command\CommandHandlerInterface;
use Invis1ble\Messenger\Event\EventHandlerInterface;
use Invis1ble\Messenger\Query\QueryHandlerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterMessageHandlersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $messageHandlerTag = 'messenger.message_handler';

        foreach ([
            CommandHandlerInterface::class => 'messenger.bus.command',
            QueryHandlerInterface::class => 'messenger.bus.query',
            EventHandlerInterface::class => 'messenger.bus.event.async',
        ] as $handler => $bus) {
            $definition = $container->registerForAutoconfiguration($handler);

            if ($definition->hasTag($messageHandlerTag)) {
                return;
            }

            $definition
                ->setPublic(true)
                ->addTag($messageHandlerTag, ['bus' => $bus])
            ;
        }
    }
}
