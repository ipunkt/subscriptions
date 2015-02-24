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


## Usage

### Getting all plans

	/** @var Plan[] $plans */
	$plans = Subscription::plans();

### Getting the current plan

	/** @var Plan|null $plan */
	$plan = Subscription::plan();

### Each plan can have benefits (features)

	$plan->can('feature');               // returns true or false
	$plan->can('countable-feature', 14); // returns true or false
