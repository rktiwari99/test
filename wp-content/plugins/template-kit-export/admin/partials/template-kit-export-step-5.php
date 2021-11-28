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
	$template_kit_errors = $exporter->detect_any_errors_with_template_kit();
} catch ( Exception $exception ) {
	wp_die( 'Error:: ' . esc_html( $exception->getMessage() ) );
}

?>

<div class="zip-wrapper">
	<?php
	// If we find any errors for the images below we should highlight them here.
	if ( $template_kit_errors ) {
		?>
			<div class="envato-tk-notice">
				<div class="tk-error">
					<p>
						<strong>We've detected some problems with the Template Kit. </strong> <br/>
						Please resolve these errors before proceeding:
					</p>
					<ol>
					  <?php
						foreach ( $template_kit_errors as $error_category => $error_category_messages ) {
							foreach ( $error_category_messages as $error_category_message ) {
								?>
								<li><?php echo esc_html( $error_category_message ); ?></li>
								<?php
							}
						}
						?>
					</ol>
				</div>
			</div>
		<?php
	}
	?>
	<h2>Ready to export?</h2>
	<?php if ( Template_Kit_Export_Options::get( 'export_type' ) === TEMPLATE_KIT_EXPORT_TYPE_ELEMENTOR ) { ?>
		<p>Clicking the button below will take you to Elementor Export tool.</p>
		<p><strong>Important:</strong> Please remember to copy &amp; paste the generated item page HTML content into the Envato upload tool. This allows the reviewer to see if your Kit contains any images that were sourced from Envato Elements.</p>
		<a class="tk-button zip" href="<?php echo $template_kit_errors ? '#' : esc_url( admin_url( 'admin.php?page=elementor-tools#tab-import-export-kit' ) ); ?>" target="_blank">
			Open Elementor Export
		</a>
	<?php } else { ?>
		<p>Clicking export below will generate a ZIP file containing a copy of the Template Kit.</p>
		<a class="tk-button zip" href="<?php echo $template_kit_errors ? '#' : esc_url( wp_nonce_url( admin_url( 'admin.php?action=envato_tk_export_zip' ), 'export_the_zip' ) ); ?>">
			Download ZIP File <?php echo $template_kit_errors ? ' (disabled)' : ''; ?>
		</a>
	<?php } ?>
</div>

<div class="markup-wrapper">
	<h2>Item Page HTML Generator</h2>
	<p>Here is some code you need to copy &amp; paste into the ThemeForest item submission. This code explains what assets are included in this Template Kit and provides helpful links so a customer can license their own copies of these photos if they choose to use them. </p>
	<p>
		<strong>ThemeForest item page HTML:</strong>
		<br/>
		<textarea name="themeforest_markup" class="markup-textbox" onclick="this.focus();this.select()" readonly="readonly"><?php echo esc_textarea( $exporter->generate_item_page_markup( 'themeforest' ) ); ?></textarea>
	</p>
	<p>
		<strong>Envato Elements item page markdown:</strong>
		<br/>
		<textarea name="elements_markup" class="markup-textbox" onclick="this.focus();this.select()" readonly="readonly"><?php echo esc_textarea( $exporter->generate_item_page_markup( 'elements' ) ); ?></textarea>
	</p>
</div>
