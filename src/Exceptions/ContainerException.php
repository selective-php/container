<?php

namespace Selective\Container\Exceptions;

use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Throwable;

/**
 * Generic container exception.
 */
final class ContainerException extends LogicException implements ContainerExceptionInterface
{
    /**
     * Create exception instance.
     *
     * @param string $id The id
     * @param Throwable $previous The previous exception
     *
     * @return self
     */
    public static function create(string $id, Throwable $previous): self
    {
        return new self(sprintf('Could not create service with id "%s"', $id), 0, $previous);
    }
}
