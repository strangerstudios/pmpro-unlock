<?php
/**
 * Plugin Name:       Paid Memberships Pro - Unlock Protocol Add On
 * Description:       Integrate Paid Memberships Pro with Unlock Protocol.
 * Plugin URI:        https://www.paidmembershipspro.com/add-ons/unlock-protocol-integration
 * Version:           1.3
 * Requires at least: 5.0
 * Author:            Paid Memberships Pro
 * Author URI:        https://www.paidmembershipspro.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pmpro-unlock
 * Domain Path:       /languages
 */

// Constants
define( 'PMPRO_UNLOCK_DIR', dirname( __FILE__ ) );
define( 'PMPRO_UNLOCK_BASENAME', plugin_basename( __FILE__ ) );
define( 'PMPRO_UNLOCK_CHECKOUT', 'https://app.unlock-protocol.com/checkout' );
define( 'PMPRO_UNLOCK_AUTH', 'https://locksmith.unlock-protocol.com/api/oauth' );

// Includes
require_once( PMPRO_UNLOCK_DIR . '/includes/admin.php' );
require_once( PMPRO_UNLOCK_DIR . '/includes/membership-level-settings.php' );
require_once( PMPRO_UNLOCK_DIR . '/includes/functions.php' );
require_once( PMPRO_UNLOCK_DIR . '/includes/profile.php' );
require_once( PMPRO_UNLOCK_DIR . '/includes/checkout.php' );
require_once( PMPRO_UNLOCK_DIR . '/includes/defaults.php' );
require_once( PMPRO_UNLOCK_DIR . '/includes/login.php' );
require_once( PMPRO_UNLOCK_DIR . '/includes/deprecated.php' );

// Bare basic functions can go here.
/**
 * Initialize the plugin's text domain for translations.
 */
function pmpro_unlock_load_plugin_textdomain() {
	load_plugin_textdomain( 'pmpro-unlock', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'pmpro_unlock_load_plugin_textdomain' );