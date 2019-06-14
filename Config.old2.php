<?php

/**
TODOS:
unset fuuuuuuuuu
Service
handling objects
deferring to services instead of internal config
layers
paths

point to get() via parent instead?

**/


class Config {
	public $__array;

	public function __construct(&$resource=null, &$context=null) {
		/* OLD
		switch(gettype($resource)) {
			case 'array':
				$this->__array = &$resource;
				break;
			case 'object':
				$this->__array = &$resource->__array;
				break;
			case 'NULL':
				$this->__array = [];
				break;
			default:
				throw new Exception('Invalid Construct type: '. gettype($resource));
			// TODO string, obj
		}
		$this->__array['__config'] 	= &$this;  			// cache Config object
		$this->__array['__array'] 	= &$this->__array;	// to keep the array matching keys
		// TODO: $app->config->set(&$this->__array, &$this);
		*/
		if (isset($context))
			$context = Config::construct($context);
		switch(gettype($resource)) {
			case 'array':
				$this->__array 	= &$resource;
				break;
			case 'object':
				// TODO may not be a valid resource
				$this->__array 	= &$resource->__array;
				break;
			case 'NULL':
				$this->__array = [];
				break;
			case 'string':
				if (strpos($resource, '/') !== false) {
					$key 	= strtok($resource, '/');
					$path 	= strtok($resource, '');
				} else
					$key 	= $resource;
				if (!isset($context))
					$context = $app->config; // TODO
				$this->__array 	= &$context->__array[$key];
				break;
			default:
				throw new Exception('Invalid Construct type: '. gettype($resource));
			// TODO string, obj	
		}

		// SET CONFIG PROPERTIES:
				
		$this->__array['__array'] 	= &$this->__array;	// to keep the array matching keys
		$this->__array['__config'] 	= &$this;  			// cache Config object
		// TODO: 	$this->set('__array', $this);
		// OR: 		Config::set('__array', $this, $this);
		if (isset($key))
			$this->__key = $key;
			// TODO Key::set($this, $key);
		if (isset($context))
			$this->__parent = &$context;
			// TODO Parent::set($this, $context)
		// TODO __file stuff would go here!
	}

	// supports STATIC or NON-STATIC calling
	public function &construct(&$resource=null, &$context=null) {
		
		$class_name = get_called_class();
		$class_keyword = snake_case('__'.$class_name);
		if (!isset($context) && isset($this) && $this instanceof self) // if non-static context
			$context = &$this;
		else {
			switch (gettype($context)) {
				case 'NULL':
					$context 	= &$app->$class_name; // TODO
					break;	
				case 'object':
				case 'array':
				case 'string':
					$context 	= &$class_name::construct($context);
					break;
				default:
					throw new Exception('Invalid Parent given for Config::get()');
			}
		}
		// $context is a ClassName past this point

		switch (gettype($resource)) {
			
			case 'object':
				if ($resource instanceof $class_name)
					return $resource;
				return $class_name::construct($resource->__array, $context);
				if (isset($resource->__array[$class_keyword])) {
					if (!$resource->__array[$class_keyword] instanceof $class_name)
						throw new Exception('Invalid '.$class_keyword.' key set.  Should be a '.$class_name.' object or null. Instead it\'s: '.gettype($resource->__array[$class_keyword]));
					return $resource->__array[$class_keyword];
				} else
					return new $class_name($resource, $context);
				// TODO handle external classes somehow
			
			case 'array':
				if (isset($resource[$class_keyword])) {
					if (!$resource[$class_keyword] instanceof $class_name)
						throw new Exception('Invalid '.$class_keyword.' key set.  Should be a '.$class_name.' object or null. Instead it\'s: '.gettype($resource[$class_keyword]));
					return $resource[$class_keyword];
				} else
					return new $class_name($resource, $context);
			
			case 'string':
				if (strpos($resource, '/') !== false) {
					$key 	= strtok($resource, '/');
					$path 	= strtok($resource, '');
				} else
					$key = $resource;
				
				if (isset($context->__array[$key][$class_keyword])) {
					if (!$context->__array[$key][$class_keyword] instanceof $class_name)
						throw new Exception('Invalid '.$class_keyword.' key set.  Should be a '.$class_name.' object or null. Instead it\'s: '.gettype($resource[$class_keyword]));
					$service = $context->__array[$key][$class_keyword];
				} else
					$service = new $class_name($resource, $context);
				
				if (isset($path))
					return $class_name::construct($path, $service);
				else
					return $service;

			case 'NULL':
				return new $class_name($resource, $context);
			
			default:
				throw new Exception('Invalid toService input');
		}
	}

	public function &get(&$resource=null, &$context=null) {

		$class_name 		= get_called_class();
		// $class_keyword 	= snake_case('__'. $class_name);
		if (!isset($context) && isset($this) && $this instanceof self) // if non-static context
			$context = &$this;
		else {
			switch (gettype($context)) {
				case 'NULL':
					$context 	= &$app->$class_name; // TODO
					break;	
				case 'object':
				case 'array':
				case 'string':
					$context 	= &$class_name::construct($context);
					break;
				default:
					throw new Exception('Invalid Parent given for Config::get()');
			}
		}

		switch(gettype($resource)) {
			case 'NULL':
				// TODO
				return $class_name::construct($context);
			case 'string':
				if (strpos($resource, '/') !== false) {
					$key 	= strtok($resource, '/');
					$path 	= strtok($resource, '');
				} else
					$key 	= $resource;


				switch($key) {
					case '':
					case '.':
						$service = $context; 
						break;
					case '..':
						$service = $context->__parent;
						break;
					case '/':
						$service = $app;  // TODO ?
						break;
					default:
						// check for __keyword
						if (strpos($key, '__') === 0) {
							$service_key 	= substr($key, 2);
							$service 		= $service_key::get($context);
						} else
							$service 		= Config::construct($key, $context);
				}
				if (isset($path))
					return Config::get($path, $service);
				else
					return $service;
		}		
	}

	// ALIAS
	public static function &of(&$resource=null) {
		return self::construct($resource);
	}


/*
	public static function &of(&$resource=null) {
		$class_name = get_called_class();
		$class_keyword = snake_case('__'.$class_name);
		switch (gettype($resource)) {
			case 'object':
				if ($resource instanceof $class_name)
					return $resource;
				if (isset($resource->__array[$class_keyword])) {
					if (!$resource->__array[$class_keyword] instanceof $class_name)
						throw new Exception('Invalid '.$class_keyword.' key set.  Should be a '.$class_name.' object or null. Instead it\'s: '.gettype($resource->__array[$class_keyword]));
						// TODO Service might be a special case of this since it returns a general class not necessarily a Service class
					return $resource->__array[$class_keyword];
				} else
					return new $class_name($resource);
				// TODO handle external classes somehow
			case 'array':
				if (isset($resource[$class_keyword])) {
					if (!$resource[$class_keyword] instanceof $class_name)
						throw new Exception('Invalid '.$class_keyword.' key set.  Should be a '.$class_name.' object or null. Instead it\'s: '.gettype($resource[$class_keyword]));
					return $resource[$class_keyword];
				} else
					return new $class_name($resource);
			case 'string':
				// TODO pathing
			case 'NULL':
				// remove?
				return new $class_name();
			default:
				throw new Exception('Invalid toService input');
		}
	}

	public function &invoke(...$args) {
		return Service::invoke(...$args);
	}

	public function &__invoke(...$args) {
		return self::invoke(...$args);
	}

	public function &__get($path) {
		if (strpos($path, '/') !== false) {
			$key = strtok($path, '/');
			$path = strtok($path, '');
			return $this->$key->$path;
		}
		$key = $path;
		switch($key) {
			case '__value': 	// want raw value
			case '__path': 		// want string
			case '__config':	// return raw config object ($this)
			case '__key':		// want string
				return $this->__array[$key];
				// TODO $app->service_keyword->get($this);
			case '__parent':	// want reference to parent config
				return Config::of($this->__array[$key]);
			case '__service': 	// want reference to service
				return Service::of($this); // invoke it to return its value
				// TODO $app->service->get($this);
			case '__array':
				throw new Error('Shouldnt be possible');
			default:
				return $this->get($key);
		}
	}

		// TODO __config, __value, __key etc settings via this?
	// can be called with STATIC or NON-STATIC context
	public function &get($resource=null, &$parent=null) {
		$class_name = get_called_class();
		$class_keyword = snake_case('__'.$class_name);

		// handle static or non-static contexts
		if (isset($this) && $this instanceof self) // if non-static context
			$parent = &$this;
		else { // static context
			switch (gettype($parent)) {
				case 'NULL':
					return Config::of($resource);		
				case 'object':
				case 'array':
				case 'string':
					$parent = &Config::of($parent);
					break;
				default:
					throw new Exception('Invalid Parent given for Config::get()');
			}
		}
		// $parent is a Config past this point

		switch (gettype($resource)) {
			case 'NULL':  
				// $config->get(); returns $config
				return $parent;
			case 'string':
				if (strpos($resource, '/') !== false) {
					$key = strtok($resource, '/');
					$path = strtok($resource, '');
					return $this->$key->$path;
				}
				$key = $resource;
				
				$array = &$parent->__array[$key];
				if (!is_array($array))
					$array = ['__value' => $array];	
				$array['__parent'] 	= &$parent->__array;
				$array['__key'] 	= $key;
				$config = &Config::of($array);
				
				if ($key !== $resource)
					return Config::get($path, $config);
				return $config;

			//case 'array':
			//case 'object':
			default:
				// could maybe make array and object use their "__key"s to set as children in $parent?
				throw new Exception('Not handled right now:'. gettype($resource));
		}
	}


	// DEPRECATED?
	public function &getValue($key) {
		return $this->__array[$key];
	}
	*/

	// TODO separate into $app->set, $app->config->set
	// NOTE: saves $value as a COPY of whatever's setting, like standard __set
	public function set($resource=null, $value=null, &$parent=null) {
		// handle static or non-static contexts
		if (isset($this) && $this instanceof self) // if non-static context
			$parent = &$this;
		else { // static context
			switch (gettype($parent)) {
				case 'object':
				case 'array':
				case 'string':
					$parent = &Config::of($parent);
					break;
				case 'NULL':
					/* TODO should work:
					$parent = &Config::of($resource)->__parent;
					*/
				default:
					throw new Exception('Invalid Parent given for Config::get(), type is '.gettype($parent));
			}
		}

		switch (gettype($resource)) {	
			case 'string':
				if (strpos($resource, '/') !== false) {
					$key = strtok($resource, '/');
					$path = strtok($resource, '');
					$this->$key->$path = $value;
					return;
				}
				$key = $resource;

				// SET:
				switch (gettype($value)) {
					case 'array':
						$parent->__array[$key] = &$value;
						return;
					case 'object':
						if ($value instanceof Config || $value instanceof Service)
							$parent->__array[$key] = &$value->__array;
						// else treat object as normal value:
					default:

						$parent->__array[$key] = &$value;
						// TODO Value::set($key, $value, $parent);
				}
				return;

			//case 'NULL':
			//case 'object':
			//case 'array':
			default:
				throw new Exception('Not sure what to do with these yet, but maybe...');
		}
	}

	public function __set($path, $value) {
		$this->set($path, $value);
	}

	public function __isset($path) {
		if (strpos($path, '/') !== false) {
			$key 	= strtok($path, '/');
			$path 	= strtok($path, '');
			return isset($this->$key) && isset($this->$key->$path);
		}
		return isset($this->__array[$path]);
	}

	public function __unset($path) {
		if (strpos($path, '/') !== false) {
			$key 	= strtok($path, '/');
			$path 	= strtok($path, '');
			unset($this->$key->$path);
			return;
		}
		unset($this->__array[$path]);
		// TODO
	}

	public function &toArray() {
		return $array = &$this->__array;
	}

	public function __toString() {
		return ppp($this->__array, '', true, ['__parent', '__config', '__value', '__array'], 10, 1);
	}
}

// TODO need to define that __parent, __path, __key, __service, __config are all auto-invoked
	// what if just aliases?
	// what if e.g. Parent::getConfig($resource) just looks at $resource->__parent contents and then returns the parent's service
		// Path::getConfig() same deal but returns string...
// OR just force invokes to render.  even for __parent, __path, etc. hm
// OR __parent, __service, __config are all kept as aliases, so return services, and __path and __key need to be invoked

/**
$service->$key returns $key as a service always
$service->__$key returns $key as a service...
	sometimes
		exception for __path, __key, __parent, __config, __service
	always
		always return service
$service->__$key() runs $key as a function, returning whatever its return value is
$key::getConfig($service) returns the resource $service as a service
//$key::call($service, ...$args) runs the resource $service, returning its return value
	unnecessary.  if we have $key::getConfig($service), then we have $key::getConfig($service)(...$args);

way I see it, we just implement those static ones and then all others use one of them, dependent on situation.
Guarantees access to all services and values

UNFORTUNATE names: parent, function
**/