<?php namespace Ipunkt\Subscriptions\Plans;

/**
 * Class PaymentOption
 *
 * Payment option for a plan
 *
 * @package Ipunkt\Subscriptions\Plans
 */
class PaymentOption
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
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * returns Quantity
	 *
	 * @return int
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}

	/**
	 * returns Interval
	 *
	 * @return string
	 */
	public function getInterval()
	{
		return $this->interval;
	}
}