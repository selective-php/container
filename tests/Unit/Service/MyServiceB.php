<?php

namespace Selective\Tests\Container\Unit\Service;

/**
 * Service.
 */
class MyServiceB
{
    /**
     * The constructor.
     *
     * @param MyService $service The service
     */
    public function __construct(MyService $service)
    {
        $service->foo();
    }

    /**
     * Foo.
     *
     * @return void
     */
    public function bar(): void
    {
    }
}
