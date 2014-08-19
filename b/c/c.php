<?php
namespace b\c;

class C {
	public static function init() {
		echo __CLASS__, '<br/>';
		
		$std = new stdClass();
		
		var_dump($std);
		
	}
}