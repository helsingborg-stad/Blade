<?php

namespace HelsingborgStad\Tests\BladeService;

use DateTime;
use Mockery;
use HelsingborgStad\BladeService\BladeService;
use HelsingborgStad\BladeService\BladeServiceInterface;
use Illuminate\Container\Container;
use Illuminate\View\FileViewFinder;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use phpmock\mockery\PHPMockery;
use PHPUnit\Framework\Attributes\TestDox;

class BladeServiceTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    private string $cachePath                    = 'tests/phpunit/tests/BladeService/cache';
    private ?BladeServiceInterface $bladeService = null;

    private function getBladeService(): BladeServiceInterface
    {
        if (!($this->bladeService instanceof BladeServiceInterface)) {
            $views     = ['tests/phpunit/tests/BladeService/views'];
            $container = new Container();

            $this->bladeService = new BladeService($views, $this->cachePath, $container);
        }

        return $this->bladeService;
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
        $output = $this->getBladeService()->makeView('basic')->render();
        $this->assertEquals('Hello World!', trim($output));
    }

    /**
     * @testdox Cache path can be set from constant
     * @runInSeparateProcess
     */
    public function testCachePathCanBeSetFromConstant()
    {
        $testCachePath = 'tests/phpunit/tests/BladeService/cachePathFromConstant';
        define('BLADE_CACHE_PATH', $testCachePath);
        $views     = ['tests/phpunit/tests/BladeService/views'];
        $container = new Container();

        $this->bladeService = new BladeService($views, null, $container);
        $cachePath          = $this->getBladeServicePrivateCachePathPropertyValue($this->bladeService);

        $this->assertEquals($testCachePath, $cachePath);
    }

    /**
     * @testdox Cache path defaults to system tmp dir
     */
    public function testCachePathDefaultsToSystemTmpDir()
    {
        $views           = ['tests/phpunit/tests/BladeService/views'];
        $container       = new Container();
        $systemCachePath = sys_get_temp_dir() . '/blade-cache';

        $this->bladeService = new BladeService($views, null, $container);
        $cachePath          = $this->getBladeServicePrivateCachePathPropertyValue($this->bladeService);

        $this->assertEquals($systemCachePath, $cachePath);
    }

    private function getBladeServicePrivateCachePathPropertyValue(BladeServiceInterface $bladeService)
    {
        $reflection = new \ReflectionClass($bladeService);
        $cachePath  = $reflection->getProperty('cachePath');
        $cachePath->setAccessible(true);

        return $cachePath->getValue($this->bladeService);
    }

    /**
     * @testdox Instantiating the BladeService throws if cache path does not exist and can not be created
     * @runInSeparateProcess
     */
    public function testThrowsIfCachePathCanNotBeCreated()
    {
        // Ensure that folder can not be created.
        $bladeServiceNamespace = '\HelsingborgStad\BladeService';
        PHPMockery::mock($bladeServiceNamespace, "file_exists")->andReturn(false);
        PHPMockery::mock($bladeServiceNamespace, "mkdir")->andReturn(false);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache path does not exist and could not be created');

        $views     = ['tests/phpunit/tests/BladeService/views'];
        $container = new Container();

        $this->bladeService = new BladeService($views, null, $container);
        Mockery::close();
    }

    /**
     * @testdox Instantiating the BladeService throws if cache path is not writable
     * @runInSeparateProcess
     */
    public function testThrowsIfCachePathIsNotWritable()
    {
        // Ensure that folder is not writable.
        $bladeServiceNamespace = '\HelsingborgStad\BladeService';
        PHPMockery::mock($bladeServiceNamespace, "is_writable")->andReturn(false);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache path is not a directory or is not writable');

        $views     = ['tests/phpunit/tests/BladeService/views'];
        $container = new Container();

        $this->bladeService = new BladeService($views, null, $container);
    }

    /**
     * @testdox Variables can be passed to a component
     */
    public function testVariables()
    {
        $output = $this->getBladeService()->makeView('variables', ['name' => 'John Doe'])->render();
        $this->assertEquals('Hello John Doe!', trim($output));
    }

    /**
     * @testdox Directive can be registered
     */
    public function testDirective()
    {
        $this->getBladeService()->registerDirective('datetime', function ($expression) {
            return "<?php echo with({$expression})->format('F d, Y'); ?>";
        });

        $output = $this->getBladeService()->makeView('directive', ['birthday' => new DateTime('1983-09-28')])->render();
        $this->assertEquals('Your birthday is September 28, 1983', trim($output));
    }

    /**
     * @testdox A component can be registered
     */
    public function testComponentComposer()
    {
        $this->getBladeService()->registerComponent('variables', function ($view) {
            $view->with('name', 'John Doe');
        });

        $output = $this->getBladeService()->makeView('variables')->render();
        $this->assertEquals('Hello John Doe!', trim($output));
    }

    /**
     * @testdox A component can be registered with an alias directive
     */
    public function testComponentDirective()
    {
        $this->getBladeService()->registerComponentDirective('alias.source', 'sayHello');

        $this->getBladeService()->registerComponent('alias.source', function ($view) {
            $view->with('name', 'World');
        });

        $output = $this->getBladeService()->makeView('alias.usage')->render();
        $this->assertEquals('Hello World!', trim($output));
    }

    /**
     * @testdox A view path can be added
     */
    public function testAddViewPath()
    {
        $this->getBladeService()->addViewPath('tests/phpunit/tests/BladeService/extra-views');

        $output = $this->getBladeService()->makeView('extra')->render();
        $this->assertEquals('Hello Extra!', trim($output));
    }

    /**
     * @testdox A view path can be prepended
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddViewPathPrepend()
    {
        $fileViewFinder = Mockery::mock(FileViewFinder::class);
        $factoryMock    = Mockery::mock('overload:Illuminate\View\Factory');
        $factoryMock->shouldReceive('getFinder')->andReturn($fileViewFinder);
        $factoryMock->shouldReceive('setContainer');
        $factoryMock->shouldReceive('share');
        $prepend = true;

        // Expectation
        $fileViewFinder->shouldReceive('prependLocation')
            ->once()
            ->with('tests/phpunit/tests/BladeService/extra-views');

        $this->getBladeService()->addViewPath('tests/phpunit/tests/BladeService/extra-views', $prepend);
    }

    /**
     * @testdox Multiple view paths can be added
     */
    public function testAddViewPaths()
    {
        $this->getBladeService()->addViewPaths(['tests/phpunit/tests/BladeService/extra-views']);

        $output = $this->getBladeService()->makeView('extra')->render();
        $this->assertEquals('Hello Extra!', trim($output));
    }
}
