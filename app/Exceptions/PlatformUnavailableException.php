<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when a gaming platform's API is temporarily unavailable.
 */
class PlatformUnavailableException extends Exception
{
    /**
     * Create a new platform unavailable exception.
     *
     * @param  string  $platform  The name of the gaming platform that is unavailable
     * @param  string  $reason  The reason for the platform's unavailability
     */
    public function __construct(string $platform, string $reason = 'API temporarily unavailable')
    {
        parent::__construct("$platform platform is currently unavailable: $reason");
    }
}
