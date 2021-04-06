<?php

namespace VGirol\JsonApi\Services;

use Illuminate\Support\Collection;
use Throwable;
use VGirol\JsonApi\Exceptions\JsonApiException;

class ExceptionService
{
    /**
     * Collection of exceptions
     *
     * @var Collection
     */
    private $errors;

    public function __construct()
    {
        $this->errors = collect([]);
    }

    /**
     * Undocumented function
     *
     * @return Collection
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Undocumented function
     *
     * @return int
     */
    public function count(): int
    {
        return $this->errors->count();
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function hasErrors(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Undocumented function
     *
     * @param Throwable $e
     * @param boolean $check
     *
     * @return $this
     */
    public function addException(Throwable $e, $check = true)
    {
        $this->errors->push($e);
        if ($check) {
            $this->check($e);
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param integer $status
     * @param string $message
     * @param boolean $stop
     * @param boolean $check
     *
     * @return $this
     */
    public function add(int $status, string $message, $stop = false, $check = true)
    {
        $className = "VGirol\\JsonApi\\Exceptions\\JsonApi{$status}Exception";
        if (class_exists($className)) {
            $e = new $className($message);
        } else {
            $e = new JsonApiException($message);
            $e->status($status)
                ->stop($stop);
        }
        return $this->addException($e, $check);
    }

    /**
     * Undocumented function
     *
     * @param Throwable $e
     *
     * @return void
     */
    protected function check(Throwable $e)
    {
        if (config('jsonapi.stopAtFirstError') || (is_a($e, JsonApiException::class) && $e->stop)) {
            throw $e;
        }
    }
}
