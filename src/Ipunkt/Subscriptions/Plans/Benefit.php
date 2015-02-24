<?php namespace Ipunkt\Subscriptions\Plans;

use Illuminate\Support\Contracts\ArrayableInterface;

/**
 * Class Benefit
 *
 * Benefit entity
 *
 * @package Ipunkt\Subscriptions\Plans
 */
class Benefit implements ArrayableInterface
{
	/**
	 * benefit feature
	 *
	 * @var string
	 */
	private $feature;

	/**
	 * minimum value on countable features
	 *
	 * @var int
	 */
	private $min = 0;

	/**
	 * maximum value on countable features
	 *
	 * @var null|int
	 */
	private $max = null;

	/**
	 * @param string $feature
	 * @param null|int $min
	 * @param null|int $max
	 */
	public function __construct($feature, $min = null, $max = null)
	{
		$this->feature = strtoupper($feature);

		if (null !== $min)
			$this->min = $min + 0;

		if (null !== $max)
			$this->max = $max + 0;
	}

	/**
	 * returns Feature
	 *
	 * @return string
	 */
	public function feature()
	{
		return $this->feature;
	}

	/**
	 * is the feature available
	 *
	 * @param null|int $value
	 *
	 * @return bool
	 */
	public function can($value = null)
	{
		//	when no value given, simply return true, because feature exists
		if (null === $value)
			return true;

		//  unlimited feature
		if (null === $this->max)
			return $this->min <= $value;

		return $this->min <= $value && $this->max >= $value;
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return [
			'feature' => $this->feature(),
			'min' => $this->min,
			'max' => $this->max,
		];
	}
}