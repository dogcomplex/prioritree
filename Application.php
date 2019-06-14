<?php

/**
APPLICATION
Root service, acting as central point for all services that need access to a root.

Usage:
$application = \Application::$default; to statically get the application root from anywhere

Dependencies:
Service

Notes:
'Cms' is one particular instance of Application
**/

class Application {

	public static $root;
		// default Application root
	public $__array; 
		// essential pointer
	protected $__get;  
		// cache.  locked-in on construct to /get
	protected $__set;  // cache
		// cache.  locked-in on construct to /set

	public __construct(&$array=[]) {
		if (!is_array($array))
			throw new Exception('Invalid input: not an array');
		// TODO $context in constructor?
		$this->__array = &$array;

		self::$root = &$this;

		
		// initialize GET (force override)
		$this->_bootstrapSetService('get', $get = new Get($this->__array['get']));
		$this->_bootstrapSetService('__get', $get, true);

		// TODO CALL? ISSET?

		// TODO extends Service?
		//parent::__construct(...$args);
	}



	protected _bootstrapGetService(
		$key, 
			// string
		&$service, 
			// object reference
		$cache = false
			// bool
	) {
		/*
			Should only be used for services that can't be run without a bootstrap 
			Use standard Get and let the system do its work otherwise
		*/

		if (!isset($this->__array[$key]))
			$this->__array[$key] = [];
		$this->__array[$key]['__parent']	= &$this;
		$this->__array[$key]['__key']		= $key;

		$service = &$this->__array[$key]['__service'] = $service;
		if (is_callable($service))
			$service->bindTo($this);
		if ($cache) {
			$this->$key = $service;
			$this->__array[$__key]['__cached'] 	= true;
		}
	}

	public __get($path) {
		// works because of __construct _bootstrapService(
		return ($this->__get)($path);
	}
				
	public __set($path, $value) {
		return ($this->__set)($path, $value);
	}

	// get($path, $context);
	public __call($path, $args)
		return ($this->$path)(...$args);			
	
	public __isset($path) {
		return ($this->__isset)($path);
	}

}