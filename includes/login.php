<?php
/**
 * Login functions for authenticating with wallets.
 */


/**
 * Add a "Connect Your Crypto Wallet" button to the default login page of WordPress.
 */
function pmpro_unlock_add_button_to_login_form() {
	pmpro_unlock_connect_wallet_button( 'login' );
}
add_action( 'login_form', 'pmpro_unlock_add_button_to_login_form' );

/**
 * Authenticate via crypto network.
 */
function pmpro_unlock_authenticate_via_wallet( $user ) {
	// If the user is already logged in, don't do anything.
	if ( $user instanceof WP_User ) {
		return $user;
	}

	// Check if user is trying to log in via a crypto wallet.
	if ( ! pmpro_unlock_verify_state() ) {
		return $user;
	}

	// Without an auth code there is nothing to authenticate with.
	if ( empty( pmpro_unlock_get_auth_code() ) ) {
		return $user;
	}

	// Let's get the wallet address from the auth code.
	$wallet = pmpro_unlock_try_to_get_wallet();

	if ( is_wp_error( $wallet ) || ! pmpro_unlock_is_valid_wallet( $wallet ) ) {
		$user  = new WP_Error( 'authentication_failed', __( 'ERROR: There was a problem retrieving the wallet address.' ) );
		return $user;
	}

	// Try to get a user via their wallet now
	$user = pmpro_unlock_get_user_by_wallet( $wallet );
	if ( ! $user ) {
		$user  = new WP_Error( 'authentication_failed', __( 'ERROR: Unable to find an account with that wallet. Please create a WordPress account first and link your wallet.' ) );
		return $user;
	}
	return $user;
}
add_filter( 'authenticate', 'pmpro_unlock_authenticate_via_wallet', 10, 1 );