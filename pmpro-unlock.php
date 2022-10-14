<?php
/**
 * Plugin Name:       Paid Memberships Pro - Unlock Protocol Add On
 * Description:       Integrate Paid Memberships Pro with Unlock Protocol.
 * Plugin URI:        https://www.paidmembershipspro.com/TBD
 * Version:           0.1
 * Requires at least: 5.0
 * Author:            Stranger Studios
 * Author URI:        https://strangerstudios.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pmpro-unlock
 * Domain Path:       /languages
 */

// Constants
define( 'PMPROUP_DIR', dirname( __FILE__ ) );
define( 'PMPROUP_BASENAME', plugin_basename( __FILE__ ) );

// Includes
require_once( PMPROUP_DIR . '/includes/membership-level-settings.php' );
require_once( PMPROUP_DIR . '/includes/functions.php' );
require_once( PMPROUP_DIR . '/includes/profile.php' );
require_once( PMPROUP_DIR . '/includes/checkout.php' );
require_once( PMPROUP_DIR . '/includes/defaults.php' );
require_once( PMPROUP_DIR . '/includes/login.php' );

// Bare basic functions can go here.
/**
 * Initialize the plugin's text domain for translations.
 */
function pmpro_up_load_plugin_textdomain() {
	load_plugin_textdomain( 'pmpro-unlock', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'pmpro_up_load_plugin_textdomain' );