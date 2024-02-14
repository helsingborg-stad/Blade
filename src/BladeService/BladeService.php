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
use Illuminate\View\ViewServiceProvider;
use InvalidArgumentException;

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

    /**
     * Constructor for the BladeService class.
     *
     * @param array $viewPaths The array of view paths.
     * @param ?string $cachePath The path to the cache directory. If not provided, the default cache path will be used.
     * If the default cache path is not available, caching will be disabled. To override the cache path, define the
     * constant 'BLADE_CACHE_PATH' with the desired cache path.
     */
    public function __construct(array $viewPaths, ?string $cachePath = null)
    {
        $this->viewPaths = $viewPaths;
        $this->container = new Container();
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
     * If the cache path does not exist, it will be created with the default permissions of 0755.
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
            $cachePathPermissions = 0755;
            $couldCreateCachePath = mkdir($cachePath, $cachePathPermissions, true);

            if (!$couldCreateCachePath) {
                throw new InvalidArgumentException('Cache path does not exist and could not be created');
            }
        }

        if (!is_dir($cachePath) || !is_writable($cachePath)) {
            throw new InvalidArgumentException('Cache path is not a directory or is not writable');
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

        $this->factory  = $this->container->get('view');
        $this->compiler = $this->container->get('blade.compiler');

        Facade::setFacadeApplication($this->container);
    }

    /**
     * Make a view with the given data.
     *
     * @param string $view The name of the view to render.
     * @param array $data The data to pass to the view.
     * @param array $mergeData The data to merge with the view data.
     * @return View The rendered view.
     */
    public function makeView(string $view, array $data = [], array $mergeData = []): View
    {
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
            $this->factory->addLocation($path);
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
}
