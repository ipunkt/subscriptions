# Subscription handling package for Laravel 4.x applications

[![Latest Stable Version](https://poser.pugx.org/ipunkt/subscriptions/v/stable.svg)](https://packagist.org/packages/ipunkt/subscriptions) [![Latest Unstable Version](https://poser.pugx.org/ipunkt/subscriptions/v/unstable.svg)](https://packagist.org/packages/ipunkt/subscriptions) [![License](https://poser.pugx.org/ipunkt/subscriptions/license.svg)](https://packagist.org/packages/ipunkt/subscriptions) [![Total Downloads](https://poser.pugx.org/ipunkt/subscriptions/downloads.svg)](https://packagist.org/packages/ipunkt/subscriptions)

## Installation

Add to your composer.json following lines

	"require": {
		"ipunkt/subscriptions": "dev-master"
	}

Run `php artisan config:publish ipunkt/subscriptions`

Add `'Ipunkt\Subscriptions\SubscriptionsServiceProvider',` to `providers` in `app/config/app.php`.

Add `'Subscription' => 'Ipunkt\Subscriptions\SubscriptionsFacade',` to `aliases` in `app/config/app.php`.

