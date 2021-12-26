<?php
/**
 * Fired during plugin deactivation
 *
 * @link       -
 * @since      1.0.0
 *
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/includes
 * @author     Envato <->
 */
class Template_Kit_Export_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Force WordPress to rebuild rewrite rules completely:
		delete_option( 'rewrite_rules' );
	}

}
