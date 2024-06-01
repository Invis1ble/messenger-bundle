<?php

declare(strict_types=1);

namespace Invis1ble\MessengerBundle\Tests\DependencyInjection;

use Invis1ble\MessengerBundle\DependencyInjection\RegisterMessageHandlersPass;
use Invis1ble\MessengerBundle\Tests\MessageHandler\TestCommandHandler;
use Invis1ble\MessengerBundle\Tests\MessageHandler\TestEventHandler;
use Invis1ble\MessengerBundle\Tests\MessageHandler\TestQueryHandler;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterMessageHandlersPassTest extends AbstractCompilerPassTestCase
{
    #[DataProvider('provideHandlerAndCorrespondingBus')]
    public function testMessageHandlerTagged(
        string $handlerFqn,
        string $bus,
    ): void {
        $definition = new Definition();
        $definition->setAutoconfigured(true);
        $this->setDefinition($handlerFqn, $definition);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            $handlerFqn,
            'messenger.message_handler',
            ['bus' => $bus],
        );
    }

    /**
     * @return \Generator<array<string, string>>
     */
    public static function provideHandlerAndCorrespondingBus(): iterable
    {
        yield [TestCommandHandler::class, 'messenger.bus.command'];
        yield [TestQueryHandler::class, 'messenger.bus.query'];
        yield [TestEventHandler::class, 'messenger.bus.event.async'];
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            new RegisterMessageHandlersPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            99999,
        );
    }
}
