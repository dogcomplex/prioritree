<?php

/**
PHP
Storage for all the php global functions and modifications we make

**/

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


function __autoload($namespace) {
	$ROOT = \Root::default();
	$ROOT->$namespace;

	// TODO remove below, since above should auto-fetch files via $ROOT->$namespace->__get()
	if (file_exists($namespace.'.php'))
		require_once($namespace.'.php');
	else
		require_once(PascalCase($namespace).'.php');
}


function pp($target, $id='', $top=true, $blacklist=['__parent', '__config', '__value'], $max_depth=10, $blacklist_max_depth=1, $ignore_prefixes=false) {
	echo ppp($target, $id, $top, $blacklist, $max_depth) .'<br/>';
}

function ppp($target, $id='', $top=true, $blacklist=['__parent', '__config', '__value'], $max_depth=10, $blacklist_max_depth=1, $ignore_prefixes=false) {
	// TODO ignore_prefixes doesnt work, meh.
	
	if ($top && !$ignore_prefixes)
		$prefix = gettype($target). ": ";
	else
		$prefix = "\t:\t";
	$cache = [];
	if ( 
		$max_depth > 0
		&& (
			(is_array($target) && !empty($target))
			|| (is_object($target) && $target instanceof Closure)			// TODO empty object
		) 
	) {
		$s = '<ul class="collapsibleList">';
		foreach ($target as $key => $value) {
			$s .= '<li id="'.$id.'-'.$key.'">'. $key;
			if (gettype($value) !== 'array' && !$ignore_prefixes)
				$s .= ': <i>'. gettype($value) .'</i>';
			if (in_array($key, $blacklist, true)) {
				$s .= ppp($value, $id.'-'.$key, false, $blacklist, $blacklist_max_depth - 1, 0);
			} else
				$s .= ppp($value, $id.'-'.$key, false, $blacklist, $max_depth -1);
			$s .= '</li>';
		}
		$s .= '</ul>';
	} else {
		$s = '';
		switch(gettype($target)) {
			case 'boolean': 
				$s .= $prefix.'<i>'.($target ? 'true' : 'false').'</i>';
				break;
			case 'NULL':
				$s .= '';
				break;
			case 'array':
				if (empty($target))
					$s .= $prefix.'[]';	
				else
					$s .= $prefix.'<i>[...]</i>';
				break;
			case 'object':
				$s .= $prefix.' '. get_class($target). ' '. ppp(get_object_vars($target), '', false);
				/*
				if (is_callable($target))
					$s .= $prefix.'<i>function</i>';
				else
					$s .= $prefix.get_class($target);
				*/
				break;
			default:	
				if ($target === '')
					$s .= $prefix.'<i>""</i>';	
				else
					$s .= $prefix.$target;
				break;
		}
	}
	return $s;
}
 



function microtime_float() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}



function is_sequential($array, $limit=null) {
	if (!is_array($array))
		throw new Exception('Not an array');
	$i = 0;
	foreach($array as $key => &$value) {
		if ($key !== $i++)
			return false;
		if ($i > $limit)
			break;
	}
	return true;
}

function is_assoc($array, $limit=null) {
	if (!is_array($array))
		throw new Exception('Not an array');
	if ($limit === 1)
		return array_key_exists($array, 0);
	$i = 0;
	foreach($array as $key => &$value) {
		if (is_int($key))
			return false;
		if (++$i > $limit)
			break;
	}
	return true;
}




function is_static() {
	// asserts if currently in a static context or not
	$backtrace = debug_backtrace();
	return $backtrace[1]['type'] === '::';
}

function _log_to_string($log) {
	return ppp([
    	(isset($log['class']) && isset($log['type']) ? 
    		$log['class'] . $log['type'] 
    		: '<i>global</i>::'
    	).$log['function'] => $log
    ]);
}

function log_this() {
	$log = debug_backtrace()[1];
    print _log_to_string($log);
}

function print_trace() {
	$trace = debug_backtrace();
	array_shift($trace);
	$s = '';
	foreach ($trace as $log) {
		$s = print _log_to_string($log);
	}
	print $s;
}


// http://php.net/manual/en/function.array-map.php#116938
function array_map_recursive(callable $func, array $array) {
    return filter_var($array, \FILTER_CALLBACK, ['options' => $func]);
}


// CMS KEYWORD FUNCTIONS:

	function &service($context=null, $resource=null) {
		// return an object representing the given $resource
		// may be Service class type (default) or any particular class (if overwritten by __service keyword)
			// see \Service class for details
		return \Service::__service($context, $resource);
	}
	function &s($context=null, $resource) {
		return \Service::__service($context, $resource);
	}
	function &config($context=null, $resource=null) {
		return \Config::__function($context, $resource);
	}
	function &value($context, ...$args) {
		return \Value::__function($context, ...$args);
	}

	function path($context) {
		if (isset($context))
			return service($context)->__path();
		else
			return \Root::default()->__path();
	}
	function error($context) {
		// TODO have this plug into \Root->errors
		switch(gettype($context)) {
			case 'string':
			default:
				throw new \Exception($context);
		}
	}

	function to_array($context=null, $resource=null) {
		return \Config::toArray($context, $resource);
	}
	function is_config($context) {
		return is_array($context) && isset($context['__config']);
	}

// PATH FUNCTIONS (\Path ?)
	
	function walk($context, $resource=null) {  // AKA relative_path
		// TODO move to Path class?
		if (is_string($context))
			$context = str_replace('\\', '/', snake_case($context));
		if ($resource === null)
			return $context;
		else
			error('TODO calculate walk between context and resource');
	}
	function relative_path($context, $resource=null) {
		return walk($context, $resource);
	}
	function camelCase($string, $capitalise_first_char = false) {
		return $string;
	}
	function PascalCase($str) {
		return camelCase($str, true);
	}
	function snake_case($string, $separator = "_") {
	    return strtolower(
	    	preg_replace(
	    		'/(?<=\d)(?=[A-Za-z])|(?<=[A-Za-z])(?=\d)|(?<=[a-z])(?=[A-Z])/', 
		    	$separator, 
		    	$string
	    	)
	    );
	}
	function to_namespace($context) {
		// TODO move to Path class?
		$namespace = str_replace('/', '\\', PascalCase($context));
		if (strpos($namespace, '\\') !== 0)
			$namespace = '\\'. $namespace;
		return $namespace;
	}



// DEPRECATED
function test($function, $iterations=100000) {
	if (!is_callable($function))
		throw new Exception('Invalid function');
	$time_start = microtime_float();
	$mem_start = memory_get_usage();
	for ($i =0; $i < $iterations; $i++) {
		$function();
	}
	$time_end = microtime_float();
	$mem_end = memory_get_usage();
	$time = $time_end - $time_start;
	$mem = $mem_end - $mem_start;
	echo "Total time: $time seconds \t <br \>";
	echo "Total memory: $mem b \t <br \>";
}


function assert_test($test) {
	echo ($bool = assert($test) ? 'pass' : '<b>FAIL</b>') . '</br>';
	return $bool;
}

function refs_same(&$a, &$b, &...$args) {
  $not_b = $b === 0 ? 1 : 0;
  $a_orig = $a;
  $a = $not_b;
  $is_ref = $b === $not_b;
  $a = $a_orig;
  if ($args) 
  	return $is_ref && refs_same($b, ...$args);
  return $is_ref;
}

function arrayCopy( array $array ) {
    $result = array();
    foreach( $array as $key => $val ) {
        if( is_array( $val ) ) {
            $result[$key] = arrayCopy( $val );
        } elseif ( is_object( $val ) ) {
            $result[$key] = clone $val;
        } else {
            $result[$key] = $val;
        }
    }
    return $result;
}



// IDEAS:

function call($context, $args) {
	return service($context)(...$args);
}
function get($context, $resource) {
	return service($context, $resource);
}
function post($context, ...$args) {
	return call($context, ...$args);
}
