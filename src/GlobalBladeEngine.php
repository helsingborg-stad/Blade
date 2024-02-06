<?php

namespace HelsingborgStad;

use HelsingborgStad\Services\BladeService\BladeService;

class GlobalBladeEngine {

    private static ?BladeService $bladeService = null;

    public static function getInstance():BladeService {

        if( self::$bladeService === null ) {
            self::$bladeService = new class implements BladeService {
                public function render($template, $data = []) {
                    return 'Rendered template';
                }
            };
        }

        return self::$bladeService;
    }
}