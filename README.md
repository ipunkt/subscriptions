# Subscription handling package for Laravel 4.x applications

[![Latest Stable Version](https://poser.pugx.org/ipunkt/subscriptions/v/stable.svg)](https://packagist.org/packages/ipunkt/subscriptions) [![Latest Unstable Version](https://poser.pugx.org/ipunkt/subscriptions/v/unstable.svg)](https://packagist.org/packages/ipunkt/subscriptions) [![License](https://poser.pugx.org/ipunkt/subscriptions/license.svg)](https://packagist.org/packages/ipunkt/subscriptions) [![Total Downloads](https://poser.pugx.org/ipunkt/subscriptions/downloads.svg)](https://packagist.org/packages/ipunkt/subscriptions)

## Installation

Add to your composer.json following lines

	"require": {
		"ipunkt/subscriptions": "dev-master"
	}

Run `php artisan config:publish ipunkt/subscriptions`

Then edit `plans.php` in `app/config/packages/ipunkt/subscriptions` to your needs. All known plans are still in there.

Add `'Ipunkt\Subscriptions\SubscriptionsServiceProvider',` to `providers` in `app/config/app.php`.

Add `'Subscription' => 'Ipunkt\Subscriptions\SubscriptionsFacade',` to `aliases` in `app/config/app.php`.

Run `php artisan migrate --package=ipunkt/subscriptions` to migrate the necessary database tables.

## Configuration

### Plan configuration

	//  @see src/config/plans.php
	return [
    	'PLAN-ID' => [
    		'name' => 'TRIAL',
    		'description' => 'Trial subscription.',
    	],
    ];

#### Benefit configuration for a plan

	//  @see src/config/plans.php
	return [
    	'PLAN-ID' => [
    		// [..]    		
			'benefits' => [
				'feature-1' => [],  // feature is present
				'feature-2-countable' => [
					'min' => 10,    // feature is present and has margins/countable range
				],
				'feature-3-countable' => [
					'min' => 10,
					'max' => 50,
				],
				'feature-4-countable' => [
					'max' => 50,    // min is automatically 0 (zero)
				],
			],
    	],
    ];

#### Payment options for a plan

	//  @see src/config/plans.php
    return [
        'PLAN-ID' => [
            // [..]    		
            'payments' => [
                [
                    'price' => 1,           // for 1.00
                    'quantity' => 12,       // in 12-times
                    'days' => 30,           // of 30-days
                    'methods' => ['paypal'], // allowed payment methods
                ],
                [
                    'price' => 2,           // for 2.00
                    'quantity' => 12,       // in 12-times
                    'days' => 30,           // of 30-days
                    'methods' => ['paypal', 'invoice'],
                ],
            ],
        ],
    ];

#### Choosing a default plan for all subscribers

For setting a default plan to all subscribers you can use the `src/config/defaults.php` and set the id for the default
 plan. So every call on plan-based feature checking will resolve this default plan when the subscriber has no plan yet.

## Usage

### Getting all plans

	/** @var Plan[] $plans */
	$plans = Subscription::plans();

### Getting the current plan for a subscriber

	/** @var Plan|null $plan */
	$plan = Subscription::plan($subscriber);

### Does a subscription already exists for a subscriber

	Subscription::exists($subscriber); // returns true when a subscription exists

### Each plan can have benefits (features)

	$plan->can('feature');               // returns true or false
	$plan->can('countable-feature', 14); // returns true or false

Or use the `Subscription` facade instead to check against current subscription plan for a subscriber. This is recommended:

	Subscription::can($subscriber, 'feature');               // returns true or false
	Subscription::can($subscriber, 'countable-feature', 14); // returns true or false

### Getting all possible payment options for a plan

	/** @var PaymentOption[] $paymentOptions */
	$paymentOptions = $plan->paymentOptions();

### Creating a new subscription

	/** creating a subscription for a subscriber, maybe the current authenticated user */
	$subscription = Subscription::create($plan, $paymentOption, SubscriptionSubscriber $subscriber);

For creating a subscription you have to give the `Plan` or the id of a plan and the selected `PaymentOption` 
 or the identifier for the payment option.
The `$subscriber` is the entity the subscription belongs to. This can be any morphable eloquent object.

After a subscription was created successfully an event of type
 `Ipunkt\Subscriptions\Subscription\Events\SubscriptionWasCreated` gets fired.

The underlying repository controls for duplicates itself. So for existing subscriptions it will update the
 current subscription and fires an event of type `Ipunkt\Subscriptions\Subscription\Events\SubscriptionWasUpdated` 
 instead.

You can upgrade the subscription to any other plan. The same method `Subscription::create()` handles this upgrade.

The fired events have both the current subscription, the selected plan and the payment option as properties.
 So you can listen on these events and do your own stuff.

### Getting the current subscription for a subscriber

	/** @var Subscription|null $subscription */
	$subscription = Subscription::current($subscriber);

### Check subscriber on a Trial

	/** be careful because current() can return null when no subscription existing */
	$onTrial = Subscription::current($subscriber)->onTrial();

### Check subscription paid

	$subscription = Subscription::current($subscriber);
	$isPaid = $subscription->paid(); // or Subscription::paid($subscriber);

### Getting all periods for a subscription

	/** @var Period[] $periods */
	$periods = $subscription->periods;
