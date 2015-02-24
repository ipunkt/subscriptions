<?php

/**
 * Plan configuration:
 *
 * [
 *   PLAN-ID => [
 *     'name' => Name of the plan,
 *     'description' => Description of the plan,
 *
 *     'benefits' => [
 *       FEATURE-NAME => [], // feature present, or:
 *       FEATURE-NAME => [
 *         'min' => 0,    // default, minimum margin value, optional
 *         'max' => null, // default, maximum margin value, optional, unbounded by default
 *       ],
 *     ],
 *   ],
 * ],
 *
 */
return [
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
	],
];