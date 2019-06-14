<?php

/**
KEY
Returns the last token of the path of the current resource, or NULL if unknown.

Dependencies:
Config to handle arbitrary path/array/service data
Path/key to 

Usage:
$x->y->__key; returns string 'y' when x and y are services
$x->y->__key; returns Key config when x and y are configs
{new Config([])}->__key; returns null since no key defined on empty array
($x->y->__key)(); returns string 'y' when x and y are configs
Key::of($path or $config); returns string key of $path or $config


**/

class Key extends Service {	

	public static function of(&$target) {
		// $target may be Config, Path, or Array

		$config = \Config::of($target);
		$value 	= &$config->getRaw('__key');
		if ($value === null) {
			$value = false; // for caching purposes in case Path has Key dependencies (it does)
			return $value = ($config->__path->key)(); // assign Path to find Key
		} elseif ($value === false) {
			return null; // cache failure in case of dependency loop
		} elseif (is_string($value)) {
			// TODO part of key/validate
			return $value;
		}
	}
}