<?php

/**
CONFIG
Nested array reader (and other formats later) which wraps nested data in objects and modifies the 

DEPENDENCIES:
Application - for accessing the root config

USAGE:
$path = '/resource';
$array = [ // aka /resource
	'x'	=> 1,
	'y'	=> [
		'z'	=> 2
	]
];
$config 	= new Config($path or $array); 
$x_config 	= $config->x;  				// returns Config object
$x_value 	= $config->x->__value; 		// returns 1. (See Value service)
$y_config 	= $config->y;
$z_config 	= $config->y->z;
$z_value 	= $config->y->z->__value; 	// returns 2
$config 	= $x_config->__parent;  	// See Parent service
$z_config 	= $config->{'y/z'};  		// Accepts Paths as get arguments
$z_value	= $config->{'y/z/__value'};	// returns 2

$config->x 	= 3;
$x_config 	= $config->x; 				// returns Config object
$x_value 	= $config->x->__value;		// returns 3
$config->y 	= $config->x;				
$y_value 	= $config->y->__value;		// returns 3 (x value)
$z_config	= $config->y->z;			// returns null. z not found (x overwrote y)


**/

class Config {
	private $_value;
	/**
	[ e.g.
		'__key'		=> 'resource',
		'__parent'	=> $parent_config,
		'__class'	=> '/Resource',
		'__service'	=> $resource,
		'__value'	=> $resourcevalue,
		'somekey'	=> $config on that key
		'__config'	=> [ $config of these config settings:
			'__key'		=> '__config',
			'__parent'	=> $resource_parent_config, // or $resource service?, thinking config...
			'__class'	=> '/config',
			'__value'	=> $this->_value, // the config raw array
			'__service'	=> $this,		
			'some_config_setting' => $config of config setting	
			// '__config'	=> config of config ...
		],
	];
	**/

	public function __construct(&$resource=[]) {
		if (!is_array($resource))
			; // TODO convert paths
		$this->_value = $resource;

		if (isset($resource['__config']))
			; // TODO customize settings
		if (!isset($resource['__parent']))
			$resource['__parent'] = $this;
		
		
	}


/*
	public static function of(&$target) {
		// returns a Config of $target data
		// same as construct, except uses existing Config object if built before

		$config = null;
		switch gettype($target) {
			case "string":
				// treat as PATH to data
				
				break;
			case "array":
				// treat as raw data
				$config = $target['__config'] ??;
				break;
			case "object":
				// treat as Configurable object
				$config = $target->__config ??;
				break;
			case "resource":
				// TODO but doable as file handle into config
			default:
			//case "NULL":
			//case "boolean": 
			//case "integer":
			//case "double": // or float
			//case "unknown type":
				throw new Exception('Invalid input');
		}


		if (is_string($target)) {
			// get config of path

		}
		if (is_array($target)) {
			// check __config key

		}
		if (is_object($target)) {
			// check $target->__config

		}
		if (is)
	} 


	public function __get($path) {
		// PATH
		$key = strtok($path, '/');
		$app = \Application::default();		

		// VALUE
		$value = &$this->_value[$key];
		// make $value an array
		switch($key) {

			default:
				// boot any services starting with "__"
				if (strpos($key, '__') === 0) { // TODO constant this
					$service_key = substr($key, 2);
					var_dump($service_key);
					return ($app->$service_key)($this);
				}
			
		}
		// case default: normal key:

		// VALUE
		if (!is_array($value)) {
			if (is_callable($value)) {
				$value = [
					'__function'	=> $value
				];
			} else {
				$value = [
					'__value'	=> $value
				];
			}
		}
		// KEY
		if (!isset($value['__key']))
			$value['__key'] 	= $key;		
		// PARENT
		if (!isset($value['__parent']))
			$value['__parent']	= &$this;
		

		// CONFIG
		if ($key === '__config') {

		}
		if (isset($value['__config'])) {
			if (isset($value['__config']['__service']))
				$config_service = &$value['__config']['__service'];
			else
				$config_service = new Config($value);	
		} else
			$config_service = new Config($value);


		// PATH
		if ($path === $key)
			return $config_service;							 
		else {
			$path = strtok('');
			return $config_service->$path;
		}
	}	
	
	function __set($key, $value) {
		switch($key) {
			case '__value':
			case '__key':
			case '__parent':
			case '__path':
				$this->$key = $value;
				return;
			default:
				$get = &$this->$key;
				if ($value instanceof self) {
					// is config
					// $this->$key = $value;
					// TODO.  clone?
				} else
					$this->$key->__value = $value;
		}
	}

	function __toString() {
		return ppp($this->_value);
	}
	*/

}

/**
Ideas:
1. Invoke is the same as calling a function to return a __value, so link it to __value
Default invoke just returns __value, others do things.
Invoke takes params, so its the __value for that context.  a blank call should be == __value usually
2. 
**/

/*
resource
	__config
		__value = $array
		__service = $config object
		__key = __config
		__parent = 'resource' // string?
		__class = Config.php
		__config // settings
			__value = $array of config settings
			__service = $config object
			__key = __config
			__parent = resource/__config
			__class = Config.php
			__config = false // maximum depth
	__value = $resource raw value (alias of /__config/__value/__value ?)
	__service = $resource object (alias of /__config/__value/__service? )
	__key = resource (alias of /__config/__value/__key?)
	__parent = '/' (alias of /__config/__value/__parent?)
	__class = Resource (alias of /__config/__value/__class?)
						
So how to do:
[] config array

*/


/*
class Config {
	// public $__config; // a Config_Settings object
		// $__value // the raw config array itself
	// public static $__root;  // the top-level config


	function __construct(&$target = null) {
		if (is_array($target)) {
			if (isset($target['__config'])) {  // custom settings
				if ($target['__config'] instanceof Config) { // already built
					$this->__config = $target['__config'];
					// TODO this is now just a copy of the original config...
				} else
					$this->__config = new Config($target['__config']); 
			}
			$this->__config->__value 	= &$target;
			$this->__config->__service 	= &$this;
		}
		// TODO	
	}

	function __get($key) {
		if ($key === '__config')
			$config = new Config(); 

		if (isset($this->__config->__value[$key]))
			$config = new Config($this->__config->__value[$key]);	
		
		$config->__key 		= $key;
		$config->__parent 	= &$this;
		return $this->$key 	= &$config;
	}

}

$array = [
	'a'	=> 1
];
$config = new Config($array);
var_dump($config);
*/