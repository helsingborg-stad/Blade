<?php

namespace HelsingborgStad\BladeService;

use Illuminate\Contracts\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\Contracts\View\View;
use Illuminate\View\Factory;
use Illuminate\View\ViewServiceProvider;

/**
 * Class BladeServiceInstance
 *
 * This class provides functionality for managing Blade views.
 */
class BladeService implements BladeServiceInterface
{
    private Factory $factory;
    private BladeCompiler $compiler;

    /**
     * Constructor for the BladeService class.
     *
     * @param array $viewPaths The array of view paths.
     * @param string $cachePath The path to the cache directory.
     * @param Container $container The dependency injection container.
     */
    public function __construct(array $viewPaths, string $cachePath, Container $container)
    {
        $this->registerBindings($container);
        $this->initializeFactoryAndCompiler($viewPaths, $cachePath, $container);
    }

    /**
     * Register necessary service bindings.
     *
     * @param Container $container The IoC container instance.
     * @return void
     */
    private function registerBindings(Container $container): void
    {
        $container->bindIf('files', Filesystem::class, true);
        $container->bindIf('events', Dispatcher::class, true);
    }

    /**
     * Initialize the view factory and blade compiler.
     *
     * @param array $viewPaths The paths to the Blade views.
     * @param string $cachePath The path to store compiled views.
     * @param Container $container The IoC container instance.
     * @return void
     */
    private function initializeFactoryAndCompiler(array $viewPaths, string $cachePath, Container $container): void
    {
        $container->bindIf('config', function () use ($viewPaths, $cachePath) {
            return [
            'view.paths'    => $viewPaths,
            'view.compiled' => $cachePath,
            ];
        }, true);

        $viewServiceProvider = new ViewServiceProvider($container);
        $viewServiceProvider->register();

        $this->factory  = $container->get('view');
        $this->compiler = $container->get('blade.compiler');

        Facade::setFacadeApplication($container);
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
        $this->compiler->directive($alias, function ($expression) use ($component) {
            $data = isset($expression) ? "$expression" : "[]";
            return <<<PHP
                <?php \$__env->startComponent('{$component}', {$data}); ob_start(); ?>
            PHP;
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
        $this->compiler->directive("end{$alias}", function () {
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
    public function addViewPath(string $path, $prepend = true): void
    {
        $this->factory->addLocation($path);
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
