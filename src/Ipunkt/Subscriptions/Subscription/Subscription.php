<?php namespace Ipunkt\Subscriptions\Subscription;

use Illuminate\Database\Eloquent\Model;
use Ipunkt\Subscriptions\Subscription\Contracts\SubscriptionSubscriber;
use Laracasts\Commander\Events\EventGenerator;

/**
 * Class Subscription
 *
 * Subscription entity
 *
 * @property integer $id
 * @property integer $model_id
 * @property string $model_class
 * @property string $plan
 * @property \Carbon\Carbon $trial_ends_at
 * @property \Carbon\Carbon $subscription_ends_at
 * @property string $state
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|Subscription whereId($value)
 * @method static \Illuminate\Database\Query\Builder|Subscription whereModelId($value)
 * @method static \Illuminate\Database\Query\Builder|Subscription whereModelClass($value)
 * @method static \Illuminate\Database\Query\Builder|Subscription wherePlan($value)
 * @method static \Illuminate\Database\Query\Builder|Subscription whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Query\Builder|Subscription whereSubscriptionEndsAt($value)
 * @method static \Illuminate\Database\Query\Builder|Subscription whereState($value)
 * @property-read \Illuminate\Database\Eloquent\Model $subscriber
 * @property-read \Ipunkt\Subscriptions\Subscription\Period[] $periods
 *
 * @package Ipunkt\Subscriptions\Subscription
 */
class Subscription extends Model
{
	use EventGenerator;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'model_id',
		'model_class',
		'plan',
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['trial_ends_at', 'subscription_ends_at'];

	/**
	 * returns subscriber
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphTo
	 */
	public function subscriber() {
		return $this->morphTo('Company', 'model_class', 'model_id');
	}

	/**
	 * related periods
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function periods()
	{
		return $this->hasMany('\Ipunkt\Subscriptions\Subscription\Period');
	}

	/**
	 * is the subscription subscribed to the given subscriber
	 *
	 * @param SubscriptionSubscriber $subscriber
	 *
	 * @return bool
	 */
	public function isSubscribedTo(SubscriptionSubscriber $subscriber)
	{
		return $this->model_id == $subscriber->getSubscriberId()
			&& $this->model_class == $subscriber->getSubscriberModel();
	}

	/**
	 * are we on trial period
	 *
	 * @return bool
	 */
	public function onTrial()
	{
		return $this->trial_ends_at->isFuture();
	}
}