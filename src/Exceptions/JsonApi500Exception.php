<?php

namespace VGirol\JsonApi\Exceptions;

class JsonApi500Exception extends JsonApiException
{
    public $status = 500;
    public $stop = true;
}
