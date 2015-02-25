<?php namespace Ipunkt\Subscriptions;

use Ipunkt\Subscriptions\Plans\PaymentOption;
use Ipunkt\Subscriptions\Plans\Plan;
use Ipunkt\Subscriptions\Plans\PlanRepository;
use Ipunkt\Subscriptions\Subscription\Contracts\SubscriptionSubscriber;
use Ipunkt\Subscriptions\Subscription\Subscription;
use Ipunkt\Subscriptions\Subscription\SubscriptionRepository;

/**
 * Class SubscriptionManager
 *
 * Subscription manager handles all subscription stuff
 *
 * @package Ipunkt\Subscriptions
 */
class SubscriptionManager
{
	/**
	 * plan repository
	 *
	 * @var PlanRepository
	 */
	private $planRepository;

	/**
	 * subscriptions repository
	 *
	 * @var SubscriptionRepository
	 */
	private $subscriptionRepository;

	/**
	 * current plan
	 *
	 * @var Subscription
	 */
	private $subscription;

	/**
	 * @param PlanRepository $planRepository
	 * @param SubscriptionRepository $subscriptionRepository
	 */
	public function __construct(PlanRepository $planRepository, SubscriptionRepository $subscriptionRepository)
	{
		$this->planRepository = $planRepository;
		$this->subscriptionRepository = $subscriptionRepository;
	}

	/**
	 * does a subscription already exists
	 *
	 * @param SubscriptionSubscriber $subscriber
	 *
	 * @return bool
	 */
	public function exists(SubscriptionSubscriber $subscriber)
	{
		return $this->plan($subscriber) !== null;
	}

	/**
	 * returns all configured plans
	 *
	 * @return array|Plan[]
	 */
	public function plans()
	{
		return $this->planRepository->all();
	}

	/**
	 * returns the current plan
	 *
	 * @param SubscriptionSubscriber $subscriber
	 *
	 * @return \Ipunkt\Subscriptions\Plans\Plan|null
	 */
	public function plan(SubscriptionSubscriber $subscriber)
	{
		$subscription = $this->current($subscriber);
		if (null === $subscription)
			return null;

		$plan = $subscription->plan;
		return $this->planRepository->find($plan);
	}

	/**
	 * tries to find a plan
	 *
	 * @param string $plan
	 *
	 * @return Plan|null
	 */
	public function findPlan($plan)
	{
		return $this->planRepository->find($plan);
	}

	/**
	 * feature check on the current subscription
	 *
	 * @param SubscriptionSubscriber $subscriber
	 * @param string $feature
	 * @param null|int $value
	 *
	 * @return bool
	 */
	public function can(SubscriptionSubscriber $subscriber, $feature, $value = null)
	{
		$currentPlan = $this->plan($subscriber);
		if (null === $currentPlan)
			return false;

		return $currentPlan->can($feature, $value);
	}

	/**
	 * returns current subscription for subscriber
	 *
	 * @param SubscriptionSubscriber $subscriber
	 *
	 * @return \Ipunkt\Subscriptions\Subscription\Subscription|null
	 */
	public function current(SubscriptionSubscriber $subscriber)
	{
		if ($this->subscription === null || ! $this->subscription->isSubscribedTo($subscriber)) {
			$subscription = $this->subscriptionRepository->findBySubscriber($subscriber);
			if (null === $subscription)
				return null;

			$this->subscription = $subscription;
		}

		return $this->subscription;
	}

	/**
	 * creates a subscription with plan and payment option
	 *
	 * @param string|Plan $plan
	 * @param string|PaymentOption $paymentOption
	 * @param SubscriptionSubscriber $subscriber
	 *
	 * @return \Ipunkt\Subscriptions\Subscription\Subscription
	 */
	public function create($plan, $paymentOption, SubscriptionSubscriber $subscriber)
	{
		if ( ! $plan instanceof Plan)
			$plan = $this->findPlan($plan);

		if ( ! $paymentOption instanceof PaymentOption)
			$paymentOption = $plan->findPaymentOption($paymentOption);

		return $this->subscriptionRepository->create($plan, $paymentOption, $subscriber);
	}

	/**
	 * is the current period subscription paid
	 *
	 * @return bool
	 */
	public function paid()
	{
		if (null === $this->subscription)
			return false;

		return $this->subscription->paid();
	}
}