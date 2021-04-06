<?php

namespace VGirol\JsonApi\Tests\Feature;

use VGirol\JsonApi\Tests\UsesTools;

trait CompleteSetUp
{
    use UsesTools;

    /**
     * Setup before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTools(true);
    }
}
