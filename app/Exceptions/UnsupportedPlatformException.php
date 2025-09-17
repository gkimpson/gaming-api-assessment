<?php

declare(strict_types=1);

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * Exception thrown when attempting to lookup a user on a gaming platform that is not supported.
 */
class UnsupportedPlatformException extends InvalidArgumentException
{
    /**
     * Create a new unsupported platform exception.
     *
     * @param  string  $platform  The name of the gaming platform that is not supported
     */
    public function __construct(string $platform)
    {
        parent::__construct("Gaming platform '$platform' is not supported. Supported platforms: minecraft, steam, xbl"); // TODO: Use ENUMS later
    }
}
