<?php

namespace HelsingborgStad\Tests;

use HelsingborgStad\GlobalBladeEngine;
use HelsingborgStad\Services\BladeService\BladeService;
use PHPUnit\Framework\Attributes\BackupStaticProperties;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[BackupStaticProperties(true)]
final class GlobalBladeEngineTest extends TestCase
{
    #[TestDox('The class exists')]
    public function testClassExists()
    {
        $this->assertTrue(class_exists(GlobalBladeEngine::class));
    }

    #[TestDox('The getInstance method returns an instance of BladeService')]
    public function testGetInstanceReturnsBladeService()
    {
        $this->assertInstanceOf(BladeService::class, GlobalBladeEngine::getInstance([''], '/tmp/blade-cache'));
    }
}
