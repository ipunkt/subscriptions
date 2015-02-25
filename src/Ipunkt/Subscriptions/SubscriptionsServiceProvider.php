<?php namespace Ipunkt\Subscriptions;

use Illuminate\Support\ServiceProvider;
use Ipunkt\Subscriptions\Plans\PlanRepository;

class SubscriptionsServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * booting the service
	 */
	public function boot()
	{
		$this->package('ipunkt/subscriptions');

		/** @var \Illuminate\Config\Repository $config */
		$config = $this->app['config'];

		$this->app->bind('Ipunkt\Subscriptions\Plans\PlanRepository', function () use ($config) {
			$repository = new PlanRepository($config->get('subscriptions::plans'));
			$repository->setDefaultPlan($config->get('subscriptions::defaults.plan'));

			return $repository;
		});
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}
}