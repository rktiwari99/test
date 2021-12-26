<?php
/**
 * Adds styles in <head> when in the admin area of the plugin
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

// Fonts directory URL.
$fonts_dir_url = plugins_url( 'assets/fonts/', dirname( __FILE__ ) );
// Create font styles.
$style = '<style type="text/css">
		/*<![CDATA[*/
		@font-face {
			font-family: "template-kit-export";
			src:url("' . $fonts_dir_url . 'envato.eot?20180730");
			src:url("' . $fonts_dir_url . 'envato.eot?#iefix20180730") format("embedded-opentype"),
			url("' . $fonts_dir_url . 'envato.woff?20180730") format("woff"),
			url("' . $fonts_dir_url . 'envato.ttf?20180730") format("truetype"),
			url("' . $fonts_dir_url . 'envato.svg?20180730#envato") format("svg");
			font-weight: normal;
			font-style: normal;
		}
		#adminmenu .toplevel_page_envato-export-template-kit .menu-icon-generic div.wp-menu-image:before {
			font: normal 20px/1 "template-kit-export" !important;
			content: "\e600";
			speak: none;
			padding: 6px 0;
			height: 34px;
			width: 20px;
			display: inline-block;
			-webkit-font-smoothing: antialiased;
			-moz-osx-font-smoothing: grayscale;
			-webkit-transition: all .1s ease-in-out;
			-moz-transition:    all .1s ease-in-out;
			transition:         all .1s ease-in-out;
		}
		/*]]>*/
	</style>';
// Remove space after colons.
$style = str_replace( ': ', ':', $style );
// Remove whitespace.
// phpcs:ignore
echo str_replace( array( "\r\n", "\r", "\n", "\t", '	', '		', '		', '  ', '    ' ), '', $style );
