<?php

namespace Selective\Tests\Container\Unit\Exceptions;

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

        self::assertSame('message', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
    }
}
