<?php
/**
 * Sets up our custom post type for template kits.
 *
 * This class registers the Template Kit custom post type.
 *
 * @since      1.0.0
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/includes
 * @author     Envato <->
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register the custom post type for the plugin.
 *
 * Maintain a list of all the labels and settings that are
 * used in the custom post type and register the custom post type
 * with the core register_post_type() function.
 *
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/includes
 * @author     Envato <->
 */
class Template_Kit_Export_Options {

	const OPTIONS_KEY = 'envato_tk_settings';

	/**
	 * Save data for template kit
	 *
	 * @since     1.0.0
	 * @param    mixed $key The key to be saved in template kit array.
	 * @param    mixed $value The value to the key to be saved in the template kit array.
	 */
	public static function save( $key, $value ) {
		$all_options         = self::get();
		$all_options[ $key ] = sanitize_text_field( $value );
		update_option( self::OPTIONS_KEY, $all_options );
	}

	/**
	 * Get data for template kit
	 *
	 * @since     1.0.0
	 * @param    mixed $key The key to be saved in template kit array.
	 * @param    mixed $default The value to the key to be saved in the template kit array.
	 * @return   mixed Options array saved for the template kit
	 */
	public static function get( $key = false, $default = false ) {
		$all_options = get_option( self::OPTIONS_KEY, array() );
		if ( ! is_array( $all_options ) ) {
			$all_options = array();
		}
		if ( $key ) {
			if ( ! empty( $all_options[ $key ] ) ) {
				return maybe_unserialize( $all_options[ $key ] );
			}
			return $default;
		}
		return $all_options;
	}

}
