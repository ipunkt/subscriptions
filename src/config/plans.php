<?php

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