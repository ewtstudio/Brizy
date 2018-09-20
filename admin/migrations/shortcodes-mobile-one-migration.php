<?php

class Brizy_Admin_Migrations_ShortcodesMobileOneMigration implements Brizy_Admin_Migrations_MigrationInterface {
	use Brizy_Admin_Migrations_HelpersMigration;

	/**
	 * Return the version
	 *
	 * @return mixed
	 */
	public function getVersion() {
		return '1.0.30';
	}

	/**
	 * 
	 */
	public function execute() {
		if( is_admin() ) {
			return;
		}

		global $wpdb;
		// query all posts (all post_type, all post_status) that have meta_key = 'brizy' and is not 'revision'
		$result = $wpdb->get_results("
			SELECT p.ID, pm.meta_value FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = 'brizy'
			AND p.post_type != 'revision'
		");
		/*echo '<pre>';
		print_r($result);
		echo '</pre>';*/

		foreach ( $result as $item ) {
			if ( 153 == $item->ID ) {
				$new_meta = $this->migrate_post($item->meta_value);
			}
		}

		die();
	}

	/**
	 * Migrate post
	 */
	public function migrate_post($old_meta) {
		$new_meta     = $old_meta;
		$old_meta_arr = unserialize($old_meta);
		if ( isset( $old_meta_arr['brizy-post']['editor_data'] ) ) {
			$old_json = json_decode( base64_decode( $old_meta_arr['brizy-post']['editor_data'] ), true );

			$debug = true;
			//$debug = false;
			if ( $debug ) {
				// write in before.json to track the changes
				$result_old = file_put_contents('1-before.json', json_encode(
					$old_json,
					JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT
				));
				echo 'put-contents-before-json=='.$result_old.'<br>';
			}

			// todo: need here to inspect if is allow inline function in PHP 5.4
			$new_arr = $this->array_walk_recursive_and_delete($old_json, function ($value, $key) {
				if ( is_array($value) ) {
					return empty($value);
				}

				if ( isset($value['type']) && isset($value['value']) ) {
					// if is shortcode return true
					return true;
				}
			});

			if ( $debug ) {
				// write in before.json to track the changes
				$result_new = file_put_contents('2-after.json', json_encode(
					$new_arr,
					JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT
				));
				echo 'put-contents-before-json=='.$result_new.'<br>';
			}
		}

		return $new_meta;
	}

	public function parse_shortcodes(array &$array) {
		// Line
		$array = $this->unset_mobile_key( $array, "Line", "mobileWidth" );
		$array = $this->unset_mobile_key( $array, "Line", "mobileBorderWidth" );

		return $array;
	}

}
