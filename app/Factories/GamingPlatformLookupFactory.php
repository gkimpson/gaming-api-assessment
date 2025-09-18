<?php

declare(strict_types=1);

namespace App\Factories;

use App\Contracts\GamingPlatformLookupInterface;
use App\Enums\GamingPlatform;
use App\Exceptions\UnsupportedPlatformException;
use App\Services\MinecraftLookupService;
use App\Services\SteamLookupService;
use App\Services\XboxLiveLookupService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;

/**
 * Gaming Platform Lookup Factory
 *
 * Factory class responsible for resolving the appropriate gaming platform lookup service based on the platform type
 */
class GamingPlatformLookupFactory
{
    private array $serviceMap;

    /**
     * Create a new gaming platform lookup factory
     */
    public function __construct(
        private readonly Container $container
    ) {
        $this->serviceMap = [
            GamingPlatform::MINECRAFT->value => MinecraftLookupService::class,
            GamingPlatform::STEAM->value => SteamLookupService::class,
            GamingPlatform::XBOX_LIVE->value => XboxLiveLookupService::class,
        ];
    }

    /**
     * Create a lookup service instance for the specified platform
     *
     * @param  string  $platform  The gaming platform identifier
     * @return GamingPlatformLookupInterface The appropriate platform service
     *
     * @throws UnsupportedPlatformException When the platform is not supported
     * @throws BindingResolutionException When the service cannot be resolved from the container
     */
    public function make(string $platform): GamingPlatformLookupInterface
    {
        if (! array_key_exists($platform, $this->serviceMap)) {
            throw new UnsupportedPlatformException($platform);
        }

        return $this->container->make($this->serviceMap[$platform]);
    }

    /**
     * Get all supported platform identifiers
     *
     * @return array<string> Array of supported platform names
     */
    public function getSupportedPlatforms(): array
    {
        return GamingPlatform::values();
    }

    /**
     * Check if a platform is supported
     *
     * @param  string  $platform  The platform to check
     * @return bool True if the platform is supported
     */
    public function isSupported(string $platform): bool
    {
        return array_key_exists($platform, $this->serviceMap);
    }
}
