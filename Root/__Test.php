<?php 

require_once(ROOT_DIR. "Root.php");
require_once(ROOT_DIR. "Test.php");

/*
$default 		= [ 
	'__test'		=> [
		'order'		=> [
			'first_runtime_empty_default',
			'latest_root'
		]
	],
	'__tests'		=> [
		'first_runtime_empty_default' => [
			// PROBLEM: how would we ever ensure this test is run FIRST before any ::default() call? (ever)
			'__description'				=> "default() is null before first Root is constructed"
			'__test'					=> function() {
				return \Root::default() === null;
			},
		],
		'latest_root' => [
			'__description'		=> "::default() is the last constructed Root"
			'__test'			=> function() {
				$ROOT = new \Root($data);
				$bool = $ROOT === \Root::default();
				$ROOT2 = new \Root($data);
				return $bool && $ROOT2 === \Root::default() && $ROOT !== $ROOT2;
			},
		],
	],
];

$construct = [
	'__tests'		=> [
		'empty_input'	=> [
			'__description'	=> 'Construct with empty input',
			'__function'	=> function() {
				$ROOT = new \Root();
				return $ROOT instanceOf \Root;
			},
		],
		'new_empty_instances_differ' => [
			'__description'	=> 'Tests whether two new empty instances differ or point to the same object',
			'__function'	=> function() {
				$ROOT = new \Root();
				return $ROOT !== new \Root();
			},
		],
		'invalid_input'	=> [
			'__description'	=> 'Tests whether an invalid input produces the correct error',
			'__function'	=> function($input) {
				try{
					$ROOT = new \Root($s = 'invalid_string12345');
					return false;
				} catch(\Exception $e) {
					// TODO pull constant message from Root
					return $e->getMessage() === 'Root must be initialized with a root source array';
				}
			},
		],
		'full_input'	=> function() {
			$ROOT = new \Root($data);
			return $ROOT instanceOf \Root;
		},
		'new_full_instances_differ'	=> function() {
			$root_a = new \Root($data);
			// echo($root_a);
			$root_b = new \Root($data);
			// echo($root_b);
			assert($root_a !== $root_b);
				// two different objects with same data
		},
	],
];

$__service = [
	'__tests'		=> [
		'empty_input'	=> [
			'__description'	=> 'Construct with empty input',
			'__function'	=> function() {
				$ROOT = new \Root();
				return $ROOT instanceOf \Root;
			},
		],

		echo "__service() is itself</br>";
		$ROOT = new \Root($data);
		assert($ROOT->__service() === $ROOT);
		$reference = $ROOT->__service();

		echo "__config()['__service'] is itself</br>";
		$reference = &$ROOT->__config()['__service'];
		assert($reference === $ROOT);

		echo "__config()['__service'] is an actively-used reference</br>";
		$reference = &$ROOT->__config()['__service'];
		$reference = 123;
		assert($ROOT->__config()['__service'] === 123);
		$ROOT->__service();

		'new_empty_instances_differ' => [
			'__description'	=> 'Tests whether two new empty instances differ or point to the same object',
			'__function'	=> function() {
				$ROOT = new \Root();
				return $ROOT !== new \Root();
			},
		],
		'invalid_input'	=> [
			'__description'	=> 'Tests whether an invalid input produces the correct error',
			'__function'	=> function($input) {
				try{
					$ROOT = new \Root($s = 'invalid_string12345');
					return false;
				} catch(\Exception $e) {
					// TODO pull constant message from Root
					return $e->getMessage() === 'Root must be initialized with a root source array';
				}
			},
		],
		'full_input'	=> function() {
			$ROOT = new \Root($data);
			return $ROOT instanceOf \Root;
		},
		'new_full_instances_differ'	=> function() {
			$root_a = new \Root($data);
			// echo($root_a);
			$root_b = new \Root($data);
			// echo($root_b);
			assert($root_a !== $root_b);
				// two different objects with same data
		},
	],
];

$__test => [
	'children'	=> [
		'__value'	=> true, // auto-propagates
		'order'		=> [
			'default',
			// ...the rest
		],
	],
	'data'			=> [
		'a' 			=> 1,
		'get'			=> [
			'__service'		=> '/Get'
		],
		'isset'			=> [
			'__service'		=> '/Isset'
		],
		'extend'		=> [
			'__service'		=> '/Extend'
		],
		'parent'		=> [
			'__service'		=> '/ParentService'
		],
		'set'			=> [
			'__service'		=> '/Set'
		],
		'value'			=> [
			'__service'		=> '/Value'
		], 
	],
];
*/

$data  = [
	'a' 		=> 1,
	'get'		=> [
		'__service'	=> '/Get'
	],
	'isset'		=> [
		'__service'	=> '/Isset'
	],
	'extend'	=> [
		'__service'	=> '/Extend'
	],
	'parent'	=> [
		'__service'	=> '/ParentService'
	],
	'set'		=> [
		'__service'	=> '/Set'
	],
	'value'		=> [
		'__service'	=> '/Value'
	], 
];



echo "<p>ROOT: <br/>";

	echo "::default() is null before first Root is constructed <br/>";
	assert(\Root::default() === null);

	echo "::default() is the last constructed Root <br/>";
	$ROOT = new \Root($data);
	assert($ROOT === \Root::default());
	$ROOT2 = new \Root($data);
	assert($ROOT2 === \Root::default() && $ROOT !== $ROOT2);

	echo "::__construct() is different <br/>";
	$ROOT = new \Root();
	assert($ROOT !== new \Root());

	echo "::__construct('bad input') <br/>";
	try{
		$ROOT = new \Root($s = 'bad');
		assert(false);
	} catch(\Exception $e) {
		assert($e->getMessage() === 'Root must be initialized with a root source array');
	}

	echo "::__construct(\$data) <br/>";
	$root_a = new \Root($data);
	// echo($root_a);
	$root_b = new \Root($data);
	// echo($root_b);
	assert($root_a !== $root_b);
		// two different objects with same data

	// TODO test service()
	// TODO test array()

	// TODO generalize this to test any object is a valid service:
	echo "<p>ROOT SERVICE: </br>";
		echo "__service() is itself</br>";
		$ROOT = new \Root($data);
		assert($ROOT->__service() === $ROOT);
		$reference = $ROOT->__service();

		echo "__config()['__service'] is itself</br>";
		$reference = &$ROOT->__config()['__service'];
		assert($reference === $ROOT);

		echo "__config()['__service'] is an actively-used reference</br>";
		$reference = &$ROOT->__config()['__service'];
		$reference = 123;
		assert($ROOT->__config()['__service'] === 123);
		$ROOT->__service();

	echo "/ROOT SERVICE</p>";

echo "/ROOT </p>";

require_once(ROOT_DIR. "/Service/__Test.php");
