<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTOs\UserProfileDto;
use App\Exceptions\PlatformUnavailableException;
use App\Exceptions\UserNotFoundException;
use App\Services\MinecraftLookupService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class MinecraftLookupServiceTest extends TestCase
{
    private MinecraftLookupService $service;

    private Client $mockClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockClient = $this->createMock(Client::class);
        $config = [
            'api_base' => 'https://api.mojang.com',
            'session_server' => 'https://sessionserver.mojang.com',
            'avatar_base' => 'https://crafatar.com/avatars/',
            'timeout' => 10,
            'supports_username' => true,
            'supports_id' => true,
        ];

        $this->service = new MinecraftLookupService($this->mockClient, $config);
    }

    public function test_get_platform_name_returns_minecraft(): void
    {
        $this->assertEquals('Minecraft', $this->service->getPlatformName());
    }

    public function test_supports_both_username_and_id_search(): void
    {
        $this->assertTrue($this->service->supportsUsernameSearch());
        $this->assertTrue($this->service->supportsIdSearch());
    }

    /**
     * @throws GuzzleException
     * @throws UserNotFoundException
     * @throws PlatformUnavailableException
     * @throws JsonException
     */
    public function test_successful_username_lookup(): void
    {
        $responseData = [
            'name' => 'TestPlayer',
            'id' => 'test-uuid-123',
        ];

        $response = new Response(200, [], json_encode($responseData, JSON_THROW_ON_ERROR));

        $this->mockClient
            ->expects($this->once())
            ->method('get')
            ->with('https://api.mojang.com/users/profiles/minecraft/TestPlayer', ['timeout' => 10])
            ->willReturn($response);

        $result = $this->service->lookupByUsername('TestPlayer');

        $this->assertInstanceOf(UserProfileDto::class, $result);
        $this->assertEquals('TestPlayer', $result->username);
        $this->assertEquals('test-uuid-123', $result->id);
        $this->assertEquals('https://crafatar.com/avatars/test-uuid-123', $result->avatar);
        $this->assertFalse($result->isCached);
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     * @throws PlatformUnavailableException
     * @throws UserNotFoundException
     */
    public function test_successful_id_lookup(): void
    {
        $responseData = [
            'name' => 'TestPlayer',
            'id' => 'test-uuid-123',
        ];

        $response = new Response(200, [], json_encode($responseData, JSON_THROW_ON_ERROR));

        $this->mockClient
            ->expects($this->once())
            ->method('get')
            ->with('https://sessionserver.mojang.com/session/minecraft/profile/test-uuid-123', ['timeout' => 10])
            ->willReturn($response);

        $result = $this->service->lookupById('test-uuid-123');

        $this->assertInstanceOf(UserProfileDto::class, $result);
        $this->assertEquals('TestPlayer', $result->username);
        $this->assertEquals('test-uuid-123', $result->id);
        $this->assertEquals('https://crafatar.com/avatars/test-uuid-123', $result->avatar);
    }

    /**
     * @throws ReflectionException
     */
    public function test_build_username_url(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('buildUsernameUrl');

        $url = $method->invoke($this->service, 'TestPlayer');

        $this->assertEquals('https://api.mojang.com/users/profiles/minecraft/TestPlayer', $url);
    }

    /**
     * @throws ReflectionException
     */
    public function test_build_id_url(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('buildIdUrl');

        $url = $method->invoke($this->service, 'test-uuid-123');

        $this->assertEquals('https://sessionserver.mojang.com/session/minecraft/profile/test-uuid-123', $url);
    }

    /**
     * @throws ReflectionException
     */
    public function test_is_valid_response_with_valid_data(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('isValidResponse');

        $validData = [
            'name' => 'TestPlayer',
            'id' => 'test-uuid-123',
        ];

        $this->assertTrue($method->invoke($this->service, $validData));
    }

    /**
     * @throws ReflectionException
     */
    public function test_is_valid_response_with_missing_name(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('isValidResponse');

        $invalidData = [
            'id' => 'test-uuid-123',
        ];

        $this->assertFalse($method->invoke($this->service, $invalidData));
    }

    /**
     * @throws ReflectionException
     */
    public function test_is_valid_response_with_missing_id(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('isValidResponse');

        $invalidData = [
            'name' => 'TestPlayer',
        ];

        $this->assertFalse($method->invoke($this->service, $invalidData));
    }

    /**
     * @throws ReflectionException
     */
    public function test_is_valid_response_with_empty_data(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('isValidResponse');

        $this->assertFalse($method->invoke($this->service, []));
    }

    /**
     * @throws ReflectionException
     */
    public function test_create_user_profile_dto(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('createUserProfileDto');

        $data = [
            'name' => 'TestPlayer',
            'id' => 'test-uuid-123',
        ];

        $dto = $method->invoke($this->service, $data);

        $this->assertInstanceOf(UserProfileDto::class, $dto);
        $this->assertEquals('TestPlayer', $dto->username);
        $this->assertEquals('test-uuid-123', $dto->id);
        $this->assertEquals('https://crafatar.com/avatars/test-uuid-123', $dto->avatar);
        $this->assertFalse($dto->isCached);
    }
}
