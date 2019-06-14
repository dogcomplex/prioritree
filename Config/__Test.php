<?php


$path = substr(__DIR__ .'/'. basename(__FILE__, '.php'), strlen(ROOT_FOLDER));
$parent_path = substr($path, 0, strlen(basename(__FILE__, '.php')));
pp($path);
pp($parent_path);

$self = service(null, $path);
pp($self);


function include_cleanly($file) {
	return include $file;
}

$test = include_cleanly( ROOT_FOLDER. '/Test.cfg' );


$to_array = include_cleanly( ROOT_FOLDER. $parent_path . '/ToArray.cfg' );
$to_array['__test'] = array_merge_recursive($test, $to_array['__test'] ?? []);
pp($to_array);


/*
// SIMPLE TEST
	$test = [
		'a' => 1
	];
	$result = [
		'a'	 		=> 1,
		'__array'	=> &$test,
	];

	$changed_result = $result;
	$changed_result['b'] = 2;

	// flat array COPY
	$array = \ArrayService::toArray($test);
	assert($array === $result);
	assert($test === $result);
*/

// DONE LINE
return;

	// referencing test
	$array['b'] = 2;
	assert($array === $changed_result);
	assert($test !== $changed_result);
	assert($test !== $array);

	// flat array REFERENCE
	$array = &\ArrayService::toArray($test);
	assert($array === $result);
	assert($test === $result);
	$array['b'] = 2;
	assert($array === $changed_result);
	assert($test === $changed_result);
	assert($test === $array);

// REPEAT TEST

unset($array);

	$result = $changed_result;
	$changed_result = $result;
	$changed_result['c'] = 3;

	// flat array COPY
	$array = \ArrayService::toArray($test);
	assert($array === $result);
	assert($test === $result);

	// referencing test
	$array['c'] = 3;
	assert($array === $changed_result);
	assert($test !== $changed_result);
	assert($test !== $array);

	// flat array REFERENCE
	$array = &\ArrayService::toArray($test);
	assert($array === $result);
	assert($test === $result);
	$array['c'] = 3;
	assert($array === $changed_result);
	assert($test === $changed_result);
	assert($test === $array);


unset($test);
unset($result);
unset($changed_result);
unset($array);

// DEPTH TEST
	$test = [
		'a' => [
			'b' => 1
		]
	];
	$result = [
		'a' => [
			'b' => 1
		],
		'__array'	=> &$test,
	];
	$changed_result = $result;
	$changed_result['b'] = 2;

	// flat array COPY
	$array = \ArrayService::toArray($test);
	assert($array === $result);
	assert($test === $result);

	// referencing test
	$array['b'] = 2;
	assert($array === $changed_result);
	assert($test !== $changed_result);
	assert($test !== $array);

	// flat array REFERENCE
	$array = &\ArrayService::toArray($test);
	assert($array === $result);
	assert($test === $result);
	$array['b'] = 2;
	assert($array === $changed_result);
	assert($test === $changed_result);
	assert($test === $array);


// DEPTH TEST
	$test = [
		'a' => $test_a = [
			'b' => 1
		]
	];

	$array = \ArrayService::toArray($test);
	$array['a'] = \ArrayService::toArray($array['a']);
	assert($array === $array['__array']);
	assert($array['a'] === $array['a']['__array']);
	assert($array['a'] === $array['a']['__array']);

	$array = &\ArrayService::toArray($test);
	$array['a'] = &\ArrayService::toArray($array['a']);
	assert($array === $array['__array']);
	assert($array['a'] === $array['a']['__array']);
	assert($array['a'] === $array['a']['__array']);


	$array_b = &\ArrayService::get($test, 'a');
	pp($array_b);
	assert($array_b['__array'] === $array_b);
	assert($array_b['__key'] === 'a');
	assert($array_b['__parent'] === $array);
	assert($array_b['__parent'] === \ArrayService::toArray($test));
	assert($array_b['__parent'] === $test);
	assert($array_b['__parent'] === $array);
	assert($array === $array['__array']);
	assert($array['a'] === $array['a']['__array']);


unset($test);
unset($result);
unset($changed_result);
unset($array);


$resource_types = [
	'NULL' 			=> null,
	'int'			=> 1,
	'string'		=> 'a',
	'closure'		=> function(){},
	'object'		=> new stdClass(),
	'service'		=> new \Service(),
	'root_path'		=> '/',
	'root_path2' 	=> '',
	'root_service' 	=> \Root::default(),
	'empty_array'	=> [],
	'array'			=> [
		1 		=> 1,
		'a'		=> 2,
	],
];

$toArray_expected_results = [
	'NULL'			=> $null_array = [
		'__array'		=> &$null_array
	],
];



\ArrayService::toArray($resource);

$ROOT->array->__test();

// /Tests
$tests = [
	'resource_type_gamut'	=> [
		'__extend'				=> '/test',
		'__function'			=> function() {

		}
	]

];


$test_config = [
	'toArray'		=> [
		'__tests'		=> [
			// 'NULL'		=> \Test::withArgs(null);
			// or just assume value is args?
			'NULL' 			=> [
				// __extend 'test'
				// 'function' => a('../..')
				// calls function with args, stores returned value in result, 
				// compares to expected_result, marks pass/fail
				// catches any error
				'args'		=> [  // __call? // __value?
					0 				=> [
						'__value'		=> null
					]
				],
				'expected_return' => [],
				// 'result'			=> null, // fills after test
				// 'passed'			=> null,
				// 'error'			=> null,  // __value?
					// extend /error?
				// 'expected_error'	=> null,
				// '__tests'  		=> [...] // sub-tests?
					// applies to parent in addition
				// 'message'
					// extend /message?
			],
		]
	],
	
];

exit();
