<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */


// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'test');

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );


/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'CL.^lDnANiIGK~uW9`djnQjfdoq2|FEJG9p8`tCX>rFZU&w[N1{{ HD.0YZ3hSLL' );
define( 'SECURE_AUTH_KEY',  '-Ok,J;k]yfP.]PV7cA(I`k>J$LI&K#J6@OqJ8P*h,CX3SCQf6U@LDhOu/np(b]Yh' );
define( 'LOGGED_IN_KEY',    'qM5@6PH~`wUFleThRUQD@NGm*?~H(e2g<wMUe(tDz}?u{IbW$+@x1/w+ep`k<&L.' );
define( 'NONCE_KEY',        'v*xP5dS7LiX?6VYb3KZ>VAqrO.s0q;3RG5E/KK.Vab.p2f:y(Wl|UCgVl><8*T[H' );
define( 'AUTH_SALT',        'wMVMUY}DXjTbcvv!+mn:Mk 3?U)J^l(|af%AegHMw.29FvY9xJ$)^HD2L,zTKCKr' );
define( 'SECURE_AUTH_SALT', 'q#>:r;;J_hW^WPJS[Oz^>BGG&?=,xG(kH7SbEW+W[I-7)@=NI,$]sM`xi)RiJeT5' );
define( 'LOGGED_IN_SALT',   '-+EfZ<s:kV)GAj@?b~+V{?KSppvsz?vf;GK$zMWrW6Nghr5@0}/LKDilg]d]^!6c' );
define( 'NONCE_SALT',       'SM3W@N9D&|ep(K:ra:h;^femGEmrEi`rm{oQv1x[[g2t>:qtDJ3%Gh3&Wf8d7Y)E' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
