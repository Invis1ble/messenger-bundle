<?php

declare(strict_types=1);

namespace Invis1ble\MessengerBundle\Tests;

use Invis1ble\Messenger\Command\CommandBus;
use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Command\TraceableCommandBus;
use Invis1ble\Messenger\Event\EventBus;
use Invis1ble\Messenger\Event\EventBusInterface;
use Invis1ble\Messenger\Event\TraceableEventBus;
use Invis1ble\Messenger\Query\QueryBus;
use Invis1ble\Messenger\Query\QueryBusInterface;
use Invis1ble\Messenger\Query\TraceableQueryBus;
use Invis1ble\MessengerBundle\Invis1bleMessengerBundle;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * В prod-окружении грузится только services.php: шины регистрируются напрямую,
 * алиасы *BusInterface указывают на сами *Bus, а Traceable*-декораторов нет.
 *
 * Тест фиксирует, что test-only декорация (services_test.php) НЕ просачивается
 * в prod — зеркало к Invis1bleMessengerBundleTest, который проверяет test-env.
 */
class Invis1bleMessengerBundleProdTest extends AbstractExtensionTestCase
{
    #[DataProvider('provideBus')]
    public function testContainerContainsUndecoratedBus(
        string $busFqn,
        string $busAliasFqn,
        string $traceableBusFqn,
        string $busName,
    ): void {
        $this->load();
        $this->compile();

        // Шина зарегистрирована напрямую и принимает соответствующую messenger-шину.
        $this->assertContainerBuilderHasService($busFqn);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            serviceId: $busFqn,
            argumentIndex: 0,
            expectedValue: new Reference($busName),
        );

        // Алиас интерфейса указывает на саму шину, а не на Traceable-декоратор.
        $this->assertContainerBuilderHasAlias($busAliasFqn, $busFqn);

        // Traceable-декоратор в prod отсутствует.
        $this->assertContainerBuilderNotHasService($traceableBusFqn);
    }

    /**
     * @return \Generator<string[]>
     */
    public static function provideBus(): iterable
    {
        yield [
            CommandBus::class,
            CommandBusInterface::class,
            TraceableCommandBus::class,
            'messenger.bus.command',
        ];
        yield [
            QueryBus::class,
            QueryBusInterface::class,
            TraceableQueryBus::class,
            'messenger.bus.query',
        ];
        yield [
            EventBus::class,
            EventBusInterface::class,
            TraceableEventBus::class,
            'messenger.bus.event.async',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setParameter('kernel.environment', 'prod');
        $this->setParameter('kernel.build_dir', __DIR__);
    }

    protected function getContainerExtensions(): array
    {
        return [
            $this->createBundle()->getContainerExtension(),
        ];
    }

    private function createBundle(): AbstractBundle
    {
        return new Invis1bleMessengerBundle();
    }
}
