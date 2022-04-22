<?php
// Functions go here for now.

define( 'PMPRO_UNLOCK_CHECKOUT', 'https://app.unlock-protocol.com/checkout' );
define( 'PMPRO_UNLOCK_AUTH', 'https://locksmith.unlock-protocol.com/api/oauth' );

/**
 * Get the client_id value whenever needed.
 *
 * @return string The domain name is used as the client_id for Unlock Protocol.
 */
function pmpro_up_get_client_id() {
	return wp_parse_url( home_url(), PHP_URL_HOST );
}


// Get default redirect URL (Login page.)
function pmpro_up_get_redirect_uri() {
	return apply_filters( 'unlock_protocol_get_redirect_uri', wp_login_url() );
}

// Generate the Login URL we need.
function pmpro_up_get_login_url( $redirect_uri = null ) {
	$login_url = add_query_arg(
		array(
			'client_id'    => pmpro_up_get_client_id(),
			'redirect_uri' => $redirect_uri ? $redirect_uri : pmpro_up_get_redirect_uri(),
			'pmpro_state'  => wp_create_nonce( 'pmpro_unlock_login_state' ),
		),
		PMPRO_UNLOCK_CHECKOUT
	);

	return apply_filters( 'unlock_protocol_get_login_url', esc_url( $login_url ) );
}

//Generate the purchase URL for the level.
function pmpro_up_get_checkout_url( $lock, $redirect_uri ) {

    $lock_obj = array();

    $lock_obj[$lock] = array( 'network' => 4 );

		$paywall_config = apply_filters(
			'unlock_protocol_paywall_config',
			array(
				'locks'       => $lock_obj,
				'pessimistic' => true,
			)
		);

		$checkout_url = add_query_arg(
			array(
				'redirectUri'   => $redirect_uri,
				'paywallConfig' => wp_json_encode( $paywall_config ),
			),
			PMPRO_UNLOCK_CHECKOUT
		);

    return $checkout_url;
}

/**
 * Check if user login has been successful or not. ///Not sure if totally needed. Probably.
 * 
 *
 * @param [type] $code
 * @return void
 */
function pmpro_up_validate_auth_code( $code ) {
		$params = apply_filters(
			'unlock_protocol_validate_auth_code_params',
			array(
				'grant_type'   => 'authorization_code',
				'client_id'    => pmpro_up_get_client_id(),
				'redirect_uri' => pmpro_up_get_redirect_uri(),
				'code'         => sanitize_text_field( $code ),
			)
		);

		$args = array(
			'body'        => $params,
			'redirection' => '30',
		);

		$response = wp_remote_post( esc_url( PMPRO_UNLOCK_AUTH ), $args );

		if ( is_wp_error( $response ) ) {
			return new \WP_Error( 'unlock_validate_auth_code', $response );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! array_key_exists( 'me', $body ) ) {
			return new \WP_Error( 'unlock_validate_auth_code', __( 'Invalid Account', 'unlock-protocol' ) );
		}

		return $body['me'];
	}




/// TODO: Cache results for a little bit and figure out the param calls etc.
/**
 * Validate if the user has access to the NFT (Lock) or not.
 *
 * @param [type] $network
 * @param [type] $lock_address
 * @param [type] $wallet
 * @return void
 */
function pmpro_up_validate_lock( $network, $lock_address, $wallet = null ) {
    $wallet = $wallet ? $wallet : pmpro_up_try_to_get_wallet();
    
    // If this is still empty, bail.
    if ( is_wp_error( $wallet ) ) {
        return false;
    }

    $wallet = substr( $wallet, 2 );

    $params = apply_filters(
			'pmpro_unlock_protocol_user_validate_params',
			array(
				'method'  => 'eth_call',
				'params'  => array(
					array(
						'to'   => $lock_address,
						'data' => sprintf( '0x6d8ea5b4000000000000000000000000%s', $wallet ),
					),
					'latest',
				),
				'id'      => 31337, //Local Host Chain ID.
				'jsonrpc' => '2.0',
			)
		);

		$args = array(
			'body'        => wp_json_encode( $params ),
			'redirection' => '30',
		);

		$response = wp_remote_post( esc_url( $network ), $args );

		if ( is_wp_error( $response ) ) {
			return new \WP_Error( 'unlock_validate_error', $response );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return $body;
}



/**
 * Generate a button to connect wallet to Unlock Protocol.
 * /// Important Function and Style the button!
 */
function pmpro_up_connect_wallet_button() {
    $url = pmpro_up_get_login_url( get_permalink() );
?>
    <div class='pmpro-unlock-protocol-login-container'>
        <a href="<?php echo esc_url( $url ); ?>" rel="nofollow" class="pmpro-unlock-protocol-connect-button">Connect Your Crypto Wallet</a>
    </div>
<?php
}

/**
 * Validate wallet and store to usermeta for later use so we don't have to make API calls all the time.
 * /// Important function.
 */
function pmpro_up_check_save_wallet( $user_id = null, $code = null ) {
    
	// Let's check code from REQUEST param or SESSION.
    if ( empty( $code ) ) {

		if ( pmpro_get_session_var( 'code' ) ) {
			$code = sanitize_text_field( pmpro_get_session_var( 'code' ) );
		}
		
		// If it's not available from SESSION but REQUEST param is available.
		if ( empty( $code ) && isset( $_REQUEST['code'] ) ) {
			$code = sanitize_text_field( $_REQUEST['code'] );
			pmpro_set_session_var( 'code', $code );
		}

    }

    $wallet = false; // Default value.

    // Set user to current user if variable is empty but logged-in.
    if ( empty( $user_id ) && is_user_logged_in() ) {
        global $current_user;
        $user_id = $current_user->ID;
    }

    // Try to get wallet from meta if we have their ID.
    if ( ! empty( $user_id ) ) {
        $wallet = get_user_meta( $user_id, 'pmpro_unlock_wallet', true ); // Try to get it from user meta if we know they're logged in.
    }

    // Let's try save/update the wallet to user meta for reference if we see a 'code' query param
    if ( ! empty( $code ) ) {
        $wallet = pmpro_up_validate_auth_code( $code );

        if ( ! is_wp_error( $wallet ) && $user_id ) {
           update_user_meta( $user_id, 'pmpro_unlock_wallet', $wallet ); // Update wallet even if it's false, let's assume for now that people might be unlinking their wallet.
        } elseif ( is_wp_error( $wallet ) ) {
            $wallet = $wallet->get_error_message(); /// Maybe return false?
        }
    }

    return $wallet;
}

/**
 * Helper function to try and get a stored wallet address for the user. Fallsback to Unlock Protocol Meta.
 *
 * @param int $user_id The user's WordPress ID if available.
 * @return string $wallet The user's linked wallet address.
 */
function pmpro_up_try_to_get_wallet( $user_id = null ) {
	if ( empty( $user_id ) ) {
		global $current_user;
		$user_id = $current_user->ID;
	}

	// If user ID still empty, bail.
	if ( empty( $user_id ) ) {
		return false;
	}

	// Let's try get their meta values now.
	$wallet = get_user_meta( $user_id, 'pmpro_unlock_wallet', true );
	
	/// Look into this logic.
	if ( empty( $wallet ) ) {
		$wallet = get_user_meta( $user_id, 'ethereum_address', true ); // Take a chance here, if they had Unlock Protocol installed prior sync to that.
	}

	return $wallet;
}
