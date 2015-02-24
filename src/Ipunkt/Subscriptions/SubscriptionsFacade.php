<?php namespace Ipunkt\Subscriptions;

use Illuminate\Support\Facades\Facade;

/**
 * Class SubscriptionsFacade
 *
 *
 *
 * @package Ipunkt\Subscriptions
 */
class SubscriptionsFacade extends Facade
{
	/**
	 * facade accessor
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'SubscriptionManager';
	}
}