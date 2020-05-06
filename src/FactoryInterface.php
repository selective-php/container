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
     * @return void
     */
    public function factories(array $factories): void;

    /**
     * Add a single factory.
     *
     * @param string $id The id
     * @param callable $factory The callable
     *
     * @return void
     */
    public function factory(string $id, callable $factory): void;
}
