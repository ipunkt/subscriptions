<?php namespace Ipunkt\Subscriptions\Plans;

use Illuminate\Support\Collection;

/**
 * Class Plan
 *
 * Plan entity
 *
 * @package Ipunkt\Subscriptions\Plans
 */
class Plan
{
	/**
	 * id
	 *
	 * @var string
	 */
	private $id;

	/**
	 * name
	 *
	 * @var string
	 */
	private $name;

	/**
	 * description
	 *
	 * @var string
	 */
	private $description;

	/**
	 * collection of plan benefits
	 *
	 * @var Benefit[]|Collection
	 */
	private $benefits;

	/**
	 * @param string $id
	 * @param string $name
	 * @param string $description
	 */
	public function __construct($id, $name, $description)
	{
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;

		$this->benefits = new Collection();
	}

	/**
	 * creates a new plan from array
	 *
	 * @param string $id
	 * @param array $planData
	 *
	 * @return \Ipunkt\Subscriptions\Plans\Plan
	 */
	public static function createFromArray($id, array $planData)
	{
		$plan = new self($id, $planData['name'], $planData['description']);

		if (array_key_exists('benefits', $planData)) {
			$plan->addBenefits($planData['benefits']);
		}

		return $plan;
	}

	/**
	 * returns Id
	 *
	 * @return string
	 */
	public function id()
	{
		return $this->id;
	}

	/**
	 * returns Name
	 *
	 * @return string
	 */
	public function name()
	{
		return $this->name;
	}

	/**
	 * returns Description
	 *
	 * @return string
	 */
	public function description()
	{
		return $this->description;
	}

	/**
	 * returns the collection of benefits
	 *
	 * @return Benefit[]|Collection
	 */
	public function benefits()
	{
		return $this->benefits;
	}

	/**
	 * check the availability for a plan benefit
	 * -> use value for countable feature checks
	 *
	 * @param string $feature
	 * @param null|int $value
	 *
	 * @return bool
	 */
	public function can($feature, $value = null) {
		$f = $this->benefits()->first(function ($key, Benefit $benefit) use ($feature) {
			return $benefit->feature() === strtoupper($feature);
		});

		return $f !== null && $f->can($value);
	}

	/**
	 * adds a benefit to the list
	 *
	 * @param \Ipunkt\Subscriptions\Plans\Benefit $benefit
	 *
	 * @return $this
	 */
	private function addBenefit(Benefit $benefit)
	{
		$this->benefits->push($benefit);

		return $this;
	}

	/**
	 * add benefits from array
	 *
	 * @param array $benefits
	 */
	private function addBenefits(array $benefits)
	{
		foreach ($benefits as $feature => $benefit)
		{
			$min = array_key_exists('min', $benefit) ? $benefit['min'] : null;
			$max = array_key_exists('max', $benefit) ? $benefit['max'] : null;

			$this->addBenefit(new Benefit($feature, $min, $max));
		}
	}
}