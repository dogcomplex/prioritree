<?php

class Value extends \Service {
	
	public static function &of($resource) {
		switch (gettype($resource)) {
			case 'object':
				if (isset($resource->__array['__value']))
					return $resource->__array['__value'];
				else
					return null;
				// TODO handle external classes somehow
			case 'array':
				if (isset($resource['__value']))
					return $resource['__value'];
				else
					return null;
			case 'string':
				// TODO pathing
			case 'NULL':
				// remove?
				return null;
			default:
				throw new Exception('Invalid Value::of() input');
		}
	}

}