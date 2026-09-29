<?php

declare(strict_types=1);

namespace Invis1ble\MessengerBundle\Tests\Fixtures;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\MessengerBundle\Invis1bleMessengerBundle;
use Invis1ble\MessengerBundle\Tests\MessageHandler\TestCommandHandler;
use Invis1ble\MessengerBundle\Tests\MessageHandler\TestEventHandler;
use Invis1ble\MessengerBundle\Tests\MessageHandler\TestQueryHandler;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Messenger\MessageBusInterface;

class TestKernel extends Kernel
{
    private readonly string $cacheDirectory;

    public function __construct(string $environment)
    {
        $this->cacheDirectory = sys_get_temp_dir() . '/messenger-bundle-' . bin2hex(random_bytes(8));

        parent::__construct($environment, false);
    }

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new Invis1bleMessengerBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(static function (ContainerBuilder $container): void {
            $container->setParameter('env(MESSENGER_TRANSPORT_DSN)', 'in-memory://');
            $container->loadFromExtension('framework', [
                'secret' => 'messenger-bundle-test',
                'http_method_override' => false,
                'messenger' => [
                    'routing' => [EventInterface::class => 'async'],
                ],
            ]);

            foreach ([BusConsumer::class, TestCommandHandler::class, TestQueryHandler::class, TestEventHandler::class] as $class) {
                $container->register($class, $class)
                    ->setAutoconfigured(true)
                    ->setAutowired(true)
                    ->setPublic(true);
            }

            foreach ([
                'command' => 'messenger.bus.command',
                'query' => 'messenger.bus.query',
                'event' => 'messenger.bus.event.async',
                'default' => MessageBusInterface::class,
                'routable' => 'messenger.routable_message_bus',
                'transport' => 'messenger.transport.async',
            ] as $name => $id) {
                $container->setAlias('test.' . $name, $id)->setPublic(true);
            }
        });
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__, 2);
    }

    public function getCacheDir(): string
    {
        return $this->cacheDirectory;
    }

    public function getLogDir(): string
    {
        return $this->cacheDirectory . '/log';
    }
}
