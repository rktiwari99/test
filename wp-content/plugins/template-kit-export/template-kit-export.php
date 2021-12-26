<?php
/**
 * Plugin Name:       Template Kit Export
 * Description:       Use this plugin to export Template Kits for Elementor.
 * Version:           1.0.21
 * Author:            Envato
 * Author URI:        https://envato.com
 * License:           GPLv3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       template-kit-export
 * Elementor tested up to: 3.5.0
 * Elementor Pro tested up to: 3.5.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'TEMPLATE_KIT_EXPORT_VERSION', '1.0.21' );

/**
 * Default generated thumbnail width.
 */
define( 'TEMPLATE_KIT_EXPORT_THUMBNAIL_WIDTH', 800 );

/**
 * Our supported export types
 */
define( 'TEMPLATE_KIT_EXPORT_TYPE_ENVATO', 'template-kit' );
define( 'TEMPLATE_KIT_EXPORT_TYPE_ELEMENTOR', 'elementor-kit' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-template-kit-export-deactivator.php
 */
function deactivate_template_kit_export() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-template-kit-export-deactivator.php';
	Template_Kit_Export_Deactivator::deactivate();
}

register_deactivation_hook( __FILE__, 'deactivate_template_kit_export' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-template-kit-export.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_template_kit_export() {

	$plugin = new Template_Kit_Export();
	$plugin->run();

}
run_template_kit_export();
