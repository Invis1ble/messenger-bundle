<?php

declare(strict_types=1);

namespace Invis1ble\MessengerBundle\Tests\DependencyInjection;

use Invis1ble\Messenger\Command\CommandHandlerInterface;
use Invis1ble\Messenger\Event\EventHandlerInterface;
use Invis1ble\Messenger\Query\QueryHandlerInterface;
use Invis1ble\MessengerBundle\DependencyInjection\RegisterMessageHandlersPass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterMessageHandlersPassTest extends TestCase
{
    #[DataProvider('provideHandlerAndCorrespondingMessageBus')]
    public function testProcess(
        string $handlerClassName,
        string $bus,
        string $tag = 'messenger.message_handler',
    ): void {
        $container = new ContainerBuilder();

        $this->process($container);

        $definitions = $container->getAutoconfiguredInstanceof();

        $this->assertArrayHasKey(
            $handlerClassName,
            $definitions,
            sprintf('Interface "%s" is not autoconfigured.', $handlerClassName),
        );
        $this->assertTrue(
            $definitions[$handlerClassName]->hasTag($tag),
            sprintf('Interface "%s" must be tagged as "%s".', $handlerClassName, $tag),
        );

        $tag = $definitions[$handlerClassName]->getTag($tag)[0];

        $this->assertArrayHasKey(
            'bus',
            $tag,
            sprintf('Interface "%s" tag must have attribute "bus".', $handlerClassName),
        );
        $this->assertSame(
            $bus,
            $tag['bus'],
            sprintf('Command handler definition tag attribute "bus" must be "%s".', $bus),
        );
    }

    #[DataProvider('provideHandlerAndCorrespondingMessageBus')]
    public function testAddMessageHandlerTagOnlyOnce(
        string $handlerClassName,
        string $bus,
    ): void {
        $container = new ContainerBuilder();

        $container->registerForAutoconfiguration($handlerClassName)
            ->setPublic(true)
            ->addTag('messenger.message_handler', ['bus' => $bus])
        ;

        $this->process($container);

        $definitions = $container->getAutoconfiguredInstanceof();
        $tag = $definitions[$handlerClassName]->getTag('messenger.message_handler');

        $this->assertSame([['bus' => $bus]], $tag);
    }

    /**
     * @return \Generator<array<string, string>>
     */
    public static function provideHandlerAndCorrespondingMessageBus(): \Generator
    {
        yield from [
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
