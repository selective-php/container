<?php

namespace Selective\Container\Test\Resolver\TestCase;

use PHPUnit\Framework\TestCase;
use Selective\Container\Container;
use Selective\Container\Exceptions\InvalidDefinitionException;
use Selective\Container\Resolver\ConstructorResolver;

final class ConstructorResolverTest extends TestCase
{
    public function testResolveOnInvalidDefinition(): void
    {
        $this->expectException(InvalidDefinitionException::class);

        $container = new Container();
        $invalidId = 'invalid_id';

        $constructorResolver = new ConstructorResolver($container);
        $constructorResolver->resolve($invalidId);
    }
}
