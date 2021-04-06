<?php

namespace VGirol\JsonApi\Exceptions;

class JsonApi403Exception extends JsonApiException
{
    public $status = 403;
    public $stop = true;
}
