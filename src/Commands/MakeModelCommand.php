<?php

namespace VGirol\JsonApi\Commands;

use Illuminate\Foundation\Console\ModelMakeCommand as BaseModelMakeCommand;
use Illuminate\Support\Str;

class MakeModelCommand extends BaseModelMakeCommand
{
    use InteractsWithInputs;
    use InteractsWithResults;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:jsonapi:model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new JsonApi model class';

    /**
     * Returns the options of the parent commant that must be deactivated
     *
     * @return array
     */
    protected function getHiddenOptions(): array
    {
        return [
            'all',
            'controller',
            'resource',
            'api'
        ];
    }

    public function collectResults(): array
    {
        return ['model' => $this->getCompleteClassName()];
    }

    protected function getNameInput()
    {
        $namespace = config()->get('jsonapi.modelNamespace', null);
        $name = parent::getNameInput();
        if (Str::startsWith($name, $namespace)) {
            $name = Str::replaceFirst($namespace, '', parent::getNameInput());
        }

        return $name;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        $namespace = config()->get('jsonapi.modelNamespace', null);

        return $rootNamespace . ($namespace ? '\\' . $namespace : '');
    }
}
