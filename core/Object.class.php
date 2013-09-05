<?php

class Object {

	function __call($method, $args) {
		if (!isset($this->$method)) {
			throw new PException('Méthode "'.$method.'" indéfinie dans "'.get_class($this).'"');
		} else {
			return $this->$method;
		}
	}
}