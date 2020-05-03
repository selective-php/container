<?php

namespace Selective\Container\Exceptions;

use Exception;
use Psr\Container\ContainerExceptionInterface;

/**
 * Invalid definition.
 */
class InvalidDefinitionException extends Exception implements ContainerExceptionInterface
{
    /**
     * Create exception.
     *
     * @param string $message The error message
     * @param Exception|null $previous The previouse exception
     *
     * @return self
     */
    public static function create(string $message, Exception $previous = null): self
    {
        return new self($message, 0, $previous);
    }
}
