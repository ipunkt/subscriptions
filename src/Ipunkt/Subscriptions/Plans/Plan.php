<?php namespace Ipunkt\Subscriptions\Plans;

use Illuminate\Support\Collection;
use Illuminate\Support\Contracts\ArrayableInterface;

/**
 * Class Plan
 *
 * Plan entity
 *
 * @package Ipunkt\Subscriptions\Plans
 */
class Plan implements ArrayableInterface
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
	 * collection of plan payment options
	 *
	 * @var PaymentOption[]|Collection
	 */
	private $paymentOptions;

	/**
	 * setting a subscription break in days
	 *
	 * @var int
	 */
	private $subscriptionBreak = 0;

	/**
	 * @param string $id
	 * @param string $name
	 * @param string $description
	 * @param int $subscriptionBreak
	 */
	public function __construct($id, $name, $description, $subscriptionBreak = 0)
	{
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
		$this->subscriptionBreak = $subscriptionBreak;

		$this->benefits = new Collection();
		$this->paymentOptions = new Collection();
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
		$subscriptionBreak = array_get($planData, 'subscription_break', 0);
		$plan = new self($id, $planData['name'], $planData['description'], $subscriptionBreak);

		if (array_key_exists('benefits', $planData)) {
			$plan->addBenefits($planData['benefits']);
		}

		if (array_key_exists('payments', $planData)) {
			$plan->addPaymentOptions($planData['payments']);
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
	 * @return Collection|Benefit[]
	 */
	public function benefits()
	{
		return $this->benefits;
	}

	/**
	 * returns the collection of payment options
	 *
	 * @return Collection|PaymentOption[]
	 */
	public function paymentOptions()
	{
		return $this->paymentOptions;
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
	public function can($feature, $value = null)
	{
		$f = $this->feature($feature);

		return $f !== null && $f->can($value);
	}

	/**
	 * returns a feature from plan
	 *
	 * @param string $feature
	 *
	 * @return Benefit|null
	 */
	public function feature($feature)
	{
		return $this->benefits()->first(function ($key, Benefit $benefit) use ($feature) {
			return $benefit->feature() === strtoupper($feature);
		});
	}

	/**
	 * add benefits from array
	 *
	 * @param array $benefits
	 */
	private function addBenefits(array $benefits)
	{
		foreach ($benefits as $feature => $benefit) {
			$min = array_get($benefit, 'min', null);
			$max = array_get($benefit, 'max', null);

			$this->addBenefit(new Benefit($feature, $min, $max));
		}
	}

	/**
	 * adds a benefit to the list
	 *
	 * @param \Ipunkt\Subscriptions\Plans\Benefit $benefit
	 */
	private function addBenefit(Benefit $benefit)
	{
		$this->benefits->put($benefit->feature(), $benefit);
	}

	/**
	 * add payment options
	 *
	 * @param array $payments
	 */
	private function addPaymentOptions(array $payments)
	{
		$p = 0;
		foreach ($payments as $options) {
			$payment = array_get($options, 'payment', $p++);
			$price = array_get($options, 'price', 0.0);
			$quantity = array_get($options, 'quantity', 1);
			$interval = array_get($options, 'days', 1);
			$methods = array_get($options, 'methods', []);

			$this->addPaymentOption(new PaymentOption($payment, $price, $quantity, $interval, $methods));
		}
	}

	/**
	 * adds a payment option to the list
	 *
	 * @param \Ipunkt\Subscriptions\Plans\PaymentOption $paymentOption
	 */
	private function addPaymentOption(PaymentOption $paymentOption)
	{
		$this->paymentOptions->push($paymentOption);
	}

	/**
	 * finds payment option by identifier
	 *
	 * @param string $paymentOption
	 *
	 * @return PaymentOption|null
	 */
	public function findPaymentOption($paymentOption)
	{
		$paymentOption = strtoupper($paymentOption);

		return $this->paymentOptions()->first(function ($index, PaymentOption $p) use ($paymentOption) {
			if ($p->payment() == $paymentOption)
				return $p;
		});
	}

	/**
	 * is this plan a free plan
	 *
	 * @return bool
	 */
	public function isFree()
	{
		return $this->paymentOptions()->filter(function (PaymentOption $paymentOption) {
			return $paymentOption->price() != 0;
		})->isEmpty();
	}

	/**
	 * returns period sum
	 *
	 * @param PaymentOption $paymentOption
	 *
	 * @return float
	 */
	public function getPeriodSum(PaymentOption $paymentOption)
	{
		return $paymentOption->price() * $paymentOption->quantity();
	}

	/**
	 * returns the necessary subscription break in days
	 *
	 * @return int
	 */
	public function subscriptionBreak()
	{
		return $this->subscriptionBreak;
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return [
			'id' => $this->id(),
			'name' => $this->name(),
			'description' => $this->description(),
			'benefits' => $this->benefits()->toArray(),
			'paymentOptions' => $this->paymentOptions()->toArray(),
			'subscription_break' => $this->subscriptionBreak,
		];
	}

	/**
	 * compare plan with another plan by id or instance
	 *
	 * @param string|Plan $plan
	 *
	 * @return bool
	 */
	public function isEqual($plan)
	{
		if ($plan instanceof Plan)
			$plan = $plan->id();

		return strtoupper($plan) === $this->id();
	}

	/**
	 * magic accessor
	 *
	 * @param string $property
	 *
	 * @return null|mixed
	 */
	public function __get($property)
	{
		if (method_exists($this, $property))
			return $this->$property();

		if (property_exists($this, $property))
			return $this->$property;

		return null;
	}
}