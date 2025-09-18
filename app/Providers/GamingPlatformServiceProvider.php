<?php

declare(strict_types=1);

namespace App\Providers;

use App\Factories\GamingPlatformLookupFactory;
use App\Services\CachedGamingPlatformLookup;
use App\Services\MinecraftLookupService;
use App\Services\SteamLookupService;
use App\Services\XboxLiveLookupService;
use GuzzleHttp\Client;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\ServiceProvider;

/**
 * Gaming Platform Service Provider
 */
class GamingPlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Client::class, function () {
            return new Client([
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);
        });

        // Bind services with caching decorators
        $this->app->bind(MinecraftLookupService::class, function ($app) {
            $service = new MinecraftLookupService(
                $app->make(Client::class),
                config('services.gaming_platforms.minecraft')
            );

            return new CachedGamingPlatformLookup($service, $app->make(Cache::class));
        });

        $this->app->bind(SteamLookupService::class, function ($app) {
            $service = new SteamLookupService(
                $app->make(Client::class),
                config('services.gaming_platforms.steam')
            );

            return new CachedGamingPlatformLookup($service, $app->make(Cache::class));
        });

        $this->app->bind(XboxLiveLookupService::class, function ($app) {
            $service = new XboxLiveLookupService(
                $app->make(Client::class),
                config('services.gaming_platforms.xbl')
            );

            return new CachedGamingPlatformLookup($service, $app->make(Cache::class));
        });

        $this->app->singleton(GamingPlatformLookupFactory::class, function ($app) {
            return new GamingPlatformLookupFactory($app);
        });
    }

    public function boot(): void {}
}
