<?php

declare(strict_types=1);

namespace Invis1ble\MessengerBundle;

use Invis1ble\MessengerBundle\DependencyInjection\RegisterMessageHandlersPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class Invis1bleMessengerBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterMessageHandlersPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 99999);
    }

    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $container->import('../config/services.xml');

        if ('test' === $container->env()) {
            $container->import('../config/services_test.xml');
        }
    }

    public function prependExtension(
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if (!$builder->hasExtension('framework')) {
            return;
        }

        $container->extension('framework', [
            'messenger' => [
                'default_bus' => 'messenger.bus.event.async',
                'transports' => [
                    [
                        'name' => 'async',
                        'dsn' => '%env(MESSENGER_TRANSPORT_DSN)%',
                        'retry_strategy' => [
                            'max_retries' => 3,
                            'delay' => 1000,
                            'multiplier' => 2,
                            'max_delay' => 0,
                        ],
                    ],
                ],
                'buses' => [
                    'messenger.bus.command' => [
                        'default_middleware' => [
                            'enabled' => false,
                        ],
                        'middleware' => 'handle_message',
                    ],
                    'messenger.bus.query' => [
                        'default_middleware' => [
                            'enabled' => false,
                        ],
                        'middleware' => 'handle_message',
                    ],
                    'messenger.bus.event.async' => [
                        'default_middleware' => [
                            'enabled' => true,
                            'allow_no_handlers' => true,
                        ],
                    ],
                ],
            ],
        ]);

        if ('test' === $container->env()) {
            $container->extension('framework', [
                'messenger' => [
                    'transports' => [
                        [
                            'name' => 'async',
                            'dsn' => 'sync://',
                            'retry_strategy' => [
                                'max_retries' => 0,
                            ],
                        ],
                    ],
                ],
            ]);
        }
    }
}
