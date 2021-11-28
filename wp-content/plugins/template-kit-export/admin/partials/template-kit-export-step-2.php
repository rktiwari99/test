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

$exporter = false;

try {
	$exporter            = Template_Kit_Export::get_exporter();
	$template_kit_errors = $exporter->detect_any_errors_with_template_kit();

	// If we find any errors for the template below we should highlight them here.
	// We only want to show this error dialog if the $_GET error parameter is set. This means the user has pressed save.
	// phpcs:ignore
	if ( isset( $_GET['error'] ) && $template_kit_errors && ! empty( $template_kit_errors['kit'] ) ) {
		?>
		<div class="tk-error">
			<p>
				<strong>We've detected some problems with this Kit. </strong> <br/>
			</p>
			<ol>
				<?php foreach ( $template_kit_errors['kit'] as $image_error ) { ?>
					<li><?php echo esc_html( $image_error ); ?></li>
				<?php } ?>
			</ol>
		</div>
		<?php
	}
} catch ( Exception $exception ) {
	// noop
}

$current_page_builder    = $exporter ? $exporter->get_page_builder_type() : false;
$available_page_builders = array( 'elementor' );

?>
<div class="envato-tk__guidelines">
	<h2 class="col-title">Kit Guidelines:</h2>
	<div class="inner-col">
		<p>
			Please read the latest <a href="https://help.author.envato.com/hc/en-us/articles/360038151251-WordPress-Template-Kit-Requirements" target="_blank" rel="noreferrer noopener">kit guidelines</a> from Envato.
		</p>
		<?php if ( Template_Kit_Export_Options::get( 'export_type' ) === TEMPLATE_KIT_EXPORT_TYPE_ELEMENTOR ) { ?>
			<p>
				Please ensure you follow the latest Elementor Kit guidelines, available via their website.
			</p>
		<?php } ?>
	</div>
</div>
<form class="container" action="<?php echo esc_url( admin_url( 'admin.php?action=envato_tk_wizard_save&step=2' ) ); ?>"
      method="post">
	<?php wp_nonce_field( 'envato_tk_wizard', 'envato_tk_wizard_nonce' ); ?>
	<div class="tk-meta col">
		<div>
			<h2 class="col-title"><label for="template-kit-name"><?php esc_html_e( 'Kit Name', 'template-kit-export' ); ?>
			</h2>
			<div class="inner-col">
				<input type="text" id="template-kit-name"
				       name="<?php echo esc_attr( Template_Kit_Export_Options::OPTIONS_KEY ); ?>[kit_name]"
				       value="<?php echo esc_attr( Template_Kit_Export_Options::get( 'kit_name' ) ); ?>"/>
			</div>
		</div>
		<div>
			<h2 class="col-title"><label
					for="template-kit-builder"><?php esc_html_e( 'Page/Site Builder', 'template-kit-export' ); ?></h2>
			<div class="inner-col">
				<select id="template-kit-builder"
				        name="<?php echo esc_attr( Template_Kit_Export_Options::OPTIONS_KEY ); ?>[page_builder]" required>
					<option value="" disabled><?php esc_html_e( 'Please Select', 'template-kit-export' ); ?></option>
					<?php
					foreach ( $available_page_builders as $page_builder ) {
						?>
						<option
							value="<?php echo esc_attr( $page_builder ); ?>" <?php selected( $page_builder, $current_page_builder ); ?>>
							<?php echo esc_attr( ucfirst( $page_builder ) ); ?>
						</option>
						<?php
					}
					?>
				</select>
			</div>
		</div>
		<div>
			<h2 class="col-title"><label
					for="template-kit-export__version-number"><?php esc_html_e( 'Version Number', 'template-kit-export' ); ?></h2>
			<div class="inner-col">
				<input type="text" id="template-kit-export__version-number"
				       name="<?php echo esc_attr( Template_Kit_Export_Options::OPTIONS_KEY ); ?>[kit_version]"
				       value="<?php echo esc_attr( Template_Kit_Export_Options::get( 'kit_version', '1.0.0' ) ); ?>"/>
			</div>
		</div>
	</div>
	<div class="tk-plugins col">
		<h2 class="col-title"><?php esc_html_e( 'Plugin Dependencies', 'template-kit-export' ); ?></h2>
		<div class="inner-col">
			<?php
			// Plugins must be from WP.org or the slug from our plugin whitelist.
			$plugin_whitelist = array( 'elementor-pro' );
			$plugin_blacklist = array( 'template-kit-export' );
			// Get all the plugins from the local install:
			$all_plugins = get_plugins();
			// Get all the plugins from any network install:
			$active_sitewide_plugins = get_site_option( 'active_sitewide_plugins' );
			// Grab a list of active plugins on the current site
			$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
			// Ensure these are arrays so we can play with them nicely
			if ( ! is_array( $active_plugins ) ) {
				$active_plugins = array();
			}
			if ( ! is_array( $active_sitewide_plugins ) ) {
				$active_sitewide_plugins = array();
			}
			// Merge the local and network plugins into a single array
			$active_plugins = array_merge( $active_plugins, array_keys( $active_sitewide_plugins ) );

			// Grab any user settings about which plugins are required
			$current_required_plugins = json_decode( Template_Kit_Export_Options::get( 'required_plugins', '[]' ), true );
			$plugins_status_on_wp_org = json_decode( Template_Kit_Export_Options::get( 'plugins_api_status_cache', '[]' ), true );

			foreach ( $active_plugins as $active_plugin => $plugin_file_path ) {
				// Get plugin slug from active plugin.
				$plugin_slug = strtok( $plugin_file_path, '/' );
				if ( array_key_exists( $plugin_file_path, $all_plugins ) && ! in_array( $plugin_slug, $plugin_blacklist, true ) ) {
					// Check if plugin uri is from wp.org.
					if ( ! isset( $plugins_status_on_wp_org[ $plugin_slug ] ) ) {
						// first time checking this slug against wp.org api, get the status and cache it.
						$args     = array(
							'slug' => $plugin_slug,
						);
						$response = wp_remote_get(
							'http://api.wordpress.org/plugins/info/1.2/',
							array(
								'body' => array(
									'action'  => 'plugin_information',
									'request' =>
										array(
											'slug' => $plugin_slug,
										),
								),
							)
						);
						if ( ! is_wp_error( $response ) ) {
							$returned_object = json_decode( wp_remote_retrieve_body( $response ), true );
							if ( ( $returned_object && empty( $returned_object['error'] ) ) || in_array( $plugin_slug, $plugin_whitelist, true ) ) {
								$plugins_status_on_wp_org[ $plugin_slug ] = true; // Allow this plugin to be chosen.
							} else {
								$plugins_status_on_wp_org[ $plugin_slug ] = false; // Don't allow this plugin.
							}
						} else {
							// Error object returned.
							echo 'An error has occurred, please try again.';
						}
					}
					if (
						Template_Kit_Export_Options::get( 'export_type' ) === TEMPLATE_KIT_EXPORT_TYPE_ELEMENTOR &&
						! in_array( $plugin_slug, [ 'elementor', 'elementor-pro', 'woocommerce' ] )
					) {
						// we only allow Elementor, Elementor Pro and WooCommerce for Elementor kits
						$disable_checkbox_status = 'disabled';
						$show_wp_error           = true;
						$plugin_class            = 'plugin tk-error';
					} else if ( $plugins_status_on_wp_org[ $plugin_slug ] ) {
						$disable_checkbox_status = '';
						$show_wp_error           = false;
						$plugin_class            = 'plugin';
					} else {
						$disable_checkbox_status = 'disabled';
						$show_wp_error           = true;
						$plugin_class            = 'plugin tk-error';
					}
					?>
					<div class="<?php echo esc_attr( $plugin_class ); ?>">
						<p>
							<?php
							if ( false === $show_wp_error ) {
								?>
								<input type="checkbox"
								       value="1"
								       id="required_plugin_<?php echo esc_attr( $plugin_file_path ); ?>"
								       name="<?php echo esc_attr( Template_Kit_Export_Options::OPTIONS_KEY ); ?>[required_plugins][<?php echo esc_attr( $plugin_file_path ); ?>][required]" <?php checked( array_key_exists( $plugin_file_path, $current_required_plugins ) ); ?> />
								<label for="required_plugin_<?php echo esc_attr( $plugin_file_path ); ?>">
									<?php echo esc_html( $all_plugins[ $plugin_file_path ]['Name'] ); ?>
								</label>
								<?php
							} else {
								?>
								<strong>
									<input type="checkbox" value="disabled" name="disabled"/>
									<?php echo esc_html( $all_plugins[ $plugin_file_path ]['Name'] ); ?>:
								</strong>
								<br/>
								<br/>
								<?php
								if ( Template_Kit_Export_Options::get( 'export_type' ) === TEMPLATE_KIT_EXPORT_TYPE_ELEMENTOR ) {
									esc_html_e( 'Sorry this plugin can not be included as a dependency. Elementor Kits are only compatible with Elementor, Elementor Pro and WooCommerce plugins. Please generate a Template Kit ', 'template-kit-export' );
								} else {
									esc_html_e( 'Sorry this plugin can not be included as a dependency. Please use plugins from WordPress.org as per the Template Kit guidelines.', 'template-kit-export' );
								}
							}
							?>
						</p>
						<input type="hidden" value="<?php echo esc_attr( $all_plugins[ $plugin_file_path ]['Name'] ); ?>"
						       name="<?php echo esc_attr( Template_Kit_Export_Options::OPTIONS_KEY ); ?>[required_plugins][<?php echo esc_attr( $plugin_file_path ); ?>][name]"/>
						<input type="hidden" value="<?php echo esc_attr( $all_plugins[ $plugin_file_path ]['Version'] ); ?>"
						       name="<?php echo esc_attr( Template_Kit_Export_Options::OPTIONS_KEY ); ?>[required_plugins][<?php echo esc_attr( $plugin_file_path ); ?>][version]"/>
						<input type="hidden" value="<?php echo esc_attr( $plugin_file_path ); ?>"
						       name="<?php echo esc_attr( Template_Kit_Export_Options::OPTIONS_KEY ); ?>[required_plugins][<?php echo esc_attr( $plugin_slug ); ?>][slug]"/>
						<input type="hidden" value="<?php echo esc_attr( $plugin_file_path ); ?>"
						       name="<?php echo esc_attr( Template_Kit_Export_Options::OPTIONS_KEY ); ?>[required_plugins][<?php echo esc_attr( $plugin_file_path ); ?>][file]"/>
						<input type="hidden" value="<?php echo esc_attr( $all_plugins[ $plugin_file_path ]['Author'] ); ?>"
						       name="<?php echo esc_attr( Template_Kit_Export_Options::OPTIONS_KEY ); ?>[required_plugins][<?php echo esc_attr( $plugin_file_path ); ?>][author]"/>
					</div>
					<?php
				}
			}
			Template_Kit_Export_Options::save( 'plugins_api_status_cache', wp_json_encode( $plugins_status_on_wp_org ) );
			?>
		</div>
	</div>
	<div class="tk-health col">
		<h2 class="col-title">Health Check</h2>
		<div class="inner-col">
			<?php
			$healthcheck = new Template_Kit_Export_Health_Check();
			$health      = $healthcheck->get_site_health();
			foreach ( $health['errors'] as $site_health_error ) {
				?>
				<div class="health tk-error"><p><?php echo esc_html( $site_health_error ); ?></p></div>
				<?php
			}
			foreach ( $health['success'] as $site_health_success ) {
				?>
				<div class="health"><p><i class="dashicons dashicons-yes"></i><?php echo esc_html( $site_health_success ); ?>
					</p></div>
				<?php
			}
			?>
		</div>
	</div>
	<div class="next-wrapper">
		<input class="tk-button" type="submit" value="Next Step"/>
	</div>
</form>
