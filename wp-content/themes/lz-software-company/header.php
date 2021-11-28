<?php
/**
 * The header for our theme
 *
 * @subpackage LZ Software Company
 * @since 1.0
 * @version 0.1
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php if ( function_exists( 'wp_body_open' ) ) {
    wp_body_open();
} else {
    do_action( 'wp_body_open' );
}?>

<a class="screen-reader-text skip-link" href="#skip-content"><?php esc_html_e( 'Skip to content', 'lz-software-company' ); ?></a>

<div class="header-box">
	<div class="container">
		<div class="top-header">
			<div class="row m-0">
				<div class="col-lg-3 col-md-4">
					<div class="logo">
				        <?php if( has_custom_logo() ){ lz_software_company_the_custom_logo();
				           	}else{ ?>
				          	<h1><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
				          	<?php $description = get_bloginfo( 'description', 'display' );
				          	if ( $description || is_customize_preview() ) : ?> 
				            <p class="site-description"><?php echo esc_html($description); ?></p>
				        <?php endif; }?>
				    </div>
				</div>
				<div class="col-lg-8 col-md-7 col-6">
					<div id="header" class="menu-section">
						<div class="toggle-menu responsive-menu">
				            <button onclick="lz_software_company_open()" role="tab" class="mobiletoggle"><i class="fas fa-bars"></i><span class="screen-reader-text"><?php esc_html_e('Open Menu','lz-software-company'); ?></span></button>
				        </div>
						<div id="sidelong-menu" class="nav sidenav">
			                <nav id="primary-site-navigation" class="nav-menu" role="navigation" aria-label="<?php esc_attr_e( 'Top Menu', 'lz-software-company' ); ?>">
			                  	<?php
				                    wp_nav_menu( array( 
										'theme_location' => 'primary',
										'container_class' => 'main-menu-navigation clearfix' ,
										'menu_class' => 'clearfix',
										'items_wrap' => '<ul id="%1$s" class="%2$s mobile_nav">%3$s</ul>',
										'fallback_cb' => 'wp_page_menu',
				                    ) ); 
			                  	?>
			                  	<a href="javascript:void(0)" class="closebtn responsive-menu" onclick="lz_software_company_close()"><i class="fas fa-times"></i><span class="screen-reader-text"><?php esc_html_e('Close Menu','lz-software-company'); ?></span></a>
			                </nav>
			            </div>
					</div>
				</div>
				<div class="col-lg-1 col-md-1 col-6">
					<div class="search-box">
      					<button type="button" data-toggle="modal" data-target="#myModal"><i class="fas fa-search"></i></button>
      				</div>
				</div>
			</div>
			<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			    <div class="modal-dialog" role="document">
			        <div class="modal-body">
				        <div class="serach_inner">
				        	<?php get_search_form(); ?>
			        	</div>
			        </div>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			    </div>
			</div>
		</div>
	</div>
</div>