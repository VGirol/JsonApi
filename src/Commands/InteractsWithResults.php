<?php

namespace VGirol\JsonApi\Commands;

trait InteractsWithResults
{
    /**
     * Undocumented function
     *
     * @return array
     */
    abstract public function collectResults(): array;

    /**
     * Undocumented function
     *
     * @return string
     */
    protected function getCompleteClassName(): string
    {
        return $this->qualifyClass($this->getNameInput());
    }
}
