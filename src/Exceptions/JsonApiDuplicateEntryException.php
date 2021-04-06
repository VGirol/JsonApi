<?php

namespace VGirol\JsonApi\Exceptions;

class JsonApiDuplicateEntryException extends JsonApi409Exception
{
    public const MESSAGE = 'test';

    public function __construct($message = '', $code = 0, $previous = null)
    {
        $exceptionMessage = static::MESSAGE;
        if ($message != '') {
            $exceptionMessage . "\n" . $message;
        }
        parent::__construct($exceptionMessage, $code, $previous);
    }
}
