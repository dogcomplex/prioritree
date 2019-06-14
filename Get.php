<?php

class Get extends \Service {
		
	
	public function &__invoke(...$args) {
		log_this();
		return $this->__function($args[0], $this->__parent());
	}

	/**
	Get
		__function
			resource
				may be NULL, indicating the $context should return itself
					== Get::__function($context, null, $return_type)
				may be a String Path, returning the Array or Value at that location (relative to $context if no leading '/')
				// TODO?: may be an array, returning a filter of $context with only the elements in $resource present
					// may be recursive
				// TODO?: may be a function, performing a function filter on $context, returning each matching element
			context
				may be NULL, or strings '', '/', or '.', indicating the Application root
				may be a String Path, indicating the value of that location relative to the Application root
				may be an array, which will be processed according to $return_type
			
			__notes
				May be called in Static or Non-Static context
					e.g. Get::__function('key', null, $resource) or $resource->key if $resource implements this __get function (or binds to it)
					$root->get('key') also valid, makes $context = $root;
			__todo
				questions
					how does $this and 'self' react with bindTo()?
			
	**/
	public function &__function(&$resource=null, &$context=null) {
		log_this();
		//$class_name = get_called_class();
		//$class_keyword = snake_case('__'.$class_name);

		$ROOT = \Root::default();
		if ($context === null)
			$context = &$ROOT;
		if ( !$context instanceof \Service )
			$context = &$ROOT->service($context);
		// $context is a Service now

		switch (gettype($resource)) {
			case 'NULL':  
				return $context;
			case 'string':
				if ($resource === '__get') {
					$get_get = clone $this;
					$get_get = 
				}
				return $context->__service($resource);
			case 'array':
			case 'object':
			default:
				// could maybe make array and object use their "__key"s to set as children in $context?
				throw new Exception('Not handled in get() right now:'. gettype($resource));
		}
	}

/** USE:
	$resource->$key
		calls $resource->__get($key); in Service class (or extension)
			attempts isset $resource->__get, object finds none (native isset) so triggers:
	__get:		calls $resource->__get(__get)
					calls $resource->__get = $root->get($key=__get, $context=$resource) // or equivalent
						detects $context is an object, so $return_type = object
						gets $context->__array, finds it (Service public property)
						looks for $array['__extend'], if found assigns to $key:
							$root->extend->inherit($key, $context) // or equivalent
								sets $array[$key][__extend] = $array[__extend].$key, unless previously set (TODO layers)
								// TODO might require turning $array[$key] = $value into some [__value = $value]
									// in which case is basically building a service.  mightcould just do that...
									// we ARE creating a service object REGARDLESS, so could be done here?
										// doesn't know if __alias will do it too though!
											// they should be mutually exclusive though
						does same for __alias
						// REMOVED: builds the service regardless of null value or not
							// if isset($array[$key]), builds it.  else shortcuts to returning $root->get function (right? or could still build)
						
						// TODO return array always? skip this step?
							// technically not needed until used. 
						then looks at $array[$key] and builds it as a service since $return_type = object
							$key_service = $root->service->build($key, $context)
								// handles changing a raw $value to [__value = $value]
								// treats null as a normal value? __value = null ? __key etc as normal?
								// knows keyword __ means auto-extends $root->get
								// handles assigning __key, __parent, __service, __class, etc
						// TODO? calls $key_service->__get(), to trigger any particular self-get functionality
							// if $key==__get would trigger $key_service->__get(__get) again, recursion... needs override to cancel
								// or $root->get->__get just returns $root->get.
							// would do $key_service->__call('__get', [])
								// which goes $key_service->__get('__get')
									// which does the above for that
								// then calls, $key_service_get();
						return &$key_service	
					$key_service->bindTo($this); // binds it here (necessary?)
					return &$key_service

				// NOTE: no isset($resource->$key) here. will return service object even if missing any data.
	$key:		calls ($resource->__get)($key)
					calls $root->get($key=$key, $context=$this) // or equivalent
						// see above
						returns $key_service
					$key_service->bindTo($this); // binds it here (necessary?)
					return &$__get__service
				
				return &$key_service
					
	isset($resource->$key)
		calls $resource->__isset($key)
			calls ($resource->__isset)($key)
				// calls $resource->__get(__isset) if not present (hardcoded isset) 
					// gets it (without using isset?)
				gets $resource->__array (public cached),
				checks $resource->__array[$key],
				checks $resource->__array[$key][$value],
				gets $extended = Service::build($resource->__array[__extend])()  === $resource->__extend()
				checks isset($extended->$key)
					// recurse
				gets $aliased = Service::build($resource->__array[__alias])()  === $resource->__alias()
				checks isset($aliased->$key)
					// recurse
				

 	

**/
}