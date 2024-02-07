<?php

namespace HelsingborgStad;

use HelsingborgStad\Services\BladeService\BladeService;
use HelsingborgStad\Services\BladeService\BladeServiceInstance;

/**
 * Class GlobalBladeEngine
 *
 * This class represents a global blade engine that provides a singleton instance of the BladeService.
 */
class GlobalBladeEngine
{
    private static ?BladeService $bladeService = null;

    /**
     * Get the singleton instance of the BladeService.
     *
     * @return BladeService The singleton instance of the BladeService.
     */
    public static function getInstance(array $views = [], $cachePath = ""): BladeService
    {
        if (self::$bladeService === null) {
            self::$bladeService = new BladeServiceInstance($views, $cachePath);
        }

        return self::$bladeService;
    }
}
