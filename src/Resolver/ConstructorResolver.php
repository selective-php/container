<?php

declare(strict_types = 1);

namespace Selective\Container\Resolver;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use Selective\Container\Exceptions\InvalidDefinitionException;
use Throwable;

/**
 * Constructor parameter resolver.
 */
final class ConstructorResolver implements DefinitionResolverInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * The constructor.
     *
     * @param ContainerInterface $container The container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Autowire dependencies.
     *
     * @param string|class-string $id The id
     *
     * @throws InvalidDefinitionException
     *
     * @return mixed The resolved value
     */
    public function resolve(string $id)
    {
        if (!$this->isResolvable($id)) {
            throw InvalidDefinitionException::create(sprintf(
                'Entry "%s" cannot be resolved: The class doesn\'t exist',
                $id
            ));
        }

        $reflectionClass = new ReflectionClass($id);

        try {
            if ($reflectionClass->getConstructor() === null) {
                return $reflectionClass->newInstance();
            }

            return $reflectionClass->newInstanceArgs($this->resolveParameters($id, $reflectionClass->getConstructor()));
        } catch (Throwable $exception) {
            throw InvalidDefinitionException::create(sprintf(
                'Entry "%s" cannot be resolved: the class is not instantiable',
                $id
            ), $exception);
        }
    }

    /**
     * Resolve method parameters.
     *
     * @param string $id The id
     * @param ReflectionMethod $method The method
     *
     * @throws InvalidDefinitionException
     *
     * @return array<mixed> The resolved parameters
     */
    private function resolveParameters(string $id, ReflectionMethod $method = null): array
    {
        if ($method === null) {
            return [];
        }

        $arguments = [];

        foreach ($method->getParameters() as $parameter) {
            $reflectionClass = $parameter->getClass();

            if ($reflectionClass === null) {
                throw InvalidDefinitionException::create(sprintf(
                    'Parameter $%s of %s has no value defined or guessable',
                    $parameter->getName(),
                    $id
                ));
            }

            // If the parameter is optional and wasn't specified, we take its default value
            if ($parameter->isDefaultValueAvailable() || $parameter->isOptional()) {
                $arguments[] = $parameter->getDefaultValue();

                continue;
            }

            // Look in the definitions or try to create it
            $arguments[] = $this->container->get($reflectionClass->getName());
        }

        return $arguments;
    }

    /**
     * Check if a definition can be resolved.
     *
     * @param string|class-string $id The full class name
     *
     * @return bool Status
     */
    public function isResolvable(string $id): bool
    {
        return class_exists($id);
    }
}
