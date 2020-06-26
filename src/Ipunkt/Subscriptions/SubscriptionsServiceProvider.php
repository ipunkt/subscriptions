<?php namespace Ipunkt\Subscriptions;

use Illuminate\Support\ServiceProvider;
use Ipunkt\Subscriptions\Plans\PlanRepository;

class SubscriptionsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('subscriptions.php'),
        ], 'config');
        $this->publishes([
            __DIR__.'/../../views' => resource_path('views/vendor/subscriptions'),
        ], 'views');

        $this->loadMigrationsFrom(__DIR__.'/../../migrations');
        $this->loadViewsFrom(__DIR__.'/../../views', 'subscriptions');

        $this->app->bind('Ipunkt\Subscriptions\Plans\PlanRepository', function () {
            $repository = new PlanRepository($this->app['config']->get('subscriptions.plans'));
            $repository->setDefaultPlan($this->app['config']->get('subscriptions.defaults.plan'));

            return $repository;
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php', 'subscriptions'
        );
    }
}
