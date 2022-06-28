<?php

namespace Selective\Container\Resolver;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
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
            throw InvalidDefinitionException::create(
                sprintf(
                    'Entry "%s" cannot be resolved: The class doesn\'t exist',
                    $id
                )
            );
        }

        $reflectionClass = new ReflectionClass($id);

        try {
            $constructor = $reflectionClass->getConstructor();
            if ($constructor === null) {
                return $reflectionClass->newInstance();
            }

            return $reflectionClass->newInstanceArgs($this->resolveParameters($id, $constructor));
        } catch (Throwable $exception) {
            throw InvalidDefinitionException::create(
                sprintf(
                    'Entry "%s" cannot be resolved: the class is not instantiable',
                    $id
                ),
                $exception
            );
        }
    }

    /**
     * Resolve method parameters.
     *
     * @param string $id The id
     * @param ReflectionMethod|null $method The method
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
            $arguments[] = $this->resolveParameter($id, $parameter);
        }

        return $arguments;
    }

    /**
     * Resolve paramameter value.
     *
     * @param string $id The id
     * @param ReflectionParameter $parameter The parameter
     *
     * @throws InvalidDefinitionException
     *
     * @return mixed The value
     */
    private function resolveParameter(string $id, ReflectionParameter $parameter)
    {
        $className = $this->getClassName($parameter);

        // Look in the definitions or try to create it
        if ($className !== null && $this->container->has($className)) {
            return $this->container->get($className);
        }

        // If the parameter is optional and wasn't specified, we take its default value
        if ($parameter->isDefaultValueAvailable() || $parameter->isOptional()) {
            return $parameter->getDefaultValue();
        }

        throw InvalidDefinitionException::create(
            sprintf(
                'Parameter $%s of %s has no value defined or guessable',
                $parameter->getName(),
                $id
            )
        );
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

    /**
     * Get class name.
     *
     * @param ReflectionParameter $parameter The parameter
     *
     * @return string|null The class name
     */
    private function getClassName(ReflectionParameter $parameter): ?string
    {
        $reflectionType = $parameter->getType();

        if ($reflectionType instanceof ReflectionNamedType && !$reflectionType->isBuiltin()) {
            return $reflectionType->getName();
        }

        return null;
    }
}
