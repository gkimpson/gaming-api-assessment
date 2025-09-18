<?php

declare(strict_types=1);

namespace App\Enums;

use InvalidArgumentException;

enum GamingPlatform: string
{
    case MINECRAFT = 'minecraft';
    case STEAM = 'steam';
    case XBOX_LIVE = 'xbl';

    /**
     * Get all platform values as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get a comma-separated string of all supported platforms
     */
    public static function implode(string $separator = ', '): string
    {
        return implode($separator, self::values());
    }

    /**
     * Create enum from string value
     */
    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            'minecraft' => self::MINECRAFT,
            'steam' => self::STEAM,
            'xbl' => self::XBOX_LIVE,
            default => throw new InvalidArgumentException(
                "'$value' is not a valid platform value for enum ".self::class
            )
        };
    }
}
