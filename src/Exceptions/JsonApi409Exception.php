<?php

namespace VGirol\JsonApi\Exceptions;

class JsonApi409Exception extends JsonApiException
{
    public $status = 409;
    public $stop = true;
}
