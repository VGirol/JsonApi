<?php

namespace VGirol\JsonApi\Exceptions;

use VGirol\JsonApi\Messages\Messages;

class JsonApi404Exception extends JsonApiException
{
    public $status = 404;
    public $stop = true;

    public function prepareException()
    {
        $matches = [];
        if ($this->message == '') {
            $this->message = Messages::BAD_ENDPOINT;
        } elseif (preg_match('/No query results for model \[.*\] (\d+)/', $this->message, $matches) === 1) {
            $this->message = sprintf(Messages::FETCHING_REQUEST_NOT_FOUND, $matches[1]);
        }
    }
}
