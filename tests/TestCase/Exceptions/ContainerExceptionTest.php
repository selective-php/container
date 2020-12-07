<?php

namespace Selective\Container\Test\TestCase\Exceptions;

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

        $this->assertSame('Could not create service with id "id"', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
