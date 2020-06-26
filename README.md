# Subscription handling package for Laravel applications

[![Latest Stable Version](https://poser.pugx.org/ipunkt/subscriptions/v/stable.svg)](https://packagist.org/packages/ipunkt/subscriptions) [![Latest Unstable Version](https://poser.pugx.org/ipunkt/subscriptions/v/unstable.svg)](https://packagist.org/packages/ipunkt/subscriptions) [![License](https://poser.pugx.org/ipunkt/subscriptions/license.svg)](https://packagist.org/packages/ipunkt/subscriptions) [![Total Downloads](https://poser.pugx.org/ipunkt/subscriptions/downloads.svg)](https://packagist.org/packages/ipunkt/subscriptions)

## Installation

Add to your composer.json following lines

	"require": {
		"ipunkt/subscriptions": "^1.0"
	}

Run `php artisan vendor:publish ipunkt/subscriptions`

Then edit `plans.php` in `config/plans.php` to your needs. All known plans are still in there.

You can use the `Subscription` facade.

Run `php artisan migrate` to migrate the necessary database tables.

## Configuration

### Plan configuration

	//  @see src/config/plans.php
	return [
    	'PLAN-ID' => [
    		'name' => 'TRIAL',
    		'description' => 'Trial subscription.',
    		'subscription_break' => 0,  // optional for preventing a subscription for X days after last subscription ends
    	],
    ];

The optional property `'subscription_break` can be used to prevent a subscriber to subscribe to this plan before a 
 configured count of days will be gone. This is for example to have a TRIAL plan which can be subscribed to only once 
 a year.

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
	

If you use the subscription break in your plan configuration, fetch all plans with the `selectablePlans` method. This 
 checks the last subscription for each breaking plan.

	/** @var Plan[] $plans */
	$plans = Subscription::selectablePlans($this->user);

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


## Userland code

### Fitting in you controllers

We use the `laracasts/commander` package for handling business commands and events.

	class SubscriptionsController extends \Controller
	{
		/**
         * use commandbus to execute commands
         */
        use Laracasts\Commander\CommanderTrait;
        
        // display an overview of all subscriptions
        public function index()
        {
            $subscribed = Subscription::exists($this->user);// $this->user represents a SubscriptionSubscriber interface
            if ( ! $subscribed) {
                $plans = Subscription::selectablePlans($this->user);    // unselectable plans filtered out already
                $defaultPlan = Subscription::plan($this->user);
    
                return View::make('subscriptions.create', compact('plans', 'defaultPlan'));
            }
    
            $plan = Subscription::plan($this->user);
            $subscription = Subscription::current($this->user);
    
            $paid = $subscription->paid();
    
            $subscriptions = Subscription::all($this->user);
    
            return View::make('subscriptions.index', compact('subscribed', 'plan', 'subscription', 'subscriptions', 'paid'));
        }
        
        //  create a plan (form)
        public function create($plan)
        {
            $plan = Subscription::findPlan($plan);
    
            $subscription = Subscription::all($this->user)->last();
            if (null !== $subscription && $subscription->subscription_ends_at->isPast())
                $subscription = null;
    
            $startDate = (null === $subscription) ? Carbon::now() : $subscription->subscription_ends_at->addSeconds(1);
    
            return View::make('subscriptions.create_plan', compact('plan', 'startDate'));
        }
        
        //  store the plan as subscription for user
        public function store()
        {
            try {
                $this->validate(Input::all());
            } catch (FormValidationException $e)
            {
                return Redirect::back()->withInput()->withErrors($e->getErrors());
            }
    
            $plan = Subscription::findPlan(Input::get('plan'));
            if (null === $plan)
                throw (new ModelNotFoundException('No plan ' . Input::get('plan') . ' found.'))->setModel(Plan::class);
    
            $this->execute(CreateSubscriptionCommand::class, Input::all());
    
            Flash::success('subscriptions.subscription_created');
    
            return Redirect::route('subscriptions.index');
        }
	}

And the corresponding command `CreateSubscriptionCommandHandler` is here (The `CreateSubscriptionCommand` is only a DTO
 for the input values):

	class CreateSubscriptionCommandHandler implements Laracasts\Commander\CommandHandler
	{
		use Laracasts\Commander\Events\DispatchableTrait;
		
		/**
         * authenticated user
         *
         * @var \Illuminate\Auth\Guard
         */
        private $auth;
    
        /**
         * @param AuthManager $auth
         */
        public function __construct(\Illuminate\Auth\AuthManager $auth)
        {
            $this->auth = $auth;
        }
        
		/**
         * Handle the command
         *
         * @param CreateSubscriptionCommand $command
         * @return mixed
         */
        public function handle($command)
        {
            /** @var User|SubscriptionSubscriber $user */
            $user = $this->auth->user();
    
            //  store invoice data
            
            //  create subscription
            $subscription = Subscription::create($command->plan, $command->payment_option, $user);
    
            //  fire event for "subscription created" or "subscription updated"
            $this->dispatchEventsFor($subscription);
        }
	}

Nearly the same you have to do for extending or upgrading a plan. You can use the same command, handler and controller 
 action. The subscription repository handles automatically an update or create for a subscription plan.

### Registering a Listener

	# in your app/listeners.php for example
	Event::listen('Ipunkt.Subscriptions.Subscription.Events.*', 'App\Subscriptions\Listeners\EmailNotifier');

	//  we use the laracasts/commander package, so you can inform you about a listener too
	class EmailNotifier extends Laracasts\Commander\Events\EventListener
    {
        /**
         * will be called when event SubscriptionWasCreated was fired
         *
         * @param SubscriptionWasCreated $event
         */
        public function whenSubscriptionWasCreated(SubscriptionWasCreated $event)
        {
            //  do something when a subscription was created (a new plan was set up and no plan exists before 
            //  or every plan subscription before was in the past)
        }
    
        /**
         * will be called when event SubscriptionWasUpdated was fired
         *
         * @param SubscriptionWasUpdated $event
         */
        public function whenSubscriptionWasUpdated(SubscriptionWasUpdated $event)
        {
            //  do something when a subscription was updated (e.g. smaller plan before gets upgraded to a more-featured
            //  plan or a subscription was extended to get longer running)
        }
    }

