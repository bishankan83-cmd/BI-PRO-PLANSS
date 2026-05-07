<?php
define( 'WP_CACHE', true );
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'planatir_wp545' );

/** Database username */
define( 'DB_USER', 'planatir_wp545' );

/** Database password */
define( 'DB_PASSWORD', 'h2Jp].94wS' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'jniglpwjzrxz7veqqqk5iddzlarvnqywvxkveve3zcxbl9jzwsedru7kjnbrdz2x' );
define( 'SECURE_AUTH_KEY',  'mcmi0p70jeebbytcmbngwyxnwswmcdvjs9h0a6bhbn5myneppqgeuuy2dvrnu7kf' );
define( 'LOGGED_IN_KEY',    'mtulxmvzrihttamctzo0n9bn4kf2f8zzypzgidvg8kg5prbkezqsyvrelk3eeav6' );
define( 'NONCE_KEY',        '8b0bvohjlpedmk4wpivxjgi86avrikulwwl71rrp2qny3evn9vsd05kmnyrjitr0' );
define( 'AUTH_SALT',        '284oydhujnx764h3pwndtpynm2mzdt2sj0zgn13wyazwa2kit30ldmakqqtv112l' );
define( 'SECURE_AUTH_SALT', 'uuzlznbvcbfgb0agjkbzhffmjkh48uf87fcc6v1pfk2tnx29eio6yyz23htg0e8k' );
define( 'LOGGED_IN_SALT',   'd45m52abycbaffi1hl7op6fmtbjzxouwncem46bum0yfgwww2zr2ev5ash30rixz' );
define( 'NONCE_SALT',       'rpwucmkvh5z62nrpiwdxfrt2ctrtqn8g38kwc3rgsp79zbjpx0quf30599ocjlgt' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpct_';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
