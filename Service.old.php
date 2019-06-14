<?php

namespace CMS;

$cms = [
	'call' 				=> [
		'__extend'			=> '/function'
		// just a function that Service auto-calls
		// TODO needed?
	],
	'function'			=> [
		'__value'			=> null,  	// the function closure itself 
		'__function'		=> function(...$args) {
			// TODO apply $this->args validation & defaults to args?
			// need to resolve args into values probably
			if (is_closure($this->__value)) {
				$this->__value->bindTo($this, $this);
				return call_user_func_array($this->__value, $args);
			}	
		}, 
		'args'				=> [
			'__map'				=> [
				// TODO auto-pull args list from function closure via reflection		
				'__validators'		=> [
					'arg_matches_closure'  => function($value) {
						return function_has_arg($this->{'../..'}, $this->{'../../../../__value'});
					}
				]
			]
		],
		'__validators'		=> [
			'is_closure'
		]
	],
	'value'				=> [
		// Probably silly to double-nest value
		// Service can just run it manually (hard-code the get/set)
		// these are just descriptive of how it works:
		// (Alternatively: Value is a Singleton, loaded by Service once and only once
		'__value'			=> a('__config'), 
		'__set'				=> function($value) { $this->__value = $value; },
		'__get'				=> a('__config'),
		'__function'		=> function(...$args) {  // i.e. call the function
			return call_user_func_array($this->__value, $args);
			// TODO error check
		}
		// summary: read/write directly to config with no formatting required
		// so config like: resource => [
		//		'__value'	=> 1	
		// ] 
		// is ALL that's needed like-ever.
	],
	'set'				=> [
		// validating SET:
		'__function'		=> function($value) {  // common function, default set?
			if (Service::is_config($value))
				$this->__config = $value;
			elseif (is_closure($value))  // TODO?
				$this->__function = $value;
			elseif ($this->is_valid($value))  // calls validation
				$this->__value = $value; 
		},
	],
	// naive SET with configs
	'naive_set'	=> function($value) {  // common function, default set?
		if (Service::is_config($value))
			$this->__config = $value;
		elseif (is_closure($value))
			$this->__function = $value;
		else
			$this->__value = $value; 
	},
	'get'				=> [
		'__function'		=> function($path=null) {
			// Note: $this is bound to a Service object here	
			if ($path === null || $path === [] || $path === '.')
				return $this;
			else if (is_string($path)) {
				// TODO more restrictions?
				$path = explode($path, '/');
			}
			if (!is_array($path))
				throw new Exception('Invalid Input');
			$key = array_shift($path);

			switch($key) {
				case '':
				// case '/':
					return Service::$root->$path;
				case '__service':
				case '.':
					return $this->$path;
				case '__parent':
				case '..':
					return $this->__parent->$path;
				case '__value':
					return $this->__config['__value']; 
				default:

					// Service::build($this->__config[$key]);

					// check cache
					if (isset($this->__config[$key]['__service']))
						return $this->__config[$key]['__service']->$path;
					if (!isset($this->__config[$key]))
						return null;
						// TODO error?
					$config = &$this->__config[$key];
					if (!is_config($config)) {
						if (is_closure($config)) {
							$config = [
								'__function'	=> $config
							];
						} else {
							$config = [
								'__value'		=> $config
							];
						}
					}
					// $config is config array now.

					// auto-extend core services
					if (strpos($key, '__') === 0) {
						if (!isset($config['__extend']))
							$config['__extend'] = '/'. substr($config['__key'], 2);
					 	else
					 		throw new Exception('TODO multiple extending');
					} 
					// build config:
					$config['__config']		= &$config;
					$config['__key']		= $key;
					$config['__parent']		= &$this->__config;
					$config['__path'] 		= $this->__path .'/'. $key;
					// TODO switch service handler if override __service:
					return $config['__service'] = new Service($config);
					// TODO build service first then assign above automatic-like?
			}

			/*

			if (is_array($path)) {
				$key = array_shift($path[0]);  // or strtok($path, '/'); ? speed?
				if ($key === '.')
					return $this->__get($path);
				if ($key === '')
					return Service::$root->__get($path);
				if ($key === '...')
					return $this->__parent->__get($path);
				
				if (isset($this->__config[$key])) {
					return $this->$key->__get($path);
				} else
					// return null; // else not found
					// TODO error? warning?
					throw new Exception('Key not found: '. $this->__path . '/ + '. $path);
			} else
				throw new Exception('Invalid path: '. $this->__path . '/ + '. $path);
			// default GET router just matches keys.  
			// need to overwrite this function if you want custom
			// mapping / fetching 
			*/
		}
	],
	'config'			=> [
		'__get'				=> function($key=null) {
			if ($key === '__service')
				return $this;
			return $this->__config[$key];
			// TODO break up path too?
			// NOTE: Service must bind this to the Service not the config 
			// (if config is an array, as I'm thinking it might be)
				// TODO decide!
		}
	],

	'service'			=> [
		// '__get'	// same as default /get :D
		'is_valid'			=> function($value) {
			// calls validation on $value as replacement/addition to self
		},
		'__set'				=> a('/set'),
		'__get'				=> a('/get'),
		'__parent'			=> null, // done by parent get
		'__key'				=> null, // done by parent get
		'__path'			=> function() {
			$this->__parent->__path .'/'. $this->__key;
		},
		
		'__function'		=> function($target, $parent=null) {
			if ($parent === null)
				$parent = Service::$root;

			if (is_string($target)) // relative path
				return $parent->$target;
			if (is_service($target))
				return $target; // TODO clone?
			if (is_closure($target)) {
				// Assign to function:
				$target = [
					'__function'	=> $target
				];
			}
			if (!is_array($target)) {
				// Convert to value:
				$target = [
					'__value'	=> $target
				];
			}	
			// $target is array past this point
			if (isset($target['__service']))
				return $target['__service'];

			// BUILD SERVICE:
			$target['__config'] 	= &$target;
			// $target->__key;  // Must be done by GET
			$target['__parent']		= &$parent->__config;
			$target['__path'] 		= $parent->__path .'/'. $target->__key;
			$target['__service'] 	= new Service($target);
			if (strpos($target['__key'], '__') === 0) {
				if (!isset($target['__extend']))
					$target['__extend'] = '/'.substr($target['__key'], 2);
			 	else
			 		throw new Exception('TODO multiple extending');
			}
		}
	]
];

/* service:
	resource = [
		'__value'	=> 1,
		'__value*'	=> 1,
		'__function'	=> function() {}
		'__function*'	=> [
			'__value'		=> function() {}
			// other fields available on GET e.g. __function/args
		],

	];

	e.g. __call($key, $args) on $this = Service(/resource)
		rewritable at /resource/$key/__call
		$function = $this->$key->__function;  
			does $this->__get($key)
				1) uses /resource/__get if present
					uses /get to get __get :P
						.. see below
					if found, uses /resource/__get/__function

				2) else uses /get
					gets /get/__get to get /get/__function ...? LOOP
						// probs need to cheat in service to resolve this
					runs /get/__function($key) bound to $this = Service(/resource)
						which either finds $key in /resource/__config/$key or returns null
							if found, it calls Service($key, $this) to build a service
								(or finds a cached one if set already)
							// alternatively: calls $this->__service($key)
								which invokes this service, building a new service object out of $key
									// or pulls from cache of services
									// or pulls from $this->__config->$key->__service cache...
										// likely this
			then does $function = ($this->$key)->__get('__function'):
				uses /resource/$key/__get if present
					...
					
					
				

			does $this->$key->__function->__get()
				which uses /function/__get() since missing by default
				which uses /get() since missing by default :P
					which uses /get/__function(), finds it
						runs it, bound to $this = $this->$key
						which returns 

				which converts its config (calling /function/__set($
		return $function(...$args);
*/

class Service implements \ArrayAccess {
	public static $root;
	public static $_services = [];
	public $__config;
	private $_i = 0;

	function __construct($service) {
		if (is_string($service)) {
			$path = $service;
			$service = get($path, self::$root, self::$root);
		}
		if (is_array($service))
			$path = $service['__path']; // TODO error check
		if (is_object($service))
			return; // TODO
		$this->__config = $service;
	}

	static function is_service($target) {
		return is_object($target); // TODO
	}



	static function construct($target) {
		// if path, get from root
		// else assumes given a valid config (array)
		// or service (object)

		// TODO isset root
		if (Service::is_service($target))
			return $target; // for speed
		else if (is_path($target)) {
			$path = $target;
		} else if (is_config($target)) {
			$path = $target['__path']; // TODO path might not be a string
			if (!isset($path))
				throw new Exception('Path not defined for config');
		} else
			throw new Exception('Invalid input to Service');
		
		var_dump($path);
		if (isset(Service::$_services[$path]))
			return Service::$_services[$path];
		else {
			$config = get($path, self::$root, self::$root);
			return Service::$_services[$path] = new Service($config);
		}
	}

	function __get($path) {
		$get = Service::$root->get;
		return $get($key);
	}

		/*
		TODO: Merge with global get() function
			- put inheritance handler in separate function
				- does __key, __parent, __path
			- converts vals to __value or __function
			- put path reading/recursing in separate function (class?)

		*/
		$get = get($key, $this->__config, self::$root);
		if ($key === '__config' || $key === '__value')
			return $get;
		return Service::construct($get);
	}

	function __set($key, $value) {
		/*
		TODO: no change.  can be dumb like this since get() does the reading to __value etc
		*/
		$this->__config[$key] = $value;
	}

	function __isset($key) {
		// TODO can this be improved?
			// problem is there might be a dynamic __get
			// which might even delete a key in $this->__config 
			// so can't reliably go isset($this->__config[$key])
		return get($key, $this->__config, self::$root) !== null;
	}
	function __unset($key) {
		unset($this->__config[$key]);
		// TODO same problem as above?
	}

	function __invoke($args) {
		return ($this->__function)(...$args);
	}

	function __call($key, $args) {
		return ($this->$key->__function)(...$args);
	}

		/*
			e.g. __call($key, $args) on $this = Service(/resource)
		rewritable at /resource/$key/__call
		$function = $this->$key->__function;  
			does $this->__get($key)
				1) uses /resource/__get if present
					uses /get to get __get :P
						.. see below
					if found, uses /resource/__get/__function

				2) else uses /get
					gets /get/__get to get /get/__function ...? LOOP
						// probs need to cheat in service to resolve this
					runs /get/__function($key) bound to $this = Service(/resource)
						which either finds $key in /resource/__config/$key or returns null
							if found, it calls Service($key, $this) to build a service
								(or finds a cached one if set already)
							// alternatively: calls $this->__service($key)
								which invokes this service, building a new service object out of $key
									// or pulls from cache of services
									// or pulls from $this->__config->$key->__service cache...
										// likely this
			then does $function = ($this->$key)->__get('__function'):
				uses /resource/$key/__get if present
					...
					
					
				

			does $this->$key->__function->__get()
				which uses /function/__get() since missing by default
				which uses /get() since missing by default :P
					which uses /get/__function(), finds it
						runs it, bound to $this = $this->$key
						which returns 

				which converts its config (calling /function/__set($
		return $function(...$args);
		*/
	}

	public static function __callStatic($name, $args) {
		return $this->__call($name, $args);
	}

	function __toString() {
		// TODO templating ;)
		return 'Service('.$this->__config['__path']. ')';
	}

	function __invoke(...$args) {
		if (isset($this->__config['__function'])) {
			return call_function($this->__config, $this->__config['__function'], ...$args);
		} else
			throw new Exception('Invoke not defined:'. $this->__config['__path']);
	}

	// ArrayAccess:
	function offsetExists($key) {
		return $this->__isset($key);
	}
	function offsetGet($key) {
		return $this->__get($key);
	}
	function offsetSet($key, $value) {
		if ($key === '')
			$key = $this->_i++;
		// TODO conflicting int keys?
		$this->__set($key, $value);
	}
	function offsetUnset($key) {
		$this->__unset($key);
	}

	// Iterator

	function current() {
		if ($key = key($this->__config) !== null)
			return get($key, $this->__config, self::$root);
		return false;
	}
	function key() {
		return key($this->__config);
	}
	function next() {
		next($this->__config);
		return $this->current();
	}
	function rewind() {
		reset($this->__config);
		return $this->current();
	}
	function valid() {
		return key($this->__config) !== null;
	}
	// TODO problem: what if fields are dynamically added?  How do we hook in, since the config won't have them yet?

	// Seekable Iterator
	/*
	function seek($pos) {
		// TODO
	}
	*/

	function each() {
		$key = key($this->__config);
		$value = &$this->current(); // &$value ?
		if ($key === null)
			return false;
		return [
			0		=> $key,
			'key'	=> $key,
			1		=> $value, 
			'value'	=> $value,
		];
	}
	function prev() {
		prev($this->__config);
		return $this->current();
	}
	function end() {
		end($this->__config);
		return $this->current();
	}


	// Countable
	function count() {
		return count($this->__config);
	}

	// Serializable
	function serialize() {
		return $this->__config;
	}
	function unserialize($serialized) {
		// TODO
	}

	/*  TODO:
	// ArrayIterator
	function append($value) {
	}
	function asort() {
	}
	function uasort() {
	}
	function ksort() {
	}
	function uksort() {
	}
	function natsort() {
	}
	function natcasesort() {
	}
	function getArrayCopy() {
	}
	function getFlags() {
	}
	function setFlags($flags) {
	}
	*/
}
