<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * User Profile Data Transfer Object
 *
 * Simple data container for transferring user profile information between gaming platform services and the controller
 */
readonly class UserProfileDto
{
    /**
     * Create a new user profile DTO
     *
     * @param  string  $username  The user's display name
     * @param  string  $id  The user's unique identifier
     * @param  string  $avatar  The URL to the user's avatar image
     */
    public function __construct(
        public string $username,
        public string $id,
        public string $avatar
    ) {}

    /**
     * Convert the DTO to an array for API response
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'id' => $this->id,
            'avatar' => $this->avatar,
        ];
    }
}
