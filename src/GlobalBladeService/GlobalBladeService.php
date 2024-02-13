<?php

namespace HelsingborgStad\GlobalBladeService;

use HelsingborgStad\BladeService\BladeService;
use HelsingborgStad\BladeService\BladeServiceInterface;
use Illuminate\Container\Container;
use InvalidArgumentException;

/**
 * Class Blade
 *
 * Represents the Blade templating engine.
 */
class GlobalBladeService implements GlobalBladeServiceInterface
{
    private static BladeServiceInterface $bladeServiceInstance;

    /**
     * Get the instance of the BladeService.
     *
     * @param array|null $viewPaths The array of view paths.
     * @param string|null $cachePath The cache path.
     * @return BladeServiceInterface The instance of the BladeService.
     */
    public static function getInstance(array $viewPaths = [], string $cachePath = ''): BladeServiceInterface
    {
        if (!isset(self::$bladeServiceInstance)) {
            self::validateRequiredInputParameters($viewPaths, $cachePath);

            $container                  = new Container();
            self::$bladeServiceInstance = new BladeService($viewPaths, $cachePath, $container);
        } elseif (!empty($viewPaths)) {
            foreach ($viewPaths as $viewPath) {
                self::$bladeServiceInstance->addViewPath($viewPath);
            }
        }

        return self::$bladeServiceInstance;
    }

    /**
     * Validates the required input parameters for the getInstance method.
     *
     * @param array $viewPaths An array containing at least one view path.
     * @param string $cachePath The path to the cache folder.
     * @throws InvalidArgumentException If the viewPaths parameter is
     * not an array or if the cachePath parameter is not a string.
     */
    private static function validateRequiredInputParameters($viewPaths, $cachePath): void
    {
        if (!is_array($viewPaths) || empty($viewPaths)) {
            $message = <<<EOF
            The \$viewPaths parameter must be an array 
            containing at least one view path when calling getInstance the first time.
            EOF;
            throw new InvalidArgumentException($message);
        }

        if (!is_string($cachePath) || empty($cachePath)) {
            $message = <<<EOF
            The \$cachePath parameter must be a string
            containing the path to the cache folder when calling getInstance the first time.
            EOF;
            throw new InvalidArgumentException($message);
        }
    }
}
