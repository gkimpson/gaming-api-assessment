<?php

declare(strict_types=1);

namespace App\Providers;

use App\Factories\GamingPlatformLookupFactory;
use App\Services\MinecraftLookupService;
use App\Services\SteamLookupService;
use App\Services\XboxLiveLookupService;
use GuzzleHttp\Client;
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
                'timeout' => 30,    // TODO: Add to config
                'connect_timeout' => 10, // TODO: Add to config
            ]);
        });

        $this->app->bind(MinecraftLookupService::class, function ($app) {
            return new MinecraftLookupService(
                $app->make(Client::class),
                config('services.gaming_platforms.minecraft')
            );
        });

        $this->app->bind(SteamLookupService::class, function ($app) {
            return new SteamLookupService(
                $app->make(Client::class),
                config('services.gaming_platforms.steam')
            );
        });

        $this->app->bind(XboxLiveLookupService::class, function ($app) {
            return new XboxLiveLookupService(
                $app->make(Client::class),
                config('services.gaming_platforms.xbl')
            );
        });

        $this->app->singleton(GamingPlatformLookupFactory::class, function ($app) {
            return new GamingPlatformLookupFactory(
                $app->make(MinecraftLookupService::class),
                $app->make(SteamLookupService::class),
                $app->make(XboxLiveLookupService::class)
            );
        });
    }

    public function boot(): void {}
}
