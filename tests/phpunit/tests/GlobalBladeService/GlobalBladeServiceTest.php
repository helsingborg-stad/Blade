<?php

namespace HelsingborgStad\Tests\GlobalBladeService;

use InvalidArgumentException;
use Mockery;
use HelsingborgStad\BladeService\BladeService;
use HelsingborgStad\BladeService\BladeServiceInterface;
use HelsingborgStad\GlobalBladeService\GlobalBladeService;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * @backupStaticAttributes enabled
 */
class GlobalBladeServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @testdox Class exists
     */
    public function testClassExists()
    {
        $this->assertTrue(class_exists('HelsingborgStad\GlobalBladeService\GlobalBladeService'));
    }

    /**
     * @testdox getInstance throws when not passing $viewPaths on first call
     */
    public function testGetInstanceThrowsWhenMissingViewPaths()
    {
        $this->expectException(InvalidArgumentException::class);
        $invalidViewPaths = [];
        GlobalBladeService::getInstance($invalidViewPaths);
    }

    /**
     * @testdox getInstance returns implementation of BladeServiceInterface on success
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGetInstanceReturnsBladeServiceInterfaceOnSuccess()
    {
        $bladeServiceMock = Mockery::mock('overload:' . BladeService::class, BladeServiceInterface::class);
        $bladeServiceMock->shouldReceive('__construct')->once()->andReturn($bladeServiceMock);
        $result = GlobalBladeService::getInstance(['viewPath'], 'cachePath');

        $this->assertInstanceOf(BladeServiceInterface::class, $result);
    }

    /**
     * @testdox getInstance adds view paths if already instantiated
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGetInstanceAddsViewPathsIfAlreadyInstantiated()
    {
        $bladeServiceMock = Mockery::mock('overload:' . BladeService::class, BladeServiceInterface::class);
        $bladeServiceMock->shouldReceive('__construct')->once()->andReturn($bladeServiceMock);
        $bladeServiceMock->shouldReceive('addViewPath')->once()->with('secondViewPath');
        GlobalBladeService::getInstance(['firstViewPath'], 'cachePath');

        // Call second time.
        GlobalBladeService::getInstance(['secondViewPath'], 'cachePath');
    }
}
