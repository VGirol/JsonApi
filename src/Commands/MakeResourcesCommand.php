<?php

namespace VGirol\JsonApi\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeResourcesCommand extends Command
{
    use InteractsWithResults;
    use FixResolveCommand;

    /**
     * Undocumented variable
     *
     * @var array
     */
    private $results = [];

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:jsonapi:resources';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new JsonApi resources';

    /**
     * {@inheritDoc}
     */
    public function collectResults(): array
    {
        return $this->results;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getOptions(): array
    {
        return [
            ['types', 't', InputOption::VALUE_REQUIRED, 'Comma separated list of the resource classes that will be created : ro, ri, roc, ric.'],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $all = ['ro', 'ri', 'roc', 'ric'];
        $types = ($this->option('types') === null) ? $all : explode(',', $this->option('types'));

        if (!empty($diff = \array_diff($types, $all))) {
            $this->warn('The following types are not allowed : "' . implode('", "', $diff) . '".');

            return 1;
        }

        foreach ($types as $type) {
            $parameters = [
                'name' => $this->getClassName($type),
                '--ro' => $this->isRO($type),
                '--ri' => $this->isRI($type),
                '--collection' => $this->isCollection($type)
            ];

            $command = $this->resolveCommand(MakeResourceCommand::class);
            $this->call($command, $parameters);

            $this->results = \array_merge(
                \call_user_func([$command, 'collectResults']),
                $this->results
            );
        }
    }

    private function getClassName(string $type): string
    {
        return $this->argument('name')
            . ($this->isRO($type) ? 'Resource' : 'ResourceIdentifier')
            . ($this->isCollection($type) ? 'Collection' : '');
    }

    private function isRO(string $type): bool
    {
        return \in_array($type, ['ro', 'roc']);
    }

    private function isRI(string $type): bool
    {
        return \in_array($type, ['ri', 'ric']);
    }

    private function isCollection(string $type): bool
    {
        return \in_array($type, ['roc', 'ric']);
    }
}
