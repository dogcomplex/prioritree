<?php

/**
APPLICATION
Root service, acting as central point for all services that need access to a root.

USAGE:
$application = \Application::$default; to statically get the application root from anywhere

DEPENDENCIES:
Service

NOTES:
'Cms' is one particular instance of Application
**/

class Application extends \Service {

	public static $service_prefix 	= '__';
	public static $default 			= null;

	static function default() {
		if (!isset(self::$default))
			self::$default = new self();
			//throw new Exception('Application not initialized');
		return self::$default;
	}

	public __construct(...$args) {
		self::$default = $this;
		parent::__construct(...$args);
	}

}