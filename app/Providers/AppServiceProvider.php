<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
      $this->app->singleton(GrokRepurposingService::class);

       $this->app->singleton(GhostwriterAgent::class, function ($app) {
        return new GhostwriterAgent(
            $app->make(GetCampaignRulesTool::class),
            $app->make(GetPostHistoryTool::class),
        );
    });

    $this->app->singleton(GhostwriterAgentService::class);
    $this->app->singleton(GrokRepurposingService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
