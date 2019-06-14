<?php

require_once(ROOT_DIR. "Service.php");

/*
ROOT

Singleton class (for now) which represents the topmost node in the Service tree of an Application.
(Alternate name: Application?  Root seems more general)

**/
class Root extends \Service{
	
	static protected $_root;
		// the last initialized Root service

	public static function default() {
		return self::$_root;
	}

	public function __construct(&$context=[]) {
		log_this();
		if (!is_array($context)) // TODO refine
			throw new \Exception('Root must be initialized with a root source array');
		self::$_root = &$this;
		parent::__construct($context); // Otherwise a normal service
	}

	// Helper for $ROOT->service()
	public static function service($context=null, $resource=null) {
		log_this();
		if (isset($this))
			return $this->__service($context, $resource);
		else
			return self::__service($context, $resource);
	}

	public static function array($context=null, $resource=null) {
		return \ArrayService::__function($context, $resource);
	}

}