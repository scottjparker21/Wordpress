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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'portfolio');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'Q[[G1jt?G8nJ=M=O27ESOK*gDnnzyo,#a%/^m8eBuBA0&yvB7sT7FHQzr=*,*X;/');
define('SECURE_AUTH_KEY',  '4!Q))^0p8S<Y9Uq;D^XeQF;9$PYVt!VK80Qn3&h;4>qDIS-^zD72U1,lTzVq-.c8');
define('LOGGED_IN_KEY',    ';b!}sp`YW@&gFxd ^x7dR6/3+K@ 9)N(Ug*.K$vEL. ~7QXi~dX:Kn/ZFu?78bv3');
define('NONCE_KEY',        '5P/l^,7Px&Z[zul_g#cj^V3kV|nIc*H]5=Tr*Fh)<%`eCW7-~?E~bfDzAW2H3/+j');
define('AUTH_SALT',        'aXy77F_NsbRJWx2US68@]&kSOgKOfwLaD9EB&3{Zf^8!OhhJc,>,s@ATE7{P6Gd2');
define('SECURE_AUTH_SALT', 'VP>t;,*W2)Je,G8FtG_)L?8XSDyN$`euQW/DS|ViwVA_zIhB+A((C%D#KwS1?^+t');
define('LOGGED_IN_SALT',   '&{{r_Pr^>},1?)P`![uy=s(Q(*%Evqd:GD*X8]M(vfa^!`$T]c4LZl iLrL-gY.m');
define('NONCE_SALT',       '^Og`],Qlq}%Dtc*FD-ZyQJq|21(XleLH>fkMc*T,Eoy6PkD_~*Sg)8=>#fwj!ROX');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
