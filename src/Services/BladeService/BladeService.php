<?php

namespace HelsingborgStad\Services\BladeService;

use Closure;
use Illuminate\Contracts\View\View;

/**
 * Interface BladeService
 *
 * This interface defines the contract for the BladeService class.
 * It provides methods for rendering views, registering components and directives,
 * composing views, and adding view paths.
 */
interface BladeService extends Make
{
    /**
     * Create a component directive with the given alias.
     *
     * @param string $component The name of the component.
     * @param string $alias The alias to use for the component.
     * @return void
     */
    public function component(string $component, string $alias): void;

    /**
     * Register a directive handler.
     *
     * @param string $name The name of the directive.
     * @param callable $handler The handler function for the directive.
     * @return void
     */
    public function directive(string $name, callable $handler): void;

    /**
     * Register a view composer.
     *
     * @param array|string $views The views to apply the composer to.
     * @param Closure|string $callback The callback function or class method to execute.
     * @return array The registered composers.
     */
    public function composer(array|string $views, Closure|string $callback): array;

    /**
     * Add a view path to the service.
     *
     * @param string $path The path to add.
     * @param bool $prepend Whether to prepend the path to the existing paths.
     * @return void
     */
    public function addViewPath(string $path, $prepend = true): void;
}
