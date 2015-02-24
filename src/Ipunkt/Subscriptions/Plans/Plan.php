<?php namespace Ipunkt\Subscriptions\Plans;

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
	 * @param string $id
	 * @param string $name
	 * @param string $description
	 */
	public function __construct($id, $name, $description)
	{
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
	}

	/**
	 * creates a new plan from array
	 *
	 * @param string $id
	 * @param array $plan
	 *
	 * @return \Ipunkt\Subscriptions\Plans\Plan
	 */
	public static function createFromArray($id, array $plan)
	{
		$plan = new self($id, $plan['name'], $plan['description']);

		return $plan;
	}

	/**
	 * returns Id
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * returns Name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * returns Description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}
}