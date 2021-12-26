<?php
/**
 * Provide a button to install Envato Elements plugin
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

$action_url = add_query_arg(
	array(
		'action'   => 'install-plugin',
		'plugin'   => 'envato-elements',
		'_wpnonce' => rawurlencode( wp_create_nonce( 'install-plugin_envato-elements' ) ),
	),
	self_admin_url( 'update.php' )
);

// Check if the Envato Elements plugin needs to be installed or actived.
foreach ( array_keys( get_plugins() ) as $plugin ) {
	if ( strpos( $plugin, 'envato-elements.php' ) !== false ) {
		// The plugin is installed, just not activated.
		$action_url = add_query_arg(
			array(
				'action'   => 'activate',
				'plugin'   => rawurlencode( 'envato-elements/envato-elements.php' ),
				'_wpnonce' => rawurlencode( wp_create_nonce( 'activate-plugin_envato-elements/envato-elements.php' ) ),
			),
			self_admin_url( 'plugins.php' )
		);
	}
}


?>
<div class="updated template-kit-export-plugin-notice notice is-dismissible">
	<p>
		<?php esc_html_e( 'License + Import photos directly into your Template Kit using the Envato Elements WordPress plugin.', 'template-kit-export' ); ?>
		<a href="<?php echo esc_url( $action_url ); ?>"><?php esc_html_e( 'Install + Activate Plugin', 'template-kit-export' ); ?></a>
	</p>
</div>
<script>
jQuery( document ).ready( function( $ ) {
$( document ).on( 'click', '.template-kit-export-plugin-notice .notice-dismiss', function() {
window.location.href = '<?php echo esc_url( admin_url( 'admin.php?action=envato_tk_dismiss_plugin_notice' ) ); ?>';
} );
} );
</script>
