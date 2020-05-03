<?php

namespace Selective\Tests\Container\Unit\Service;

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
