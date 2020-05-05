<?php

declare(strict_types = 1);

namespace Selective\Container;

use LogicException;
use Psr\Container\ContainerInterface;
use Selective\Container\Exceptions\ContainerException;
use Selective\Container\Exceptions\NotFoundException;
use Selective\Container\Resolver\DefinitionResolverInterface;
use Throwable;

/**
 * The container with factories.
 */
final class Container implements ContainerInterface, FactoryInterface
{
    /**
     * @var array<string, callable>
     */
    private $factories = [];

    /**
     * @var array<string, mixed>
     */
    private $services = [];

    /**
     * @var DefinitionResolverInterface[]
     */
    private $resolvers = [];

    /**
     * The constructor.
     *
     * @param array<string, callable> $factories The factory definitons
     */
    public function __construct(array $factories = [])
    {
        $this->factories($factories);
    }

    /**
     * Add factory definitons.
     *
     * @param array<string, callable> $factories The callables
     *
     * @return $this|FactoryInterface The container
     */
    public function factories(array $factories): FactoryInterface
    {
        foreach ($factories as $id => $factory) {
            $this->factory($id, $factory);
        }

        return $this;
    }

    /**
     * Add factory definiton.
     *
     * @param string $id The container id
     * @param callable $factory The callable
     *
     * @throws LogicException If the factory is locked
     *
     * @return self|FactoryInterface The container
     */
    public function factory(string $id, callable $factory): FactoryInterface
    {
        if (isset($this->factories[$id])) {
            throw new LogicException('The factory cannot be modified');
        }

        $this->factories[$id] = $factory;

        return $this;
    }

    /**
     * Define an object or a value in the container.
     *
     * @param string $id The identifier
     * @param mixed $value The value
     *
     * @return void
     */
    public function set(string $id, $value): void
    {
        $this->services[$id] = $value;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id The identifier of the entry to look for
     *
     * @return mixed The entry
     */
    public function get($id)
    {
        return $this->services[$id] ?? $this->services[$id] = $this->create($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Tentifier of the entry to look for
     *
     * @return bool The status
     */
    public function has($id): bool
    {
        return isset($this->factories[$id]) || $this->isResolvable($id);
    }

    /**
     * Create instance.
     *
     * @param string $id The id
     *
     * @return mixed The value
     */
    private function create(string $id)
    {
        try {
            if (isset($this->factories[$id])) {
                return ($this->factories[$id])($this);
            }
        } catch (Throwable $throwable) {
            throw ContainerException::create($id, $throwable);
        }

        if (class_exists($id, true)) {
            return $this->autowire($id);
        }

        throw NotFoundException::create($id);
    }

    /**
     * Add resolver.
     *
     * @param DefinitionResolverInterface $resolver The resolver
     *
     * @return void
     */
    public function addResolver(DefinitionResolverInterface $resolver): void
    {
        $this->resolvers[] = $resolver;
    }

    /**
     * Check if a definition can be resolved.
     *
     * @param string|class-string $id The full class name
     *
     * @return bool Status
     */
    private function isResolvable(string $id): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->isResolvable($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Autowire dependencies.
     *
     * @param string $id The id
     *
     * @return mixed|null The value obtained from the definition
     */
    private function autowire(string $id)
    {
        foreach ($this->resolvers as $resolver) {
            $value = $resolver->resolve($id);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }
}
