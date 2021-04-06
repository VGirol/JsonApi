<?php

namespace VGirol\JsonApi\Exceptions;

class JsonApi415Exception extends JsonApiException
{
    public $status = 415;
    public $stop = true;
}
