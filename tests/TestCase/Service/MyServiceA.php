<?php

namespace Selective\Container\Test\TestCase\Service;

/**
 * Test.
 */
class MyServiceA
{
    /**
     * The constructor.
     *
     * @param MyServiceB $service The service
     */
    public function __construct(MyServiceB $service)
    {
        $service->bar();
    }
}
