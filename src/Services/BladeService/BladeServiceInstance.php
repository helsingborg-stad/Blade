<?php

namespace HelsingborgStad\Services\BladeService;

use Illuminate\Contracts\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use Illuminate\View\ViewServiceProvider;

/**
 * Class BladeServiceFactory
 */
class BladeServiceInstance implements BladeService
{
    use Traits\Make;

    protected Container $container;
    private Factory $factory;
    private BladeCompiler $compiler;

    /**
     * BladeService constructor.
     *
     * @param array $viewPaths
     * @param string $cachePath
     */
    public function __construct(array $viewPaths = [], string $cachePath = "")
    {
        $this->container = new \Illuminate\Container\Container();

        $this->setupContainer((array) $viewPaths, $cachePath);
        (new ViewServiceProvider($this->container))->register();

        $this->factory  = $this->container->get('view');
        $this->compiler = $this->container->get('blade.compiler');
    }

    /**
     * Register a component class with an optional alias.
     *
     * @param string $class
     * @param string|null $alias
     * @return void
     */
    public function component(string $component, string $alias): void
    {
        $this->compiler->directive($alias, function ($expression) use ($component) {
            $data = isset($expression) ? "$expression" : "[]";
            return <<<PHP
                <?php \$__env->startComponent('{$component}', {$data}); ob_start(); ?>
            PHP;
        });

        $this->compiler->directive("end{$alias}", function () {
            return <<<PHP
                <?php echo ob_get_clean(); echo \$__env->renderComponent(); ?>
            PHP;
        });
    }

    /**
     * Add a view path to the service.
     *
     * @param string $path
     * @param bool $prepend
     * @return void
     */
    public function addViewPath(string $path, $prepend = true): void
    {
        $this->factory->addLocation($path);
    }

    /**
     * Register a custom Blade directive.
     *
     * @param string $name
     * @param callable $handler
     * @return void
     */
    public function directive(string $name, callable $handler): void
    {
        $this->compiler->directive($name, $handler);
    }

    /**
     * Register a view composer event.
     *
     * @param string|array $views
     * @param \Closure|string $callback
     * @return array
     */
    public function composer($views, $callback): array
    {
        return $this->factory->composer($views, $callback);
    }

    /**
     * Setup the container bindings.
     *
     * @param array $viewPaths
     * @param string $cachePath
     * @return void
     */
    protected function setupContainer(array $viewPaths, string $cachePath)
    {
        $this->container->bindIf('files', function () {
            return new Filesystem();
        }, true);

        $this->container->bindIf('events', function () {
            return new Dispatcher();
        }, true);

        $this->container->bindIf('config', function () use ($viewPaths, $cachePath) {
            return [
                'view.paths'    => $viewPaths,
                'view.compiled' => $cachePath,
            ];
        }, true);

        Facade::setFacadeApplication($this->container);
    }
}
