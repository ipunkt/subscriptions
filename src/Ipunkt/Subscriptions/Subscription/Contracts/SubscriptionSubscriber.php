<?php namespace Ipunkt\Subscriptions\Subscription\Contracts;

/**
 * Interface SubscriptionSubscriber
 *
 * @package Ipunkt\Subscriptions\Subscription\Contracts
 */
interface SubscriptionSubscriber
{
    /**
     * returns the subscriber id
     *
     * @return int
     */
    public function getSubscriberId(): int;

    /**
     * returns the subscriber model name
     *
     * @return string
     */
    public function getSubscriberModel(): string;
}
