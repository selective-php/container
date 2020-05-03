<?php

namespace Selective\Container;

/**
 * The Factory Container Interface.
 */
interface FactoryInterface
{
    /**
     * Add array of factories.
     *
     * @param array<string, callable> $factories The callables
     *
     * @return self
     */
    public function factories(array $factories): self;

    /**
     * Add a single factory.
     *
     * @param string $id The id
     * @param callable $factory The callable
     *
     * @return self
     */
    public function factory(string $id, callable $factory): self;
}
