<?php
/**
 * Gutenberg template exporter.
 *
 * @link       -
 * @since      1.0.0
 *
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/builders
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Gutenberg template exporter.
 *
 * This class handles exporting from Gutenberg.
 *
 * @since      1.0.0
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/builders
 * @author     Envato <->
 */
class Template_Kit_Export_Builders_Gutenberg extends Template_Kit_Export_Builders_Base {

	/**
	 * Get the type of page builder.
	 *
	 * @since    1.0.0
	 * @return   string.
	 */
	public function get_page_builder_type() {
		return 'gutenberg';
	}

	/**
	 * Export the Gutenberg data
	 *
	 * @since      1.0.0
	 * @param      int $template_id The id of the template that will be exported.
	 * @return     array The things we still need to do.
	 */
	public function get_template_export_data( $template_id ) {
		// check this template is actually an ELementor template.
		return array(
			'data' => 'TODO: from gutenberg export',
		);
	}

	/**
	 * Add the additional help file to our zip
	 *
	 * @return void
	 * @since    1.0.0
	 */
	public function add_additional_files_to_zip() {

		$this->add_file_to_zip( 'help.html', plugin_dir_path( __DIR__ ) . 'zip-contents/gutenberg/help.html' );

	}
}
