# selective/container

[![Latest Version on Packagist](https://img.shields.io/github/release/selective-php/container.svg)](https://packagist.org/packages/selective/container)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Build Status](https://github.com/selective-php/container/workflows/build/badge.svg)](https://github.com/selective-php/container/actions)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/selective-php/container.svg)](https://scrutinizer-ci.com/g/selective-php/container/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/quality/g/selective-php/container.svg)](https://scrutinizer-ci.com/g/selective-php/container/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/selective/container.svg)](https://packagist.org/packages/selective/container/stats)

## Description

A PSR-11 container implementation with optional **autowiring**.

## Requirements

 * PHP 8.1+

## Installation

```
composer require selective/container
```

## Usage

```php
use Selective\Container\Container;

$container = new Container();
// ...

$myService = $container->get(MyService::class);
```

### Enable Autowiring

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

### Defining DI Container Definitions

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
    return new MyService();
});
```

## Defining Multiple DI Container Definitions

```php
use Psr\Container\ContainerInterface;
// ...

$entries = [
    MyService::class => function (ContainerInterface $container) {
        return new MyService();
    },
    
    PDO::class => function (ContainerInterface $container) {
        return new PDO('sqlite:example.db');
    },
    
    // and so on...
];

$container->factories($entries);
```


### Service Providers

Service providers give the benefit of organising your container 
definitions along with an increase in performance for larger applications 
as definitions registered within a service provider are lazily registered 
at the point where a service is retrieved.

To build a service provider create a invokable class and 
return the definitions (factories) you would like to register.

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

$container->factories((new MyServiceFactoryProvider())());
```

### Set definitions directly

In addition to defining entries in an array of factories / callbacks, 
you can also set the value directly as shown below:

```php
$container->set(\App\Domain\MyService::class, new \App\Domain\MyService());
```

### Fetching DI container entries

To fetch a value use the `get` method:

```php
$pdo = $container->get(PDO::class);
```

### Testing

* Make sure that your container will be recreated for each test. You may use the phpunit `setUp()` method to initialize the container definitions.
* You can use the `set()` method to overwrite existing container entries.

#### Mocking

The `set` method can also be used to set mocked objects directly into the container.

This example requires phpunit:

```php
<?php

$class = \App\Domain\User\Repository\UserRepository::class;

$mock = $this->getMockBuilder($class)
    ->disableOriginalConstructor()
    ->getMock();

$mock->method('methodToMock1')->willReturn('foo');
$mock->method('methodToMock2')->willReturn('bar');

$container->set($class, $mock);
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

// Load container definitions
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
]
```

## PhpStorm Integration

If you use PhpStorm, then create a new file `.phpstorm.meta.php`
in your project root directory and copy/paste the following content:

```php
<?php

namespace PHPSTORM_META;

override(\Psr\Container\ContainerInterface::get(0), map(['' => '@']));
```

## Performance Comparison

`selective/container` is about:

* 11% faster then `php-di/php-di`.
* 5.4% faster then `league/container`.

All tests where made with enabled autowiring.

## Migrating from PHP-DI

This PSR-11 container implementation mimics the behavior of PHP-DI.

If you already use [factories](https://php-di.org/doc/php-definitions.html#factories) for your container definitions,
the switch should be very simple.

Replace this:

```php
<?php
use DI\ContainerBuilder;

// ...

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions(__DIR__ . '/container.php');

$container = $containerBuilder->build();
```

... with this:

```php
<?php
use Selective\Container\Container;
use Selective\Container\Resolver\ConstructorResolver;
// ...

$container = new Container();

// Enable auto-wiring
$container->addResolver(new ConstructorResolver($container));

// Add definitions
$container->factories(require __DIR__ . '/container.php');
```

That's it.

## Credits

* Dominik Zogg (chubbyphp)

## Similar libraries

* https://github.com/chubbyphp/chubbyphp-container
* http://php-di.org/
* https://container.thephpleague.com/

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
