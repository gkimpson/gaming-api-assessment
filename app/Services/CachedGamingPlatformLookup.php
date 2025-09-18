<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\GamingPlatformLookupInterface;
use App\DTOs\UserProfileDto;
use Illuminate\Contracts\Cache\Repository as Cache;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Cached Gaming Platform Lookup Decorator
 *
 * Decorates any GamingPlatformLookupInterface implementation with caching functionality
 * Implements dual cache key strategy to ensure single cache entry per user per platform
 * e.g. user can lookup services by username or ID, so build cache key for both
 */
readonly class CachedGamingPlatformLookup implements GamingPlatformLookupInterface
{
    public function __construct(
        private GamingPlatformLookupInterface $service,
        private Cache $cache
    ) {}

    /**
     * {@inheritDoc}
     *
     * @param  string  $username  The username to lookup
     * @return UserProfileDto The user profile data
     *
     * @throws InvalidArgumentException
     */
    public function lookupByUsername(string $username): UserProfileDto
    {
        $platformName = $this->service->getPlatformName();
        $usernameKey = $this->buildCacheKey($platformName, $username);

        if ($cachedProfile = $this->getCachedProfile($usernameKey)) {
            return $cachedProfile;
        }

        $profile = $this->service->lookupByUsername($username);
        $idKey = $this->buildCacheKey($platformName, $profile->id);

        $this->cacheProfileWithCrossReference($profile, $usernameKey, $idKey);

        return $profile;
    }

    /**
     * {@inheritDoc}
     *
     * @param  string  $id  The user ID to lookup
     * @return UserProfileDto The user profile data
     *
     * @throws InvalidArgumentException
     */
    public function lookupById(string $id): UserProfileDto
    {
        $platformName = $this->service->getPlatformName();
        $idKey = $this->buildCacheKey($platformName, $id);

        if ($cachedProfile = $this->getCachedProfile($idKey)) {
            return $cachedProfile;
        }

        $profile = $this->service->lookupById($id);
        $usernameKey = $this->buildCacheKey($platformName, $profile->username);

        $this->cacheProfileWithCrossReference($profile, $idKey, $usernameKey);

        return $profile;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsUsernameSearch(): bool
    {
        return $this->service->supportsUsernameSearch();
    }

    /**
     * {@inheritDoc}
     */
    public function supportsIdSearch(): bool
    {
        return $this->service->supportsIdSearch();
    }

    /**
     * {@inheritDoc}
     */
    public function getPlatformName(): string
    {
        return $this->service->getPlatformName();
    }

    /**
     * Get cached profile if it exists
     *
     * @param  string  $cacheKey  The cache key to lookup
     * @return UserProfileDto|null The cached profile or null if not found
     *
     * @throws InvalidArgumentException
     */
    private function getCachedProfile(string $cacheKey): ?UserProfileDto
    {
        $cached = $this->cache->get($cacheKey);

        return $cached ? UserProfileDto::fromCached($cached) : null;
    }

    /**
     * Cache profile with cross-reference entry
     * e.g. cache username and id based references
     *
     * @param  UserProfileDto  $profile  The user profile to cache
     * @param  string  $primaryKey  The primary cache key (for current lookup type)
     * @param  string  $secondaryKey  The secondary cache key (for cross-reference lookup)
     */
    private function cacheProfileWithCrossReference(
        UserProfileDto $profile,
        string $primaryKey,
        string $secondaryKey
    ): void {
        $cacheTtlHours = config('services.gaming_platforms.cache_ttl_hours', 24);

        $this->cache->put(
            $primaryKey,
            $profile,
            now()->addHours($cacheTtlHours)
        );
        $this->cache->put(
            $secondaryKey,
            $profile,
            now()->addHours($cacheTtlHours)
        );
    }

    /**
     * Build cache key for the given platform and identifier
     *
     * @param  string  $platform  The platform name
     * @param  string  $identifier  The username or ID
     * @return string The cache key
     */
    private function buildCacheKey(string $platform, string $identifier): string
    {
        return sprintf('%s_%s', $platform, $identifier);
    }
}
