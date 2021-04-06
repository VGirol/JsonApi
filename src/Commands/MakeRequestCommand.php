<?php

namespace VGirol\JsonApi\Commands;

use Illuminate\Foundation\Console\RequestMakeCommand as BaseRequestMakeCommand;

class MakeRequestCommand extends BaseRequestMakeCommand
{
    use InteractsWithResults;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:jsonapi:request';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new JsonApi form request class';

    /**
     * {@inheritDoc}
     */
    public function collectResults(): array
    {
        return ['request' => $this->getCompleteClassName()];
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/request.stub';
    }
}
