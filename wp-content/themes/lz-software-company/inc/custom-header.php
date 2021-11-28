<?php
/**
 * Custom header implementation
 */

function lz_software_company_custom_header_setup() {
	add_theme_support( 'custom-header', apply_filters( 'lz_software_company_custom_header_args', array(
		'default-text-color'     => 'fff',
		'header-text' 			 =>	false,
		'width'                  => 1200,
		'height'                 => 95,
		'wp-head-callback'       => 'lz_software_company_header_style',
	) ) );
}

add_action( 'after_setup_theme', 'lz_software_company_custom_header_setup' );

if ( ! function_exists( 'lz_software_company_header_style' ) ) :
/**
 * Styles the header image and text displayed on the blog
 *
 * @see lz_software_company_custom_header_setup().
 */
add_action( 'wp_enqueue_scripts', 'lz_software_company_header_style' );
function lz_software_company_header_style() {
	//Check if user has defined any header image.
	if ( get_header_image() ) :
	$custom_css = "
        .page-template-custom-home-page .top-header, .header-box {
			background-image:url('".esc_url(get_header_image())."');
			background-size: 100% 100%;
		}";
	   	wp_add_inline_style( 'lz-software-company-basic-style', $custom_css );
	endif;
}
endif; // lz_software_company_header_style