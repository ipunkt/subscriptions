<?php namespace Ipunkt\Subscriptions;

use Illuminate\Support\Collection;
use Ipunkt\Subscriptions\Plans\PaymentOption;
use Ipunkt\Subscriptions\Plans\Plan;
use Ipunkt\Subscriptions\Plans\PlanRepository;
use Ipunkt\Subscriptions\Subscription\Contracts\SubscriptionSubscriber;
use Ipunkt\Subscriptions\Subscription\Subscription;
use Ipunkt\Subscriptions\Subscription\SubscriptionRepository;

/**
 * Class SubscriptionManager
 *
 * Subscription manager handles all subscription stuff
 *
 * @package Ipunkt\Subscriptions
 */
class SubscriptionManager
{
    /**
     * plan repository
     *
     * @var PlanRepository
     */
    private $planRepository;

    /**
     * subscriptions repository
     *
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;

    /**
     * current plan
     *
     * @var Subscription
     */
    private $subscription;

    /**
     * @param PlanRepository $planRepository
     * @param SubscriptionRepository $subscriptionRepository
     */
    public function __construct(PlanRepository $planRepository, SubscriptionRepository $subscriptionRepository)
    {
        $this->planRepository = $planRepository;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * does a subscription already exists
     *
     * @param SubscriptionSubscriber $subscriber
     *
     * @return bool
     */
    public function exists(SubscriptionSubscriber $subscriber): bool
    {
        return $this->plan($subscriber, false) !== null;
    }

    /**
     * returns all configured plans
     *
     * @return array|Plan[]|Collection
     */
    public function plans()
    {
        return $this->planRepository->all();
    }

    /**
     * returns the current plan
     *
     * @param SubscriptionSubscriber $subscriber
     * @param bool $useDefaultPlan
     *
     * @return Plan|null
     */
    public function plan(SubscriptionSubscriber $subscriber, $useDefaultPlan = true): ?Plan
    {
        $subscription = $this->current($subscriber);
        if (null === $subscription) {
            return $useDefaultPlan
                ? $this->planRepository->defaultPlan()
                : null;
        }

        $plan = $subscription->plan;

        return $this->planRepository->find($plan);
    }

    /**
     * tries to find a plan
     *
     * @param string $plan
     *
     * @return Plan|null
     */
    public function findPlan($plan): ?Plan
    {
        return $this->planRepository->find($plan);
    }

    /**
     * feature check on the current subscription
     *
     * @param SubscriptionSubscriber $subscriber
     * @param string $feature
     * @param null|int $value
     *
     * @return bool
     */
    public function can(SubscriptionSubscriber $subscriber, $feature, $value = null): bool
    {
        $currentPlan = $this->plan($subscriber);
        if (null === $currentPlan) {
            return false;
        }

        return $currentPlan->can($feature, $value);
    }

    /**
     * returns a feature when it exists on the current plan or null when missing
     *
     * @param SubscriptionSubscriber $subscriber
     * @param string $feature
     *
     * @return \Ipunkt\Subscriptions\Plans\Benefit|null
     */
    public function feature(SubscriptionSubscriber $subscriber, $feature)
    {
        $currentPlan = $this->plan($subscriber);
        if (null === $currentPlan) {
            return null;
        }

        return $currentPlan->feature($feature);
    }

    /**
     * returns current subscription for subscriber
     *
     * @param SubscriptionSubscriber $subscriber
     *
     * @return Subscription|null
     */
    public function current(SubscriptionSubscriber $subscriber): ?Subscription
    {
        if ($this->subscription === null || !$this->subscription->isSubscribedTo($subscriber)) {
            $subscription = $this->subscriptionRepository->findBySubscriber($subscriber);
            if (null === $subscription) {
                return null;
            }

            $this->subscription = $subscription;
        }

        return $this->subscription;
    }

    /**
     * returns all subscriptions for a subscriber
     *
     * @param SubscriptionSubscriber $subscriber
     *
     * @return array|Subscription[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all(SubscriptionSubscriber $subscriber)
    {
        return $this->subscriptionRepository->allBySubscriber($subscriber);
    }

    /**
     * creates a subscription with plan and payment option
     *
     * @param string|Plan $plan
     * @param string|PaymentOption $paymentOption
     * @param SubscriptionSubscriber $subscriber
     *
     * @return Subscription
     */
    public function create($plan, $paymentOption, SubscriptionSubscriber $subscriber): ?Subscription
    {
        if (!$plan instanceof Plan) {
            $plan = $this->findPlan($plan);
        }

        if (!$paymentOption instanceof PaymentOption) {
            $paymentOption = $plan->findPaymentOption($paymentOption);
        }

        return $this->subscriptionRepository->create($plan, $paymentOption, $subscriber);
    }

    /**
     * is the current period subscription paid
     *
     * @param SubscriptionSubscriber $subscriber
     *
     * @return bool
     */
    public function paid(SubscriptionSubscriber $subscriber): bool
    {
        $this->current($subscriber);

        if (null === $this->subscription) {
            return false;
        }

        return $this->subscription->paid();
    }

    /**
     * returns only selectable plans
     *
     * @param SubscriptionSubscriber $subscriber
     *
     * @return array|Collection|Plan[]
     */
    public function selectablePlans(SubscriptionSubscriber $subscriber)
    {
        $plans = $this->plans();

        $breakingPlans = $plans->filter(function (Plan $plan) {
            return $plan->subscriptionBreak() > 0;
        });

        if ($breakingPlans->isEmpty()) {
            return $plans;
        }

        $subscriptions = $this->subscriptionRepository->allBySubscriberForPlans($subscriber,
            $breakingPlans->lists('id'));
        foreach ($subscriptions as $subscription) {
            /** @var Plan $breakingPlan */
            $breakingPlan = $breakingPlans->get($subscription->plan);
            if ($subscription->subscription_ends_at->addDays($breakingPlan->subscriptionBreak())->isFuture()) {
                $plans->forget($subscription->plan);
            }
        }

        return $plans;
    }
}
