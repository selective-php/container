<?php

namespace Selective\Container\Test\TestCase\Exceptions;

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

        $this->assertSame('There is no service with id "id"', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }
}
