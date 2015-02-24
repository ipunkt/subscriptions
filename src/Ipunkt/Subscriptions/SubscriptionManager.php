<?php namespace Ipunkt\Subscriptions;

use Ipunkt\Subscriptions\Plans\Plan;
use Ipunkt\Subscriptions\Plans\PlanRepository;

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
	 * setting a plan repository
	 *
	 * @param PlanRepository $planRepository
	 */
	public function setPlanRepository(PlanRepository $planRepository)
	{
		$this->planRepository = $planRepository;
	}

	/**
	 * does a subscription already exists
	 *
	 * @return bool
	 */
	public function exists()
	{
		return $this->plan() !== null;
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
	 * @return Plan|null
	 */
	public function plan()
	{
		return $this->planRepository->find('');
	}

	/**
	 * feature check on the current subscription
	 *
	 * @param string $feature
	 * @param null|int $value
	 *
	 * @return bool
	 */
	public function can($feature, $value = null)
	{
		$currentPlan = $this->plan();
		if (null === $currentPlan)
			return false;

		return $currentPlan->can($feature, $value);
	}
}