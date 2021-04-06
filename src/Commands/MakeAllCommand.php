<?php

namespace VGirol\JsonApi\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeAllCommand extends Command
{
    use FixResolveCommand;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'make:jsonapi:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description =
    'Creates all the files associated to a JsonApi model (model, form request, resource, controller) ' .
    'and updates routes and configuration.';

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected $data = [];

    /**
     * Undocumented variable
     *
     * @var array
     */
    private $usedCommands = [];

    /**
     * Create a new console command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->registerCommands([
            'model' => MakeModelCommand::class,
            'request' => MakeRequestCommand::class,
            'resources' => MakeResourcesCommand::class,
            'controller' => MakeControllerCommand::class,
            'alias' => AliasCommand::class,
            'routes' => RouteCommand::class
        ]);

        parent::__construct();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return $this->collectArguments(
            [
                ['name', InputArgument::REQUIRED, 'The base name of all generated classes (without namespace)']
            ]
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->collectOptions(
            [
                ['jsonapi-request', null, InputOption::VALUE_NONE, 'Generate a form request class for JSON:API'],
                ['jsonapi-controller', null, InputOption::VALUE_NONE, 'Generate a controller class for JSON:API'],
                ['jsonapi-resources', null, InputOption::VALUE_NONE, 'Generate a resource class for JSON:API'],
            ],
            [
                'api-model',
                'api-request',
                'api-controller',
                'api-resource-ro',
                'api-resource-roc',
                'api-resource-ri',
                'api-resource-ric',
                'controller'
            ]
        );
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach ($this->usedCommands as $type => $commandName) {
            $method = 'call' . ucfirst(strtolower($type));

            if (!method_exists($this, $method)) {
                $method = 'callDefault';
            }

            call_user_func_array(
                [$this, $method],
                [ $commandName, $type]
            );
        }
    }

    /**
     * Undocumented function
     *
     * @param string $commandName
     * @param string $commandType
     *
     * @return void
     */
    protected function callDefault(string $commandName, string $commandType)
    {
        if ($this->hasOption("jsonapi-{$commandType}") && ($this->option("jsonapi-{$commandType}") === false)) {
            return;
        }

        $this->executeCommand(
            $commandName,
            $this->extractParametersForCommand(
                $commandName,
                function ($value, $key) use ($commandType) {
                    if ($key === 'name') {
                        switch ($commandType) {
                            case 'request':
                                $value .= 'FormRequest';
                                break;
                            case 'controller':
                                $value .= 'Controller';
                                break;
                        }
                    }

                    return $value;
                }
            )
        );
    }

    /**
     * Undocumented function
     *
     * @param string $commandName
     *
     * @return void
     */
    protected function callAlias(string $commandName)
    {
        $parameters = $this->extractParametersForCommand($commandName);
        if (array_key_exists('model', $this->data)) {
            $parameters->put('--api-model', $this->data['model']);
        }
        if (array_key_exists('request', $this->data)) {
            $parameters->put('--api-request', $this->data['request']);
        }
        if (array_key_exists('controller', $this->data)) {
            $parameters->put('--api-controller', $this->data['controller']);
        }
        foreach (['ro', 'ri', 'roc', 'ric'] as $resType) {
            if (array_key_exists("resource-{$resType}", $this->data)) {
                $parameters->put("--api-resource-{$resType}", $this->data["resource-{$resType}"]);
            }
        }

        $this->executeCommand($commandName, $parameters);
    }

    /**
     * Undocumented function
     *
     * @param string $commandName
     *
     * @return void
     */
    protected function callRoutes(string $commandName)
    {
        $parameters = $this->extractParametersForCommand($commandName);
        if (array_key_exists('controller', $this->data)) {
            $parameters->put(
                '--controller',
                str_replace('\\', '\\\\', str_replace('App\\Http\\Controllers\\', '', $this->data['controller']))
            );
        }

        $this->executeCommand($commandName, $parameters);
    }

    /**
     * Undocumented function
     *
     * @param array $commands
     *
     * @return $this
     */
    protected function registerCommands(array $commands)
    {
        $this->usedCommands = \array_merge($this->usedCommands, $commands);

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param array $prepend
     * @param array $excluded
     *
     * @return array
     */
    protected function collectArguments(array $prepend, array $excluded = []): array
    {
        return $this->collectParameters('argument', $prepend, $excluded);
    }

    /**
     * Undocumented function
     *
     * @param array $prepend
     * @param array $excluded
     *
     * @return array
     */
    protected function collectOptions(array $prepend, array $excluded = []): array
    {
        return $this->collectParameters('option', $prepend, $excluded);
    }

    /**
     * Undocumented function
     *
     * @param string     $commandName
     * @param Collection $parameters
     *
     * @return void
     */
    protected function executeCommand(string $commandName, $parameters)
    {
        $command = $this->resolveCommand($commandName);
        $this->call($command, $parameters->toArray());

        $this->data = \array_merge(
            \call_user_func([$command, 'collectResults']),
            $this->data
        );
    }

    /**
     * Undocumented function
     *
     * @param string $type
     * @param array  $prependParameters
     * @param array  $excludedParameters
     *
     * @return array
     */
    private function collectParameters(
        string $type,
        array $prependParameters,
        array $excludedParameters = []
    ): array {
        $methodName = 'get' . ucfirst($type) . 's';
        $createName = 'createInput' . ucfirst($type);

        return collect($prependParameters)
            ->map(
                /**
                 * @param array $item
                 */
                function ($item) use ($createName) {
                    return call_user_func_array([$this, $createName], $item);
                }
            )
            ->merge(
                collect($this->usedCommands)
                    ->filter()
                    ->values()
                    ->flatMap(
                        /**
                         * @param string $commandName
                         */
                        function ($commandName) use ($methodName) {
                            return $this->getCommandParameters($commandName, $methodName);
                        }
                    )
            )->unique(
                /**
                 * @param InputArgument|InputOption $item
                 */
                function ($item) {
                    return $item->getName();
                }
            )->filter(
                /**
                 * @param InputArgument|InputOption $item
                 */
                function ($item) use ($excludedParameters) {
                    return !\in_array($item->getName(), $excludedParameters);
                }
            )->values()
            ->toArray();
    }

    /**
     * Undocumented function
     *
     * @param string $commandName
     * @param string $methodName
     *
     * @return array
     */
    private function getCommandParameters(string $commandName, string $methodName): array
    {
        return call_user_func([resolve($commandName)->getDefinition(), $methodName]);
    }

    /**
     * Undocumented function
     *
     * @param string  $name
     * @param integer $mode
     * @param string  $description
     * @param mixed   $default
     *
     * @return InputArgument
     */
    private function createInputArgument(string $name, int $mode = null, string $description = '', $default = null)
    {
        return new InputArgument($name, $mode, $description, $default);
    }

    /**
     * Undocumented function
     *
     * @param string  $name
     * @param string  $shortcut
     * @param integer $mode
     * @param string  $description
     * @param mixed   $default
     *
     * @return InputOption
     */
    private function createInputOption(string $name, string $shortcut = null, int $mode = null, string $description = '', $default = null)
    {
        return new InputOption($name, $shortcut, $mode, $description, $default);
    }

    /**
     * Undocumented function
     *
     * @param string $commandName
     *
     * @return Collection
     */
    private function extractParametersForCommand(string $commandName, callable $callback = null): Collection
    {
        $parameters = $this->extractArguments($this->getCommandParameters($commandName, 'getArguments'))
            ->merge($this->extractOptions($this->getCommandParameters($commandName, 'getOptions')));

        return ($callback === null) ? $parameters : $parameters->map($callback);
    }

    /**
     * Undocumented function
     *
     * @param array $allowed
     *
     * @return Collection
     */
    private function extractArguments(array $allowed): Collection
    {
        return collect($this->arguments())
            ->filter(function ($value, $key) use ($allowed) {
                return in_array(
                    $key,
                    collect($allowed)->map(
                        function ($obj) {
                            return $obj->getName();
                        }
                    )->toArray()
                );
            })
            ->map(
                function ($value, $key) {
                    if ($key === 'name') {
                        $namespaces = [
                            $this->laravel->getNamespace(),
                            config()->get('jsonapi.modelNamespace', null) . '\\'
                        ];
                        foreach ($namespaces as $namespace) {
                            if (Str::startsWith($value, $namespace)) {
                                $value = Str::replaceFirst($namespace, '', $value);
                            }
                        }
                    }

                    return $value;
                }
            );
    }

    /**
     * Undocumented function
     *
     * @param array $allowed
     *
     * @return Collection
     */
    private function extractOptions(array $allowed = null): Collection
    {
        return collect($this->options())
            ->filter(function ($value, $key) use ($allowed) {
                return in_array(
                    $key,
                    collect($allowed)->map(
                        function ($obj) {
                            return $obj->getName();
                        }
                    )->toArray()
                );
            })
            ->mapWithKeys(function ($item, $key) {
                return ["--{$key}" => $item];
            });
    }
}
