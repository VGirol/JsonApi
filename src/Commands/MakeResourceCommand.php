<?php

namespace VGirol\JsonApi\Commands;

use Illuminate\Foundation\Console\ResourceMakeCommand as BaseResourceMakeCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeResourceCommand extends BaseResourceMakeCommand
{
    use InteractsWithInputs;
    use InteractsWithResults;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:jsonapi:resource';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new JsonApi resource';

    /**
     * {@inheritDoc}
     */
    public function collectResults(): array
    {
        $key = 'resource' . ($this->isRo() ? '-ro' : '-ri') . ($this->isCollection() ? 'c' : '');

        return [$key => $this->getCompleteClassName()];
    }

    /**
     * {@inheritDoc}
     */
    protected function getAddedOptions(): array
    {
        return [
            ['ro', null, InputOption::VALUE_NONE, 'Create a resource object class'],
            ['ri', null, InputOption::VALUE_NONE, 'Create a resource identifier class'],
        ];
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/resource.stub';
    }

    /**
     * {@inheritDoc}
     */
    protected function buildClass($name)
    {
        return $this->replaceBaseClass(parent::buildClass($name));
    }

    /**
     * Replace the base class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceBaseClass($stub)
    {
        $class = 'Resource' . ($this->isRo() ? 'Object' : 'Identifier') . ($this->isCollection() ? 'Collection' : '');

        return str_replace(['{{ baseClass }}', '{{baseClass}}'], $class, $stub);
    }

    private function isRo(): bool
    {
        return $this->option('ro') || !$this->option('ri');
    }

    private function isCollection(): bool
    {
        return $this->option('collection');
    }
}
