<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       -
 * @since      1.0.0
 *
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/public
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/public
 * @author     Envato <->
 */
class Template_Kit_Export_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name    The name of the plugin.
	 * @param    string $version        The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/css/template-kit-export-public.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/js/template-kit-export-public.min.js', array( 'jquery' ), $this->version, false );

	}


	/**
	 * Register the templates for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function template_include( $template ) {

		if ( is_post_type_archive( Template_Kit_Export_CPT::CPT_SLUG ) ) {
			$theme_files     = array( 'archive-template-kit.php' );
			$exists_in_theme = locate_template( $theme_files, false );
			if ( $exists_in_theme ) {
				return $exists_in_theme;
			} else {
				return plugin_dir_path( __FILE__ ) . 'partials/template-kit-export-public-archive.php';
			}
		}
		return $template;

	}

	/**
	 * Outputs some additional markup on the single template kit pages
	 *
	 * @since    1.0.0
	 */
	public function wp_head() {
		if ( is_singular( Template_Kit_Export_CPT::CPT_SLUG ) || is_singular( 'elementor_library' ) ) {
			require plugin_dir_path( __FILE__ ) . 'partials/template-kit-export-public-header.php';
		}
	}

	/**
	 * Outputs some additional markup on the single template kit pages
	 *
	 * @since    1.0.0
	 */
	public function wp_footer() {
		if ( is_singular( Template_Kit_Export_CPT::CPT_SLUG ) || is_singular( 'elementor_library' ) ) {
			require plugin_dir_path( __FILE__ ) . 'partials/template-kit-export-public-footer.php';
		}
	}


	/**
	 * Allows us to view Elementor Template preview urls.
	 * Undoes the hook at wp-content/plugins/elementor/includes/template-library/sources/local.php:938
	 *
	 * @since    1.0.1
	 */
	public function fix_up_frontend_template_previews() {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$source = \Elementor\Plugin::$instance->templates_manager->get_source( 'local' );
			remove_action( 'template_redirect', array( $source, 'block_template_frontend' ) );
		}
	}

}
