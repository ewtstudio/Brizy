<?php

trait Brizy_Admin_Migrations_HelpersMigration {

	public function array_walk_recursive_and_delete(array &$array, callable $callback, $userdata = null) {
		foreach ($array as $key => &$value) {
			if ( is_array($value) ) {
				$value = $this->array_walk_recursive_and_delete($value, $callback, $userdata);
				// check if is shortcode
				if ( isset( $value['type'] ) && isset( $value['value'] ) ) {
					$this->parse_shortcodes($value);
				}
			}
		}

		return $array;
	}

	public function parse_shortcodes(array &$array) {
		// rewrite this function in your class

		return $array;
	}

	public function unset_mobile_key(array &$array, $shortcode = "", $mobile_key = "") {
		if ( empty($shortcode) && empty($key) ) {
			return $array;
		}

		if ( $shortcode == $array['type'] ) {
			/*echo '<pre>';
			echo 'before';
			print_r($array);
			echo '</pre>';*/
			// replace "mobile" with empty string then make first letter lowercase
			$key = lcfirst( str_replace("mobile", "", $mobile_key) );
			if ( isset( $array['value'][$key] )
				&& isset( $array['value'][$mobile_key] )
				&& $array['value'][$key] === $array['value'][$mobile_key] )
			{
				unset( $array['value'][$mobile_key] );
			}
			/*echo '<pre>';
			echo 'after';
			print_r($array);
			echo '</pre>';*/
		}

		return $array;
	}

}

