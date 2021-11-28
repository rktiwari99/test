<?php
/**
 * The data-check functionality of the plugin.
 *
 * @link       -
 * @since      1.0.0
 *
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The data-check functionality of the plugin.
 *
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/admin
 * @author     Envato <->
 */
class Template_Kit_Export_Health_Check {

	/**
	 * Check if this WordPress site is publically available.
	 *
	 * @return bool
	 */
	private function is_wordpress_running_on_a_public_domain() {
		$site_url = get_site_url();
		return ! (
			preg_match( '#\.test$#', $site_url ) ||
			preg_match( '#\.local$#', $site_url ) ||
			preg_match( '#localhost#', $site_url ) ||
			preg_match( '#\d+\.\d+\.\d+\.\d+#', $site_url )
		);
	}

	private function does_wordpress_core_need_an_update() {
		$wp_status = get_option( 'update_core' );
		return ( false !== $wp_status );
	}

	public function get_site_health() {
		$health = array(
			'errors'  => array(),
			'success' => array(),
		);

		if ( ! $this->is_wordpress_running_on_a_public_domain() ) {
			$health['errors'][] = __( 'Please ensure your website is publicly accessible (i.e. not localhost). Users will need to access your website to preview any templates and import any images.', 'template-kit-export' );
		}

		if ( $this->does_wordpress_core_need_an_update() ) {
			$health['errors'][] = __( 'Please up date WordPress to the latest version.', 'template-kit-export' );
		} else {
			$health['success'][] = __( 'WordPress is up-to-date.', 'template-kit-export' );
		}

		$update_plugins      = get_site_transient( 'update_plugins' );
		$update_plugin_array = $update_plugins->response;
		$all_plugins         = get_plugins();
		if ( count( $update_plugin_array ) > 0 ) {
			foreach ( $update_plugin_array as $update_plugin ) {
				$plugin_name = isset( $update_plugin->name ) ? $update_plugin->name : $all_plugins[ $update_plugin->plugin ]['Name'];
				/* translators: %s: Plugin Name */
				$health['errors'][] = sprintf( __( 'Please update plugin to latest version: %s', 'template-kit-export' ), $plugin_name );
			}
		}

		try {
			// Now we ask our page builder (e.g. Elementor) to check for any additional site health issues.
			$exporter             = Template_Kit_Export::get_exporter();
			$exporter_site_health = $exporter->get_site_health();
			$health['errors']     = array_merge( $health['errors'], $exporter_site_health['errors'] );
			$health['success']    = array_merge( $health['success'], $exporter_site_health['success'] );
		} catch ( Exception $e ) {
			$health['errors'][] = __( 'Please choose a Page/Site Builder from the available drop down options.', 'template-kit-export' );
		}

		return $health;
	}

}
