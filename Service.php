<?php

require_once(ROOT_DIR. 'Php.php');
require_once(ROOT_DIR. 'Config.php');

/*
ALLOWABLE FORMATS:

<resource>
	__service 	= $service_obj | "relative/path/to/service/namespace" | null (implies boot /Service ) | [
		__value 	= $service_obj | null
		class 		= "relative/path/to/service/namespace" (from __service)
	]
	__config 	= [...] (self-referencing resource config) | null


*/

class Service {

	protected $_config;
		// reference to the config representing the class

	
	public function __construct(&$context=null) {

		log_this();
		$config = &\Config::toConfig($context);

		/*
		$service = &$config['__service'];
		switch (gettype($service)) {

			case 'NULL':
			case 'object':
				$service = &$this;
				break;

			case 'integer':
				$service = ''. $service;
			case 'string':
				$service  = [
					'class'	 	=> to_namespace($service),
					'__value'	=> &$this
				];
				break;
			
			case 'array':
				// __SERVICE/__VALUE
				$service_value = &config($service, '__value');
				switch(gettype($service_value)) {
					
					case 'NULL':
					case 'object':
						$service['__value'] = $this;
						break;

					case 'integer':
						$service = ''.$service;
					case 'string':
						$service['class'] 	= $service_value;
						$service['__value'] = $this;
						
					case 'array':
						error("Error in __service/__value defintion: can't be a config");
					default:
						error('Invalid __service/__value');
				}

			default:
				error('Invalid __service');
			
		}
		*/
		$config['__service'] = &$this;
		$this->_config = $config;

	}


/*
	protected static function &_getServiceKeyword($context, $new_service) {
		//$context = service($context);
		$config = &$context->__config();
		$service_raw = $config['__service'];

		switch (gettype($service_raw)) {
			
			case 'object':
				if (!$service_raw instanceof \Service)
					error('Non-service object assigned to __service keyword');
				return $service_raw;
				
			case 'integer':
				$service_raw = ''.$service_raw;
			case 'string':
				$service_path = to_namespace($service_raw);
				$service_raw = new $service_path($config),
				return $service_raw['__value'];
			
			case 'NULL':
				if (isset($config['__extend'])) {
					if ($key === '__extend')  // special bootstrapping case
						$service_path = get_class($context->__service($config['__extend']));
					else
						$service_path = get_class($context->__extend());
					$service_raw = [
						'class'		=> $service_raw,
						'__value'	=> new $service_path($config)
					];
					return $service_raw['__value'];
				} else if (isset($config['__alias'])) {
					if ($key === '__alias') // special bootstrapping case
						$service_path = get_class($context->__service($config['__alias']));
					else
						$service_path = get_class($context->__alias());
					$service_raw = [
						'class'		=> $service_raw,
						'__value'	=> new $service_path($config)
					];
					return $service_raw['__value'];
					// treats the two as the same since alias must still create a new class (it just references the same data via __alias extension and __set affecting it)
						// i.e. the relationship is virtual only
					// NOTE: results in trouble if two resources have different classes that internalize vars...
					// maybe it could be an Alias class, which just feeds down into the lower one?
						// Extend class? probably not
				} else {
					// TODO if class exists at this path, use that instead of generic
						// so /resource would automatically boot the /Resource class
						// trick is getting the __path kinda needs a Service already ugh.
						// something like $path = $context->__path($key); return new to_namespace($path);
		
					$service_raw = [
						'__value'	=> new \Service($config)
					];
					return $service_raw['__value'];
				}

			case 'array':
				// __SERVICE/__VALUE
				$service_value = &$service_raw['__value'] ?? null;
				switch(gettype($service_value)) {
					
					case 'object':
						if (!$service_value instanceof \Service)
							error('Non-service object assigned to __service keyword');
						return $service_value;

					case 'integer':
						$service_value = ''.$service_value;
					case 'string':
						$service_path = to_namespace($service_value);
						$service_raw['class'] = $service_path;
															
					case 'array':
						error("Error in __service/__value defintion: can't be a config");
					
					case 'NULL':
						if (isset($config['__extend'])) {
							if ($key === '__extend') // special bootstrapping case
								$service_path = get_class($context->__service($config['__extend']));
							else
								$service_path = get_class($context->__extend());
							$service_path = get_class($context->__extend());
							$service_raw['class'] = $service_path;

						} else if (isset($config['__alias'])) {
							if ($key === '__alias') // special bootstrapping case
								$service_path = get_class($context->__service($config['__alias']));
							else
								$service_path = get_class($context->__alias());
							$service_raw = [
								'class'		=> $service_path,
								'__value'	 => new $service_path($config)
							];
							return $service_value['__value'];
						} else {
							// NOTE above
							$service_raw = [
								'__value'	 => new \Service($config)
							];
							return $service_value['__value'];
						}

					default:
						error('Invalid __service/__value');
				}

			default:
				error('Invalid __service');
			
		}
	}
*/


	protected static function &__service(&$context=null, &$resource=null) {
		log_this();
		// handles knowing if __keyword or not
		// creates a Service object (or equivalent: calls __class) at the given $resource=key, with additional values $value, given context $context
		// null context means root-less independent Service?  NOT root??
		// handles __extend forwarding, __key, __parent, and __service setting
		// may require a GET to $resource, may require a creation of [] along the way
		// doesnt care if nothing there. doesnt care if value==null too. can create empty objects in other words
		// works???

		/**
		DEPENDENCIES:
			ROOT
			GET() (if path given)
			PARENT() (if '..' given)
			CONFIG()
		**/

		// CONTEXT
		if (isset($context) && !$context instanceof \Service ) {
			$null = null;
			$context = &self::__service($null, $context);
		}
		// $context is a Service now or null (if called statically)

		// RESOURCE:
		switch (gettype($resource)) {
			
			case 'NULL':  
				if ($context === null) {
					$new_service = new \Service();
					return $new_service;
				} 
				//var_dump(999);
				return $context;
							
			case 'array':
				if ($context === null)
					$config = &\Config::toConfig($resource);
						// TODO doesnt do anything currently, but shouldnt it be wrapped like this? is this ideal? hmmm
				else
					$config = &$context->__config($resource);  // merge??
				break;
				$config = $resource;
				// TODO problem is how to assign key, parent etc ugh
				
				// TODO create a key-less child object service (independent service) with a link to __parent (if $context is set)
				// unless __key is specified, in which case add it
				// NOTE: shoudlnt create it in root, so need to move $context assignment down into switch
			
			case 'object':
				if ($context === null)
					$config = &\Config::toConfig($resource);
				else
					$config = &$context->__config($resource);
					// NOTE: $parent1->__service($resource) merges $resource over $parent1. hmmm...
				break;

			default:
				// could maybe make config and object use their "__key"s to set as children in $context?
				error('Not handled in get() right now:'. gettype($resource));
			
			case 'string':
				$ROOT = \Root::default();
				$context = $context ?? $ROOT;	

				if ($pos = strpos($resource, '/') !== false) {
					$key = substr($resource, 0, $pos-1);
					$path = substr($resource, $pos);
					return $context->$key->__service($path);
				}
				$key = $resource;
 
 				// KEY:
				switch($key) {
					// PATH routing:
					case '': // root, result of leading slash "/foo"
					//case '/':
						return $ROOT;
					case '.': // self
						return $context;
					case '..': // parent
						return $context->__parent();		
					default:
						$config = &\Config::get($context, $key);
						break;
				}
				break;
		}
		// $config is set now or else we've returned a Service

		// GET OR BUILD SERVICE:
		// TODO handle complex __service values
		//		return self::_getServiceKeyword($context);
		if (isset($config['__service']) && $config['__service'] instanceof \Service) {
			return $config['__service'];
		} else {
			$config['__service'] = new \Service($config);
			// assert($config === \Config::get($context, $key));
			return $config['__service'];
		}

	}

	public function &__config($resource = null) {
		//pp($this->_config);
		//exit();
		if (!isset($resource))
			return $this->_config;
		// else TODO merge the resource in?
		return $this->_config;
	} 
		
	// Returns a config of the given $context, with $args layered overtop
	/*
		Service::__config() returns empty config []
		Service::__config($resource) returns config of $resource
		Service::__config($resource1, $resource2) returns config of $resource1 with $resource2 merged into it
			Some implementations might simply extend $resource2 with $resource1
			Note: does not modify $resource1
		$resource->__config(); returns the config of $resource
		$resource->__config($resource2); ERRORS (not supported yet)

		PROBLEM: none of this covers the use case of __config($key) returning a converted config of $key...
		tempted to instead of merge, make it a select.  so $key would return $context config with $key built
			[$key1 => [], $key2 => [], ...] would return $context config with just those keys built
		could do __config_get($key) instead. probably simpler.  or \Config::get($context, $key) even better
	
	public function &__config($resource=null) {
		log_this();

		switch (gettype($resource)) {
			
			case 'NULL':  
				return $this->_config;

			default:
				error('Not supported right now.  TODO config or objects passed to __config could return a new config that filters the $context->__config() or could append to it.  TO BE DECIDED');

			case 'integer':
				$resource = ''.$resource;
			case 'string':
				if ($pos = strpos($resource, '/') !== false) {
					$key = substr($resource, 0, $pos-1);
					$path = substr($resource, $pos);
					return $this->$key->__config($path);
				}
				$key = $resource;
 
 				// KEY:
				switch($key) {
					// PATH routing:
					case '': // root, result of leading slash "/foo"
					//case '/':
						return $ROOT->__config();
					case '.':
						return $this->_config;
					case '..':
						return $this->__parent()->__config();		
					default:

						$parent_config = &$this->_config;
						$value = &$parent_config[$key] ?? null;

						// CONVERT TO CONFIG
						switch (gettype($value)) {
							case 'NULL':
								$config = [];
								break;
							case 'object':
								if ($value instanceof \Closure) {
									$config 		= [
										'__function'	=> &$value,
									];	
								} else {
									// TODO how to handle non Service objects
									if ($value instanceof \Service) {
										$config 		= [
											'__service'		=> &$value,
										];	
									} else 
										error("Can't handle non-Service objects yet: ". get_class($resource));
								}
								break;
							default:
								if (!is_config($value)) {
									$config 	= [
										'__value'	=> &$value,
									];
								} else
									$config = &$value;
								break;
						}

						// SET CONFIG
						$config['__config'] 	= &$config;						
						$config['__key'] 	= $key;
						$config['__parent'] 	= &$parent_config;
						if (!isset($config['__extend']) && strpos($key, '__') === 0) {
							// TODO merge __extend in
							$config['__extend'] = '/'. substr($key, 2);
								// auto-inherit root services
						}
						if ($key !== '__extend' && isset($this->__extend))
							$this->__extend->inherit($key);
						if ($key !== '__alias' && isset($this->__alias))
							$this->__extend->inherit($key);
						
						// SET IT
						$this->_config[$key] = &$config;

						// FETCH __FILE
						// TODO put this in $ROOT->file($this) ?
						$file_config = \Config::get($config, '__file');
						switch(gettype($file_config)) {
							
							case 'boolean':
								if (!$file_config)  // skip file check if false
									break;	

							case 'NULL':
								// any other way to call this without forcing service?  \Path::of($config) ?
									// would check __parent, concat __parent/__path .'/'. __key. easy...
									// maybe $ROOT->path($config) would do.  eh, sure.  Same thing?
								$file_path = \Root::default()->path($config) . '.php';

							case 'string':
								$path = \Root::default()->path($config) . $file_config . '.php';  // something like that. relative path
							
							case 'object':
							case 'array':
								$file_config_value = \Config::get($file_config, '__value');
								switch(gettype($file_config_value)

							$config['__file'] = [];

						}
						
						return $this->_config[$key];	
				}
		}	
	}
	*/

	// made static public by __callStatic()
	// also non-static public by __call() 
		// e.g. $context->merge($resource, $replace_conflicts);
	protected static function &__merge(&$context, &$new_config, $replace_conflicts=true) {
		//TODO configservice screw this up.  think it through
		// TODO move to configservice?
		if (!is_config($context))
			$context = &\Config::valueToConfig($context);
		if (!is_config($new_config))
			$new_config = &\Config::valueToConfig($new_config);
		array_walk(
			$new_config,
			function(&$value, $key, &$context) use($replace_conflicts) {
				$original_value = &$context[$key];
				if (isset($original_value)) {
					if (!isset($value) || $replace_conflicts === false)
						return;
					if ($key === '__value') {
						$context[$key] = $value;
						return;
					}
					self::__merge($original_value, $value, $replace_conflicts);
				}
				$context[$key] = $value;
			},
			$context
		);

		return $context;
	}

	public function &__call($path, $args) {
		log_this();

		if ($pos = strpos($path, '/') !== false) {
			$key = substr($path, 0, $pos-1);
			$path = substr($path, $pos);
			return $this->$key->$path(...$args);
		}
		$key = $path;

		if (method_exists($this, $key)) {
			$method = new \ReflectionMethod($this, $key);
			if ($method->isStatic())
				return get_class($this)::$key($this, ...$args);
			$return = &$this->$key(...$args);
			return $return;
		}

		return ($this->$key)(...$args);
	}


	public static function &__callStatic($path, $args) {
		// NOTE: assumes $args[0] is always $context
		
		log_this();
		if (strpos($path, '/') !== false)
			error("Calling a path statically not supported");
		$key = $path;
		
		// TODO let functions turn on/off accessibility in configs via __function = false ?
		if (method_exists(get_called_class(), $key)) {
			$method = new \ReflectionMethod(get_called_class(), $key);
			if ($method->isStatic())
				return get_called_class()::$key(...$args);
		}

		$context = array_shift($args);
		if (!isset($context))
			$context = \Root::default();  
			//error('Context not specified');
		if (!$context instanceof \Service)
			$context = service($context);
		if (method_exists(get_called_class(), $key))
			return $context->$key(...$args);
		return ($context->$key)(...$args);
		// NOTE: let __invoke handle Not Found errors
		

		// TODO put this somewhere:
		// \Some\Service::$path($context, ...$args);  == (service($context)->$path)(...$args);
			// e.g. \User::login($user, $password) == $user->login($password)
			// e.g. \Number::add($number1, $number2) == $number1->add($number2)
			// e.g. \Service::get($resource1, $resource2) == $resource1->get($resource2);
		// \Some\Service::$path(null, ...$args);  == (service('\Some\Service\')->$path)(...$args);
			// e.g. \User::login(null, $password) == $ROOT->user->login($password)  HMMMMMMMMM
			// e.g. \Number::add(null, $number2) == $ROOT->math->add($number2); HMMMMMM
			// e.g. \Service::get(null, $resource2) == $ROOT->service->get($resource2); HMMMM kinda works here?
		// OR \Some\Service::$path(null, ...$args);  == ((new \Some\Service())->$path)(...$args);
			// e.g. \User::login(null, $password) == (new \User())->login($password)  HMMMMMMMMM kinda works
			// e.g. \Number::add(null, $number2) == (new \Number())->add($number2); HMMMMMM kinda works
			// e.g. \Service::get(null, $resource2) == (new \Service())->get($resource2); DOESNT WORK
		// ultimately though, each service should probably handle it itself.  null might not be the only thing needing screening too:
			// e.g. \User::login('bob', $password) == service('bob')->login($password) should be service('\Users\bob')->login($password)
			// e.g. \Number::add([1,2,3,4], $number2) == service([1,2,3,4])->add($number2) should instead map add(number2) to each number in config 
			// e.g. \Service::get($resource1, $resource2) == $resource1->get($resource2);
		// soooo... can't screen these here. each function must make its own $context = service($context) if thats all it wants.
			
	}

	public function &__invoke(...$args) {
		log_this();

		if (isset($this->__function)) {
			pp($this->__config());
				exit();

			$this->__function->bindTo($this);
			return ($this->__function)(...$args);
		}
		// TODO: Deprecated? == default $root->function? i.e. we can always assume __function is set?
		return $this->__value();
	}

	/*
	TODO put in config
	Attempts to run this class as a function, independent of its parent class (i.e. static)
	By default $context=$this when called non-statically
	USAGE:
	$parent->__function('../other_parent/child', ...$args);
	$parent->__function($other_parent, ...$args);
	$parent->__function(null, ...$args);  // uses $context = $parent
	some/class::__function($parent, ...$args);
	some/class::__function(null, ...$args); // uses $context = null
	*/
	public static function &__function(&$context=null, ...$args) {
		if ($context !== null) {
			if (isset($this))
				$context = $this;
		} else if (!$context instanceof \Service)
			$context = service($context, $this ?? null);

		// TODO toooo many ways hm.
		if ($context !== null && method_exists($context, '__invoke'))
			return $context->__invoke(...$args);
		else if (isset($context->__invoke))
			return ($context->__invoke)(...$args);
		else if (isset($this->__function))
			return ($this->__function)($context, ...$args);
		else if ($context !== null && method_exists($context, '__method'))
			return $context->__method(...$args);
		else if ($context !== null && isset($context->__method))
			return $method(...$args);

		error('No function found');
	}

	/*
	TODO put in config
	Attempts to run this class as a method of its parent class
	ALIAS of __invoke(...$args)
	$parent->__method(...$args);
	*/
	public function &__method(...$args) {
		if (method_exists($this, '__invoke'))
			return $this->__invoke(...$args);
		else 
			return $this->__function($this, ...$args);
		error('No method found');
	}


	public function &__get($path) {
		log_this();
		
		if ($path === '__config')
			error("Warning: \$resource->__config not supported.  Use \$resource->__config() instead.");
			// TODO should we error here? want all __config calls to be __config() for futureproofing and consistency
			// might implement ConfigObject on it though, making __config same as old Configs plan
			
		/*
		if (strpos($key, '__') === 0 && !isset($this->$key)) { // CALLS __ISSET
			$no_prefix_key = substr($key, 2);
			// TODO extend this key? 
			$service = \Root::service($no_prefix_key);
			if (!isset($service))
				error("Service $key not recognized");	
			return $service;
		} */
		
		if ($path !== '__get' && isset($this->__get))
			return ($this->__get)($path); 
		elseif ($path === '') {
			$ROOT = \Root::default();
			return $ROOT;
		} else
			return self::__service($this, $path);				
	}

/*
	public static function &get(&$context=null, $resource=null) {
		log_this();
		if (!isset($context))
			$context = $this  ?? \Root::default();
		if ( !$context instanceof \Service )
			$context = &$ROOT->__service($context);
		if ($resource === '__config')
			error('Config not accessible through Get(), must call __config()');
		return ($context->__get)($resource);
	}
*/
	

	public function __toString() {
		log_this();
		return ppp($this->__config(), '', true, ['__parent', '__config', '__value', '__config'], 10, 0);
	}

	public function __set($path=null, $value) {
		log_this();

		// NOTE: $this->$key = $value === $this->__set($key, $value) === self::_set($this->$key, $value); === $this->$key->_set(null, $value)
		if ($path === '__service') {
			// BLAH TODO exception
		}
		if (isset($this->__set))
			return ($this->__set)($path, $value);
		
		if ($path !== null)
			return service($this, $path)>__set(null, $value);
		
		$this->__config = $value;
	}


	public function __isset($path) {
		/** 
			Handles EXTEND and ALIAS
		**/

		$config = $this->_config;
		log_this();
		switch($path) {
			case '__config':
				return isset($config);
			case '__get':
			case '__isset':
			case '__extend':
			case '__alias':
			case '__parent':
				return isset($config[$path]);
			default:
				if (isset($config['__isset']))
					return ($this->__isset)($path);
				else {
					return (
						isset($config[$path]) 
						//|| isset($this->__extend()->$path) 
						//|| isset($this->__alias()->$path)
					);
				}
		}
	}

	// SPEED-UP HELPER FUNCTIONS:

	function &___extend() {
		log_this();
		
	}

	function &___alias() {
		log_this();
		
	}

	/*
	function __test($context=null) {
		// runs $this($context); primarily, but then also triggers a few additional tests below
		$test = $this->__test;
		$passed = $test();
		if (isset($this)) {			
			if (isset($context)) {
				// TODO create new instance of this service with context overlaid and test it.
			} else {
				// just sanity check this service			
				// does it have a config
				// is the config valid (run \Config::__test($config) on it)
				// anything else..?
				return isset($this->__config()) 
					&& $ROOT->config->__test($this->__config());
			}	
		} else {
			// static test, to see if this Service class is setup correctly in general
			// tests each function,
				//__construct,  __service, __config, __get, __isset, __unset, ...
		}

		// modifies $this->__test with results, uses it for configuration.
		// OR should just feed this as a closure into the /Test function, which runs this particular set of tests


		return true;
	}
	*/
	
}