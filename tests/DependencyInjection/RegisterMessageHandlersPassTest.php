<?php

declare(strict_types=1);

namespace DependencyInjection;

use Invis1ble\Messenger\Command\CommandHandlerInterface;
use Invis1ble\Messenger\Event\EventHandlerInterface;
use Invis1ble\Messenger\Query\QueryHandlerInterface;
use Invis1ble\MessengerBundle\DependencyInjection\RegisterMessageHandlersPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterMessageHandlersPassTest extends TestCase
{
    /**
     * @dataProvider processDataProvider
     */
    public function testProcess(
        string $fqcn,
        string $bus,
        string $tag = 'messenger.message_handler'
    ): void {
        $container = new ContainerBuilder();

        $this->process($container);

        $definitions = $container->getAutoconfiguredInstanceof();

        $this->assertArrayHasKey($fqcn, $definitions, sprintf('Interface "%s" is not autoconfigured.', $fqcn));
        $this->assertTrue(
            $definitions[$fqcn]->hasTag($tag),
            sprintf('Interface "%s" must be tagged as "%s".', $fqcn, $tag)
        );

        $tag = $definitions[$fqcn]->getTag($tag)[0];

        $this->assertArrayHasKey('bus', $tag, sprintf('Interface "%s" tag must have attribute "bus".', $fqcn));
        $this->assertSame(
            $bus,
            $tag['bus'],
            sprintf('Command handler definition tag attribute "bus" must be "%s".', $bus)
        );
    }

    public static function processDataProvider(): array
    {
        return [
            [CommandHandlerInterface::class, 'messenger.bus.command'],
            [QueryHandlerInterface::class, 'messenger.bus.query'],
            [EventHandlerInterface::class, 'messenger.bus.event.async'],
        ];
    }

    protected function process(ContainerBuilder $container): void
    {
        (new RegisterMessageHandlersPass())
            ->process($container);
    }
}
