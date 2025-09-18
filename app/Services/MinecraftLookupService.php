<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\UserProfileDto;
use App\Enums\GamingPlatform;

/**
 * Minecraft Lookup Service
 *
 * Handles user profile lookups for the Minecraft platform
 * Supports both username and ID-based searches
 */
readonly class MinecraftLookupService extends AbstractGamingPlatformLookupService
{
    /**
     * {@inheritDoc}
     */
    public function getPlatformName(): string
    {
        return ucfirst(GamingPlatform::MINECRAFT->value);
    }

    /**
     * {@inheritDoc}
     *
     * @param  string  $username  The Minecraft username
     * @return string The Mojang API URL for username lookup
     */
    protected function buildUsernameUrl(string $username): string
    {
        return "{$this->config['api_base']}/users/profiles/minecraft/$username";
    }

    /**
     * {@inheritDoc}
     *
     * @param  string  $id  The Minecraft user UUID
     * @return string The Mojang session server URL for profile lookup
     */
    protected function buildIdUrl(string $id): string
    {
        return "{$this->config['session_server']}/session/minecraft/profile/$id";
    }

    /**
     * {@inheritDoc}
     *
     * @param  array<string, mixed>  $data  The API response data
     * @return bool True if response contains name and id fields
     */
    protected function isValidResponse(array $data): bool
    {
        return ! empty($data) && isset($data['name'], $data['id']);
    }

    /**
     * {@inheritDoc}
     *
     * @param  array<string, mixed>  $data  The validated Minecraft API response
     * @return UserProfileDto The user profile with generated avatar URL
     */
    protected function createUserProfileDto(array $data): UserProfileDto
    {
        return new UserProfileDto(
            username: $data['name'],
            id: $data['id'],
            avatar: $this->config['avatar_base'].$data['id']
        );
    }
}
