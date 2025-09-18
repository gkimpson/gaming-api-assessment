<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\GamingPlatformLookupInterface;
use App\DTOs\UserProfileDto;
use App\Exceptions\PlatformUnavailableException;
use App\Exceptions\UserNotFoundException;
use App\Services\CachedGamingPlatformLookup;
use Illuminate\Contracts\Cache\Repository as Cache;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

class CachedGamingPlatformLookupTest extends TestCase
{
    private CachedGamingPlatformLookup $cachedService;

    private GamingPlatformLookupInterface $mockService;

    private Cache $mockCache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockService = $this->createMock(GamingPlatformLookupInterface::class);
        $this->mockCache = $this->createMock(Cache::class);

        $this->cachedService = new CachedGamingPlatformLookup(
            $this->mockService,
            $this->mockCache
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws PlatformUnavailableException
     * @throws UserNotFoundException
     */
    public function test_lookup_by_username_cache_hit(): void
    {
        $cachedProfile = new UserProfileDto(
            username: 'TestUser',
            id: 'test-id-123',
            avatar: 'https://tebex.com/avatar.png'
        );

        $this->mockService
            ->expects($this->once())
            ->method('getPlatformName')
            ->willReturn('Steam');

        $this->mockCache
            ->expects($this->once())
            ->method('get')
            ->with('Steam_TestUser')
            ->willReturn($cachedProfile);

        $this->mockService
            ->expects($this->never())
            ->method('lookupByUsername');

        $result = $this->cachedService->lookupByUsername('TestUser');

        $this->assertInstanceOf(UserProfileDto::class, $result);
        $this->assertTrue($result->isCached);
        $this->assertEquals('TestUser', $result->username);
    }

    /**
     * @throws InvalidArgumentException
     * @throws PlatformUnavailableException
     * @throws UserNotFoundException
     */
    public function test_lookup_by_id_cache_hit(): void
    {
        $cachedProfile = new UserProfileDto(
            username: 'TestUser',
            id: 'test-id-123',
            avatar: 'https://tebex.com/avatar.png'
        );

        $this->mockService
            ->expects($this->once())
            ->method('getPlatformName')
            ->willReturn('Steam');

        $this->mockCache
            ->expects($this->once())
            ->method('get')
            ->with('Steam_test-id-123')
            ->willReturn($cachedProfile);

        $this->mockService
            ->expects($this->never())
            ->method('lookupById');

        $result = $this->cachedService->lookupById('test-id-123');

        $this->assertInstanceOf(UserProfileDto::class, $result);
        $this->assertTrue($result->isCached);
        $this->assertEquals('test-id-123', $result->id);
    }

    public function test_supports_username_search_delegates_to_service(): void
    {
        $this->mockService
            ->expects($this->once())
            ->method('supportsUsernameSearch')
            ->willReturn(true);

        $result = $this->cachedService->supportsUsernameSearch();

        $this->assertTrue($result);
    }

    public function test_supports_id_search_delegates_to_service(): void
    {
        $this->mockService
            ->expects($this->once())
            ->method('supportsIdSearch')
            ->willReturn(false);

        $result = $this->cachedService->supportsIdSearch();

        $this->assertFalse($result);
    }

    public function test_get_platform_name_delegates_to_service(): void
    {
        $this->mockService
            ->expects($this->once())
            ->method('getPlatformName')
            ->willReturn('Steam');

        $result = $this->cachedService->getPlatformName();

        $this->assertEquals('Steam', $result);
    }

    /**
     * @throws ReflectionException
     */
    public function test_build_cache_key(): void
    {
        $reflection = new ReflectionClass($this->cachedService);
        $method = $reflection->getMethod('buildCacheKey');

        $key = $method->invoke($this->cachedService, 'Steam', 'TestUser');

        $this->assertEquals('Steam_TestUser', $key);
    }

    /**
     * @throws ReflectionException
     */
    public function test_get_cached_profile_returns_cached_dto(): void
    {
        $originalProfile = new UserProfileDto(
            username: 'TestUser',
            id: 'test-id-123',
            avatar: 'https://tebex.com/avatar.png'
        );

        $this->mockCache
            ->expects($this->once())
            ->method('get')
            ->with('Steam_TestUser')
            ->willReturn($originalProfile);

        $reflection = new ReflectionClass($this->cachedService);
        $method = $reflection->getMethod('getCachedProfile');

        $result = $method->invoke($this->cachedService, 'Steam_TestUser');

        $this->assertInstanceOf(UserProfileDto::class, $result);
        $this->assertTrue($result->isCached);
        $this->assertEquals('TestUser', $result->username);
    }

    /**
     * @throws ReflectionException
     */
    public function test_get_cached_profile_returns_null_when_cache_empty(): void
    {
        $this->mockCache
            ->expects($this->once())
            ->method('get')
            ->with('Steam_TestUser')
            ->willReturn(null);

        $reflection = new ReflectionClass($this->cachedService);
        $method = $reflection->getMethod('getCachedProfile');

        $result = $method->invoke($this->cachedService, 'Steam_TestUser');

        $this->assertNull($result);
    }
}
