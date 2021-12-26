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

try {
	$exporter            = Template_Kit_Export::get_exporter();
	$all_templates       = $exporter->get_all_templates_in_kit();
	$template_fields     = $exporter->get_template_meta_fields();
	$template_kit_errors = $exporter->detect_any_errors_with_template_kit();

	// If we find any errors for the template below we should highlight them here.
	// We only want to show this error dialog if the $_GET error parameter is set. This means the user has pressed save.
	// phpcs:ignore
	if ( isset( $_GET['error'] ) && $template_kit_errors && ! empty( $template_kit_errors['templates'] ) ) {
		?>
		<div class="tk-error">
			<p>
				<strong>We've detected some problems with Templates in this Kit. </strong> <br/>
				Please review the Template Kit guidelines.
			</p>
			<ol>
				<?php foreach ( $template_kit_errors['templates'] as $image_error ) { ?>
					<li><?php echo esc_html( $image_error ); ?></li>
				<?php } ?>
			</ol>
		</div>
		<?php
	}
} catch ( Exception $exception ) {
	wp_die( 'Error:: ' . esc_html( $exception->getMessage() ) );
}

if ( Template_Kit_Export_Options::get( 'export_type' ) === TEMPLATE_KIT_EXPORT_TYPE_ELEMENTOR ) {

	?>
	<p>You have chosen to export an <strong>Elementor Kit</strong>. This will do a full site export for the customer to import. Therefore there is no need to select individual templates to include in your export Please continue below.</p>

	<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Template_Kit_Export_Admin::ADMIN_MENU_SLUG . '&step=4' ) ); ?>" class="button tk-button">Continue to step 4</a>
	<?php

} else {

// Init JavaScript API so we can use the media uploader.
wp_enqueue_media();

?>

<p>Please choose which Templates are to be included in the Kit. Drag &amp; drop the Templates to choose the order. Upload screenshot previews to each template. Add more Templates to this Kit from the <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . Template_Kit_Export_CPT::CPT_SLUG ) ); ?>">Templates</a> menu. See the <a href="https://help.author.envato.com/hc/en-us/articles/360038151251-WordPress-Template-Kit-Requirements" target="_blank" rel="noreferrer noopener">Template Kit Help Section</a> for more details.</p>

<form id="template-kit-sortable-container" class="container"
	action="<?php echo esc_url( admin_url( 'admin.php?action=envato_tk_wizard_save&step=3' ) ); ?>" method="post">
	<?php
	wp_nonce_field( 'envato_tk_wizard', 'envato_tk_wizard_nonce' );
	if ( ! count( $all_templates ) ) {
		?>
		Please <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . Template_Kit_Export_CPT::CPT_SLUG ) ); ?>">create some templates</a> first.
		<?php
	}
	foreach ( $all_templates as $template ) {
		?>
		<div class="templates col" data-template-id="<?php echo esc_attr( $template['id'] ); ?>">
			<div class="screenshot" style="<?php
				try {
					$screenshot = $exporter->get_template_screenshot( $template['id'] );
					if ( $screenshot && ! empty( $screenshot['url'] ) ) {
						echo 'background-image: url(' . esc_url( $screenshot['url'] ) . ');';
					}
				} catch ( Exception $e ) {
					// no screenshot for this template yet
				}
				?>">
				<button class="upload-image">Upload Screenshot</button>
			</div>
			<div class="inner-col">
				<input class="post-title"
				type="text"
				name="<?php echo esc_attr( Template_Kit_Export_Options::OPTIONS_KEY ); ?>[templates][<?php echo esc_attr( $template['id'] ); ?>][name]"
				value="<?php echo esc_attr( $template['name'] ); ?>"
				/>
				<?php
				// This loops over our available input fields and renders a bunch of input tags on the page.
				// We pull these fields in from our Builder class (e.g. Elementor or Gutenberg) so that these classes can define their own set of fields.
				// For example: Elementor wants to have an "Elementor Pro" field but we don't want that visible in Gutenberg mode.
				foreach ( $template_fields as $template_field ) {
					$label_id   = sprintf( '%s_%d', $template_field['name'], $template['id'] );
					$input_name = sprintf( '%s[templates][%d][%s]', Template_Kit_Export_Options::OPTIONS_KEY, $template['id'], $template_field['name'] );
					?>
					<p>
						<?php if ( 'text' === $template_field['type'] ) { ?>
							<label for="<?php echo esc_attr( $label_id ); ?>"><?php echo esc_html( $template_field['label'] ); ?></label>
							<input type="text"
								id="<?php echo esc_attr( $label_id ); ?>"
								name="<?php echo esc_attr( $input_name ); ?>"
								value="<?php echo esc_attr( ! empty( $template['metadata'][ $template_field['name'] ] ) ? $template['metadata'][ $template_field['name'] ] : '' ); ?>"
								/>
							<?php
						}
						if ( 'select' === $template_field['type'] ) {
							$current_value = ! empty( $template['metadata'][ $template_field['name'] ] ) ? $template['metadata'][ $template_field['name'] ] : '';
							?>
							<select id="<?php echo esc_attr( $label_id ); ?>" name="<?php echo esc_attr( $input_name ); ?>">
								<?php
								foreach ( $template_field['options'] as $option_key => $option_value ) {
									if ( is_array( $option_value ) ) {
										// We allow nested option groups here
										?>
										<optgroup label="<?php echo esc_attr( $option_value['label'] ); ?>">
											<?php foreach ( $option_value['options'] as $option_group_value => $option_group_label ) { ?>
												<option value="<?php echo esc_attr( $option_group_value ); ?>"<?php selected( $option_group_value, $current_value ); ?>><?php echo esc_attr( $option_group_label ); ?></option>
											<?php } ?>
										</optgroup>
									<?php } else { ?>
										<option value="<?php echo esc_attr( $option_key ); ?>"<?php selected( $option_key, $current_value ); ?>><?php echo esc_attr( $option_value ); ?></option>
									<?php } ?>
								<?php } ?>
							</select>
							<?php
						}
						if ( 'checkbox' === $template_field['type'] ) {
							$is_value_selected = ! empty( $template['metadata'][ $template_field['name'] ] ) && '1' === $template['metadata'][ $template_field['name'] ];
							?>
							<input id="<?php echo esc_attr( $label_id ); ?>"
								type="checkbox"
								name="<?php echo esc_attr( $input_name ); ?>"
								value="1"
								<?php checked( $is_value_selected ); ?> />
							<label for="<?php echo esc_attr( $label_id ); ?>"><?php echo esc_html( $template_field['label'] ); ?></label>
						<?php } ?>
					</p>
				<?php } ?>
				<?php
				if ( ! empty( $template['metadata']['additional_template_information'] ) ) {
					?>
					<div class="envato-tk__additional-template-information">
						<?php foreach ( $template['metadata']['additional_template_information'] as $additional_information ) { ?>
							<p><?php echo wp_kses_post( $additional_information ); ?></p>
						<?php } ?>
					</div>
					<?php
				}
				?>
				<input type="hidden" name="<?php echo esc_attr( Template_Kit_Export_Options::OPTIONS_KEY ); ?>[templates][<?php echo esc_attr( $template['id'] ); ?>][template_id]" value="<?php echo esc_attr( $template['id'] ); ?>"/>
				<input class="position_id" type="hidden" name="<?php echo esc_attr( Template_Kit_Export_Options::OPTIONS_KEY ); ?>[templates][<?php echo esc_attr( $template['id'] ); ?>][position_id]" value="" />
				<input class="tk-preview-image-id" type="hidden" name="<?php echo esc_attr( Template_Kit_Export_Options::OPTIONS_KEY ); ?>[templates][<?php echo esc_attr( $template['id'] ); ?>][thumb_id]" value="<?php echo esc_attr( get_post_thumbnail_id( $template['id'] ) ); ?>" />
			</div>
		</div>
		<?php
	}
	?>
	<div class="next-wrapper">
		<input class="tk-button" type="submit" value="Next Step"/>
	</div>
</form>

<?php }
