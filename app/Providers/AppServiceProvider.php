<?php

namespace App\Providers;

use App\Models\WebsiteDrawBadge;
use App\Observers\WebsiteDrawBadgeObserver;
use App\Services\InstallationService;
use App\Services\PermissionsService;
use App\Services\RconService;
use App\Services\SettingsService;
use App\Services\ViteService;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Blaze\Blaze;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            Vite::class,
            ViteService::class,
        );

        $this->app->singleton(
            InstallationService::class,
            fn () => new InstallationService,
        );

        $this->app->singleton(
            SettingsService::class,
            fn () => new SettingsService,
        );

        $this->app->singleton(
            PermissionsService::class,
            fn () => new PermissionsService,
        );

        $this->app->singleton(
            RconService::class,
            fn () => new RconService,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        Blaze::optimize()
            ->in(resource_path('themes/atom/components'))
            ->in(resource_path('themes/dusk/components'));

        if (config('habbo.site.force_https')) {
            URL::forceScheme('https');
        }

        Table::configureUsing(function (Table $table) {
            $table->paginated([10, 25, 50]);
        });

        $settingsService = app(SettingsService::class);
        $badgePath = $settingsService->getOrDefault('badge_path_filesystem', '/var/www/gamedata/c_images/album1584');
        Config::set('filesystems.disks.badges.root', $badgePath);

        $adsPath = $settingsService->getOrDefault('ads_path_filesystem', '/var/www/gamedata/custom');
        Config::set('filesystems.disks.ads.root', $adsPath);

        WebsiteDrawBadge::observe(WebsiteDrawBadgeObserver::class);

        // GLOBALLY SHARE ONLINE USERS COUNT WITH ALL BLADE VIEWS
        View::composer('*', function ($view) {
            $onlineUsersCount = 0;

            if (Schema::hasTable('users')) {
                // If using Arcturus/Morningstar user_online table
                if (Schema::hasTable('users_online')) {
                    $onlineUsersCount = DB::table('users_online')->count();
                }
                // Strict check for online = '1' or '2'
                elseif (Schema::hasColumn('users', 'online')) {
                    $onlineUsersCount = DB::table('users')
                        ->where('online', '1')
                        ->orWhere('online', '2')
                        ->count();
                } elseif (Schema::hasColumn('users', 'is_online')) {
                    $onlineUsersCount = DB::table('users')
                        ->where('is_online', '1')
                        ->count();
                }
            }

            $view->with('onlineUsersCount', $onlineUsersCount);
        });
    }
}