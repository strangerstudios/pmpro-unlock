<?php
// Functions go here for now.

define( 'PMPROUP_CHECKOUT', 'https://app.unlock-protocol.com/checkout' );
define( 'PMPROUP_AUTH', 'https://locksmith.unlock-protocol.com/api/oauth' );

/**
 * Get the client_id value whenever needed.
 *
 * @return string The domain name is used as the client_id for Unlock Protocol.
 */
function pmproup_get_client_id() {
	return wp_parse_url( home_url(), PHP_URL_HOST );
}


// Get default redirect URL (Login page.)
function pmproup_get_redirect_uri() {
	return apply_filters( 'unlock_protocol_get_redirect_uri', wp_login_url() );
}

// Generate the Login URL we need.
function pmproup_get_login_url( $redirect_uri = null ) {
	$login_url = add_query_arg(
		array(
			'client_id'    => pmproup_get_client_id(),
			'redirect_uri' => $redirect_uri ? $redirect_uri : pmproup_get_redirect_uri(),
			'state'  => wp_create_nonce( 'pmproup_state' ),
		),
		PMPROUP_CHECKOUT
	);

	return apply_filters( 'unlock_protocol_get_login_url', esc_url( $login_url ) );
}

//Generate the purchase URL for the level.
function pmproup_get_checkout_url( $lock, $redirect_uri ) {

	// Build the checkout array to buy the NFT.
    $lock_checkout = array();

	$lock_address = $lock['lock_address'];
	$lock_checkout[$lock_address] = array( 'network' => $lock['network_id'] );

		$paywall_config = apply_filters(
			'pmproup_paywall_config',
			array(
				'locks'       => $lock_checkout,
				'pessimistic' => true,
			)
		);

		$checkout_url = add_query_arg(
			array(
				'redirectUri'   => $redirect_uri,
				'paywallConfig' => urlencode( wp_json_encode( $paywall_config ) ),
			),
			PMPROUP_CHECKOUT
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
function pmproup_validate_auth_code( $code ) {
	$params = apply_filters(
		'pmproup_validate_auth_code_params',
		array(
			'grant_type'   => 'authorization_code',
			'client_id'    => pmproup_get_client_id(),
			'redirect_uri' => pmproup_get_redirect_uri(),
			'code'         => sanitize_text_field( $code ),
			'state'		=> sanitize_text_field( $_REQUEST['state'] )
		)
	);

	$args = array(
		'body'        => $params,
		'redirection' => '30',
	);

	// If the nonce isn't available or failed to verify stop.
	if ( ! $params['state'] ) {
		return false;
	} else {
		if ( ! wp_verify_nonce( $params['state'], 'pmproup_state' ) ) {
			return false;
		}
	}

	$response = wp_remote_post( esc_url( PMPROUP_AUTH ), $args );

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
function pmproup_validate_lock( $network, $lock_address, $wallet = null ) {
    $wallet = $wallet ? $wallet : pmproup_try_to_get_wallet();
    
    // If this is still empty, bail.
    if ( is_wp_error( $wallet ) ) {
        return false;
    }

    $wallet = substr( $wallet, 2 );

    $params = apply_filters(
			'pmproup_user_validate_params',
			array(
				'method'  => 'eth_call',
				'params'  => array(
					array(
						'to'   => $lock_address,
						'data' => sprintf( '0x6d8ea5b4000000000000000000000000%s', $wallet ),
					),
					'latest',
				),
				'id'      => 31337,
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
 */
function pmproup_connect_wallet_button( $state = null ) {
	if ( is_admin() ) {
		$redirect_uri = admin_url( basename( $_SERVER['REQUEST_URI'] ) );
	} else {
		$redirect_uri = get_permalink();
	}

	switch ( $state ) {
		case 'login':
			$button_text = esc_html__( 'Login with Crypto Wallet', 'pmpro-unlock' );
			break;

		default:
			$button_text = esc_html__( 'Connect Your Crypto Wallet', 'pmpro-unlock' );
			break;
	}
	
    $url = pmproup_get_login_url( esc_url( $redirect_uri ) );
?>
    <div class='pmproup-protocol-login-container' style="margin-bottom:20px;">
        <a href="<?php echo esc_url( $url ); ?>" rel="nofollow" class="pmproup-protocol-connect-button" style="background-color: black;color:white;padding:1em;"><?php echo $button_text; ?></a>
    </div>
<?php
}

/**
 * Validate wallet and store to usermeta for later use so we don't have to make API calls all the time.
 * 
 * @return string|bool $wallet The user's recently saved wallet address. Returns false if no wallet address is found/saved.
 */
function pmproup_check_save_wallet( $user_id = null, $code = null ) {
	// Let's check code from REQUEST param or SESSION.
    if ( empty( $code ) ) {
		$code = pmproup_get_auth_code();
    }

    $wallet = false; // Default value.

    // Set user to current user if variable is empty but logged-in.
    if ( empty( $user_id ) && is_user_logged_in() ) {
        global $current_user;
        $user_id = $current_user->ID;
    }

    // Try to get wallet from meta if we have their ID.
    if ( ! empty( $user_id ) && ! $code ) {
        $wallet = get_user_meta( $user_id, 'pmproup_wallet', true ); // Try to get it from user meta if we know they're logged in.
    }

    // Let's try save/update the wallet to user meta for reference if we see a 'code' query param
    if ( $code ) {
        $wallet = pmproup_validate_auth_code( $code );

        if ( ! is_wp_error( $wallet ) && $user_id ) {
           update_user_meta( $user_id, 'pmproup_wallet', $wallet ); // Update wallet even if it's false, let's assume for now that people might be unlinking their wallet.
		   pmpro_unset_session_var( 'code' );
        } elseif ( is_wp_error( $wallet ) ) {
			pmpro_unset_session_var( 'code' );
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
function pmproup_try_to_get_wallet( $user_id = null ) {
	if ( empty( $user_id ) ) {
		global $current_user;
		$user_id = $current_user->ID;
	}

	$wallet = '';

	// If user ID isn't empty, try to get wallet from meta.
	if ( ! empty( $user_id ) ) {
		// Let's try get their meta values now.
		$wallet = get_user_meta( $user_id, 'pmproup_wallet', true );
		
		/// Look into this logic.
		if ( empty( $wallet ) ) {
			$wallet = get_user_meta( $user_id, 'ethereum_address', true ); // Take a chance here, if they had Unlock Protocol installed prior sync to that.
		}
	}

	if ( empty( $wallet ) ) {
		$code = pmproup_get_auth_code();	
		if ( $code ) {
			$wallet = pmproup_validate_auth_code( $code );
		}
	}

	return $wallet;
}

/// Function to get the $code value from request or session. TODO: Check nonce too.
/**
 * Helper function to try and get the auth code from either session or query params.
 *
 * @return string $code The oAuth code when connecting a wallet.
 */
function pmproup_get_auth_code() {
	$code = '';

	// Let's try to overwrite any session data with REQUEST param stuff.
	if ( isset( $_REQUEST['code' ] ) ) {
		pmpro_unset_session_var( 'code' ); // Let's try unset any SESSION data we might have.
		$code = sanitize_text_field( $_REQUEST['code'] );
		pmpro_set_session_var( 'code', $code );
	}

	// try get it from the session.
	if ( empty( $code ) ) {
		$code = pmpro_get_session_var( 'code' );
	}

	return $code;
}

/**
 * Checking to ensure the member still has access to the NFT periodically. If they don't, set it to false.
 *
 * @return bool $has_level A boolean value to check if a user should have a level or not.
 */
function pmproup_has_membership_level( $has_level, $user_id, $levels ) {
	// if they don't have access already, just bail.
	if ( ! $has_level ) {
		return $has_level;
	}

	$level = pmpro_getMembershipLevelForUser( $user_id );
	$level_id = $level->ID;

	$level_lock_options = get_option( 'pmproup_' . $level_id, false );
	$wallet = pmproup_try_to_get_wallet( $user_id );

	// If no wallet is found, let's leave it to PMPro to handle.
	var_dump( $level_lock_options );
	if ( empty( $level_lock_options ) || empty( $wallet ) ) { /// Improve this check here.
		return $has_level;
	}

	// Check if they have lock access.
	$check_lock = pmproup_has_lock_access( $level_lock_options['network_rpc'], $level_lock_options['lock_address'], $wallet );
	
	// If they hold an active NFT, deny access.
	if ( ! $check_lock ) {
		$has_level = false;
	}

	return $has_level;
}
add_filter( 'pmpro_has_membership_level', 'pmproup_has_membership_level', 10, 3 );

/**
 * Undocumented function
 *
 * @param string $network The Network RCP Endpoint.
 * @param string $lock The lock's address (contract address).
 * @param string $wallet The user's crypto wallet address.
 * @return bool $has_lock_access Check Unlock Protocols network to ensure the wallet address has access to a lock.
 */
function pmproup_has_lock_access( $network, $lock, $wallet ) {

	$network = sanitize_text_field( $network );
	$lock = sanitize_text_field( $lock );
	$wallet = sanitize_text_field( $wallet );

	$check_lock = pmproup_validate_lock( $network, $lock, $wallet );

	$has_lock_access = false;

	if ( ! is_wp_error( $check_lock ) && hexdec( $check_lock['result'] ) == 1 ) {
		$has_lock_access = true;
	}

	return apply_filters( 'pmproup_has_lock_access', $has_lock_access, $network, $lock, $wallet );
}

/**
 * Function to get the user account linked to a wallet address that's stored in meta.
 *
 * @param [type] $wallet
 * @return object|bool $user Returns the user object or false if the user isn't found.
 */
function pmproup_get_user_by_wallet( $wallet ) {
	$args = array(
		'meta_key'     => 'pmproup_wallet',
		'meta_value'   => sanitize_text_field( $wallet ),
		'meta_compare' => '=',
	);

	$user_query = new \WP_User_Query( $args );
	$users      = $user_query->get_results();
	if ( ! empty( $users ) ) {
		return $users[0];
	}

	return false;
}