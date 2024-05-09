MessengerBundle
==================

![CI Status](https://github.com/Invis1ble/messenger-bundle/actions/workflows/ci.yml/badge.svg?event=push)
[![Packagist](https://img.shields.io/packagist/v/Invis1ble/messenger-bundle.svg)](https://packagist.org/packages/Invis1ble/messenger-bundle)
[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE)

The `MessengerBundle` provides integration of the [invis1ble/messenger](https://github.com/Invis1ble/messenger) library into the Symfony framework.


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


Development
-----------

### Getting started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up -d --wait` to start the Docker containers
4. Run `docker compose exec php composer install` to install dependencies
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

### Check for Coding Standards violations

Run PHP_CodeSniffer checks:

```sh
docker compose exec -it php bin/php_codesniffer
```

Run PHP-CS-Fixer checks:

```sh
docker compose exec -it php bin/php-cs-fixer
```


License
-------

[The MIT License](./LICENSE)
