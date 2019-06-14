<?php

require_once(ROOT_DIR. "Service.php");

/**
Config 
-------------------------

A specialized array (or TODO: ArrayObject?) containing hierarchical keys in the format used internally to this CMS system.
	NOTE: all Arrays are Configs and vice-versa for now.  Just an important type distinction - particularly for sub-functions that care about those keywords.

Currently this is just a static class containing all custom Config functionality related to the CMS system.
Any service may go $service->__config; and return an instantiation of this object
	Though for now it does nothing special (all functions on this class are static), and so is not recommended.
	Invoking it ($service->__config)(); calls \Config::toConfig($service); which should be the same as if just calling $service->__config() directly
Any custom functions generally-applicable to any config belong here.

TODO / FUTURE WORK:
	Will likely wrap the standard php array functions and arrayaccess (e.g. array_merge(), $config['key']) to use any Resource as if it's an array.
	Will likely extend ArrayObject so $service->__config returns an actually useful object 
		The php object representation of the same raw config returned via $service->__config()


KEYWORDS USED:
	__config
	__service
	__parent
	__key
	__function
	__extend
	__alias
	// __layers TODO
 
DEPENDENCIES:
	\Root
	\Service
	\Closure

**/

class Config extends \Service {

	// $service->__config(...$args) === \Config::toConfig($service, ...$args);
	function &__invoke(...$args) {
		return self::toConfig($this->__parent(), ...$args);
	}

	static function &__function(&$context=null, ...$args) {
		return self::toConfig($context, ...$args);
	}

	public static function &toArray(&$context=null, $options=null) {
		// for now this is the same as toConfig
		// future plans: it will still be the same, except it guarantees a php array type, while toConfig may return an ArrayObject type  (Config)
		return self::toConfig($context, $options);
	}

	public static function &toConfig(&$context=null, $options=null) {
		// Takes a resource and returns the corresponding config (by finding, converting, or extracting)
			// given a string, returns the resource located at the string's path relative to root
		// Leaves a recursive reference to the config within the config
		// TODO use options, as defined in /Config.cfg, located at $ROOT->config

		switch(gettype($context)) {
			
			case 'array':
				$config = &$context;
				break;

			case 'NULL':
				$config = [];
				break; 

			case 'integer':
			case 'string':
				$config = &self::get(null, $context);
				break;

			case 'object':
				if ($context instanceof \Service && isset($context->__config))
					$config = &$context->__config();
				else
					error('Not an instance of Service');
				break;
			
			default:
				error('Invalid resource');
		}
		if (!isset($config['__config']))
			$config['__config'] = &$config;
		return $config;
	}
	public static function &resourceToConfig($context) {
		// ALIAS of toConfig()

		return toConfig($context);
	}

	public static function &valueToConfig($value=null) {
		// converts a VALUE to a config
		// does NOT treat strings as resource ids, only raw values
		// TODO problem: valueToConfig($context === $value) ???
		
		switch(gettype($value)) {
			case 'array':
				$config = &$value;
				break;

			case 'NULL':
				$config = [];
				break;

			case 'object':
				if (isset($value->__config))
					$config = $value->__config();
				elseif ($value instanceof \Closure) {
					$config = [
						'__function'	=> $value
					];
				} elseif ($value instanceof \Service) {
					$config = [
						'__service'		=> $value
					];
				}
				// else default (no break):
			default:
				$config = [
					'__value'		=> $value
				];
		}
		
		if (!isset($config['__config']))
			$config['__config'] = &$config;

		return $config;
	}


	public static function &get(&$context=null, $path=null) {
		// converts a RESOURCE $context to a config, with additional optional depth $path
		// TODO put in \Config\__get\__function

		if (!isset($context))
			$context = &\Root::default()->__config();
		elseif (!is_config($context))
			$context = &self::toConfig($context); 
			
		switch (gettype($path)) {
			
			case 'NULL':  
				return $context;

			case 'integer':
				$path = ''.$path;
			case 'string':
				if ($pos = strpos($path, '/') !== false) {
					$key = substr($path, 0, $pos-1);
					$path = substr($path, $pos);
					return self::get(self::get($context, $key), $path);
				}
				$key = $path;

				switch($key) {
					// PATH routing:
					case '': // root, result of leading slash "/foo"
					//case '/':
						return \Root::default()->__config();
					case '.':
						return $context;
					case '..':
						return service($context)->__parent()->__config();		
					default:

						$config = &self::valueToConfig($context[$key]);		
						
						// SET CONFIG											
						$config['__key'] 	= $key;  // TODO: what if __key has custom __function logic? 
						$config['__parent'] 	= &$context;  // TODO: what if __parent has custom __function logic?
						
						if (!isset($config['__extend']) && strpos($key, '__') === 0) {
							// TODO merge __extend in
							$config['__extend'] = '/'. substr($key, 2);
								// auto-inherit root services
						}
						if ($key !== '__extend' && isset($context['__extend']))
							service($context)->__extend->inherit($key);
							// could do \Extend::inherit($context, $key);
						if ($key !== '__alias' && isset($context['__alias']))
							service($context)->__extend->inherit($key);

						// SET IT
						//pp([$context, $key, $config, debug_backtrace()]);
						$context[$key] = &$config;
						
												
						/*
						// TRIGGER FILE SEARCH
						// TODO put this in \Root::file($config) ?  \File::__function($config) ?  service($config)->__file() ?
						$file_config = self::get($config, '__file');
						switch(gettype($file_config)) {
							
							case 'boolean':
								// skip check if true (file exists and is checked) or false (file does not exist but was checked)
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
								$file_config_value = self::get($file_config, '__value');
								switch(gettype($file_config_value)

							$config['__file'] = [];

						*/

						return $context[$key];	
					
				}

			default:
				error('Not supported right now.');  
				// TODO config or objects passed to __config could return a new config that filters the $context->__config() 
				// or could append to it.  TO BE DECIDED

		}
	}

	// TODO:


	// treat these as if operating on a raw array. i.e. no special treatment of __keywords etc
	public function &offsetGet($path) {
		
	}
	public function &offsetSet($path, $value) {
		
	}
	public function &offsetExists($path) {
		
	}
	public function &offsetUnset($path) {
		
	}

}