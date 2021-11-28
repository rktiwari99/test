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
	$all_images          = $exporter->find_all_images();
	$image_fields        = $exporter->get_image_meta_fields();
	$template_kit_errors = $exporter->detect_any_errors_with_template_kit();
} catch ( Exception $exception ) {
	wp_die( 'Error:: ' . esc_html( $exception->getMessage() ) );
}

?>
<p>Please fill in additional information about all the images included in this Template Kit. This allows us to ensure images and photos are licensed correctly for customer use. Fore more information please see the <a href="https://help.author.envato.com/hc/en-us/articles/360038151251-WordPress-Template-Kit-Requirements" target="_blank" rel="noreferrer noopener">Template Kit Help Section</a>.</p>
<?php
// If we find any errors for the images below we should highlight them here.
if ( $template_kit_errors && ! empty( $template_kit_errors['images'] ) ) {
	?>
		<div class="tk-error">
			<p>
				<strong>We've detected some problems with images in this Template Kit. </strong> <br/>
				Please review the Template Kit guidelines.
			</p>
			<ol>
				<?php foreach ( $template_kit_errors['images'] as $image_error ) { ?>
						<li><?php echo esc_html( $image_error ); ?></li>
				<?php } ?>
			</ol>
		</div>
		<?php
}
?>
<form action="<?php echo esc_url( admin_url( 'admin.php?action=envato_tk_wizard_save&step=4' ) ); ?>" method="post">
	<?php wp_nonce_field( 'envato_tk_wizard', 'envato_tk_wizard_nonce' ); ?>
	<table class="wp-list-table widefat fixed striped">
		<thead>
		<tr>
			<th>Thumbnail</th>
			<th>Image Name</th>
			<th>Used on Template</th>
			<th colspan="3">Image Data</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $all_images as $image ) { ?>
		<tr>
			<td>
				<img src="<?php echo esc_url( $image['thumbnail_url'] ); ?>" alt="<?php echo esc_attr( $image['filename'] ); ?>" width="100" />
			</td>
			<td>
				<?php echo esc_html( $image['filename'] ); ?>
				<br/>
				(<?php echo esc_html( $image['dimensions'][0] . 'x' . $image['dimensions'][1] . 'px @ ' . number_format( $image['filesize'] / 1048576, 2 ) . ' MB' ); ?>)
				<?php
				if ( $image['filesize'] > 510000 ) {
					?>
					<strong><em>Warning: Please try to reduce original image size.</em></strong>
					<?php
				}
				?>
			</td>
			<td>
				<?php foreach ( $image['used_on_templates'] as $template_id => $template_name ) { ?>
					<?php
					// todo: we could link to the template from here so the user can quickly access the editor ?
					echo esc_html( $template_name );
					?>
					<br/>
				<?php } ?>
			</td>
			<td colspan="3">
				<?php foreach ( $image_fields as $image_field ) { ?>
				<div class="envato-tk__image-data">
					<lable class="envato-tk__image-data-label"><?php echo esc_html( $image_field['label'] ); ?></lable>
					<?php if ( 'text' === $image_field['type'] ) { ?>
						<input type="text"
				class="envato-tk__image-data-input"
						name="<?php echo esc_attr( Template_Kit_Export_Options::OPTIONS_KEY ); ?>[images][<?php echo esc_attr( $image['image_id'] ); ?>][<?php echo esc_attr( $image_field['name'] ); ?>]"
						value="<?php echo esc_attr( ! empty( $image['user_data'][ $image_field['name'] ] ) ? $image['user_data'][ $image_field['name'] ] : '' ); ?>"
						placeholder="<?php echo esc_attr( ! empty( $image_field['placeholder'] ) ? $image_field['placeholder'] : '' ); ?>" />
					<?php } ?>
					<?php
					if ( 'select' === $image_field['type'] ) {
						$current_value = ! empty( $image['user_data'][ $image_field['name'] ] ) ? $image['user_data'][ $image_field['name'] ] : '';
						?>
						<select name="<?php echo esc_attr( Template_Kit_Export_Options::OPTIONS_KEY ); ?>[images][<?php echo esc_attr( $image['image_id'] ); ?>][<?php echo esc_attr( $image_field['name'] ); ?>]" class="envato-tk__image-data-input">
							<?php foreach ( $image_field['options'] as $option_key => $option_value ) { ?>
								<option value="<?php echo esc_attr( $option_key ); ?>"<?php echo selected( $option_key, $current_value ); ?>><?php echo esc_attr( $option_value ); ?></option>
							<?php } ?>
						</select>
					<?php } ?>
				</div>
				<?php } ?>
			</td>
		</tr>
		<?php } ?>
		</tbody>
	</table>
  <div class="next-wrapper">
		<input class="tk-button" type="submit" value="Next Step" />
	</div>
</form>
