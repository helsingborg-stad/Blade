<?php

namespace HelsingborgStad\Tests\IntegrationTests;

use DateTime;
use HelsingborgStad\GlobalBladeEngine;
use HelsingborgStad\Services\BladeService\BladeServiceInstance;
use PHPUnit\Framework\Attributes\BackupStaticProperties;
use PHPUnit\Framework\Attributes\TestDox;

#[BackupStaticProperties(true)]
class RegisterComponentTest extends \PHPUnit\Framework\TestCase
{
    private BladeServiceInstance $blade;

    public function setUp(): void
    {
        $views     = ['tests/phpunit/tests/IntegrationTests/views'];
        $cachePath = 'tests/phpunit/tests/IntegrationTests/cache';
        $this->clearCache($cachePath);
        $this->blade = GlobalBladeEngine::getInstance($views, $cachePath);
    }

    private function clearCache(string $cachePath)
    {
        $files = glob($cachePath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    #[TestDox('A component can be rendered')]
    public function testBasicComponent()
    {
        $output = $this->blade->make('basic')->render();
        $this->assertEquals('Hello World!', trim($output));
    }

    #[TestDox('Variables can be passed to a component')]
    public function testVariables()
    {
        $output = $this->blade->make('variables', ['name' => 'John Doe'])->render();
        $this->assertEquals('Hello John Doe!', trim($output));
    }

    #[TestDox('Directive can be registered')]
    public function testDirective()
    {
        $this->blade->directive('datetime', function ($expression) {
            return "<?php echo with({$expression})->format('F d, Y'); ?>";
        });

        $output = $this->blade->make('directive', ['birthday' => new DateTime('1983-09-28')])->render();
        $this->assertEquals('Your birthday is September 28, 1983', trim($output));
    }

    #[TestDox('A component can be registered with a composer')]
    public function testComponentComposer()
    {
        $this->blade->composer('variables', function ($view) {
            $view->with('name', 'John Doe');
        });

        $output = $this->blade->make('variables')->render();
        $this->assertEquals('Hello John Doe!', trim($output));
    }

    #[TestDox('A component can be registered with an alias directive')]
    public function testComponentAlias()
    {
        $this->blade->component('alias.source', 'sayHello');

        $this->blade->composer('alias.source', function ($view) {
            $view->with('name', 'World');
        });

        $output = $this->blade->make('alias.usage')->render();
        $this->assertEquals('Hello World!', trim($output));
    }

    #[TestDox('A view path can be added')]
    public function testAddViewPath()
    {
        $this->blade->addViewPath('tests/phpunit/tests/IntegrationTests/extra-views');

        $output = $this->blade->make('extra')->render();
        $this->assertEquals('Hello Extra!', trim($output));
    }
}
