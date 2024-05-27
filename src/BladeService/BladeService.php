<?php

namespace HelsingborgStad\BladeService;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\Contracts\View\View;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\ViewServiceProvider;
use InvalidArgumentException;
use HelsingborgStad\BladeError\BladeError;
use Throwable;

/**
 * Class BladeServiceInstance
 *
 * This class provides functionality for managing Blade views.
 */
class BladeService implements BladeServiceInterface
{
    private ContainerInterface $container;
    private Factory $factory;
    private BladeCompiler $compiler;
    private array $viewPaths;
    private ?string $cachePath;
    private array $fileExtensions;

    /**
     * Constructor for the BladeService class.
     *
     * @param array $viewPaths The array of view paths.
     * @param ?string $cachePath The path to the cache directory. If not provided, the default cache path will be used.
     * @param array $fileExtensions The array of file extensions to use for Blade views. Defaults to ['blade.php'].
     * If the default cache path is not available, caching will be disabled. To override the cache path, define the
     * constant 'BLADE_CACHE_PATH' with the desired cache path.
     */
    public function __construct(array $viewPaths, ?string $cachePath = null, array $fileExtensions = ['blade.php'])
    {
        $this->fileExtensions = $fileExtensions;
        $this->viewPaths      = $viewPaths;
        $this->container      = new Container();
        $this->setCachePath($cachePath);
        $this->registerBindings();
        $this->initializeFactoryAndCompiler();
    }

    /**
     * Sets the cache path for Blade templates.
     *
     * If the constant 'BLADE_CACHE_PATH' is defined and not empty, it will be used as the cache path.
     * Otherwise, if the $cachePath parameter is not empty, it will be used as the cache path.
     * If both the constant and the parameter are empty, a default cache path will be used.
     *
     * If the cache path does not exist, it will be created with the default permissions of 0775.
     * If the cache path cannot be created, an InvalidArgumentException will be thrown.
     *
     * If the cache path is not a directory or is not writable, an InvalidArgumentException will be thrown.
     *
     * @param string|null $cachePath The cache path to set. If null, the default cache path will be used.
     * @throws InvalidArgumentException If the cache path does not exist and could not be created,
     * or if the cache path is not a directory or is not writable, or if the cache path is empty.
     */
    private function setCachePath(?string $cachePath)
    {
        if (defined('BLADE_CACHE_PATH') && !empty(constant('BLADE_CACHE_PATH'))) {
            $cachePath = constant('BLADE_CACHE_PATH');
        } elseif (!empty($cachePath)) {
            $cachePath = $cachePath;
        } else {
            $cachePath = sys_get_temp_dir() . '/blade-cache';
        }

        if (!file_exists($cachePath)) {
            $cachePathPermissions = 0775;
            $couldCreateCachePath = mkdir($cachePath, $cachePathPermissions, true);

            if (!$couldCreateCachePath) {
                throw new InvalidArgumentException('Cache path [' . $cachePath . '] does not exist and could not be created');
            }
        }

        if (!is_dir($cachePath) || !is_writable($cachePath)) {
            throw new InvalidArgumentException('Cache path [' . $cachePath . '] is not a directory or is not writable');
        }

        $this->cachePath = $cachePath;
    }

    /**
     * Register necessary service bindings.
     *
     * @param Container $container The IoC container instance.
     * @return void
     */
    private function registerBindings(): void
    {
        $this->container->bindIf('files', Filesystem::class, true);
        $this->container->bindIf('events', Dispatcher::class, true);
    }

    /**
     * Initialize the view factory and blade compiler.
     *
     * @param array $viewPaths The paths to the Blade views.
     * @param string $cachePath The path to store compiled views.
     * @param Container $container The IoC container instance.
     * @return void
     */
    private function initializeFactoryAndCompiler(): void
    {
        $this->container->bindIf('config', function () {
            return [
            'view.paths'    => $this->viewPaths,
            'view.compiled' => $this->cachePath,
            ];
        }, true);

        $viewServiceProvider = new ViewServiceProvider($this->container);
        $viewServiceProvider->register();

        $this->factory  = $this->createFactory();
        $this->compiler = $this->container->get('blade.compiler');

        Facade::setFacadeApplication($this->container);
    }

    /**
     * Creates a new instance of the Blade Factory.
     *
     * @return Factory The newly created Blade Factory instance.
     */
    private function createFactory(): Factory
    {
        $engines = $this->container->get('view.engine.resolver');
        $finder  = new FileViewFinder($this->container->get('files'), $this->viewPaths, $this->fileExtensions);
        $events  = $this->container->get('events');
        return new Factory($engines, $finder, $events);
    }

    /**
     * Make a view with the given data.
     *
     * @param string $view The name of the view to render.
     * @param array $data The data to pass to the view.
     * @param array $mergeData The data to merge with the view data.
     * @param string|array|null $viewPath The path(s) to be added before making view.
     * @return View The rendered view.
     */
    public function makeView(string $view, array $data = [], array $mergeData = [], $viewPath = null): View
    {
        if ($viewPath !== null) {
            $factory = $this->createFactory();

            /** @var \Illuminate\View\FileViewFinder $finder */
            $finder = $factory->getFinder();

            if (is_array($viewPath)) {
                foreach ($viewPath as $path) {
                    $finder->prependLocation($path);
                }
            } else {
                $finder->prependLocation($viewPath);
            }

            return $factory->make($view, $data, $mergeData);
        }

        return $this->factory->make($view, $data, $mergeData);
    }

    /**
     * Register a component directive with an optional alias.
     *
     * @param string $component The component class.
     * @param string $alias The optional alias for the component.
     * @return void
     */
    public function registerComponentDirective(string $component, string $alias): void
    {
        $this->registerComponentDirectiveOpeningTag($component, $alias);
        $this->registerComponentDirectiveClosingTag($alias);
    }

    /**
     * Registers a component directive opening tag.
     *
     * @param string $component The component name.
     * @param string $alias The directive alias.
     * @return void
     */
    private function registerComponentDirectiveOpeningTag(string $component, string $alias): void
    {
        $this->registerDirective($alias, function ($expression) use ($component) {
            // @codeCoverageIgnoreStart
            $data = isset($expression) ? "$expression" : "[]";
            return <<<PHP
                <?php \$__env->startComponent('{$component}', {$data}); ob_start(); ?>
            PHP;
            // @codeCoverageIgnoreEnd
        });
    }

    /**
     * Registers a component directive closing tag.
     *
     * @param string $alias The alias of the component directive.
     * @return void
     */
    private function registerComponentDirectiveClosingTag(string $alias): void
    {
        $this->registerDirective("end{$alias}", function () {
            return <<<PHP
                <?php echo ob_get_clean(); echo \$__env->renderComponent(); ?>
            PHP;
        });
    }

    /**
     * Add a view path to the service.
     *
     * @param string $path The path to add.
     * @param bool $prepend Whether to prepend the path.
     * @return void
     */
    public function addViewPath(string $path, $prepend = false): void
    {
        if ($prepend) {
            $this->prependViewPath($path);
        } else {
            $this->viewPaths[] = $path;
            $this->factory->addLocation($path);
        }
    }

    /**
     * Add an array of view paths to the service.
     *
     * @param string[] $path The paths to add.
     * @param bool $prepend Whether to prepend the paths.
     * @return void
     */
    public function addViewPaths(array $paths, $prepend = false): void
    {
        foreach ($paths as $path) {
            $this->addViewPath($path, $prepend);
        }
    }

    /**
     * Prepend a view path to the file view finder.
     *
     * @param string $path The path to prepend.
     * @return void
     */
    private function prependViewPath(string $path): void
    {
        $this->viewPaths = array_merge([$path], $this->viewPaths);

        /** @var \Illuminate\View\FileViewFinder $finder */
        $finder = $this->factory->getFinder();
        $finder->prependLocation($path);
    }

    /**
     * Register a custom Blade directive.
     *
     * @param string $name The name of the directive.
     * @param callable $handler The callback function for the directive.
     * @return void
     */
    public function registerDirective(string $name, callable $handler): void
    {
        $this->compiler->directive($name, $handler);
    }

    /**
     * Register a component.
     *
     * @param string|array $views The views to compose.
     * @param \Closure|string $callback The callback function for the composer.
     * @return array
     */
    public function registerComponent($views, $callback): array
    {
        return $this->factory->composer($views, $callback);
    }

    /**
     * Returns the error handler.
     *
     * @param Throwable $e The error object.
     * @return Error The error handler.
     */
    public function errorHandler(Throwable $e): BladeError
    {
        $bladeError =  new BladeError($this);
        $bladeError->setThrowable($e);
        return $bladeError;
    }

    /**
     * Get the view paths.
     * 
     * @return array The view paths.
     */
    public function getViewPaths(): array
    {
        return $this->viewPaths;
    }

    /**
     * Get the cache path.
     * 
     * @return string|null The cache path.
     */
    public function getCachePath(): ?string
    {
        return $this->cachePath;
    }
}
