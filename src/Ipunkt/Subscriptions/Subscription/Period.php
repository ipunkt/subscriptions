<?php namespace Ipunkt\Subscriptions\Subscription;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Ipunkt\Subscriptions\Subscription\Events\SubscriptionWasPaid;
use Laracasts\Commander\Events\EventGenerator;

/**
 * Class Period
 *
 * @property integer $id
 * @property integer $subscription_id
 * @property \Carbon\Carbon $start
 * @property \Carbon\Carbon $end
 * @property string $state
 * @property string $invoice_reference
 * @property float $invoice_sum
 * @property \Carbon\Carbon $invoice_date
 * @property-read \Ipunkt\Subscriptions\Subscription\Subscription $subscription
 * @method static \Illuminate\Database\Query\Builder|\Ipunkt\Subscriptions\Subscription\Period whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\Ipunkt\Subscriptions\Subscription\Period
 *     whereSubscriptionId($value)
 * @method static \Illuminate\Database\Query\Builder|\Ipunkt\Subscriptions\Subscription\Period whereStart($value)
 * @method static \Illuminate\Database\Query\Builder|\Ipunkt\Subscriptions\Subscription\Period whereEnd($value)
 * @method static \Illuminate\Database\Query\Builder|\Ipunkt\Subscriptions\Subscription\Period whereState($value)
 * @method static \Illuminate\Database\Query\Builder|\Ipunkt\Subscriptions\Subscription\Period
 *     whereInvoiceReference($value)
 * @method static \Illuminate\Database\Query\Builder|\Ipunkt\Subscriptions\Subscription\Period whereInvoiceSum($value)
 * @method static \Illuminate\Database\Query\Builder|\Ipunkt\Subscriptions\Subscription\Period whereInvoiceDate($value)
 *
 * @package Ipunkt\Subscriptions\Subscription
 */
class Period extends Model
{
	CONST STATE_PAID = 'paid';
	const STATE_UNPAID = 'unpaid';

	use EventGenerator;

	/**
	 * used table name
	 *
	 * @var string
	 */
	protected $table = 'subscription_periods';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'start',
		'end',
		'state',
		'invoice_sum',
		'invoice_date',
	);

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * mutate to carbon instances
	 *
	 * @var array
	 */
	protected $dates = ['start', 'end', 'invoice_date'];

	/**
	 * is period paid
	 *
	 * @return bool
	 */
	public function isPaid()
	{
		return $this->state === self::STATE_PAID;
	}

	/**
	 * related subscription
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function subscription()
	{
		return $this->belongsTo('Ipunkt\Subscriptions\Subscription\Subscription');
	}

	/**
	 * marks the current period as being paid
	 *
	 * @param mixed $invoiceReference
	 * @param Carbon $invoiceDate
	 * @param null|float $invoiceSum
	 *
	 * @return $this
	 */
	public function markAsPaid($invoiceReference, Carbon $invoiceDate = null, $invoiceSum = null)
	{
		$this->invoice_reference = $invoiceReference;
		if (null === $invoiceDate)
			$invoiceDate = Carbon::now();

		$raiseEvent = ! $this->isPaid();

		$this->state = self::STATE_PAID;
		$this->invoice_date = $invoiceDate;
		if (null !== $invoiceSum && is_numeric($invoiceSum))
			$this->invoice_sum = $invoiceSum;

		if ($this->save() && $raiseEvent)
			$this->raise(new SubscriptionWasPaid($this));

		return $this;
	}
}