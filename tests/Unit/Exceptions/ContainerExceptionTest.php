<?php

namespace Selective\Tests\Container\Unit\Exceptions;

use Exception;
use PHPUnit\Framework\TestCase;
use Selective\Container\Exceptions\ContainerException;

/**
 * @internal
 */
final class ContainerExceptionTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testCreate(): void
    {
        $previous = new Exception();

        $exception = ContainerException::create('id', $previous);

        self::assertSame('Could not create service with id "id"', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }
}
