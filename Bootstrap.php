<?php

error_reporting(E_ALL);
define('ROOT_DIR', __DIR__.'/');
define('ROOT_FOLDER', ROOT_DIR);  // TODO remove

require_once(ROOT_DIR. 'Root/__Test.php');
exit();

////////////////////////

require_once(ROOT_DIR. 'Php.php');
require_once(ROOT_DIR. 'Service.php');
require_once(ROOT_DIR. 'Root.php');
require_once(ROOT_DIR. 'Config.php');


$ROOT_CONFIG = [
	'a' 		=> 1,
	'get'		=> [
		'__service'	=> '/Get'
	],
	'isset'		=> [
		'__service'	=> '/Isset'
	],
	'extend'	=> [
		'__service'	=> '/Extend'
	],
	'parent'	=> [
		'__service'	=> '/ParentService'
	],
	'set'		=> [
		'__service'	=> '/Set'
	],
	'value'		=> [
		'__service'	=> '/Value'
	], 

	// TODO:
	'__context'	=> [
		'__alias'	=> '__parent'
	],
	'context'	=> [
		'__alias' 	=> 'parent'
	],
	'back'		=> [
		'__alias'	=> 'parent'
	],

];
$ROOT = new \Root($ROOT_CONFIG);

exit();

// http://code.stephenmorley.org/javascript/collapsible-lists/
echo $js = '

<script type="text/javascript" src="../js/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="../js/CollapsibleLists.src.js"></script>

<script type="text/javascript">
	CollapsibleLists.apply();
	$(document).ready(function(){
		CollapsibleLists.apply();
	});
</script>
';

echo '
<script type="text/javascript" src="../js/jquery-3.2.1.min.js"></script>

<script type="text/javascript">
	
	$(document).ready(function(){
	    $("button").click(function(){
	        $(this).hide();
	    });
	});

	$(document).ready(function () {
	    setTimeout(function () {
	        $("#latest").load("arbitrate.php?key=soundbodyandmind #bot",  function(response, status, xhr){    
	            $("#time").html(Date.now());
	            CollapsibleLists.apply();
	        });
	        
	    }, 10000); // 10s

	});
</script>
';



class IssetService extends \Service  {
	function &__invoke(...$args) {
		log_this();
		
		$path = $args;
		// TODO bind this
		$context = &$this->__context();
		return 
			isset($context) 
			&& (
				isset($context->__config()[$path]) 
				|| isset($context->__extend()->$path) 
				|| isset($context->__alias()->$path)
			);
	}
}
class_alias('IssetService', 'Isset');

class Extend extends \Service {
	function &__invoke(...$args) {
		
		log_this();

		$__context = &$this->__context();  // AKA &$this->__parent();
		return self::__function($__context);
	}

	static function &__function($__context=null, ...$args) {
		// TODO handle in-place extending via $__value=null, where you can return a temporary service here cloning $__context but with __extend = $__value
			// if __extend already set, extends that?

		log_this();

		if (!isset($__context))
			error('Context required');
		$__context = service($__context);

		// TODO handle __value...
		if (isset($__context->__extend)) {
			$__value = $__context->__value('__extend');
			if (!is_string($__value))
				error('Extend must be a string path (for now)');
			return $__context->$__value; // GET the extend path relative to $context
				// if $extend is a root path, it will get the Root
				// returns a Service of that extend with default GET behavior
		}
		return null;
	}

	function inherit($resource=null, $context=null) {
		/*
			Inherit parent's __extend to its child $resource
		*/
	}
}

class ParentService extends \Service {
	/**
	ALLOWABLE FORMATS:
	__parent = &$config_reference_to_parent

	**/
	function &__invoke(...$args) {
		log_this();
		
		$config = &$this->__config();
		$null = null;
		if (!isset($config['__parent']))
			return $null;
		$parent_config = &$config['__parent'];

		// fetch or create a service out of the $parent_config
		return service($parent_config);
	}	
}




class FunctionService extends \Service {

	// TODO

	// $context
	function &__invoke(...$args) {
		$context = array_shift($args);
		$function = $this->__value();

		// ETC:

		// default:
		if (isset($function)) {
			$function->bindTo($context);
			return $function(...$args);
			// ETC
		} else
			return $context->__value();			
	}

}
class_alias('FunctionService', 'Function');


class Method extends \Service {
	// === Function but first arg $conext is implied to be $this->__context() == $this->__parent();
	function &__invoke(...$args) {
		$context = $this->__context();
		return $context->__function($context, ...$args);	
			// or reverse. whatever	
	}
}


class Set extends \FunctionService {
	
	public $new_resource = null;
		// default to set when given $new_resource is null

	public static function &__function($context=null, ...$args) {
		log_this();
		$replace = array_shift($args) ?? true;
		
		if ($replace)
			return $this->replace($new_resource);
		else
			return $this->mergeUnder($new_resource);
		// TODO when to merge under, over? hmmm
	}

	public function replace(&$new_resource) {
		// call like $resource->__set->replace($new_resource);
		// or $resource->__replace($new_resource); probably
		// or $resource->__set($new_resource, $replace=true); probably
		// or $resource->__set($new_resource); probably
		
		$this->__context()->__config = $new_resource; 
			// calls $context->__set('__config', $new_resource);
			// calls $context->_config = $context->__config($new_resource);
			// spawns recursive sets? nah, just blanket replaces
	}

	/*
	public function merge(&$new_resource, $under=true) {
		if ($under)
			return $this->mergeUnder($new_resource);
		else
			return $this->mergeOver($new_resource);
	}

	public function mergeOver(&$new_resource) {
		$this->__context()->__config = $context->__config($new_resource); 
	}

	public function mergeUnder(&$new_resource) {
		// call like $resource->__set->merge($new_resource);
		// or $resource->__set($new_resource, $replace=false);
		// or $resource->__set($new_resource, $replace=false);
		
		$context = $this->__context();
		$new_service = service($new_resource);
			// converts $new_resource into a config config
		foreach($new_service as $key => $value) {
			if (isset($context->$key))
				$context->$key->__set->mergeUnder($value);
				// hmmmm. maybe would work?
			else
				$context->$key = $value;
				// regular __set
		}

		// TODO ORRR could take both configs, go through each key, and then somehow call $this->mergeUnder()
		// statically on them... hmmm...
		// if we make self::mergeUnder($context, $new_resource); work with any specified $context, we're golden
			// ServiceName::__callStatic($path, ...$args) {
			//   $context = $args[0];
			//   $function = $ROOT->$serviceName->$path->__function; // returns a Function object
			// 	 return $function($context, ...$args); 
			// } 
		// then can just config_walk()
	}
	*/

	/*
	can already assume $service is built, $service->__set is $this class (duh)
	*/	

}

class Value extends \Service {
	function &__invoke(...$args) {
		log_this();
		
		$context = &$this->__context();
		if (isset($args[0]))
			return $context->$resource->__value();
		return $context->__config()['__value'] 
			?? $context->__alias()->__value()
			?? $context->__extend()->__value()
			?? null;
	}
}





$config_config = require_once(ROOT_DIR. 'config.cfg');
pp($config_config);
$config_service = service(null, $config_config);
pp($config_service);

require_once(ROOT_DIR. 'Config/__Test.php');
require_once(ROOT_DIR. 'Root/__Test.php');

require_once(ROOT_DIR. 'Service/__Test.php');
