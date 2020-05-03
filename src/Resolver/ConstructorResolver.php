<?php

declare(strict_types = 1);

namespace Selective\Container\Resolver;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use Selective\Container\Exceptions\InvalidDefinitionException;
use Selective\Container\Exceptions\NotFoundException;

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
        if (!class_exists($id, true)) {
            throw NotFoundException::create(sprintf('The class %s does not exists', $id));
        }

        $reflectionClass = new ReflectionClass($id);

        if ($reflectionClass->isInterface() ||
            $reflectionClass->isAbstract() ||
            $reflectionClass->isTrait() ||
            $reflectionClass->isAnonymous()
        ) {
            throw InvalidDefinitionException::create(sprintf('The definition %s is not guessable', $id));
        }

        if ($reflectionClass->getConstructor() === null) {
            return $reflectionClass->newInstance();
        }

        $constructorArguments = $this->resolveParameters($id, $reflectionClass->getConstructor());

        return $reflectionClass->newInstanceArgs($constructorArguments);
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
}
