<?php

namespace Selective\Tests\Container\Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use Selective\Container\Exceptions\NotFoundException;

/**
 * Test.
 *
 * @internal
 */
final class NotFoundExceptionTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testCreate(): void
    {
        $exception = NotFoundException::create('id');

        self::assertSame('There is no service with id "id"', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
    }
}
