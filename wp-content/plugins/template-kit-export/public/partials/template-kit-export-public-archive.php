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

try {
	$exporter      = Template_Kit_Export::get_exporter();
	$all_templates = $exporter->get_all_templates_in_kit( true );
} catch ( Exception $exception ) {
	wp_die( 'Template Kit Demo Currently Unavailable' );
}

get_header();

?>

<div class="template-kit-preview">
	<h1 class="template-kit-preview__title"><?php echo esc_html( Template_Kit_Export_Options::get( 'kit_name' ) ); ?>: <?php echo count( $all_templates ); ?> Templates</h1>
	<div class="template-kit-preview__grid">
		<?php
		foreach ( $all_templates as $template ) {
			$screenshot_url = '';
			try {
				$screenshot = $exporter->get_template_screenshot( $template['id'] );
				if ( $screenshot && ! empty( $screenshot['url'] ) ) {
					$screenshot_url = $screenshot['url'];
				}
			} catch ( Exception $e ) {
				// no screenshot for this template yet
			}
			?>
			<div class="template-kit-preview__template">
				<a href="<?php echo esc_url( get_permalink( $template['id'] ) ); ?>" target="_blank" class="template-kit-preview__link">
					<img
						src="<?php echo esc_url( $screenshot_url ); ?>"
						class="template-kit-preview__screenshot"
						alt="<?php echo esc_attr( $template['name'] ); ?>" />
					<div class="template-kit-preview__name"><?php echo esc_html( $template['name'] ); ?></div>
				</a>
			</div>
		<?php } ?>
	</div>
</div>

<?php

get_footer();
