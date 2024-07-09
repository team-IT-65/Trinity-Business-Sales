<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
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
define( 'DB_NAME', 'trinity' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

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
define( 'AUTH_KEY',         'Bp#B^a[A.JkiR6ItL,-g^n;JhhjEYX{nP8!~YUL=tkgz<-0M #9lA?o@*sL%Qj~8' );
define( 'SECURE_AUTH_KEY',  '<oY+prRv)X#K#?C]b3.x{jd`EO*~jL#GKw$I[8;~;i|Txk|Xoo%9ojq`dx2Svjp3' );
define( 'LOGGED_IN_KEY',    '@,ug&[J!rEljn1q5.Wd.sJZ.`rje?%-;uqC#8ayD!sS7OMIT 7(/!STwYNoLbPnT' );
define( 'NONCE_KEY',        ')AWN+Y+0klfEC,w?v_Xa%DBsZK d)%Hfv;RhSyv-2{jtn<]OYIOL4P4MYGNFIiFy' );
define( 'AUTH_SALT',        '@|Y#M-$Ar_-k@Z[IyaJ~BTC]%mh/xCxaDq5!?:G.RyS,QEpWhhR@;h0)HM%[r<33' );
define( 'SECURE_AUTH_SALT', '@%X;19zmyK..aZ#NhS_^.kZm2A4pF @~_/~HVwN)<`O3`J)D3y;Urq8Ddu+-v#44' );
define( 'LOGGED_IN_SALT',   'Yl;npxWNv,DWgg<1PchFOw;sL;z`4C;8VlvxXkc`x|iWS^R^qfy>5v?[R9 4yV1G' );
define( 'NONCE_SALT',       'Wmajlf<%PkfK)$+~C`-><>#T/1wpZlKO`!n_PIduJr,2d}PI4QPmE9*(DH`KZJ2I' );

/**#@-*/

/**
 * WordPress database table prefix.
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
