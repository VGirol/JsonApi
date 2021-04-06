<?php

namespace VGirol\JsonApi\Commands;

use Illuminate\Routing\Console\ControllerMakeCommand as BaseControllerMakeCommand;

class MakeControllerCommand extends BaseControllerMakeCommand
{
    use InteractsWithInputs;
    use InteractsWithResults;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:jsonapi:controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new JsonApi controller class';

    /**
     * Returns the options of the parent commant that must be deactivated
     *
     * @return array
     */
    protected function getHiddenOptions(): array
    {
        return [
            'api',
            'invokable',
            'model',
            'parent',
            'resource'
        ];
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/controller.stub';
    }

    public function collectResults(): array
    {
        return ['controller' => $this->getCompleteClassName()];
    }
}
