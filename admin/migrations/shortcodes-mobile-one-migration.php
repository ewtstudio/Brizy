<?php

class Brizy_Admin_Migrations_ShortcodesMobileOneMigration implements Brizy_Admin_Migrations_MigrationInterface {

	use Brizy_Admin_Migrations_HelpersTrait;

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
		$result = $this->get_posts_and_meta();

		// parse each post
		foreach ( $result as $item ) {
			$instance = Brizy_Editor_Storage_Post::instance($item->ID);
			$old_meta = $instance->get(Brizy_Editor_Post::BRIZY_POST, false);

			if ( is_array($old_meta) ) {
				$json_value = base64_decode($old_meta['editor_data']);
			}
			else {
				// this method works only in this function
				$json_value = $old_meta->get_editor_data();
			}

			// test only for specific post id
			//if ( 153 == $item->ID ) {
				if( !is_null($json_value) ) {
					$new_meta = $this->migrate_post($json_value, $item->ID); // $item->ID only for test with json
				}
			//}
		}
		die();
	}

	/**
	 * Get posts and meta
	 */
	public function get_posts_and_meta() {
		global $wpdb;

		// query all posts (all post_type, all post_status) that have meta_key = 'brizy' and is not 'revision'
		return $wpdb->get_results("
			SELECT p.ID, pm.meta_value FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = 'brizy'
			AND p.post_type != 'revision'
			AND p.post_type != 'attachment'
		");
	}

	/**
	 * Migrate post
	 */
	public function migrate_post($json_value, $post_id) {
		$new_json = $json_value;
		$old_arr  = json_decode($json_value, true);

		//$debug = true;
		$debug = false;
		if ( $debug ) {
			// write in before.json to track the changes
			$result_old = file_put_contents($post_id.'-before.json', json_encode(
				$old_arr,
				JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT
			));
			echo 'put-contents-before-json=='.$result_old.'<br>';
		}


		// todo: need here to inspect if is allow inline function in PHP 5.4
		$new_arr = $this->array_walk_recursive_and_delete($old_arr, function ($value, $key) {
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
			$result_new = file_put_contents($post_id.'-after.json', json_encode(
				$new_arr,
				JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT
			));
			echo 'put-contents-before-json=='.$result_new.'<br>';
		}

		return $new_json;
	}

	/**
	 * Parse shortcodes
	 */
	public function parse_shortcodes(array &$array) {
		// Line
		$array = $this->unset_mobile_key( $array, "Line", "mobileWidth" );
		$array = $this->unset_mobile_key( $array, "Line", "mobileBorderWidth" );

		// Spacer
		$array = $this->unset_mobile_key( $array, "Spacer", "mobileHeight" );

		// Video
		$array = $this->unset_mobile_key( $array, "Video", "mobileSize" );

		// EmbedCode
		$array = $this->unset_mobile_key( $array, "EmbedCode", "mobileWidth" );

		// Map
		$array = $this->unset_mobile_key( $array, "Map", "mobileSize" );
		$array = $this->unset_mobile_key( $array, "Map", "mobileHeight" );

		// SoundCloud
		$array = $this->unset_mobile_key( $array, "SoundCloud", "mobileWidth" );
		$array = $this->unset_mobile_key( $array, "SoundCloud", "mobileHeight" );

		// Countdown
		$array = $this->unset_mobile_key( $array, "Countdown", "mobileWidth" );

		// ProgressBar
		$array = $this->unset_mobile_key( $array, "ProgressBar", "mobileWidth" );

		// Wrapper
		$array = $this->mobile_migation_wrapper_align( $array, "Wrapper" );

		// Cloneable
		$array = $this->mobile_migation_wrapper_align( $array, "Cloneable" );

		// WOOCategories
		$array = $this->unset_mobile_key( $array, "WOOCategories", "mobileWidth" );

		// WOOPages
		$array = $this->unset_mobile_key( $array, "WOOPages", "mobileWidth" );

		// WOOProducts
		$array = $this->unset_mobile_key( $array, "WOOProducts", "mobileWidth" );

		// WPSidebar
		$array = $this->unset_mobile_key( $array, "WPSidebar", "mobileWidth" );

		// WPCustomShortcode
		$array = $this->unset_mobile_key( $array, "WPCustomShortcode", "mobileWidth" );

		// WOOProductPage
		$array = $this->unset_mobile_key( $array, "WOOProductPage", "mobileWidth" );

		// WPNavigation
		$array = $this->unset_mobile_key( $array, "WPNavigation", "mobileItemPadding" );

		// need to finish Column
		/*$array = $this->unset_mobile_key( $array, "Column", "mobileBgImageWidth" );
		$array = $this->unset_mobile_key( $array, "Column", "mobileBgImageHeight" );
		$array = $this->unset_mobile_key( $array, "Column", "mobileBgImageSrc" );*/

		return $array;
	}

	/**
	 * Special Migration for Wrapper and Cloneable "Align"
	 */
	public function mobile_migation_wrapper_align(array &$array, $shortcode = "") {
		if ( empty($shortcode) ) {
			return $array;
		}

		if ( $shortcode == $array['type'] ) {
			if ( isset( $array['value']['horizontalAlign'] )
				&& isset( $array['value']['mobileHorizontalAlign'] )
				&& $array['value']['horizontalAlign'] === $array['value']['mobileHorizontalAlign'] )
			{
				unset( $array['value']['mobileHorizontalAlign'] );
			}
			else
			{
				// !Attention this need only 1-time execution in JSON (to not apply to the same JSON 2 times)
				if ( isset( $array['value']['horizontalAlign'] )
					&& $array['value']['horizontalAlign'] !== "center"
					&& !isset( $array['value']['mobileHorizontalAlign'] ) )
				{
					$array['value']['mobileHorizontalAlign'] = "center";
				}
			}
		}

		return $array;
	}

}
