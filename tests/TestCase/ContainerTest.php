<?php

namespace Selective\Container\Test\TestCase;

use Exception;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionProperty;
use Selective\Container\Container;
use Selective\Container\Exceptions\ContainerException;
use Selective\Container\Exceptions\InvalidDefinitionException;
use Selective\Container\Exceptions\NotFoundException;
use Selective\Container\Resolver\ConstructorResolver;
use Selective\Container\Test\TestCase\Service\MyAbstractService;
use Selective\Container\Test\TestCase\Service\MyService;
use Selective\Container\Test\TestCase\Service\MyServiceA;
use Selective\Container\Test\TestCase\Service\MyServiceInvalid;
use stdClass;

/**
 * Test.
 *
 * @internal
 */
final class ContainerTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testConstruct(): void
    {
        $container = new Container([
            'id' => static function () {
                return new stdClass();
            },
        ]);

        $reflectionFactories = new ReflectionProperty($container, 'factories');
        $reflectionFactories->setAccessible(true);

        $factories = $reflectionFactories->getValue($container);

        $this->assertCount(1, $factories);

        $factory = array_shift($factories);

        $service = $factory($container);

        $this->assertInstanceOf(stdClass::class, $service);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testFactories(): void
    {
        $container = new Container();
        $container->factories([
            'id' => static function () {
                return new stdClass();
            },
        ]);

        $reflectionFactories = new ReflectionProperty($container, 'factories');
        $reflectionFactories->setAccessible(true);

        $factories = $reflectionFactories->getValue($container);

        $this->assertCount(1, $factories);

        $factory = array_shift($factories);

        $service = $factory($container);

        $this->assertInstanceOf(stdClass::class, $service);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testFactory(): void
    {
        $container = new Container();
        $container->factory('id', static function () {
            return new stdClass();
        });

        $reflectionFactories = new ReflectionProperty($container, 'factories');
        $reflectionFactories->setAccessible(true);

        $factories = $reflectionFactories->getValue($container);

        $this->assertCount(1, $factories);

        $factory = array_shift($factories);

        $service = $factory($container);

        $this->assertInstanceOf(stdClass::class, $service);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testReplace(): void
    {
        $this->expectException(LogicException::class);
        $this->expectErrorMessage('The factory cannot be modified');

        $container = new Container();

        $container->factory('id', static function () {
        });

        $container->factory('id', static function () {
        });
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testSet(): void
    {
        $container = new Container();
        $container->set('id', 'value');
        $this->assertSame('value', $container->get('id'));

        $stdClass = new stdClass();
        $container->set(stdClass::class, $stdClass);
        $this->assertInstanceOf(stdClass::class, $container->get(stdClass::class));
        $this->assertSame($stdClass, $container->get(stdClass::class));

        $container->set(stdClass::class, new stdClass());
        $this->assertInstanceOf(stdClass::class, $container->get(stdClass::class));
        $this->assertNotSame($stdClass, $container->get(stdClass::class));
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testGet(): void
    {
        $factories = [];
        $factories['id'] = static function () {
            return new stdClass();
        };

        $container = new Container();

        $reflectionFactories = new ReflectionProperty($container, 'factories');
        $reflectionFactories->setAccessible(true);
        $reflectionFactories->setValue($container, $factories);

        $service = $container->get('id');

        $this->assertInstanceOf(stdClass::class, $service);

        $this->assertSame($service, $container->get('id'));
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testGetWithMissingId(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('There is no service with id "id"');
        $this->expectExceptionCode(0);

        $container = new Container();
        $container->get('id');
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testGetWithException(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Could not create service with id "id"');
        $this->expectExceptionCode(0);

        $factories = [];
        $factories['id'] = static function (ContainerInterface $container): void {
            $container->get('unknown');
        };

        $container = new Container();

        $reflectionFactories = new ReflectionProperty($container, 'factories');
        $reflectionFactories->setAccessible(true);
        $reflectionFactories->setValue($container, $factories);

        $service = $container->get('id');

        $this->assertInstanceOf(stdClass::class, $service);

        $this->assertSame($service, $container->get('id'));
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testHas(): void
    {
        $container = new Container();

        $this->assertFalse($container->has('id'));

        $factories = [];
        $factories['id'] = static function () {
            return new stdClass();
        };

        $reflectionFactories = new ReflectionProperty($container, 'factories');
        $reflectionFactories->setAccessible(true);
        $reflectionFactories->setValue($container, $factories);

        $this->assertTrue($container->has('id'));
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testAutowireWithClass(): void
    {
        $container = new Container();
        $container->addResolver(new ConstructorResolver($container));
        $service = $container->get(MyService::class);

        $this->assertInstanceOf(MyService::class, $service);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testAutowireWithClassA(): void
    {
        $container = new Container();
        $container->addResolver(new ConstructorResolver($container));
        $service = $container->get(MyServiceA::class);

        $this->assertInstanceOf(MyServiceA::class, $service);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testAutowireWithInvalidClasses(): void
    {
        $this->expectException(InvalidDefinitionException::class);

        $container = new Container();
        $container->addResolver(new ConstructorResolver($container));
        $container->get(MyServiceInvalid::class);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testAutowireWithInvalidInternalClass(): void
    {
        $container = new Container();
        $container->addResolver(new ConstructorResolver($container));

        // https://3v4l.org/1AXpr
        if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
            // PHP 8+
            $this->assertInstanceOf(Exception::class, $container->get(Exception::class));
        } else {
            // PHP 7.x
            // Cannot determine default value for internal functions
            $this->expectException(InvalidDefinitionException::class);
            $container->get(Exception::class);
        }
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testAutowireWithInvalidInternalInterface(): void
    {
        $container = new Container();
        $container->addResolver(new ConstructorResolver($container));

        if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
            // PHP 8+
            $this->assertInstanceOf(NotFoundException::class, $container->get(NotFoundException::class));
        } else {
            // PHP 7.x
            // Cannot determine default value for internal functions
            $this->expectException(InvalidDefinitionException::class);
            $container->get(NotFoundException::class);
        }
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testAutowireWithInvalidAbstractClass(): void
    {
        $this->expectException(InvalidDefinitionException::class);
        $this->expectErrorMessage('Entry "Selective\Container\Test\TestCase\Service\MyAbstractService" cannot ' .
            'be resolved: the class is not instantiable');

        $container = new Container();
        $container->addResolver(new ConstructorResolver($container));
        $container->get(MyAbstractService::class);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testAutowireWithNotExistingClass(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectErrorMessage('There is no service with id "Nada\Foo"');

        $container = new Container();
        $container->addResolver(new ConstructorResolver($container));
        $container->get('Nada\Foo');
    }
}
