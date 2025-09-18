<?php

declare(strict_types=1);

namespace Tests\Unit\Factories;

use App\Contracts\GamingPlatformLookupInterface;
use App\Exceptions\UnsupportedPlatformException;
use App\Factories\GamingPlatformLookupFactory;
use App\Services\SteamLookupService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use PHPUnit\Framework\TestCase;

class GamingPlatformLookupFactoryTest extends TestCase
{
    private Container $mockContainer;

    private GamingPlatformLookupFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockContainer = $this->createMock(Container::class);
        $this->factory = new GamingPlatformLookupFactory($this->mockContainer);
    }

    /**
     * @throws BindingResolutionException
     */
    public function test_can_create_supported_platform_service(): void
    {
        $mockService = $this->createMock(GamingPlatformLookupInterface::class);

        $this->mockContainer
            ->expects($this->once())
            ->method('make')
            ->with(SteamLookupService::class)
            ->willReturn($mockService);

        $result = $this->factory->make('steam');

        $this->assertInstanceOf(GamingPlatformLookupInterface::class, $result);
    }

    /**
     * @throws BindingResolutionException
     */
    public function test_throws_exception_for_unsupported_platform(): void
    {
        $this->expectException(UnsupportedPlatformException::class);

        $this->factory->make('unsupported-platform');
    }

    public function test_is_supported_returns_correct_values(): void
    {
        $this->assertTrue($this->factory->isSupported('steam'));
        $this->assertTrue($this->factory->isSupported('minecraft'));
        $this->assertTrue($this->factory->isSupported('xbl'));
        $this->assertFalse($this->factory->isSupported('unsupported'));
    }
}
