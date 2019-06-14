<?php

class_alias('Function_Service', 'Function');
class Function_Service extends Service {
	function __invoke(...$args) {
		return self::call($this->__value, $args, Service::of($this->__parent));
	}

	public static function call($closure, $args, $context=null) {
		if (!is_callable($closure))
			throw new Exception('Incorrect Function definition.  Function is not callable');
		if (isset($context))
			$closure::bindTo($context);
		return $closure(...$args);
	}

	public static function &of(&$resource=null) {
		switch (gettype($resource)) {
			case 'object':
				// TODO if is_callable, just use itself? infinite loop..?
				if (isset($resource->__array))
					return self::of($resource->__array);
				else
					throw new Exception('Non-Service objects not supported yet');
			case 'array':
				if (isset($resource['__function'])) {
					$closure = Value::of($resource['__function']);
					$closure::bindTo($resource);
					return $closure;
				} else
					// TODO return null?
					throw new Exception('Function not defined for '. $service->__path);
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
