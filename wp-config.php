<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'u505216193_xahyr');

/** MySQL database username */
define('DB_USER', 'u505216193_pazuz');

/** MySQL database password */
define('DB_PASSWORD', 'LydaraZury');

/** MySQL hostname */
define('DB_HOST', 'mysql');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'SZgrn5ehfakkJvOloFTCDQKm347iq2sPoRlqU0powt4G9mGaagCcJiFFUTKfXb20');
define('SECURE_AUTH_KEY',  'Xum9iHte51tkseT7cEIJbOcVJUdmaOuw1bTnnq70josezdCoYWs5Jj4Uuur7hk1M');
define('LOGGED_IN_KEY',    'ms167zfPV47KD0YUfUyP7aFo3H1BqjrxbKe2qV3Q6XJUsrrb0zCPRYURdE0HYMM9');
define('NONCE_KEY',        'DCLnxLv0X6a3K2vpk77HZKitkmlvHEHD1AuanFEBXukwYctFaG9SdhV18nj4KFgD');
define('AUTH_SALT',        'ugtNbapKveID07zxdyj14p5M3nSTy051Sr37WOMfAVoXXMPhtzChevUlxl9TQOch');
define('SECURE_AUTH_SALT', 'tBJgTOj5yUYuyo1czw9kISuNaHud5QuxXu4wDzJNaEKE92LPQ4KqwIpkN0RrtvB8');
define('LOGGED_IN_SALT',   'opmoccfR3oA2xMGtu7dI16FqC0CBvUXjPoxABT1E1PbdRoJH03bWTCsBFgcAJHZ9');
define('NONCE_SALT',       'dKB6Rt57IPdESYcIcRXrLaIwSyoRwyxSoZH7UHkcId86FjHrgPdnsjo8j1rIbaoa');

/**
 * Other customizations.
 */
define('FS_METHOD','direct');define('FS_CHMOD_DIR',0755);define('FS_CHMOD_FILE',0644);
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');

/**
 * Turn off automatic updates since these are managed upstream.
 */
define('AUTOMATIC_UPDATER_DISABLED', true);


/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'oifr_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
