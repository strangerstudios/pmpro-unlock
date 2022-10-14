<?php
/**
 * Login functions for authenticating with wallets.
 */


/**
 * Add a "Connect Your Crypto Wallet" button to the default login page of WordPress.
 */
function pmproup_add_button_to_login_form() {
	echo pmproup_connect_wallet_button( 'login' );
}
add_action( 'login_form', 'pmproup_add_button_to_login_form' );

/**
 * Authenticate via crypto network.
 */
function pmproup_authenticate_via_wallet( $user ) {

    if ( $user instanceof WP_User ) {
			return $user;
		}

		$code  = sanitize_text_field( $_REQUEST['code'] );
		$state = sanitize_text_field( $_REQUEST['state'] );

		if ( '' === $code || ! wp_verify_nonce( $state, 'pmproup_state') ) {
			return $user;
		}

        // Let's get the wallet address from the auth code.
        $wallet = pmproup_try_to_get_wallet();

        if ( is_wp_error( $wallet ) ) {
            $error = new WP_Error();
            $user  = new WP_Error( 'authentication_failed', __( 'ERROR: There was a problem retrieving the wallet address.' ) );
            return $user;
        }
        // Try to get a user via their wallet now
        $user = pmproup_get_user_by_wallet( $wallet );

        if ( ! $user ) {
            $error = new WP_Error();
            $user  = new WP_Error( 'authentication_failed', __( 'ERROR: Unable to find an account with that wallet. Please create a WordPress account first and link your wallet.' ) );
            return $user;
        }
        return $user;
}
add_action( 'authenticate', 'pmproup_authenticate_via_wallet' );

