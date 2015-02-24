<?php namespace Ipunkt\Subscriptions\Subscription;

/**
 * Class SubscriptionRepository
 *
 * Repository for accessing the subscriptions
 *
 * @package Ipunkt\Subscriptions\Subscription
 */
class SubscriptionRepository
{
	/**
	 * subscription model
	 *
	 * @var \Ipunkt\Subscriptions\Subscription\Subscription
	 */
	private $subscription;

	/**
	 * @param \Ipunkt\Subscriptions\Subscription\Subscription $subscription
	 */
	public function __construct(Subscription $subscription)
	{
		$this->subscription = $subscription;
	}
}