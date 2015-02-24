<?php namespace Ipunkt\Subscriptions\Subscription\Events;

use Ipunkt\Subscriptions\Plans\PaymentOption;
use Ipunkt\Subscriptions\Plans\Plan;
use Ipunkt\Subscriptions\Subscription\Subscription;

/**
 * Class SubscriptionWasCreated
 *
 * Event was fired when subscription created
 *
 * @package Ipunkt\Subscriptions\Subscription\Events
 */
class SubscriptionWasCreated
{
	/**
	 * created subscription
	 *
	 * @var \Ipunkt\Subscriptions\Subscription\Subscription
	 */
	public $subscription;

	/**
	 * plan
	 *
	 * @var \Ipunkt\Subscriptions\Plans\Plan
	 */
	public $plan;

	/**
	 * payment option
	 *
	 * @var \Ipunkt\Subscriptions\Plans\PaymentOption
	 */
	public $paymentOption;

	/**
	 * @param \Ipunkt\Subscriptions\Subscription\Subscription $subscription
	 * @param \Ipunkt\Subscriptions\Plans\Plan $plan
	 * @param \Ipunkt\Subscriptions\Plans\PaymentOption $paymentOption
	 */
	public function __construct(Subscription $subscription, Plan $plan, PaymentOption $paymentOption)
	{
		$this->subscription = $subscription;
		$this->plan = $plan;
		$this->paymentOption = $paymentOption;
	}
}