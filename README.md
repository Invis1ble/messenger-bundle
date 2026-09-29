MessengerBundle
==================

![CI Status](https://github.com/Invis1ble/messenger-bundle/actions/workflows/ci.yml/badge.svg?event=push)
[![Code Coverage](https://codecov.io/gh/Invis1ble/messenger-bundle/graph/badge.svg?token=K7S3BXER5K)](https://codecov.io/gh/Invis1ble/messenger-bundle)
[![Packagist](https://img.shields.io/packagist/v/Invis1ble/messenger-bundle.svg)](https://packagist.org/packages/Invis1ble/messenger-bundle)
[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE)

The `MessengerBundle` provides integration of the [invis1ble/messenger](https://github.com/Invis1ble/messenger) library into the Symfony framework.

Compatibility
-------------

Version 6.2 adds Symfony 8 support and requires `invis1ble/messenger` 5.1 or later
within the 5.x series. Public bus interfaces and service IDs are unchanged.

| Symfony | PHP requirement |
| --- | --- |
| 6.4 / 7.x | PHP 8.2+ |
| 8.0 | PHP 8.4+ |
| 8.1 | PHP 8.4.1+ |

Stable dependencies are used by default. CI checks Symfony 6.4, 7.4, 8.0, and 8.1,
including a real kernel, `lint:container`, and message dispatch in `prod` and `test`.

To upgrade an existing Symfony 8.1 application, run Composer on PHP 8.4.1 or later:

```sh
composer require 'invis1ble/messenger-bundle:^6.2' --with-all-dependencies
```

When upgrading Symfony itself, also update the application's Symfony constraints
and its `extra.symfony.require` setting if Symfony Flex uses it to restrict versions.
No platform requirement bypass is needed.


Installation
------------

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
$ composer require invis1ble/messenger-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require invis1ble/messenger-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Invis1ble\MessengerBundle\Invis1bleMessengerBundle::class => ['all' => true],
];
```

Bus configuration
-----------------

Enable Symfony's FrameworkBundle and configure `MESSENGER_TRANSPORT_DSN` for the
`async` transport. The bundle provides these services and autowired interfaces:

| Bus interface | Symfony service ID | Handler interface |
| --- | --- | --- |
| `Invis1ble\Messenger\Command\CommandBusInterface` | `messenger.bus.command` | `Invis1ble\Messenger\Command\CommandHandlerInterface` |
| `Invis1ble\Messenger\Query\QueryBusInterface` | `messenger.bus.query` | `Invis1ble\Messenger\Query\QueryHandlerInterface` |
| `Invis1ble\Messenger\Event\EventBusInterface` | `messenger.bus.event.async` | `Invis1ble\Messenger\Event\EventHandlerInterface` |

Register handlers as services with `autoconfigure: true` and implement the matching
handler interface. The bundle assigns each handler to its corresponding bus; an
additional `messenger.message_handler` tag or `#[AsMessageHandler]` is unnecessary.
Command and query buses handle messages synchronously. The event bus is the default
Symfony bus and permits events without handlers.

Route events to the `async` transport in your application, for example:

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        routing:
            'Invis1ble\Messenger\Event\EventInterface': async
```

Consume queued events with `php bin/console messenger:consume async`. In the `test`
environment the bundle uses `sync://` for this transport and decorates all three
bus interfaces with traceable buses, so routed events are handled immediately.
Application configuration can override the bundle's defaults, including transports
and retry policies.


Development
-----------

### Getting started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up -d --wait` to start the Docker containers
4. Run `docker compose exec php composer install` to install dependencies
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

The development image uses PHP 8.5. To use PHP 8.4, set `PHP_VERSION=8.4` for both
the build and subsequent Compose commands. For a clean dependency resolution, use
a fresh checkout without `vendor/` or `composer.lock`.

Run all package checks:

```sh
docker compose exec -T php composer check
```

### Check for Coding Standards violations

Run PHP_CodeSniffer checks:

```sh
docker compose exec -it php bin/php_codesniffer
```

Run PHP-CS-Fixer checks:

```sh
docker compose exec -it php bin/php-cs-fixer
```


Testing
-------

To run Unit tests during development

```sh
docker compose exec php vendor/bin/phpunit
```

To run with coverage

```sh
XDEBUG_MODE=coverage docker compose up -d --wait
docker compose exec php vendor/bin/phpunit --coverage-clover var/log/coverage-clover.xml
```


License
-------

[The MIT License](./LICENSE)
