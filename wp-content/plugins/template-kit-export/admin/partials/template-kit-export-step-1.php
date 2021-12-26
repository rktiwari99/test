<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       -
 * @since      1.0.0
 *
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/admin/partials
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$current_export_type = Template_Kit_Export_Options::get( 'export_type' );

?>
<div class="envato-tk__guidelines">
	<h2 class="col-title">Exporter:</h2>
	<div class="inner-col">
		Welcome! The first step is to choose between an <strong>Envato Template Kit</strong> or a <strong>Elementor Kit</strong>. A quick summary of the difference is listed below, with links to learn more:
		<ol>
			<li><strong>Envato Template Kit:</strong> The original Template Kit format as found on Envato websites (ThemeForest and Envato Elements). Customers can pick and choose individual templates to import. Supports 3rd party plugins. May not support all latest Elementor features. <a href="https://elements.envato.com/template-kits" target="_blank">Read More</a></li>
			<li><strong>Elementor Kit:</strong> Newly released feature from Elementor. No 3rd party plugins allowed. This is a "Full Site" export. Customers cannot choose individual templates, only a "Full Site" import overwriting their current site. <a href="https://library.elementor.com/" target="_blank">Read More</a></li>
		</ol>
	</div>
</div>
<form class="container" action="<?php echo esc_url( admin_url( 'admin.php?action=envato_tk_wizard_save&step=1' ) ); ?>" method="post">
	<?php wp_nonce_field( 'envato_tk_wizard', 'envato_tk_wizard_nonce' ); ?>
	<div class="tk-meta col">
		<div>
			<h2 class="col-title"><label for="export-type"><?php esc_html_e( 'Choose the Export Kit Type', 'template-kit-export' ); ?></h2>
			<div class="inner-col">
				<select id="export-type" name="<?php echo esc_attr( Template_Kit_Export_Options::OPTIONS_KEY ); ?>[export_type]" required>
					<option value=""><?php esc_html_e( 'Please Select', 'template-kit-export' ); ?></option>
					<option value="<?php echo esc_attr( TEMPLATE_KIT_EXPORT_TYPE_ENVATO ); ?>" <?php selected( TEMPLATE_KIT_EXPORT_TYPE_ENVATO, $current_export_type ); ?>>Envato Template Kit</option>
					<option value="<?php echo esc_attr( TEMPLATE_KIT_EXPORT_TYPE_ELEMENTOR ); ?>" <?php selected( TEMPLATE_KIT_EXPORT_TYPE_ELEMENTOR, $current_export_type ); ?>>Elementor Kit</option>
				</select>
			</div>
		</div>
	</div>
	<div class="next-wrapper">
		<input class="tk-button" type="submit" value="Next Step" />
	</div>
</form>
