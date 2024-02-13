<?php

namespace HelsingborgStad\Tests\BladeService;

use DateTime;
use HelsingborgStad\BladeService\BladeService;
use HelsingborgStad\BladeService\BladeServiceInterface;
use Illuminate\Container\Container;
use PHPUnit\Framework\Attributes\TestDox;

class BladeServiceTest extends \PHPUnit\Framework\TestCase
{
    private BladeServiceInterface $bladeService;

    public function setUp(): void
    {
        $views     = ['tests/phpunit/tests/BladeService/views'];
        $cachePath = 'tests/phpunit/tests/BladeService/cache';
        $container = new Container();

        $this->clearCache($cachePath);
        $this->bladeService = new BladeService($views, $cachePath, $container);
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

    /**
     * @testdox A view can be rendered
     */
    public function testBasicComponent()
    {
        $output = $this->bladeService->makeView('basic')->render();
        $this->assertEquals('Hello World!', trim($output));
    }

    /**
     * @testdox Variables can be passed to a component
     */
    public function testVariables()
    {
        $output = $this->bladeService->makeView('variables', ['name' => 'John Doe'])->render();
        $this->assertEquals('Hello John Doe!', trim($output));
    }

    /**
     * @testdox Directive can be registered
     */
    public function testDirective()
    {
        $this->bladeService->registerDirective('datetime', function ($expression) {
            return "<?php echo with({$expression})->format('F d, Y'); ?>";
        });

        $output = $this->bladeService->makeView('directive', ['birthday' => new DateTime('1983-09-28')])->render();
        $this->assertEquals('Your birthday is September 28, 1983', trim($output));
    }

    /**
     * @testdox A component can be registered
     */
    public function testComponentComposer()
    {
        $this->bladeService->registerComponent('variables', function ($view) {
            $view->with('name', 'John Doe');
        });

        $output = $this->bladeService->makeView('variables')->render();
        $this->assertEquals('Hello John Doe!', trim($output));
    }

    /**
     * @testdox A component can be registered with an alias directive
     */
    public function testComponentDirective()
    {
        $this->bladeService->registerComponentDirective('alias.source', 'sayHello');

        $this->bladeService->registerComponent('alias.source', function ($view) {
            $view->with('name', 'World');
        });

        $output = $this->bladeService->makeView('alias.usage')->render();
        $this->assertEquals('Hello World!', trim($output));
    }

    /**
     * @testdox A view path can be added
     */
    public function testAddViewPath()
    {
        $this->bladeService->addViewPath('tests/phpunit/tests/BladeService/extra-views');

        $output = $this->bladeService->makeView('extra')->render();
        $this->assertEquals('Hello Extra!', trim($output));
    }
}
