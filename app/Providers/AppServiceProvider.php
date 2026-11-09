<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', fn (User $user): bool => $user->isAdmin());

        Gate::define('view-order', fn (User $user, Order $order): bool => $user->isAdmin() || $order->user()->is($user));

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by(
            Str::transliterate(Str::lower($request->string('username')).'|'.$request->ip())
        ));
    }
}
