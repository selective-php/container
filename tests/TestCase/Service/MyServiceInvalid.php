<?php

namespace Selective\Container\Test\TestCase\Service;

/**
 * Service.
 */
class MyServiceInvalid
{
    /**
     * The constructor.
     *
     * @param array<mixed> $settings The settings
     */
    public function __construct(array $settings)
    {
        $settings = [];
    }
}
