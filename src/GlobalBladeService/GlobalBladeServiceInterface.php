<?php

namespace HelsingborgStad\GlobalBladeService;

use HelsingborgStad\BladeService\BladeServiceInterface;

interface GlobalBladeServiceInterface
{
    /**
     * Returns an instance of the BladeServiceInterface.
     *
     * @param array $viewPaths An array of view paths.
     * @param ?string $cachePath The path to the cache directory.
     * @return BladeServiceInterface An instance of the BladeServiceInterface.
     */
    public static function getInstance(array $viewPaths = [], ?string $cachePath = null): BladeServiceInterface;
}
