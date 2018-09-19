<?php

trait Brizy_Admin_Migrations_HelpersMigration {

	public function array_walk_recursive_and_delete(array &$array, callable $callback, $userdata = null) {
		foreach ($array as $key => &$value) {
			/*echo 'array_walk_recursive_and_delete --- function';
			echo '<pre>';
			print_r($key);
			print_r($value);
			echo '</pre>';*/

			if ( is_array($value) ) {
				$value = $this->array_walk_recursive_and_delete($value, $callback, $userdata);
				// check if is shortcode
				if ( isset($value['type']) && isset($value['value']) ) {
					$this->parse_shortcodes($value);
				}
			}
			if ($callback($value, $key, $userdata)) {
				//unset($array[$key]);
			}
		}

		return $array;
	}

	public function parse_shortcodes(array &$array) {
		if ( 'Line' == $array['type'] ) {
			$array = $this->parse_line($array);
		}

		/*echo '<pre>';
		echo 'parse_shortcodes';
		print_r($array);
		echo '</pre>';*/
		return $array;
	}

	public function parse_line(array &$v) {
		echo '<pre>';
		echo 'parse_line<br>';
		print_r($v);
		echo '</pre>';
		if ( isset( $v['value']['borderWidth'] )
			&& isset( $v['value']['mobileBorderWidth'] )
			&& $v['value']['borderWidth'] === $v['value']['mobileBorderWidth'] )
		{
			unset( $v['value']['mobileBorderWidth'] );
		}

		return $v;
	}

}

