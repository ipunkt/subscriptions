<?php namespace Ipunkt\Subscriptions\Subscription\Events;

use Ipunkt\Subscriptions\Subscription\Period;

/**
 * Class SubscriptionWasPaid
 *
 * Event will be fired when a subscription was paid (a period was paid)
 *
 * @package Ipunkt\Subscriptions\Subscription\Events
 */
class SubscriptionWasPaid
{
    /**
     * paid period
     *
     * @var \Ipunkt\Subscriptions\Subscription\Period
     */
    public $period;

    /**
     * paid subscription
     *
     * @var \Ipunkt\Subscriptions\Subscription\Subscription
     */
    public $subscription;

    /**
     * @param \Ipunkt\Subscriptions\Subscription\Period $period
     */
    public function __construct(Period $period)
    {
        $this->period = $period;
        $this->subscription = $period->subscription;
    }
}
