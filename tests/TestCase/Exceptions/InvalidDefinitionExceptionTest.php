<?php

namespace Selective\Container\Test\TestCase\Exceptions;

use PHPUnit\Framework\TestCase;
use Selective\Container\Exceptions\InvalidDefinitionException;

/**
 * Test.
 *
 * @internal
 */
final class InvalidDefinitionExceptionTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testCreate(): void
    {
        $exception = InvalidDefinitionException::create('message');

        $this->assertSame('message', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }
}
