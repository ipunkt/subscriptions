<?php

/**
 * Plan configuration:
 *
 * [
 *   PLAN-ID => [
 *     'name' => Name of the plan,
 *     'description' => Description of the plan,
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
 *         'quantity' => 12,       // in 12
 *         'interval' => 'P1M',    // 1-months
 *         // It is recommended that you use 1 within the interval definition and multiply with the quantity value
 *       ],
 *     ],
 *   ],
 * ],
 *
 */
return [
/*
	'PLAN-ID' => [
		'name' => 'TRIAL',
		'description' => 'Das ist ein Testvertrag.',

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
				'quantity' => 12,       // in 12
				'interval' => 'P1M',    // months
				'methods' => ['paypal'],
			],
			[
				'price' => 2,           // for 2.00
				'quantity' => 12,       // in 12
				'interval' => 'P1M',    // months
				'methods' => ['paypal', 'invoice'],
			],
		],
	],
*/
];