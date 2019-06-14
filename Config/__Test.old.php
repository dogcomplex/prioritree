<?php


function microtime_float()
{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
}

function test($bool) {
	echo ($bool ? 'pass' : '<b>FAIL</b>') . '</br>';
}




$function = function(){};
$data = [
	'a'				=> [
		'__value'		=> 'A',
		'aa'				=> 1,
		'ab'				=> $function,
		'ac'				=> [
			'__config_settings'	=> [
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


echo "__CONSTRUCT";
$config_a = new Config($data);
echo($config_a);
// should return the root config object
$config_b = new Config($data);
test($config_a !== $config_b);
$config = new Config($config_a);
test($config_a !== $config);
test($config->__config === $config); // ahead of ourselves here, but just to make sure...
var_dump($config->__config);
var_dump($config);

$config_empty = new Config();
test($config_empty !== new Config());
echo($config_empty);


echo "STATIC 'OF' CONSTRUCTOR";
$config2 = &Config::of($config);
test($config2 === $config);
$config3 = &Config::of($data);
test($config3 === $config);
$new_array = [];
$config4 = &Config::of($new_array);
test($config4 !== $config);
test($config4 === Config::of($new_array));
$config5 = new Config($new_array);
test($config4 !== $config5);
$config_empty2 = Config::of();


echo "BASICS __GET()";

echo($config->a);
test($config->a === $config->a);
// should return the config object for a
echo($config->a->aa);
// should return the config object for a/aa
echo($a = $config->a);
// should return the config object for a
// should look different after expanding a/aa

echo "STATIC GET()";
echo(Config::get($config));
echo($a = Config::get('a', $config));
echo($aa = Config::get('aa', $a));
test(Config::get($config) === $config);
test($a === $config->a);
test($aa === $config->a->aa);

echo "NON-STATIC GET()";
echo($config->get());
test(count(array_unique([$config->get(), Config::get($config), $config, Config::of($config)])) == 1);
echo($a = $config->get('a'));
test(count(array_unique([$config->get('a'), Config::get('a', $config), $a = $config->a, Config::of($a)])) == 1);
echo($aa = $a->get('aa'));
test(count(array_unique([$a->get('aa'), Config::get('aa', $a), $aa = $a->aa, Config::of($aa)])) == 1);

echo "REPEATING";
echo $a2 = $config->a;
// should return the config object for a
// shouldn't create a new object, but re-use the previous config object
test($a === $a2);
$a->test = 1;
$a2->test = 2;
test($a2->test === $a->test);

echo "__ISSET";
test(isset($config->a) === true);
test(isset($config->b) === true);
test(isset($config->a->aa) === true);
test(isset($config->missing) === false);
test(isset($config->a->__value) === true);
test(isset($config->a->ab) === true);
test(isset($config->a->ac) === true);

echo "KEYWORDS: __VALUE </br>";
test($config->__value === null);
test($config->a->__value === 'A');
test($config->a->aa->__value === 1);
test($config->a->ab->__value === $function);
test($config->a->ac->__value === null);
test($config->b->__value === null);


echo "KEYWORDS: __CONFIG";
echo($config->__config);
// should return the config object for root
test($config === $config->__config);
test($config === $config->__config->__config);
test($config->a === $config->a->__config);
test($config->a->aa === $config->a->aa->__config);
echo($config->a->ac->__config);
// should return the config object for a/ac

echo "KEYWORDS: __ARRAY";
pp($data2 = $config->__array);
test($data === $data2);
// should return the initial raw data object (array)

echo "KEYWORDS: __config_settings";
// TODO hate this
test($config->a->ac->__config_settings->special_setting->__value === true);
// this could allow things like custom merging rules etc

//echo "APPLICATION";
//echo \Application::get($data);

echo "__SET";
$config->test = 1;
test($config->test->__value === 1);
test(Config::get($data)->test->__value === 1);
test(Config::get($config)->test->__value === 1);

$config->test = 11;
test($config->test->__value === 11);
test(Config::get($data)->test->__value === 11);
test(Config::get($config)->test->__value === 11);

$test = Config::get('test', $config);
$test->deeper = 'deeper';
test($test === Config::get('test', $config));
test($test->deeper === Config::get('test', $config)->deeper);
test($test->deeper->__value === Config::get('test', $config)->deeper->__value);

$two = 2;
echo($config);
Config::set('test2', $two, $config);
test(isset($config->test2));
echo($config->test2);
test($config->test2->__value === 2);
test(Config::get($data)->test2->__value === 2);
test(Config::get($config)->test2->__value === 2);
test($config->test2->__value === $two);
test(Config::get($data)->test2->__value === $two);
test(Config::get($config)->test2->__value === $two);

/*
echo "KEYWORDS: __SERVICE";
echo($service = $config->__service);
echo($config);
test($config->__service === Service::of($config));
test($config->__service === Service::of($data));
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

	public function test($x) {
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
	//Config::get('a', $config);
	// __foo() via creating a new object each time and running it
	$stds[$i] = new Foo();
	$foo = clone $base_class;
	$foo->a = 2;
	$sum += $foo->test($i);
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
	//$config->a;
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

// echo $config->a->b;
// var_dump($config->a->b->__value);

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
	if ($config->$get !== $compare)
		$failures[$get] = $config->$get;	
}
return $failures;
*/