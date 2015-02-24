<?php namespace Ipunkt\Subscriptions\Subscription;

use Illuminate\Database\Eloquent\Model;

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
 *
 * @package Ipunkt\Subscriptions\Subscription
 */
class Subscription extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'model_id',
		'model_class',
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['trial_ends_at', 'subscription_ends_at'];
}