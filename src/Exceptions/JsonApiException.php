<?php

namespace VGirol\JsonApi\Exceptions;

use Exception;

class JsonApiException extends Exception
{

    /**
     * The status code to use for the response.
     *
     * @var int
     */
    public $status = 400;

    /**
     * Stops request lifecycle
     *
     * @var bool
     */
    public $stop = false;

    /**
     * Set the HTTP status code to be used for the response.
     *
     * @param  int  $status
     * @return $this
     */
    public function status($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Stops request lifecycle.
     *
     * @param  bool  $stop
     * @return $this
     */
    public function stop($stop)
    {
        $this->stop = $stop;

        return $this;
    }
}
