<?php

namespace Selective\Container\Resolver;

use DI\Definition\Definition;

/**
 * Resolves a definition to a value.
 */
interface DefinitionResolverInterface
{
    /**
     * Resolve a definition to a value.
     *
     * @param string|class-string $id The full class name
     *
     * @return mixed the value obtained from the definition
     */
    public function resolve(string $id);

    /**
     * Check if a definition can be resolved.
     *
     * @param string|class-string $id The full class name
     *
     * @return bool Status
     */
    public function isResolvable(string $id): bool;
}
