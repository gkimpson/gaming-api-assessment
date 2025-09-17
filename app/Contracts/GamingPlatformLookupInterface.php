<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\UserProfileDto;
use App\Exceptions\PlatformUnavailableException;
use App\Exceptions\UserNotFoundException;

/**
 * Gaming Platform Lookup Interface
 */
interface GamingPlatformLookupInterface
{
    /**
     * Lookup user profile by username
     *
     * @param  string  $username  The username to lookup
     * @return UserProfileDto The user's profile information
     *
     * @throws UserNotFoundException When user is not found
     * @throws PlatformUnavailableException When the platform API is unavailable
     */
    public function lookupByUsername(string $username): UserProfileDto;

    /**
     * Lookup user profile by user ID
     *
     * @param  string  $id  The user ID to lookup
     * @return UserProfileDto The user's profile information
     *
     * @throws UserNotFoundException When user is not found
     * @throws PlatformUnavailableException When the platform API is unavailable
     */
    public function lookupById(string $id): UserProfileDto;

    /**
     * Check if this platform supports username based searches
     *
     * @return bool True if username searches are supported
     */
    public function supportsUsernameSearch(): bool;

    /**
     * Check if this platform supports ID based searches
     *
     * @return bool True if ID searches are supported
     */
    public function supportsIdSearch(): bool;

    /**
     * Get the platform identifier name
     *
     * @return string The platform name (e.g 'minecraft', 'steam', 'xbl')
     */
    public function getPlatformName(): string;
}
