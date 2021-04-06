<?php

namespace VGirol\JsonApi\Tests\Tools\Factory;

use VGirol\JsonApiFaker\Factory\JsonapiFactory as BaseFactory;

class JsonapiFactory extends BaseFactory
{
    public function __construct()
    {
        $this->setVersion(config('jsonapi.version'));
    }
}
