<?php

class Extend extends \Service{

	public function __invoke() {
		// just return the service pointed to by $this->__value (or whatever)
		
	}

	//RENAME: public function inheritExtendToChild($parent, $child_key)
		// handles the assignment of $parent->__extend->__value to $child = $parent->__extend->__value .'/'. $child_key
			// could handle any $child_key type as long as it has a __key set 
}