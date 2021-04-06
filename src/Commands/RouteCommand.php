<?php

namespace VGirol\JsonApi\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RouteCommand extends Command
{
    use InteractsWithResults;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'jsonapi:route';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new CRUD routes to "routes/api.php" file';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new controller creator command instance.
     *
     * @param Filesystem $files
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['route', InputArgument::REQUIRED, 'The route name'],
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
            ['controller', null, InputOption::VALUE_REQUIRED, 'The controller class name (without namespace)'],
            ['relationships', 'r', InputOption::VALUE_OPTIONAL, 'Add routes for relationships and related resources'],
        ];
    }

    /**
     * Undocumented function
     *
     * @return array
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
        $fileName = 'routes/api.php';
        $path = base_path($fileName);
        if (!$this->files->exists($path)) {
            $this->files->ensureDirectoryExists(base_path('routes'));

            $startContent = $this->files->get($this->getStub());
            $this->files->put($path, $startContent);
        }

        $name = $this->argument('route');

        $content = $this->files->get($path);

        if (strpos($content, "'{$name}'") !== false) {
            $this->warn('A route with the same name allready exists.');
            $choice = $this->choice('Do you want to update or cancel ?', ['Cancel', 'Update'], 0);
            if ($choice == 'Cancel') {
                $this->info('Operation canceled.');

                return;
            }

            $content = \preg_replace(
                "/Route::jsonApiResource\(\s*'{$name}'[^;]*\);\n/s",
                $this->createContentToAdd(),
                $content
            );
        } else {
            $content .= $this->createContentToAdd();
        }

        if ($this->files->put($path, $content) === false) {
            $this->error('An error occurs while saving the new route file in "' . $fileName . '".');

            return 1;
        }

        $this->info('Routes file "' . $fileName . '" successfully updated.');
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/api_routes.stub';
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    private function createContentToAdd(): string
    {
        $name = $this->argument('route');
        $controller = $this->option('controller');
        $withRelationships = $this->option('relationships');

        $content = "Route::jsonApiResource(\n";
        $content .= str_repeat(' ', 4) . "'{$name}',\n";
        $content .= str_repeat(' ', 4) . (($controller === null) ? 'null' : "'{$controller}'");
        $content .= $withRelationships ? ",\n" : "\n";
        if ($withRelationships) {
            $content .= str_repeat(' ', 4) . "[\n";
            $content .= str_repeat(' ', 8) . "'relationships' => true\n";
            $content .= str_repeat(' ', 4) . "]\n";
        }
        $content .= ");\n";

        return $content;
    }
}
