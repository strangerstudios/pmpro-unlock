<?php
/**
 * Backwards compatibility for the old pmproup_ prefix.
 *
 * This plugin used to share the pmproup_ prefix with PMPro User Pages. Everything was renamed
 * to pmpro_unlock_, and the old names are kept here for custom code that still uses them.
 *
 * @since 1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Call the pmpro_unlock_ version of a deprecated pmproup_ function.
 *
 * @since 1.3
 *
 * @param string $old_function The deprecated function name.
 * @param array  $args The arguments passed to the deprecated function.
 * @return mixed The return value of the new function.
 */
function pmpro_unlock_call_deprecated_function( $old_function, $args ) {
	$new_function = 'pmpro_unlock_' . substr( $old_function, strlen( 'pmproup_' ) );
	_deprecated_function( esc_html( $old_function ), '1.3', esc_html( $new_function ) );
	return call_user_func_array( $new_function, $args );
}

/**
 * Declare the old pmproup_ functions and constants.
 *
 * Runs on plugins_loaded so every plugin has been included first. Anything already declared
 * by another plugin is skipped.
 *
 * @since 1.3
 */
function pmpro_unlock_declare_deprecated_functions() {
	// pmproup_plugin_row_meta is intentionally not declared. PMPro User Pages uses that name, and
	// declaring it here would cause a fatal error when User Pages is activated.
	if ( ! function_exists( 'pmproup_add_button_to_login_form' ) ) {
		function pmproup_add_button_to_login_form() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_add_wallet_to_checkout' ) ) {
		function pmproup_add_wallet_to_checkout() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_admin_notice' ) ) {
		function pmproup_admin_notice() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_admin_notice_activation_hook' ) ) {
		function pmproup_admin_notice_activation_hook() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_after_cancel_membership_level' ) ) {
		function pmproup_after_cancel_membership_level() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_after_checkout' ) ) {
		function pmproup_after_checkout() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_authenticate_via_wallet' ) ) {
		function pmproup_authenticate_via_wallet() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_check_save_wallet' ) ) {
		function pmproup_check_save_wallet() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_checkout_level' ) ) {
		function pmproup_checkout_level() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_clear_transients' ) ) {
		function pmproup_clear_transients() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_connect_wallet_button' ) ) {
		function pmproup_connect_wallet_button() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_get_auth_code' ) ) {
		function pmproup_get_auth_code() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_get_checkout_url' ) ) {
		function pmproup_get_checkout_url() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_get_client_id' ) ) {
		function pmproup_get_client_id() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_get_login_url' ) ) {
		function pmproup_get_login_url() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_get_redirect_uri' ) ) {
		function pmproup_get_redirect_uri() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_get_user_by_wallet' ) ) {
		function pmproup_get_user_by_wallet() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_has_lock_access' ) ) {
		function pmproup_has_lock_access() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_has_membership_level' ) ) {
		function pmproup_has_membership_level() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_hook_edit_member_profile' ) ) {
		function pmproup_hook_edit_member_profile() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_is_valid_wallet' ) ) {
		function pmproup_is_valid_wallet() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_level_settings' ) ) {
		function pmproup_level_settings() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_load_plugin_textdomain' ) ) {
		function pmproup_load_plugin_textdomain() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_networks_list' ) ) {
		function pmproup_networks_list() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_plugin_action_links' ) ) {
		function pmproup_plugin_action_links() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_pmpro_has_membership_access_filter' ) ) {
		function pmproup_pmpro_has_membership_access_filter() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_pmpro_member_edit_panels' ) ) {
		function pmproup_pmpro_member_edit_panels() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_profile_connect_wallet' ) ) {
		function pmproup_profile_connect_wallet() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_profile_remove_wallet' ) ) {
		function pmproup_profile_remove_wallet() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_registration_checks' ) ) {
		function pmproup_registration_checks() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_save_membership_level' ) ) {
		function pmproup_save_membership_level() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_save_wallet_after_level_change' ) ) {
		function pmproup_save_wallet_after_level_change() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_should_have_access' ) ) {
		function pmproup_should_have_access() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_try_to_get_wallet' ) ) {
		function pmproup_try_to_get_wallet() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_validate_auth_code' ) ) {
		function pmproup_validate_auth_code() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_validate_lock' ) ) {
		function pmproup_validate_lock() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}
	if ( ! function_exists( 'pmproup_verify_state' ) ) {
		function pmproup_verify_state() {
			return pmpro_unlock_call_deprecated_function( __FUNCTION__, func_get_args() );
		}
	}

	// Old constants.
	$old_constants = array(
		'PMPROUP_DIR'      => PMPRO_UNLOCK_DIR,
		'PMPROUP_BASENAME' => PMPRO_UNLOCK_BASENAME,
		'PMPROUP_CHECKOUT' => PMPRO_UNLOCK_CHECKOUT,
		'PMPROUP_AUTH'     => PMPRO_UNLOCK_AUTH,
	);
	foreach ( $old_constants as $old_constant => $value ) {
		if ( ! defined( $old_constant ) ) {
			define( $old_constant, $value );
		}
	}
}
add_action( 'plugins_loaded', 'pmpro_unlock_declare_deprecated_functions' );

/**
 * Map of new filter names to their deprecated pmproup_ names.
 *
 * @since 1.3
 *
 * @return array New filter name => old filter name.
 */
function pmpro_unlock_deprecated_filters() {
	return array(
		'pmpro_unlock_network_list'                         => 'pmproup_network_list',
		'pmpro_unlock_paywall_config'                       => 'pmproup_paywall_config',
		'pmpro_unlock_validate_auth_code_params'            => 'pmproup_validate_auth_code_params',
		'pmpro_unlock_user_validate_params'                 => 'pmproup_user_validate_params',
		'pmpro_unlock_has_lock_access'                      => 'pmproup_has_lock_access',
		'pmpro_unlock_has_lock_access_transient_expiration' => 'pmproup_has_lock_access_transient_expiration',
	);
}

/**
 * Run callbacks that are still attached to the old pmproup_ name of the current filter.
 *
 * @since 1.3
 *
 * @param mixed $value The value being filtered.
 * @return mixed The filtered value.
 */
function pmpro_unlock_apply_deprecated_filter( $value ) {
	$new_filter = current_filter();
	$filters    = pmpro_unlock_deprecated_filters();
	if ( empty( $filters[ $new_filter ] ) ) {
		return $value;
	}

	// Only shows a deprecation notice if something is hooked to the old filter.
	return apply_filters_deprecated( $filters[ $new_filter ], func_get_args(), '1.3', $new_filter );
}
foreach ( array_keys( pmpro_unlock_deprecated_filters() ) as $pmpro_unlock_new_filter ) {
	// Run early so callbacks on the new filter name get the final say.
	add_filter( $pmpro_unlock_new_filter, 'pmpro_unlock_apply_deprecated_filter', 1, 4 );
}
unset( $pmpro_unlock_new_filter );
