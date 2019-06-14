<?php


class Config extends ArrayObject {
	
	const __VALUE = '__value';
		// only key that will NOT be casted to a Array (extends ArrayObject) array
	const __ARRAY = '__array';
		// keyword that will cast this object to a (php primitive) array() when called
		// TODO does this kill speed?

	public function __construct(
		$resource = null, 
		$flags = ArrayObject::STD_PROP_LIST|ArrayObject::ARRAY_AS_PROPS, 
		$iterator_class = "ArrayIterator"
	) {
		switch(gettype($resource)) {
			case 'object':
				if ($resource instanceof Config)
					$config = &$resource; // TODO test
				if (isset($resource->__config))
					$config = &$resource->__config;
				if (isset($config)) {
					if (!$config instanceof Config)
						throw new Exception('Invalid Config given to constructor');
					$array = $config->getArrayCopy();
				} else
					$array = [];
				parent::__construct($array, $flags, $iterator_class);
				//$this->__array = &$array; // let's hope this maintains! else need to override offsetGet (probably safer)
				// attempt to update __config:
				$resource->__config = &$this;	
				break;
			case 'array':
				parent::__construct($resource, $flags, $iterator_class);
				//$this->__array = &$array;	
				break;
			case 'string':
				// TODO find from app root and convert into new Config	
			default:
				throw new Exception('Invalid input '. gettype($resource));
		}
	}

	public function __toString() {
		return ppp($this, '', true, ['__parent', '__config', '__value', '__array'], 10, 1);
	}

	/*
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