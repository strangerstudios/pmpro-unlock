<?php
/**
 * Plugin Name:       Paid Memberships Pro - Unlock Protocol Add On
 * Description:       Integrate Paid Memberships Pro with Unlock Protocol.
 * Plugin URI:        https://www.paidmembershipspro.com
 * Version:           0.1
 * Requires at least: 5.0
 * Author:            Stranger Studios
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-basics-plugin
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