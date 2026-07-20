<?php

namespace App\Providers;

use App\Models\Exchange;
use App\Models\Skill;
use App\Policies\ExchangePolicy;
use App\Policies\SkillPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Exchange::class, ExchangePolicy::class);
        Gate::policy(Skill::class, SkillPolicy::class);
    }
}
