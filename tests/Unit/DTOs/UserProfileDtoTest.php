<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs;

use App\DTOs\UserProfileDto;
use PHPUnit\Framework\TestCase;

class UserProfileDtoTest extends TestCase
{
    public function test_can_create_user_profile_dto(): void
    {
        $dto = new UserProfileDto(
            username: 'TestUser',
            id: 'test-id-123',
            avatar: 'https://tebex.com/avatar.png'
        );

        $this->assertEquals('TestUser', $dto->username);
        $this->assertEquals('test-id-123', $dto->id);
        $this->assertEquals('https://tebex.com/avatar.png', $dto->avatar);
        $this->assertFalse($dto->isCached);
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $dto = new UserProfileDto(
            username: 'TestUser',
            id: 'test-id-123',
            avatar: 'https://tebex.com/avatar.png',
            isCached: true
        );

        $expected = [
            'username' => 'TestUser',
            'id' => 'test-id-123',
            'avatar' => 'https://tebex.com/avatar.png',
            'is_cached' => true,
        ];

        $this->assertEquals($expected, $dto->toArray());
    }

    public function test_from_cached_creates_cached_version(): void
    {
        $originalDto = new UserProfileDto(
            username: 'TestUser',
            id: 'test-id-123',
            avatar: 'https://tebex.com/avatar.png',
            isCached: false
        );

        $cachedDto = UserProfileDto::fromCached($originalDto);

        $this->assertEquals('TestUser', $cachedDto->username);
        $this->assertEquals('test-id-123', $cachedDto->id);
        $this->assertEquals('https://tebex.com/avatar.png', $cachedDto->avatar);
        $this->assertTrue($cachedDto->isCached);
    }

    public function test_from_cached_preserves_all_data(): void
    {
        $originalDto = new UserProfileDto(
            username: 'AnotherUser',
            id: 'another-id-456',
            avatar: 'https://tebex.com/another-avatar.jpg',
            isCached: false
        );

        $cachedDto = UserProfileDto::fromCached($originalDto);

        $this->assertEquals($originalDto->username, $cachedDto->username);
        $this->assertEquals($originalDto->id, $cachedDto->id);
        $this->assertEquals($originalDto->avatar, $cachedDto->avatar);
        $this->assertNotEquals($originalDto->isCached, $cachedDto->isCached);
        $this->assertTrue($cachedDto->isCached);
    }

    public function test_dto_with_empty_strings(): void
    {
        $dto = new UserProfileDto(
            username: '',
            id: '',
            avatar: ''
        );

        $this->assertEquals('', $dto->username);
        $this->assertEquals('', $dto->id);
        $this->assertEquals('', $dto->avatar);
        $this->assertFalse($dto->isCached);
    }

    public function test_to_array_with_uncached_default(): void
    {
        $dto = new UserProfileDto(
            username: 'TestUser',
            id: 'test-id-123',
            avatar: 'https://tebex.com/avatar.png'
        );

        $array = $dto->toArray();

        $this->assertArrayHasKey('is_cached', $array);
        $this->assertFalse($array['is_cached']);
    }
}
