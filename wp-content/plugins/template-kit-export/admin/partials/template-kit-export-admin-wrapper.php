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

// phpcs:ignore
$current_active_step = isset( $_GET['step'] ) ? (int) $_GET['step'] : 1;
?>

<div class="wrap">
	<header class="header">
		<h1>Kit Export</h1>
		<p>Use this plugin to Export an "Envato Template Kit" or an "Elementor Kit".</p>
	</header>
	<?php
	// phpcs:ignore
	if ( isset( $_GET['error'] ) ) {
		?>
		<div class="envato-tk-notice">
				<div class="tk-error">
					<p>
						<strong>Sorry there was an error saving your request.</strong>
					</p>
				</div>
		</div>
		<?php
	}
	?>
	<div class="envato-tk__tabset">
		<!-- Tab 1 -->
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Template_Kit_Export_Admin::ADMIN_MENU_SLUG . '&step=1' ) ); ?>" class="envato-tk__tab<?php echo 1 === $current_active_step ? ' envato-tk__tab--current' : ''; ?>">1. Export Type</a>
		<!-- Tab 2 -->
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Template_Kit_Export_Admin::ADMIN_MENU_SLUG . '&step=2' ) ); ?>" class="envato-tk__tab<?php echo 2 === $current_active_step ? ' envato-tk__tab--current' : ''; ?>">2. Setup Kit</a>
		<!-- Tab 3 -->
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Template_Kit_Export_Admin::ADMIN_MENU_SLUG . '&step=3' ) ); ?>" class="envato-tk__tab<?php echo 3 === $current_active_step ? ' envato-tk__tab--current' : ''; ?>">3. Select Templates</a>
		<!-- Tab 4 -->
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Template_Kit_Export_Admin::ADMIN_MENU_SLUG . '&step=4' ) ); ?>" class="envato-tk__tab<?php echo 4 === $current_active_step ? ' envato-tk__tab--current' : ''; ?>">4. Media Metadata</a>
		<!-- Tab 5 -->
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Template_Kit_Export_Admin::ADMIN_MENU_SLUG . '&step=5' ) ); ?>" class="envato-tk__tab<?php echo 5 === $current_active_step ? ' envato-tk__tab--current' : ''; ?>">5. Export Template Kit</a>
	</div>
	<!-- Tab Panel-->
	<section class="envato-tk__tab-panel">
		<?php
		if ( 1 === $current_active_step || 2 === $current_active_step || 3 === $current_active_step || 4 === $current_active_step || 5 === $current_active_step ) {
			require plugin_dir_path( __FILE__ ) . 'template-kit-export-step-' . $current_active_step . '.php';
		}
		?>
	</section>
</div>
