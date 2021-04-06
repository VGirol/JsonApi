<?php

namespace VGirol\JsonApi\Exceptions;

class JsonApi400Exception extends JsonApiException
{
    public $status = 400;
    public $stop = true;
}
