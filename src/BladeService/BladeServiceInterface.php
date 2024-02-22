<?php

namespace HelsingborgStad\BladeService;

use Illuminate\Contracts\View\View;

/**
 * Interface BladeService
 *
 * This interface defines the contract for the BladeService class.
 * It provides methods for rendering views, registering components and directives,
 * composing views, and adding view paths.
 */
interface BladeServiceInterface
{
    /**
     * Create a view with the given data.
     *
     * @param string $view The name of the view to render.
     * @param array $data The data to pass to the view.
     * @param array $mergeData The data to merge with the view data.
     * @param string|null $viewPath The path to the view.
     * @return View The rendered view.
     */
    public function makeView(string $view, array $data = [], array $mergeData = [], ?string $viewPath = null): View;

    /**
     * Create a component directive with the given alias.
     *
     * @param string $component The name of the component.
     * @param string $alias The alias to use for the component.
     * @return void
     */
    public function registerComponentDirective(string $component, string $alias): void;

    /**
     * Register a directive handler.
     *
     * @param string $name The name of the directive.
     * @param callable $handler The handler function for the directive.
     * @return void
     */
    public function registerDirective(string $name, callable $handler): void;

    /**
     * Register a component.
     *
     * @param string|array $views The views to compose.
     * @param \Closure|string $callback The callback function for the composer.
     * @return array
     */
    public function registerComponent($views, $callback): array;

    /**
     * Add a view path to the service.
     *
     * @param string $path The path to add.
     * @param bool $prepend Whether to prepend the path to the existing paths.
     * @return void
     */
    public function addViewPath(string $path, $prepend = true): void;

    /**
     * Add an array of view paths to the service.
     *
     * @param string[] $path The paths to add.
     * @param bool $prepend Whether to prepend the paths to the existing paths.
     * @return void
     */
    public function addViewPaths(array $paths, $prepend = false): void;
}
