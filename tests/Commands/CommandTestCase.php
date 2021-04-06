<?php

namespace VGirol\JsonApi\Tests\Commands;

use Illuminate\Support\Facades\Storage;
use VGirol\JsonApi\Tests\TestCase;

abstract class CommandTestCase extends TestCase
{
    /**
     * Undocumented variable
     *
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $disk;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('filesystems.disks.orchestra', [
            'driver' => 'local',
            'root' => base_path()
        ]);
        $app['config']->set('filesystems.default', 'orchestra');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->disk = Storage::disk();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->disk->delete('config/jsonapi.php');
        $this->disk->delete('config/jsonapi-alias.php');
        $this->disk->delete($this->disk->allFiles('database/factories'));
        $this->disk->delete($this->disk->allFiles('database/migrations'));
        $this->disk->delete($this->disk->allFiles('database/seeds'));
        $this->disk->deleteDirectory('routes');
        foreach ($this->disk->directories('app') as $dir) {
            $this->disk->deleteDirectory($dir);
        }
    }

    protected function assertDiskHas(string $path)
    {
        $files = $this->disk->allFiles(substr($path, 0, strrpos($path, '/')));
        $message = "The file \"{$path}\" does not exist.\n";
        $message .= empty($files) ? "No files found." : count($files) . " files found :" . implode("\n - ", $files);

        $this->assertTrue($this->disk->has($path), $message);
    }

    protected function assertControllerFileContains(string $expectedPath, string $expectedNamespace, string $expectedClassName)
    {
        $content = $this->disk->get($expectedPath);

        $this->assertStringContainsString("namespace {$expectedNamespace};", $content);
        $this->assertStringContainsString("class {$expectedClassName} extends BaseController", $content);
        $this->assertStringContainsString('use JsonApiRestFul;', $content);
    }

    protected function assertFormRequestFileContains(string $expectedPath, string $expectedNamespace, string $expectedClassName)
    {
        $content = $this->disk->get($expectedPath);

        $this->assertStringContainsString("namespace {$expectedNamespace};", $content);
        $this->assertStringContainsString('use VGirol\\JsonApi\\Requests\\ResourceFormRequest', $content);
        $this->assertStringContainsString("class {$expectedClassName} extends ResourceFormRequest", $content);
    }

    protected function assertResourceFileContains(string $expectedPath, string $expectedNamespace, string $expectedClassName, string $expectedBaseClass)
    {
        $content = $this->disk->get($expectedPath);

        $this->assertStringContainsString("namespace {$expectedNamespace};", $content);
        $this->assertStringContainsString("use VGirol\\JsonApi\\Resources\\{$expectedBaseClass}", $content);
        $this->assertStringContainsString("class {$expectedClassName} extends {$expectedBaseClass}", $content);
    }

    protected function assertAliasConfigFileContains(string $expectedPath, array $expectedConfig)
    {
        $content = $this->disk->get($expectedPath);

        $expectedComment = <<<EOT
        /**
         * Each group can have the following keys :
         *  - type (string) : the resource type
         *  - route (string) : the route name
         *  - model (string) : the model class name (with namespace)
         *  - request (string) : the form request class name (with namespace)
         *  - controller (string) : the controller class name (with namespace)
         *  - resource-ro (string) : the resource class name (with namespace)
         *  - resource-roc (string) : the resource collection class name (with namespace)
         *  - resource-ri (string) : the resource identifier class name (with namespace)
         *  - resource-ric (string) : the resource identifier collection class name (with namespace)
         *  - relationships (array) : an associative array with each key is a relationship name (string) and each value is the resource type corresponding (string)
         */
        EOT;

        $this->assertStringContainsString($expectedComment, $content);

        $config = eval(str_replace('<?php', '', $content));
        $this->assertEquals($expectedConfig, $config);

        foreach ($config['groups'] as $group) {
            $this->assertEquals($group['type'], jsonapiAliases()->getResourceType($group['route']));
        }
    }

    protected function assertRoutesFileContains(string $expectedPath, string $expectedContent)
    {
        $this->assertDiskHas($expectedPath);

        $content = $this->disk->get($expectedPath);

        $this->assertStringContainsString($expectedContent, $content);
    }
}
