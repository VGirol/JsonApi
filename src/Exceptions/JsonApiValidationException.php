<?php

namespace VGirol\JsonApi\Exceptions;

use Illuminate\Validation\ValidationException;

class JsonApiValidationException extends ValidationException
{
    public function __construct($validator, $response = null, $errorBag = 'default')
    {
        parent::__construct($validator, $response, $errorBag);

        $this->status(403);
    }

    public function getStatusCode()
    {
        return $this->status;
    }

    public function prepareException()
    {
        foreach ($this->errors() as $field => $errors) {
            foreach ($errors as $key => $error) {
                $matches = [];
                if (preg_match('/\(([0-9]{3})\) (.*)/', $error, $matches) === 1) {
                    $this->status($matches[1]);
                    $this->appendToMessage($matches[2]);
                } else {
                    $this->appendToMessage($error);
                }
            }
        }
    }

    private function appendToMessage($msg)
    {
        if (!is_null($this->message)) {
            $this->message .= "\n";
        }
        $this->message .= $msg;
    }
}
