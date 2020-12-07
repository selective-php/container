<?php

declare(strict_types = 1);

namespace Selective\Container\Test\Resolver\TestCase;

use PHPUnit\Framework\TestCase;
use Selective\Container\Container;
use Selective\Container\Exceptions\InvalidDefinitionException;
use Selective\Container\Resolver\ConstructorResolver;

class ConstructorResolverTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testResolveOnInvalidDefinition(): void
    {
        $this->expectException(InvalidDefinitionException::class);

        $container = new Container();
        $invalidId = 'invalid_id';

        $constructorResolver = new ConstructorResolver($container);
        $constructorResolver->resolve($invalidId);
    }
}
