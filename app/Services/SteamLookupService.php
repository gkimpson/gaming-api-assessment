<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\UserProfileDto;
use App\Enums\GamingPlatform;
use InvalidArgumentException;

/**
 * Steam Lookup Service
 *
 * Handles user profile lookups for the Steam platform via Tebex API
 * Only supports ID-based searches as per Steam platform limitations
 */
readonly class SteamLookupService extends AbstractGamingPlatformLookupService
{
    /**
     * {@inheritDoc}
     */
    public function getPlatformName(): string
    {
        return ucfirst(GamingPlatform::STEAM->value);
    }

    /**
     * {@inheritDoc}
     *
     * @param  string  $username  The username (not supported)
     * @return string Never returns, always throws exception
     *
     * @throws InvalidArgumentException
     */
    protected function buildUsernameUrl(string $username): string
    {
        throw new InvalidArgumentException($this->getPlatformName().' does not support username lookups');
    }

    /**
     * {@inheritDoc}
     *
     * @param  string  $id  The Steam ID
     * @return string The Tebex API URL for Steam ID lookup
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
     * @param  array<string, mixed>  $data  The validated Steam API response
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
