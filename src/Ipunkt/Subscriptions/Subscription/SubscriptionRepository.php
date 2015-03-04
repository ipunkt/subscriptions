<?php namespace Ipunkt\Subscriptions\Subscription;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Ipunkt\Subscriptions\Plans\PaymentOption;
use Ipunkt\Subscriptions\Plans\Plan;
use Ipunkt\Subscriptions\Subscription\Contracts\SubscriptionSubscriber;
use Ipunkt\Subscriptions\Subscription\Events\SubscriptionWasCreated;
use Ipunkt\Subscriptions\Subscription\Events\SubscriptionWasUpdated;

/**
 * Class SubscriptionRepository
 *
 * Repository for accessing the subscriptions
 *
 * @package Ipunkt\Subscriptions\Subscription
 */
class SubscriptionRepository
{
	const MODE_CREATE = 'create';
	const MODE_UPDATE = 'update';

	/**
	 * subscription model
	 *
	 * @var \Ipunkt\Subscriptions\Subscription\Subscription
	 */
	private $subscription;

	/**
	 * @param \Ipunkt\Subscriptions\Subscription\Subscription $subscription
	 */
	public function __construct(Subscription $subscription)
	{
		$this->subscription = $subscription;
	}

	/**
	 * creates a new subscription (or updates an existing one)
	 *
	 * @param \Ipunkt\Subscriptions\Plans\Plan $plan
	 * @param \Ipunkt\Subscriptions\Plans\PaymentOption $paymentOption
	 * @param \Ipunkt\Subscriptions\Subscription\Contracts\SubscriptionSubscriber $subscriber
	 *
	 * @return \Ipunkt\Subscriptions\Subscription\Subscription
	 */
	public function create(Plan $plan, PaymentOption $paymentOption, SubscriptionSubscriber $subscriber)
	{
		$lastSubscription = $this->allBySubscriber($subscriber)->last();
		if (null !== $lastSubscription && $lastSubscription->subscription_ends_at->isPast())
			$lastSubscription = null;

		$startDate = (null === $lastSubscription) ? Carbon::now() : $lastSubscription->subscription_ends_at;

		$subscription = Subscription::firstOrNew([
			'model_id' => $subscriber->getSubscriberId(),
			'model_class' => $subscriber->getSubscriberModel(),
		]);

		if ($subscription->exists && $subscription->plan != $plan->id())
			return $this->upgrade($subscription, $plan, $paymentOption, $subscriber);

		$subscription->plan = $plan->id();

		$this->subscription = $this->saveSubscription($subscription, $plan, $paymentOption, $startDate, self::MODE_CREATE);

		return $this->subscription;
	}

	/**
	 * upgrading an existing subscription
	 *
	 * @param \Ipunkt\Subscriptions\Subscription\Subscription $subscription
	 * @param \Ipunkt\Subscriptions\Plans\Plan $plan
	 * @param \Ipunkt\Subscriptions\Plans\PaymentOption $paymentOption
	 * @param \Ipunkt\Subscriptions\Subscription\Contracts\SubscriptionSubscriber $subscriber
	 *
	 * @return \Ipunkt\Subscriptions\Subscription\Subscription
	 */
	public function upgrade(Subscription $subscription, Plan $plan, PaymentOption $paymentOption, SubscriptionSubscriber $subscriber)
	{
		$lastSubscription = $this->allBySubscriber($subscriber)->last();
		if (null !== $lastSubscription && $lastSubscription->subscription_ends_at->isPast())
			$lastSubscription = null;

		$startDate = (null === $lastSubscription) ? $subscription->subscription_ends_at : $lastSubscription->subscription_ends_at;

		if ($startDate->isPast())
			$startDate = Carbon::now();

		$subscription->model_id = $subscriber->getSubscriberId();
		$subscription->model_class = $subscriber->getSubscriberModel();
		$subscription->plan = $plan->id();

		$subscriptionData = $subscription->toArray();
		if (isset($subscriptionData['created_at'])) unset($subscriptionData['created_at']);
		if (isset($subscriptionData['updated_at'])) unset($subscriptionData['updated_at']);

		$newSubscription = Subscription::firstOrNew($subscriptionData);

		return $this->saveSubscription($newSubscription, $plan, $paymentOption, $startDate, self::MODE_UPDATE);
	}

	/**
	 * returns newest subscription for subscriber
	 *
	 * @param SubscriptionSubscriber $subscriber
	 *
	 * @return \Ipunkt\Subscriptions\Subscription\Subscription|null
	 */
	public function findBySubscriber(SubscriptionSubscriber $subscriber)
	{
		return $this->subscription->whereModelId($subscriber->getSubscriberId())
			->whereModelClass($subscriber->getSubscriberModel())
			->where('subscription_ends_at', '>=', Carbon::now())
			->orderBy('id', 'asc')
			->first();
	}

	/**
	 * returns ordered collection of all subscriptions for a subscriber
	 *
	 * @param \Ipunkt\Subscriptions\Subscription\Contracts\SubscriptionSubscriber $subscriber
	 *
	 * @return array|static[]|Subscription[]|Collection
	 */
	public function allBySubscriber(SubscriptionSubscriber $subscriber)
	{
		return $this->subscription->whereModelId($subscriber->getSubscriberId())
			->whereModelClass($subscriber->getSubscriberModel())
			->orderBy('id')
			->get();
	}

	/**
	 * returns all subscriptions for given plans for a subscriber
	 *
	 * @param SubscriptionSubscriber $subscriber
	 * @param array $plans
	 *
	 * @return array|static[]|Subscription[]|Collection
	 */
	public function allBySubscriberForPlans(SubscriptionSubscriber $subscriber, array $plans)
	{
		return $this->subscription->whereModelId($subscriber->getSubscriberId())
			->whereModelClass($subscriber->getSubscriberModel())
			->whereIn('plan', $plans)
			->orderBy('id')
			->get();
	}

	/**
	 * saves a subscription to database
	 *
	 * @param \Ipunkt\Subscriptions\Subscription\Subscription $subscription
	 * @param \Ipunkt\Subscriptions\Plans\Plan $plan
	 * @param \Ipunkt\Subscriptions\Plans\PaymentOption $paymentOption
	 * @param \Carbon\Carbon $startDate
	 * @param string $mode
	 *
	 * @return \Ipunkt\Subscriptions\Subscription\Subscription
	 */
	private function saveSubscription(Subscription $subscription, Plan $plan, PaymentOption $paymentOption, Carbon $startDate = null, $mode)
	{
		if ($startDate === null)
			$startDate = Carbon::now();

		$subscription->trial_ends_at = with(clone $startDate)->addDays($paymentOption->period());
		$subscription->subscription_ends_at = with(clone $startDate)->addDays($paymentOption->days());

		if ($subscription->save()) {

			$period = new Period([
				'start' => $startDate,
				'end' => $subscription->subscription_ends_at,
				'invoice_sum' => $plan->getPeriodSum($paymentOption),
				'invoice_date' => Carbon::now(),
				'state' => Period::STATE_UNPAID,
			]);
			$subscription->periods()->save($period);

			$event = ($mode === self::MODE_CREATE)
				? new SubscriptionWasCreated($subscription, $plan, $paymentOption)
				: new SubscriptionWasUpdated($subscription, $plan, $paymentOption);

			$subscription->raise($event);

			if ($plan->isFree()) {
				$period->markAsPaid('');

				//  assign period events to be raised via the subscription raising method
				$events = $period->releaseEvents();
				foreach ($events as $event)
					$subscription->raise($event);
			}
		}

		return $subscription;
	}
}