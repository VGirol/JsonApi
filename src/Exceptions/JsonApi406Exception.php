<?php

namespace VGirol\JsonApi\Exceptions;

class JsonApi406Exception extends JsonApiException
{
    public $status = 406;
    public $stop = true;
}
