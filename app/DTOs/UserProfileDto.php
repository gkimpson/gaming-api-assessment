<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * User Profile Data Transfer Object
 *
 * Simple data container for transferring user profile information between gaming platform services and the controller
 * Includes cache status indicator to track whether data was retrieved from cache or fresh API call
 */
readonly class UserProfileDto
{
    /**
     * Create a new user profile DTO
     *
     * @param  string  $username  The user's display name
     * @param  string  $id  The user's unique identifier
     * @param  string  $avatar  The URL to the user's avatar image
     * @param  bool  $isCached  Indicates whether this profile data was retrieved from cache
     */
    public function __construct(
        public string $username,
        public string $id,
        public string $avatar,
        public bool $isCached = false
    ) {}

    /**
     * Convert the DTO to an array for API response
     *
     * @return array<string, string|bool>
     */
    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'id' => $this->id,
            'avatar' => $this->avatar,
            'is_cached' => $this->isCached,
        ];
    }

    /**
     * Create a cached version of an existing UserProfileDto
     */
    public static function fromCached(UserProfileDto $profile): self
    {
        return new self(
            $profile->username,
            $profile->id,
            $profile->avatar,
            true
        );
    }
}
