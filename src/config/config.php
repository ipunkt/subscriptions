<?php

return [
    'defaults' => [
        'plan' => '',
    ],
    'subscriber_model' => 'Company',
    'plans' => [
        /**
         * Plan configuration:
         *
         * [
         *   PLAN-ID => [
         *     'name' => Name of the plan,
         *     'description' => Description of the plan,
         *     'subscription_break' => 0, // optional, break in days before a subscriber can subscribe to this plan again
         *
         *     //  optional entry
         *     'benefits' => [
         *       FEATURE-NAME => [], // feature present, or:
         *       FEATURE-NAME => [
         *         'min' => 0,    // default, minimum margin value, optional
         *         'max' => null, // default, maximum margin value, optional, unbounded by default
         *       ],
         *     ],
         *
         *     //  required entry
         *     'payments' => [
         *       PAYMENT-ID => [
         *         'price' => 1,           // for 1.00
         *         'quantity' => 12,       // in 12 periods of
         *         'days' => 30,           // 30-days
         *         // It is recommended that you use 1 within the interval definition and multiply with the quantity value
         *       ],
         *     ],
         *   ],
         * ],
         *
         */

        /*
            'PLAN-ID' => [
                'name' => 'TRIAL',
                'description' => 'Das ist ein Testvertrag.',
                'subscription_break' => 0,

                'benefits' => [
                    'feature-1' => [],
                    'feature-2-countable' => [
                        'min' => 10,
                    ],
                    'feature-3-countable' => [
                        'min' => 10,
                        'max' => 50,
                    ],
                ],

                'payments' => [
                    [
                        'price' => 1,           // for 1.00
                        'quantity' => 12,       // in 12-times
                        'days' => 30,           // of 30-days
                        'methods' => ['paypal'],
                    ],
                    [
                        'price' => 2,           // for 2.00
                        'quantity' => 12,       // in 12-times
                        'days' => 30,           // of 30-days
                        'methods' => ['paypal', 'invoice'],
                    ],
                ],
            ],
        */
    ]
];
