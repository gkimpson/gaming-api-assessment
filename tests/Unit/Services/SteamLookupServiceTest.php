<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Exceptions\PlatformUnavailableException;
use App\Exceptions\UserNotFoundException;
use App\Services\SteamLookupService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\TestCase;

class SteamLookupServiceTest extends TestCase
{
    private SteamLookupService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $mockClient = $this->createMock(Client::class);
        $config = [
            'api_base' => 'https://api.tebex.com',
            'timeout' => 10,
            'supports_username' => false,
            'supports_id' => true,
        ];

        $this->service = new SteamLookupService($mockClient, $config);
    }

    /**
     * @throws UserNotFoundException
     * @throws PlatformUnavailableException
     * @throws GuzzleException
     * @throws JsonException
     */
    public function test_username_lookup_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Steam does not support username lookups');

        $this->service->lookupByUsername('testuser');
    }

    public function test_supports_id_search_but_not_username(): void
    {
        $this->assertFalse($this->service->supportsUsernameSearch());
        $this->assertTrue($this->service->supportsIdSearch());
    }

    public function test_get_platform_name_returns_steam(): void
    {
        $this->assertEquals('Steam', $this->service->getPlatformName());
    }
}
