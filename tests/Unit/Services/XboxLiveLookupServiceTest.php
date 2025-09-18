<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTOs\UserProfileDto;
use App\Exceptions\PlatformUnavailableException;
use App\Exceptions\UserNotFoundException;
use App\Services\XboxLiveLookupService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class XboxLiveLookupServiceTest extends TestCase
{
    private XboxLiveLookupService $service;

    private Client $mockClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockClient = $this->createMock(Client::class);
        $config = [
            'api_base' => 'https://api.tebex.io/lookup',
            'timeout' => 10,
            'supports_username' => true,
            'supports_id' => true,
        ];

        $this->service = new XboxLiveLookupService($this->mockClient, $config);
    }

    public function test_get_platform_name_returns_xbl(): void
    {
        $this->assertEquals('Xbl', $this->service->getPlatformName());
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
            'username' => 'TestGamer',
            'id' => 'xbl-id-123',
            'meta' => [
                'avatar' => 'https://tebex.com/avatar.png',
            ],
        ];

        $response = new Response(200, [], json_encode($responseData, JSON_THROW_ON_ERROR));

        $this->mockClient
            ->expects($this->once())
            ->method('get')
            ->with('https://api.tebex.io/lookup/username/TestGamer?type=username', ['timeout' => 10])
            ->willReturn($response);

        $result = $this->service->lookupByUsername('TestGamer');

        $this->assertInstanceOf(UserProfileDto::class, $result);
        $this->assertEquals('TestGamer', $result->username);
        $this->assertEquals('xbl-id-123', $result->id);
        $this->assertEquals('https://tebex.com/avatar.png', $result->avatar);
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
            'username' => 'TestGamer',
            'id' => 'xbl-id-123',
            'meta' => [
                'avatar' => 'https://tebex.com/avatar.png',
            ],
        ];

        $response = new Response(200, [], json_encode($responseData, JSON_THROW_ON_ERROR));

        $this->mockClient
            ->expects($this->once())
            ->method('get')
            ->with('https://api.tebex.io/lookup/username/xbl-id-123', ['timeout' => 10])
            ->willReturn($response);

        $result = $this->service->lookupById('xbl-id-123');

        $this->assertInstanceOf(UserProfileDto::class, $result);
        $this->assertEquals('TestGamer', $result->username);
        $this->assertEquals('xbl-id-123', $result->id);
        $this->assertEquals('https://tebex.com/avatar.png', $result->avatar);
    }

    /**
     * @throws ReflectionException
     */
    public function test_build_username_url(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('buildUsernameUrl');

        $url = $method->invoke($this->service, 'TestGamer');

        $this->assertEquals('https://api.tebex.io/lookup/username/TestGamer?type=username', $url);
    }

    /**
     * @throws ReflectionException
     */
    public function test_build_id_url(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('buildIdUrl');

        $url = $method->invoke($this->service, 'xbl-id-123');

        $this->assertEquals('https://api.tebex.io/lookup/username/xbl-id-123', $url);
    }

    /**
     * @throws ReflectionException
     */
    public function test_is_valid_response_with_valid_data(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('isValidResponse');

        $validData = [
            'username' => 'TestGamer',
            'id' => 'xbl-id-123',
            'meta' => [
                'avatar' => 'https://tebex.com/avatar.png',
            ],
        ];

        $this->assertTrue($method->invoke($this->service, $validData));
    }

    /**
     * @throws ReflectionException
     */
    public function test_is_valid_response_with_missing_username(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('isValidResponse');

        $invalidData = [
            'id' => 'xbl-id-123',
            'meta' => [
                'avatar' => 'https://tebex.com/avatar.png',
            ],
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
            'username' => 'TestGamer',
            'meta' => [
                'avatar' => 'https://tebex.com/avatar.png',
            ],
        ];

        $this->assertFalse($method->invoke($this->service, $invalidData));
    }

    /**
     * @throws ReflectionException
     */
    public function test_is_valid_response_with_missing_meta_avatar(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('isValidResponse');

        $invalidData = [
            'username' => 'TestGamer',
            'id' => 'xbl-id-123',
            'meta' => [],
        ];

        $this->assertFalse($method->invoke($this->service, $invalidData));
    }

    /**
     * @throws ReflectionException
     */
    public function test_is_valid_response_with_missing_meta(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('isValidResponse');

        $invalidData = [
            'username' => 'TestGamer',
            'id' => 'xbl-id-123',
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
            'username' => 'TestGamer',
            'id' => 'xbl-id-123',
            'meta' => [
                'avatar' => 'https://tebex.com/avatar.png',
            ],
        ];

        $dto = $method->invoke($this->service, $data);

        $this->assertInstanceOf(UserProfileDto::class, $dto);
        $this->assertEquals('TestGamer', $dto->username);
        $this->assertEquals('xbl-id-123', $dto->id);
        $this->assertEquals('https://tebex.com/avatar.png', $dto->avatar);
        $this->assertFalse($dto->isCached);
    }
}
