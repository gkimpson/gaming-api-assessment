<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\UserProfileDto;
use App\Enums\GamingPlatform;

/**
 * Xbox Live Lookup Service
 *
 * Handles user profile lookups for the Xbox Live platform via Tebex API
 * Supports both username and ID-based searches
 */
readonly class XboxLiveLookupService extends AbstractGamingPlatformLookupService
{
    /**
     * {@inheritDoc}
     */
    public function getPlatformName(): string
    {
        return ucfirst(GamingPlatform::XBOX_LIVE->value);
    }

    /**
     * {@inheritDoc}
     *
     * @param  string  $username  The Xbox Live gamertag
     * @return string The Tebex API URL for username lookup
     */
    protected function buildUsernameUrl(string $username): string
    {
        return "{$this->config['api_base']}/username/$username?type=username";
    }

    /**
     * {@inheritDoc}
     *
     * @param  string  $id  The Xbox Live user ID
     * @return string The Tebex API URL for ID lookup
     */
    protected function buildIdUrl(string $id): string
    {
        return "{$this->config['api_base']}/username/$id";
    }

    /**
     * {@inheritDoc}
     *
     * @param  array<string, mixed>  $data  The Tebex API response data
     * @return bool True if response contains username, id, and avatar fields
     */
    protected function isValidResponse(array $data): bool
    {
        return ! empty($data) && isset($data['username'], $data['id'], $data['meta']['avatar']);
    }

    /**
     * {@inheritDoc}
     *
     * @param  array<string, mixed>  $data  The validated Xbox Live API response
     * @return UserProfileDto The user profile with avatar from meta field
     */
    protected function createUserProfileDto(array $data): UserProfileDto
    {
        return new UserProfileDto(
            username: $data['username'],
            id: $data['id'],
            avatar: $data['meta']['avatar']
        );
    }
}
