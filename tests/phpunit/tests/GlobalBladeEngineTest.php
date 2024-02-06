<?php

namespace HelsingborgStad\Tests;

use HelsingborgStad\GlobalBladeEngine;
use HelsingborgStad\Services\BladeService\BladeService;
use PHPUnit\Framework\TestCase;

class GlobalBladeEngineTest extends TestCase {

    private static BladeService $bladeService;

    public function testClassExists() {
        $this->assertTrue(class_exists(GlobalBladeEngine::class));
    }

    public function testGetInstanceReturnsBladeService() {
        $this->assertInstanceOf(BladeService::class, GlobalBladeEngine::getInstance());
    }
}