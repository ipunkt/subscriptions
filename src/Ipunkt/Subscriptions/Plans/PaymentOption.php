<?php namespace Ipunkt\Subscriptions\Plans;

use DateInterval;
use Illuminate\Support\Contracts\ArrayableInterface;

/**
 * Class PaymentOption
 *
 * Payment option for a plan
 *
 * @package Ipunkt\Subscriptions\Plans
 */
class PaymentOption implements ArrayableInterface
{
	/**
	 * payment
	 *
	 * @var string
	 */
	private $payment;

	/**
	 * price
	 *
	 * @var float
	 */
	private $price;

	/**
	 * quantity
	 *
	 * @var int
	 */
	private $quantity;

	/**
	 * interval
	 *
	 * @var string
	 */
	private $interval;

	/**
	 * @param string $payment
	 * @param float $price
	 * @param int $quantity
	 * @param string $interval
	 */
	public function __construct($payment, $price, $quantity = 1, $interval = 'P1M')
	{
		$this->payment = strtoupper($payment);
		$this->price = $price;
		$this->quantity = $quantity;
		$this->interval = $interval;
	}

	/**
	 * returns Payment
	 *
	 * @return string
	 */
	public function payment()
	{
		return $this->payment;
	}

	/**
	 * returns Price
	 *
	 * @return float
	 */
	public function price()
	{
		return $this->price;
	}

	/**
	 * returns Quantity
	 *
	 * @return int
	 */
	public function quantity()
	{
		return $this->quantity;
	}

	/**
	 * returns Interval
	 *
	 * @return DateInterval
	 */
	public function interval()
	{
		return new DateInterval($this->interval);
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return [
			'payment' => $this->payment(),
			'price' => $this->price(),
			'quantity' => $this->quantity(),
			'interval' => $this->interval,
		];
	}
}