<?php

namespace HelsingborgStad\Tests\GlobalBladeService;

use InvalidArgumentException;
use Mockery;
use HelsingborgStad\BladeService\BladeService;
use HelsingborgStad\BladeService\BladeServiceInterface;
use HelsingborgStad\GlobalBladeService\GlobalBladeService;
use PHPUnit\Framework\Attributes\BackupStaticProperties;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[BackupStaticProperties(true)]
class GlobalBladeServiceTest extends TestCase
{
    #[TestDox('Class exists')]
    public function testClassExists()
    {
        $this->assertTrue(class_exists('HelsingborgStad\GlobalBladeService\GlobalBladeService'));
    }

    #[TestDox('getInstance throws when not passing $viewPaths on first call')]
    public function testGetInstanceThrowsWhenMissingViewPaths()
    {
        $this->expectException(InvalidArgumentException::class);
        $invalidViewPaths = [];
        GlobalBladeService::getInstance($invalidViewPaths);
    }

    #[TestDox('getInstance throws when not passing $cachePath on first call')]
    public function testGetInstanceThrowsWhenMissingCachePath()
    {
        $this->expectException(InvalidArgumentException::class);
        $viewPaths        = ['views'];
        $invalidCachePath = '';
        GlobalBladeService::getInstance($viewPaths, $invalidCachePath);
    }

    #[RunInSeparateProcess]
    #[TestDox('getInstance returns implementation of BladeServiceInterface on success')]
    public function testGetInstanceReturnsBladeServiceInterfaceOnSuccess()
    {
        $bladeServiceMock = Mockery::mock('overload:' . BladeService::class, BladeServiceInterface::class);
        $bladeServiceMock->shouldReceive('__construct')->once()->andReturn($bladeServiceMock);
        $result = GlobalBladeService::getInstance(['viewPath'], 'cachePath');

        $this->assertInstanceOf(BladeServiceInterface::class, $result);
    }
}
