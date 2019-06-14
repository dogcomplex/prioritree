<?php

/**
PATH
The class defining the string representation of a Path in the Application.


**/

class Path extends \Service {

	const ROOT 	= '/';
	const SEP 	= '/';

	/**
	__value 	- The path string
	key 		- The end token of the path

	Inherited from Service:
	__path		- The path to this path!
	__parent 	- The parent service containing this path
	__config	- The config object holding data for this path
	**/

	public function __get($key) {
		if ($key === 'key')
			return $this->key 		= substr($this->__value, strrpos($this->__value, '/')+1);
		elseif ($key === '__value')
			return $this->__value 	= ($this)();
	}

	public static function composeFromParentPathAndKey(&$target) {
		$service 		= \Service::construct($target);
		$parent_path 	= &$service->__parent->__path;
		$key 			= &$service->__key;
		// TODO root logic
		if (is_string($parent_path) && is_string($key))
			return $parent_path .'/'. $key;
		return null;
	}

	public static function extractKeyFromPath(&$target) {
		// TODO might just be a Path service function
		\Service::get([$target, 'key']);
		$path = \Path::construct($target)(); 
			// constructs and invokes path to return string Path of $target
		// return null if Path not found
		if (!isset($path))
			return null;
		$key = substr($path, strrpos($path, '/')+1);
		return $key; 
		// TODO error check above
	}

	public static function of(&$target) {
		// returns string path to target or null if undefined / unknown
		// $target may be Config, Path, or Array

		$config = \Config::of($target);
		$value 	= &$config->value('__path');
		if ($value === null) {
			$value = false; // for caching purposes in case Key has Path dependencies (it does)
			return $value = \Path::composeFromParentPathAndKey($target);
		} elseif ($value === false) {
			return null; // cache failure in case of dependency loop
		} elseif (is_string($value)) {
			// TODO part of key/validate
			return $value;
		}
	}

	public function __invoke(&$target=null) {
		// returns self as a string (or null if undefined)
		// if $target specified, returns path relative to self + target

		if ($this->__config->__value !== )
	}
}

/**
Rough Draft:

$Path
	defines all essential constants/properties of paths
	converts a relative path to an absolute path relative to this path
$Path/key returns the key of this path

**/