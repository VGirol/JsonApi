<?php

namespace VGirol\JsonApi\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class AliasCommand extends Command
{
    use InteractsWithResults;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'jsonapi:alias';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new group to the "jsonapi-alias.php" config file';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['type', InputArgument::REQUIRED, 'The resource type'],
            ['route', InputArgument::REQUIRED, 'The route name']
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['api-model', null, InputOption::VALUE_REQUIRED, 'The model class name (with namespace)'],
            ['api-request', null, InputOption::VALUE_REQUIRED, 'The form request class name (with namespace)'],
            ['api-controller', null, InputOption::VALUE_REQUIRED, 'The controller class name (with namespace)'],
            ['api-resource-ro', null, InputOption::VALUE_REQUIRED, 'The resource object class name (with namespace)'],
            ['api-resource-roc', null, InputOption::VALUE_REQUIRED, 'The resource object collection class name (with namespace)'],
            ['api-resource-ri', null, InputOption::VALUE_REQUIRED, 'The resource identifier class name (with namespace)'],
            ['api-resource-ric', null, InputOption::VALUE_REQUIRED, 'The resource identifier collection class name (with namespace)'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function collectResults(): array
    {
        return [];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = config_path('jsonapi-alias.php');

        $config = file_exists($path) ? include $path : ['groups' => []];

        $alias = [
            'type' => $this->argument('type'),
            'route' => $this->argument('route')
        ];
        if ($this->option('api-model')) {
            $alias['model'] = $this->option('api-model');
        }
        if ($this->option('api-request')) {
            $alias['request'] = $this->option('api-request');
        }
        if ($this->option('api-controller')) {
            $alias['controller'] = $this->option('api-controller');
        }
        if ($this->option('api-resource-ro')) {
            $alias['resource'] = $this->option('api-resource-ro');
        }
        if ($this->option('api-resource-roc')) {
            $alias['resource-collection'] = $this->option('api-resource-roc');
        }
        if ($this->option('api-resource-ri')) {
            $alias['resource-identifier'] = $this->option('api-resource-ri');
        }
        if ($this->option('api-resource-ric')) {
            $alias['resource-identifier-collection'] = $this->option('api-resource-ric');
        }

        $index = $this->isPresent($config['groups'], $alias);
        if ($index !== false) {
            $this->warn('One of these keys is allready present in config file.');
            $choice = $this->choice('Do you want to update or cancel ?', ['Cancel', 'Update'], null);
            if ($choice == 'Cancel') {
                $this->info('Operation canceled.');

                return;
            }

            $config['groups'][$index] = $alias;
        } else {
            array_push($config['groups'], $alias);
        }

        $content = file_get_contents(realpath(__DIR__ . '/../config/jsonapi-alias.php'));
        $content = preg_replace('/\[.*\]/s', $this->exportArray($config), $content);

        if (file_put_contents($path, $content) === false) {
            $this->error('An error occurs while saving the new config in "jsonapi-alias.php".');

            return 1;
        }

        app('config')->set('jsonapi-alias', $config);
        jsonapiAliases()->init();

        $this->info('Config file "jsonapi-alias.php" successfully updated.');
    }

    private function isPresent(array $list, array $alias)
    {
        foreach ($list as $index => $array) {
            foreach ($alias as $key => $value) {
                if (isset($array[$key]) && ($array[$key] == $value)) {
                    return $index;
                }
            }
        }

        return false;
    }

    private function exportArray(array $array, $indent = 0): string
    {
        $export = '';
        $indent++;
        foreach ($array as $key => $value) {
            if ($value === null) {
                continue;
            }

            if ($export !== '') {
                $export .= ",\n";
            }
            $export .= str_repeat(' ', $indent * 4);
            if (Arr::isAssoc($array)) {
                $export .= var_export($key, true) . ' => ';
            }
            if (is_array($value)) {
                $export .= $this->exportArray($value, $indent);
            } else {
                $export .= var_export($value, true);
            }
        }
        $indent--;
        $export = "[\n{$export}\n" . str_repeat(' ', $indent * 4) . "]";

        return $export;
    }
}
