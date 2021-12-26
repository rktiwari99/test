<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       -
 * @since      1.0.0
 *
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/public/partials
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( isset( $_GET['userPreview'] ) ) {
	?>
	<style type="text/css">
		header.site-header,
		.type-elementor_library {
			display: none;
		}
	</style>
	<div class="template-kit-preview-header" style="height: 5px;">
		&nbsp;
	</div>
	<?php
}
