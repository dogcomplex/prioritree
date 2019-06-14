<?php

require_once(ROOT_DIR. '/Test.php');

$function = function(){};
$test = [
	'a'				=> [
		'__value'		=> 'A',
		'aa'				=> 1,
		'ab'				=> $function,
		'ac'				=> [
			'__service_settings'	=> [
				'special_setting'	=> true
			]
		]
	],
	'b'				=> [],
	'c'				=> [
		'__function'	=> function($a, $b) {
			var_dump('Function ran!');
			return $a .' + '. $b .' = '. $a+$b;
		}
	]
];


require_once(ROOT_FOLDER. '/Root/__Test.php');
$ROOT = \Root::default();

// BEGIN TESTS:


echo "new \Service() <br/>";
	$empty_service = new \Service();
	assert($empty_service !== new \Service());
	assert($empty_service !== $ROOT);

	
	echo "new \Service(\$array) <br/>";
	$service_a = new \Service($test);
	$service_b = new \Service($test);
	assert($service_a !== $service_b);
	
	echo "new \Service(\$service) <br/>";
	$service1 = new \Service($test);
	$service2 = new \Service($service1);
	assert($service1 !== $service2);
		// NOTE: the "new" keyword overwrote the old $service_a instance
	//echo($service2);

echo "\Service::__service() <br/>";
	$empty_service = new \Service();
	$empty_service2 = \Service::__service();
	assert($empty_service2 !== $empty_service);
	assert($empty_service2 instanceof \Service);

	echo "\Service::__service(\$array) <br/>";
	$service = new \Service($test);
	$service2 = \Service::__service($test);
	assert($service2 === $service); // $test array stores __service

	echo "\Service::__service(\$service) <br/>";
	$service = new \Service($test);
	$service2 = \Service::__service($service);
	assert($service2 === $service);  // but services are preserved
	$service3 = \Service::__service($service2);
	assert($service3 === $service2); 

// TODO other types

function __test_config(&$service, &$original, $print=false) {
	$service_config = &$service->__config();
	assert($original === $service_config);
	assert(refs_same($original, $service_config));
	$service_config2 = &$service->__config();
	assert($original === $service_config2);
	assert($service_config === $service_config2);

	if ($print)
		echo "\$Service->__config()['__service'] referencing <br/>";
	assert($service === $service_config['__service']);
	assert(refs_same($service, $service_config['__service']));
	if ($print)
		echo "\$Service->__config()['__config'] referencing <br/>";
	assert($service_config === $service_config['__config']);
	assert($service->__config() === $service_config['__config']);
	assert(refs_same($service_config, $service_config['__config']));
}

function __test_service(&$service) {

	assert($service instanceof \Service);
	$service2 = $service->__service();
	assert($service === $service2);
	$service3 = &$service->__service();
	assert($service === $service3);

}


echo "\$Service->__service() <br/>";
	$service = new \Service();
	__test_service($service);
	// __test_config($service, null);

/*  TODO
echo "\$Service->__service(\$array) <br/>";
	$service = new \Service();
	assert($service instanceof \Service);
	$service2 = $service->__service($test);
	assert($service === $service2);
	$service3 = &$service->__service($test);
	assert($service === $service3);
	__test_config($service, $input, $print);

echo "\$Service->__service(\$service) <br/>";
	$test_service = new \Service($test);			
	__test_service($test_service, true);
	$test_service2 = \Service::__service($test);
	__test_service($test_service2, false);
	$test_service3 = \Service::__service($test_service);
	__test_service($test_service3, false);
*/

function __test_get($service, $get, $static=false) {

	$service_config = $service->__config();
	$returned_service = &$service->$get;
	$returned_service_config = $returned_service->__config();
	assert($service->$get === $returned_service); 
	__test_service($returned_service);

	assert(isset($service_config[$get]));	
	assert($returned_service_config === $returned_service_config['__config']);
	assert($returned_service_config[$get] === $returned_service_config[$get]['__config']);
	
	echo "-\t setting key <br/>";
	$new_key = 'some_really_long_key_that_shouldnt_exist_'. rand();
	assert(!isset($returned_service_config[$new_key]));
	$returned_service_config[$new_key] = 'set';
	assert(isset($returned_service_config[$new_key]));
	assert($service_config[$get][$new_key] === 'set');
	assert(isset($service_config[$get][$new_key]));
	assert($service->__config()[$get][$new_key] === 'set');
	assert(isset($service->__config()[$get][$new_key]));
	assert($service->$get->__config()[$new_key] === 'set');
	assert(isset($service->$get->__config()[$new_key]));

	echo "-\t changing key <br/>";
	$returned_service_config[$new_key] = 'changed';
	assert(isset($returned_service_config[$new_key]));
	assert($service_config[$get][$new_key] === 'changed');
	assert(isset($service_config[$get][$new_key]));
	assert($service->__config()[$get][$new_key] === 'changed');
	assert(isset($service->__config()[$get][$new_key]));
	assert($service->$get->__config()[$new_key] === 'changed');
	assert(isset($service->$get->__config()[$new_key]));
				
	echo "-\t unsetting key <br/>";
	unset($returned_service_config[$new_key]);
	assert(!isset($returned_service_config[$new_key]));
	assert(!isset($service_config[$get][$new_key]));
	assert(!isset($service->__config()[$get][$new_key]));
	assert(!isset($service->$get->__config()[$new_key]));
	
};

echo "\$service->__get('a') <br/>";
	$service = new \Service($test);
	__test_get($service, 'a');
	assert($returned_service_config === $returned_service_config[$get]);

	echo "-\t changing existing key <br/>";
	assert(isset($returned_service_config['aa']));
	assert($service_a_config['aa'] === 1);
	$service_a_config['aa'] = 'changed';
	
	assert($service_config['a']['aa'] === 'changed');
	assert($service_a->__config()['aa'] === 'changed');
	assert($service->a->__config()['aa'] === 'changed');
	$service_a->__config()['aa'] = 'changed_again';
	assert($service_config['a']['aa'] === 'changed_again');
	assert($service_a->__config()['aa'] === 'changed_again');
	assert($service->a->__config()['aa'] === 'changed_again');
	

echo "\$service->->__get('a')->__get('aa')  'Deep test' <br/>";
	$service = new \Service($test);
	$service_config = $service->__config();
	$service_a = $service->a;
	__test_get($service_a, 'aa');		
	$returned_service = $service_a->aa;
	$returned_service_config = $service_a->aa->__config();
	$new_key = 'some_really_long_key_that_shouldnt_exist_deeper_'. rand();

	echo "-\t testing deep new keys <br/>";
	assert(isset($returned_service_config[$new_key]));
	$returned_service_config[$new_key] = 'changed';
	assert(isset($returned_service_config[$new_key]));
	assert($service_config['a']['aa'][$new_key] === 'changed');
	// print_r($returned_service_config); exit();
	assert(isset($service_config['a']['aa'][$new_key]));
	assert($service->__config()['a']['aa'][$new_key] === 'changed');
	assert(isset($service->__config()['a']['aa'][$new_key]));
	assert($service->a->__config()['aa'][$new_key] === 'changed');
	assert(isset($service->a->__config()['aa'][$new_key]));
	assert($service->a->aa->__config()[$new_key] === 'changed');
	assert(isset($service->a->aa->__config()[$new_key]));


echo "\$service->__get('a') REPEATED<br/>";
	$service = new \Service($test);
	__test_get($service, 'a');
	__test_get($service, 'a');
	assert($returned_service_config === $returned_service_config[$get]);


echo $service;
echo $service->a;
echo $service->b;
echo $service->c;
$service_a = $service->a;
echo $service_a->aa;
echo $service;

$service->a->aa = "changedagain";

echo "SUCCESS";
exit();


echo "__ISSET";
assert(isset($service->a) === true);
assert(isset($service->b) === true);
assert(isset($service->a->aa) === true);
assert(isset($service->missing) === false);
assert(isset($service->a->__value) === true);
assert(isset($service->a->ab) === true);
assert(isset($service->a->ac) === true);

echo "KEYWORDS: __VALUE </br>";
assert($service->__value === null);
assert($service->a->__value === 'A');
assert($service->a->aa->__value === 1);
assert($service->a->ab->__value === $function);
assert($service->a->ac->__value === null);
assert($service->b->__value === null);


echo "KEYWORDS: __CONFIG";
echo($service->__service);
// should return the service object for root
assert($service === $service->__service);
assert($service === $service->__service->__service);
assert($service->a === $service->a->__service);
assert($service->a->aa === $service->a->aa->__service);
echo($service->a->ac->__service);
// should return the service object for a/ac

echo "KEYWORDS: __config";
pp($test2 = $service->__config());
assert($test === $test2);
// should return the initial raw data object (array)

echo "KEYWORDS: __service_settings";
// TODO hate this
assert($service->a->ac->__service_settings->special_setting->__value === true);
// this could allow things like custom merging rules etc

//echo "APPLICATION";
//echo \Application::get($test);

echo "__SET";
$service->test = 1;
assert($service->test->__value === 1);
assert(\Service::get($test)->test->__value === 1);
assert(\Service::get($service)->test->__value === 1);

$service->test = 11;
assert($service->test->__value === 11);
assert(\Service::get($test)->test->__value === 11);
assert(\Service::get($service)->test->__value === 11);

$test = \Service::get('test', $service);
$test->deeper = 'deeper';
assert($test === \Service::get('test', $service));
assert($test->deeper === \Service::get('test', $service)->deeper);
assert($test->deeper->__value === \Service::get('test', $service)->deeper->__value);

$two = 2;
echo($service);
\Service::set('test2', $two, $service);
assert(isset($service->test2));
echo($service->test2);
assert($service->test2->__value === 2);
assert(\Service::get($test)->test2->__value === 2);
assert(\Service::get($service)->test2->__value === 2);
assert($service->test2->__value === $two);
assert(\Service::get($test)->test2->__value === $two);
assert(\Service::get($service)->test2->__value === $two);

/*
echo "KEYWORDS: __SERVICE";
echo($service = $service->__service);
echo($service);
assert($service->__service === Service::__service($service));
assert($service->__service === Service::__service($test));
// YUSSSSSS


echo "KEYWORDS: __SERVICE";
echo($service->c);
var_dump($service->c->__function);
*/

$n = 100000;
$time_start = microtime_float();
$mem_start = memory_get_usage();
$path = 'test/of/path';
$i=0;
$function = function(){
	return 1;
};

class Foo {
	public $a = 1;

	public function assert($x) {
		return $this->a + $x;
	}
}

$base_class = new Foo();
$function = function($x) {
	return $this->a + $x;
};
$function = $function->bindTo($base_class);


$stds = [];
$sum = 0;
for ($i =0; $i < $n; $i++) {
	//\Service::get('a', $service);
	// __foo() via creating a new object each time and running it
	$stds[$i] = new Foo();
	$foo = clone $base_class;
	$foo->a = 2;
	$sum += $foo->assert($i);
}
var_dump($sum);
$time_end = microtime_float();
$mem_end = memory_get_usage();
$time = $time_end - $time_start;
$mem = $mem_end - $mem_start;
echo "Total time: $time seconds \t <br \>";
echo "Total memory: $mem b \t <br \>";

$stds = [];
$time_start = microtime_float();
$mem_start = memory_get_usage();
$sum = 0;
for ($i =0; $i < $n; $i++) {
	// __foo() via updating root Foo's reference via bindTo and running it
	//$service->a;
	$stds[$i] = new Foo();
	$sum += $function->bindTo($stds[$i])($i);
	//$function2();
}
var_dump($sum);
$time_end = microtime_float();
$mem_end = memory_get_usage();
$time2 = $time_end - $time_start;
$mem2 = $mem_end - $mem_start;
echo "Total time: $time2 seconds \t <br \>";
echo "Total memory: $mem2 b \t <br \>";

echo "Total time: ". $time / $time2 * 100 ." % \t <br \>";
echo "Total memory: ". $mem / $mem2 * 100 ." % \t <br \>";


/*

// echo $service->a->b;
// var_dump($service->a->b->__value);

$get_tests = [
	'__value'			=> null,
	'a/__value'			=> 'A',
	'a/b/__value'		=> 1,
	'a/c/__function'	=> $function,
	'__path'			=> '/',
	'a/__path'			=> '/a',
	'a/b/__path'		=> '/a/b',
	'a/c/__path'		=> '/a/c',
];

$failures = [
];
foreach($get_tests as $get => $compare) {
	if ($service->$get !== $compare)
		$failures[$get] = $service->$get;	
}
return $failures;
*/

$service = Service::__service($test);
echo $service;
echo $service->a;