<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Contracts\GamingPlatformLookupInterface;
use App\DTOs\UserProfileDto;
use App\Exceptions\UnsupportedPlatformException;
use App\Exceptions\UserNotFoundException;
use App\Factories\GamingPlatformLookupFactory;
use Mockery;
use Tests\TestCase;

class LookupControllerTest extends TestCase
{
    public function test_successful_lookup_returns_user_profile(): void
    {
        $mockService = Mockery::mock(GamingPlatformLookupInterface::class);
        $mockFactory = Mockery::mock(GamingPlatformLookupFactory::class);

        $userProfile = new UserProfileDto(
            username: 'TestUser',
            id: 'test-id-123',
            avatar: 'https://tebex.com/avatar.png'
        );

        $mockFactory
            ->shouldReceive('make')
            ->with('steam')
            ->once()
            ->andReturn($mockService);

        $mockService
            ->shouldReceive('lookupById')
            ->with('test-id-123')
            ->once()
            ->andReturn($userProfile);

        $this->app->instance(GamingPlatformLookupFactory::class, $mockFactory);

        $response = $this->getJson('/lookup?type=steam&id=test-id-123');

        $response->assertStatus(200)
            ->assertJson([
                'username' => 'TestUser',
                'id' => 'test-id-123',
                'avatar' => 'https://tebex.com/avatar.png',
                'is_cached' => false,
            ]);
    }

    public function test_unsupported_platform_returns_400(): void
    {
        $mockFactory = Mockery::mock(GamingPlatformLookupFactory::class);

        $mockFactory
            ->shouldReceive('make')
            ->with('steam')
            ->once()
            ->andThrow(new UnsupportedPlatformException('steam'));

        $this->app->instance(GamingPlatformLookupFactory::class, $mockFactory);

        $response = $this->getJson('/lookup?type=steam&id=123');

        $response->assertStatus(400)
            ->assertJsonStructure(['error', 'code']);
    }

    public function test_user_not_found_returns_404(): void
    {
        $mockService = Mockery::mock(GamingPlatformLookupInterface::class);
        $mockFactory = Mockery::mock(GamingPlatformLookupFactory::class);

        $mockFactory
            ->shouldReceive('make')
            ->with('steam')
            ->once()
            ->andReturn($mockService);

        $mockService
            ->shouldReceive('lookupById')
            ->with('nonexistent-id')
            ->once()
            ->andThrow(new UserNotFoundException('Steam', 'nonexistent-id', 'id'));

        $this->app->instance(GamingPlatformLookupFactory::class, $mockFactory);

        $response = $this->getJson('/lookup?type=steam&id=nonexistent-id');

        $response->assertStatus(404)
            ->assertJsonStructure(['error', 'code']);
    }

    public function test_missing_required_parameters_returns_validation_error(): void
    {
        $response = $this->getJson('/lookup?type=steam');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'id']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
