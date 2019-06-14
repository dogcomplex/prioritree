<?php

class ConfigArray extends ArrayObject implements ArrayAccess {
	
	public static function &of(&$resource = null) {
		switch(gettype($resource)) {
			case 'array':
				$array = &$resource;
				break;

			case 'object':
				$array = &$resource->__array ?? [];
				if (!is_array($array))
					$array = [];
					// TODO throw error for invalid object use of __array?
				break;

			case 'NULL':
				$array = [];
				break;

			case 'string':
				// hmmmm... TODO strings are paths?
			default:
				$array = [
					'__value'	=> $resource
				];
		}
		$array['__array'] = &$array;  // always set?
		return $array;
	}


	/*
	TODO experiment with using this as a replacement for arrays/configs dichotomy
		- right now don't like it since can't use a single data object for both __array and __config (array syntax and object syntax, respectively) without rebuilds happening
			- i.e. no way to share the same data reference. would be stuck with this data structure
			- not the worst thing... after all, gotta export in/out of JSON and whatnot anyway
			- anything that wants to just edit a plain array to do its work would have to convert this to array though (slow, heavy)
				- though presumably that'd be a minority of services, since most could just use this as ArrayAccess etc
		

	const __VALUE = '__value';
		// only key that will NOT be casted to a Array (extends ArrayObject) array
	const __ARRAY = '__array';
		// keyword that will cast this object to a (php primitive) array() when called
		// TODO does this kill speed?

	public function __construct(&$resource=null) {
		switch(gettype($resource)) {
			case 'object':
				if ($resource instanceof Config)
					$config = &$resource; // TODO test
				if (isset($resource->__config))
					$config = &$resource->__config;
				if (isset($config)) {
					if (!$config instanceof Config)
						throw new Exception('Invalid Config given to constructor');
					$array = $config->unserialize();
				} else
					$array = [];
				parent::__construct($array);
				$this->__array = &$array; // let's hope this maintains! else need to override offsetGet (probably safer)
				// attempt to update __config:
				$resource->__config = &$this;	
			case 'array':
				parent::__construct($resource);
				$this->__array = &$array;	
			case 'string':
				// TODO find from app root and convert into new Config
			case 'NULL':
				return	
			default:
				throw new Exception('Invalid input');
		}
	}

	
	public static function get (
		$resource, 
			// $resource may be a string Path, object, array, etc and may be found relative to $context
		&$context = null
			// allows set context on static calls
	) {

		if (isset($this))
			$context = &$this;
		switch(gettype($context)) {
			case 'object':
				$array = &$context->__array;
			case 'array':
				$array = &$context;
			case 'string':
				// get the array from Path $context (relative to root)
				$array = Array::get($context, Application::root());
		}
		if (!is_array($array))
			throw new Exception('Context has no array');

		switch(gettype($resource)) {
			case 'string':
				if (strpos($resource, '/') !== false) {
					$key = strtok($resource, '/');
					$path = strtok($resource, '');
					return Array::get($path, Array::get($key, $context));
				}
				$value = &$array[$key];
				if (in_array($key, IMMUNE_KEYS))
					return $value;
				if (is_array($value))

			case 'array':
				return $resource;


		}	

	}

	public function __call($key, $args) {
        if (!is_callable($key) || substr($key, 0, 6) !== 'array_')
            throw new BadMethodCallException(__CLASS__.'->'.$key);
        
        return call_user_func_array(
        	$key, 
        	array_merge(
        		[$this->getArrayCopy()], 
        		$args
        	)
        );
    }



    public function offsetSet($key, $value) {
        if($key === null)
            parent::append($value);
        else
            parent::offsetSet($key, $value);
    }
    */

}