<?php

declare(strict_types=1);

namespace App\Factories;

use App\Contracts\GamingPlatformLookupInterface;
use App\Exceptions\UnsupportedPlatformException;
use App\Services\MinecraftLookupService;
use App\Services\SteamLookupService;
use App\Services\XboxLiveLookupService;

/**
 * Gaming Platform Lookup Factory
 *
 * Factory class responsible for resolving the appropriate gaming platform lookup service based on the platform type
 */
class GamingPlatformLookupFactory
{
    private array $services;

    /**
     * Create a new gaming platform lookup factory
     */
    public function __construct(
        MinecraftLookupService $minecraftService,
        SteamLookupService $steamService,
        XboxLiveLookupService $xboxLiveService
    ) {
        $this->services = [
            'minecraft' => $minecraftService,
            'steam' => $steamService,
            'xbl' => $xboxLiveService,
        ];
    }

    /**
     * Create a lookup service instance for the specified platform
     *
     * @param  string  $platform  The gaming platform identifier (minecraft, steam, xbl)
     * @return GamingPlatformLookupInterface The appropriate platform service
     *
     * @throws UnsupportedPlatformException When the platform is not supported
     */
    public function make(string $platform): GamingPlatformLookupInterface
    {
        if (! array_key_exists($platform, $this->services)) {
            throw new UnsupportedPlatformException($platform);
        }

        return $this->services[$platform];
    }

    /**
     * Get all supported platform identifiers
     *
     * @return array<string> Array of supported platform names
     */
    public function getSupportedPlatforms(): array
    {
        return array_keys($this->services);
    }

    /**
     * Check if a platform is supported
     *
     * @param  string  $platform  The platform to check
     * @return bool True if the platform is supported
     */
    public function isSupported(string $platform): bool
    {
        return array_key_exists($platform, $this->services);
    }
}
