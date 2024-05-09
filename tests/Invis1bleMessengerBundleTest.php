<?php

declare(strict_types=1);

namespace Invis1ble\MessengerBundle\Tests;

use Invis1ble\MessengerBundle\DependencyInjection\RegisterMessageHandlersPass;
use Invis1ble\MessengerBundle\Invis1bleMessengerBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Invis1bleMessengerBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $bundle = new Invis1bleMessengerBundle();
        $container = new ContainerBuilder();

        $bundle->build($container);

        $passes = $container->getCompilerPassConfig()
            ->getPasses();

        $found = false;

        foreach ($passes as $pass) {
            if ($pass instanceof RegisterMessageHandlersPass) {
                $found = true;

                break;
            }
        }

        $this->assertTrue(
            $found,
            sprintf('%s is not added to the container.', RegisterMessageHandlersPass::class),
        );
    }
}
