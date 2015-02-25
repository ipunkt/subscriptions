<?php namespace Ipunkt\Subscriptions\Subscription;

use Carbon\Carbon;
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
		$subscription = Subscription::firstOrNew([
			'model_id' => $subscriber->getSubscriberId(),
			'model_class' => $subscriber->getSubscriberModel(),
		]);

		if ($subscription->exists && $subscription->plan != $plan->id())
			return $this->upgrade($subscription, $plan, $paymentOption, $subscriber);

		$subscription->plan = $plan->id();

		$this->subscription = $this->saveSubscription($subscription, $plan, $paymentOption);

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
		$subscription->model_id = $subscriber->getSubscriberId();
		$subscription->model_class = $subscriber->getSubscriberModel();
		$subscription->plan = $plan->id();

		$subscriptionData = $subscription->toArray();
		if (isset($subscriptionData['created_at'])) unset($subscriptionData['created_at']);
		if (isset($subscriptionData['updated_at'])) unset($subscriptionData['updated_at']);

		$newSubscription = Subscription::firstOrNew($subscriptionData);

		return $this->saveSubscription($newSubscription, $plan, $paymentOption, $subscription->subscription_ends_at);
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
	 * @return array|static[]|Subscription[]
	 */
	public function allBySubscriber(SubscriptionSubscriber $subscriber)
	{
		return $this->subscription->whereModelId($subscriber->getSubscriberId())
			->whereModelClass($subscriber->getSubscriberModel())
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
	 *
	 * @return \Ipunkt\Subscriptions\Subscription\Subscription
	 */
	private function saveSubscription(Subscription $subscription, Plan $plan, PaymentOption $paymentOption, Carbon $startDate = null)
	{
		$create = false;
		if ($startDate === null) {
			$create = true;
			$startDate = Carbon::now();
		}

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

			$event = $create
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