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

	public function boot()
	{
		$this->package('ipunkt/subscriptions');

		/** @var \Illuminate\Config\Repository $config */
		$config = $this->app['config'];

		$this->app->bind('SubscriptionManager', function () use ($config) {

			$planRepository = new PlanRepository($config->get('subscriptions::plans'));

			$subscriptionManager = new SubscriptionManager();
			$subscriptionManager->setPlanRepository($planRepository);

			return $subscriptionManager;
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