<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
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
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/includes
 * @author     Envato <->
 */
class Template_Kit_Export {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Template_Kit_Export_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'TEMPLATE_KIT_EXPORT_VERSION' ) ) {
			$this->version = TEMPLATE_KIT_EXPORT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'template-kit-export';

		$this->load_dependencies();
		$this->set_locale();
		$this->register_cpt();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Template_Kit_Export_Loader. Orchestrates the hooks of the plugin.
	 * - Template_Kit_Export_i18n. Defines internationalization functionality.
	 * - Template_Kit_Export_Admin. Defines all hooks for the admin area.
	 * - Template_Kit_Export_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for reading and writing all out kit options to the db.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-template-kit-export-options.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-template-kit-export-loader.php';

		/**
		 * The class responsible for defining the CPT.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-template-kit-export-cpt.php';

		/**
		 * Load in our builder classes:
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'builders/class-template-kit-export-builders-base.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'builders/class-template-kit-export-builders-elementor.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'builders/class-template-kit-export-builders-gutenberg.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-template-kit-export-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-template-kit-export-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-template-kit-export-health-check.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-template-kit-export-public.php';

		$this->loader = new Template_Kit_Export_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Template_Kit_Export_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Template_Kit_Export_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register CPT plugin.
	 *
	 * Uses the Template_Kit_Export_CPT class in order to set up a CPT for our template kits.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function register_cpt() {

		$plugin_cpt = new Template_Kit_Export_CPT();

		$this->loader->add_action( 'init', $plugin_cpt, 'register_cpt' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_cpt, 'register_meta_boxes' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Template_Kit_Export_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_head', $plugin_admin, 'add_menu_icon' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'template_kit_add_menu' );
		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'disable_big_image_threshold' );
		$this->loader->add_action( 'after_setup_theme', $plugin_admin, 'register_media_size' );
		$this->loader->add_filter( 'image_size_names_choose', $plugin_admin, 'add_tk_preview_size' );
		$this->loader->add_action( 'parent_file', $plugin_admin, 'template_kit_nest_cpt_underneath' );

		// This is the action to handle exporting a zip file when someone visits admin.php?action=envato_tk_export_zip .
		$this->loader->add_action( 'admin_action_envato_tk_export_zip', $plugin_admin, 'export_zip' );
		$this->loader->add_action( 'admin_action_envato_tk_wizard_save', $plugin_admin, 'wizard_save' );

		// Filter to warn users about uploading incorrectly licensed images
		$this->loader->add_action( 'wp_enqueue_media', $plugin_admin, 'inject_media_upload_warning_notice' );

		// Filter to allow additional fields to the media upload form
		$this->loader->add_action( 'attachment_fields_to_edit', $plugin_admin, 'attachment_fields_to_edit', 10, 2 );
		$this->loader->add_action( 'attachment_fields_to_save', $plugin_admin, 'attachment_fields_to_save', 10, 2 );

		// Save cpt metabox data
		$this->loader->add_action( 'save_post', new Template_Kit_Export_CPT(), 'save_cpt_meta_data', 10, 2 );

		// Custom CSS messaging
		$this->loader->add_action( 'customize_register', $plugin_admin, 'customize_register', 100 );
		$this->loader->add_action( 'elementor/element/after_section_start', $plugin_admin, 'elementor_custom_css_message', 100, 2 );

		// Try to automatically add some image metadata on upload
		$this->loader->add_filter( 'wp_generate_attachment_metadata', $plugin_admin, 'wp_generate_attachment_metadata', 10, 2 );
		$this->loader->add_action( 'add_post_meta', $plugin_admin, 'add_post_meta', 10, 3 );

		// Tweak the Envato Elements WordPress plugin:
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'envato_elements_plugin_admin_notice' );
		$this->loader->add_action( 'admin_action_envato_tk_dismiss_plugin_notice', $plugin_admin, 'envato_elements_plugin_admin_notice_dismiss' );

		// Block default fonts and colours in Elementor
		$this->loader->add_filter( 'elementor/schemes/enabled_schemes', $plugin_admin, 'disable_elementor_schemes' );

		// Run some code every time the version upgrades
		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'version_upgrade_check' );

		// Run some code every time the version upgrades
		$this->loader->add_filter( 'elementor/kit/export/manifest-data', $plugin_admin, 'modify_elementor_export', 10, 2 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Template_Kit_Export_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter( 'template_include', $plugin_public, 'template_include' );
		$this->loader->add_action( 'wp_head', $plugin_public, 'wp_head' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'wp_footer' );
		$this->loader->add_action( 'init', $plugin_public, 'fix_up_frontend_template_previews' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Template_Kit_Export_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the class responsible for exporting this template kit type
	 *
	 * @return    Template_Kit_Export_Builders_Base  The class used for exporting this template kit
	 * @throws Exception
	 * @since     1.0.0
	 */
	public static function get_exporter() {

		$page_builder = Template_Kit_Export_Options::get( 'page_builder' );

		if ( 'elementor' === $page_builder ) {
			$exporter = new Template_Kit_Export_Builders_Elementor();
		} elseif ( 'gutenberg' === $page_builder ) {
			$exporter = new Template_Kit_Export_Builders_Gutenberg();
		} else {
			throw new Exception( 'Unknown page builder type set' );
		}
		return $exporter;
	}

}
