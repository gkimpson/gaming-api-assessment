<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when a user lookup operation fails.
 */
class UserNotFoundException extends Exception
{
    /**
     * Create a new user not found exception.
     *
     * @param  string  $platform  The gaming platform name where the user was not found
     * @param  string  $identifier  The username or ID that was not found
     * @param  string  $searchType  The type of search performed (either 'username' or 'id')
     */
    public function __construct(string $platform, string $identifier, string $searchType = 'identifier')
    {
        parent::__construct("User with $searchType '$identifier' not found on $platform platform");
    }
}
