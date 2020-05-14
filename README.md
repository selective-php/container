# selective/container

[![Latest Version on Packagist](https://img.shields.io/github/release/selective-php/container.svg?style=flat-square)](https://packagist.org/packages/selective/container)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://github.com/selective-php/container/workflows/PHP/badge.svg)](https://github.com/selective-php/container/actions)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/selective-php/container.svg?style=flat-square)](https://scrutinizer-ci.com/g/selective-php/container/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/quality/g/selective-php/container.svg?style=flat-square)](https://scrutinizer-ci.com/g/selective-php/container/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/selective/container.svg?style=flat-square)](https://packagist.org/packages/selective/container/stats)

## Description

A simple PSR-11 container implementation with **autowiring**.

## Requirements

 * PHP 7.2+

## Installation

```sh
composer require selective/container
```

## Usage

### Factories

You can use a factories (closures) to define injections.

```php
<?php

use App\Service\MyService;
use Selective\Container\Container;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

$container = new Container();

// Add definition
$container->factory(MyService::class, function (ContainerInterface $container) {
    return new MyService($container->get(LoggerInterface::class));
});
```

**Please note:** It's not supported to replace or extend an existing factory definition to avoid **unwanted side effects**.

### Use dependency injection

The container is able to automatically create and inject dependencies for you. This is called "autowiring".

To enable autowiring you have to add the `ConstructorResolver`:

```php
<?php

use Selective\Container\Container;
use Selective\Container\Resolver\ConstructorResolver;

$container = new Container();

// Enable autowiring
$container->addResolver(new ConstructorResolver($container));

//...

```

### Service providers

Service providers give the benefit of organising your container 
definitions along with an increase in performance for larger applications 
as definitions registered within a service provider are lazily registered 
at the point where a service is retrieved.

To build a service provider create a invokable class and 
return the definitons (factories) you would like to register.

```php
<?php

use App\Service\MyService;
use Selective\Container\Container;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class MyServiceFactoryProvider
{
    /**
     * @return array<string, callable>
     */
    public function __invoke(): array
    {
        return [
            MyService::class => function (ContainerInterface $container) {
                return new MyService($container->get(LoggerInterface::class));
            },
        ];
    }
}

$container = new Container();
$container->factories((new MyServiceFactoryProvider())());
```

## Slim 4 integration

Example to boostrap a Slim 4 application using the container:

```php
<?php

use Selective\Container\Container;
use Selective\Container\Resolver\ConstructorResolver;
use Slim\App;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$container = new Container();

// Enable autowiring
$container->addResolver(new ConstructorResolver($container));

// Load container definitons
$container->factories(require __DIR__ . '/container.php');

// Create slim app instance
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add routes, middleware etc...

$app->run();
```

The `container.php` file must return an array of factories (closures):

```php
<?php

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    'settings' => function () {
        return require __DIR__ . '/settings.php';
    },

    LoggerInterface::class => function (ContainerInterface $container) {
        $logger = new Logger('name');
        
        // ...
        
        return $logger;
    },
    
    // Add more definitions here...
}
```

## IDE integration

If you use PhpStorm, then create a new file `.phpstorm.meta.php`
in your project root directory and copy/paste the following content:

```php
<?php

namespace PHPSTORM_META;

override(\Psr\Container\ContainerInterface::get(0), map(['' => '@']));
```

## Performance

`selective/container` is about:

* 11% faster then `php-di/php-di`.
* 5.4% faster then `league/container`.

All tests where made with enabled autowiring.

## Credits

* Dominik Zogg (chubbyphp)

## Similar libraries

* https://github.com/chubbyphp/chubbyphp-container
* http://php-di.org/
* https://container.thephpleague.com/

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
