<?php

namespace VGirol\JsonApi\Commands;

trait InteractsWithInputs
{
    /**
     * Get the value of a command option.
     *
     * @param  string|null  $key
     * @return string|array|bool|null
     */
    public function option($key = null)
    {
        if (!is_null($key) && !$this->hasOption($key)) {
            return false;
        }

        return parent::option($key);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getOptions()
    {
        return $this->removeKeys(
            \array_merge(
                $this->getAddedOptions(),
                parent::getOptions()
            ),
            $this->getHiddenOptions()
        );
    }

    /**
     * Returns the options of the parent commant that must be deactivated
     *
     * @return array
     */
    protected function getHiddenOptions(): array
    {
        return [];
    }

    /**
     * Returns the options to add to parent options
     *
     * @return array
     */
    protected function getAddedOptions(): array
    {
        return [];
    }

    /**
     * Undocumented function
     *
     * @param array $args
     * @param array $keys
     *
     * @return array
     */
    protected function removeKeys(array $args, array $keys): array
    {
        return collect($args)->filter(function ($item) use ($keys) {
            return !\in_array($item[0], $keys);
        })->toArray();
    }
}
