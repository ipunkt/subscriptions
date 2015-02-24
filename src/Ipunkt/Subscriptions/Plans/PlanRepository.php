<?php namespace Ipunkt\Subscriptions\Plans;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

/**
 * Class PlanRepository
 *
 * Repository for plans
 *
 * @package Ipunkt\Subscriptions\Plans
 */
class PlanRepository
{
	/**
	 * plans
	 *
	 * @var Plan[]|Collection
	 */
	private $plans;

	/**
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->plans = new Collection();

		$this->resolvePlans($config);
	}

	/**
	 * returns all plans
	 *
	 * @return array|Plan[]|Collection
	 */
	public function all()
	{
		return $this->plans->all();
	}

	public function find($id)
	{
		return $this->plans->first(function ($key, $value) use ($id) {
			return $id === $key;
		});
	}

	/**
	 * resolves all existing plans
	 *
	 * @param array $config
	 */
	private function resolvePlans(array $config)
	{
		foreach ($config as $id => $planData) {
			$plan = Plan::createFromArray($id, $planData);

			$this->plans->put($id, $plan);
		}
	}
}