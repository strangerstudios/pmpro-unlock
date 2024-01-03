<?php
// Functions go here for now.

/**
 * Get the client_id value whenever needed.
 *
 * @return string The domain name is used as the client_id for Unlock Protocol.
 */
function pmproup_get_client_id() {
	$parsed_url =  wp_parse_url( home_url() );
	if ( ! empty( $parsed_url['host'] ) && ! empty( $parsed_url['port'] ) ) {
		return $parsed_url['host'] . ':' . $parsed_url['port'];
	} elseif( ! empty( $parsed_url['host'] ) ) {
		return $parsed_url['host'];
	}
	return '';
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
	if ( empty( $_REQUEST['state'] ) ) {
		return false;
	}

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
	global $pmpro_pages;

	if ( is_admin() ) {
		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'pmpro-member' ) {
			// We are on the Member Edit page. We need to pass the panel slug to save properly.
			$redirect_uri = add_query_arg(
				array(
					'page'                    => 'pmpro-member',
					'user_id'                 => empty( $_REQUEST['user_id'] ) ? '' : intval( $_REQUEST['user_id'] ),
					'pmpro_member_edit_panel' => 'pmproup',
				),
				admin_url( 'admin.php' )
			);
		} else {
			$redirect_uri = admin_url( basename( $_SERVER['REQUEST_URI'] ) );
		}
	} elseif( pmpro_is_checkout() ) {
		// Get the checkout level.
		$checkout_level = pmpro_getLevelAtCheckout();
		if ( ! empty( $checkout_level ) ) {
			$redirect_uri = add_query_arg( 'level', intval( $checkout_level->id ), get_permalink( $pmpro_pages['checkout'] ) );
		} else {
			$redirect_uri = get_permalink( $pmpro_pages['checkout'] );
		}
	} else {
		$redirect_uri = get_permalink();
	}

	switch ( $state ) {
		case 'login':
			$button_text = esc_html__( 'Log In with Crypto Wallet', 'pmpro-unlock' );
			break;

		default:
			$button_text = esc_html__( 'Connect Your Crypto Wallet', 'pmpro-unlock' );
			break;
	}
	
    $url = pmproup_get_login_url( esc_url( $redirect_uri ) );
?>
    <div class='pmproup-protocol-login-container' style="margin-bottom:20px;">
        <a href="<?php echo esc_url( $url ); ?>" rel="nofollow" class="pmproup-protocol-connect-button" style="background-color: black;color:white;padding:1em;"><?php echo esc_html( $button_text ); ?></a>
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
		   pmpro_unset_session_var( 'pmproup_code' );
        } elseif ( is_wp_error( $wallet ) ) {
			pmpro_unset_session_var( 'pmproup_code' );
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
	if ( ! function_exists( 'pmpro_unset_session_var' ) ) {
		return '';
	}

	$code = '';

	// Let's try to overwrite any session data with REQUEST param stuff.
	if ( isset( $_REQUEST['code' ] ) ) {
		pmpro_unset_session_var( 'pmproup_code' ); // Let's try unset any SESSION data we might have.
		$code = sanitize_text_field( $_REQUEST['code'] );
		pmpro_set_session_var( 'pmproup_code', $code );
	}

	// try get it from the session.
	if ( empty( $code ) ) {
		$code = pmpro_get_session_var( 'pmproup_code' );
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

	$has_level = pmproup_should_have_access( $user_id, $levels );

	return $has_level;
}
add_filter( 'pmpro_has_membership_level', 'pmproup_has_membership_level', 10, 3 );

/**
 * Filter access.
 */
function pmproup_pmpro_has_membership_access_filter( $hasaccess, $post, $user, $post_levels ) {
	// Bail if member has no access.
	if ( ! $hasaccess ) {
		return $hasaccess;
	}

	if ( empty ( $post_levels ) ) {
		return $hasaccess;
	}

	// Check if the user should have access to the item.
	$hasaccess = pmproup_should_have_access( $user->ID, wp_list_pluck( $post_levels, 'id' ) );


	return $hasaccess;
}
add_filter( 'pmpro_has_membership_access_filter', 'pmproup_pmpro_has_membership_access_filter', 10, 4 );

/**
 * Undocumented function
 *
 * @param string $network The Network RCP Endpoint.
 * @param string $lock The lock's address (contract address).
 * @param string $wallet The user's crypto wallet address.
 * @return bool $has_lock_access Check Unlock Protocols network to ensure the wallet address has access to a lock.
 */
function pmproup_has_lock_access( $network, $lock, $wallet ) {

	$network = sanitize_url( $network );
	$lock = sanitize_text_field( $lock );
	$wallet = sanitize_text_field( $wallet );
	$has_lock_access = false;

	// Last 8 digits of the lock and wallet for the transient, for reference.
	$ref_lock = substr( $lock, -8 );
	$ref_wallet = substr( $wallet, -8 );

	$pmproup_transient_name = 'pmproup_has_lock_' . $ref_lock . '_' . $ref_wallet;
	$transient_expiration = apply_filters( 'pmproup_has_lock_access_transient_expiration', 2 * HOUR_IN_SECONDS ); // 2 hours.

	// Check if the transient is available, if not try to get lock access and cache the results.
	if ( empty( get_transient( $pmproup_transient_name ) ) ) {
	
		$check_lock = pmproup_validate_lock( $network, $lock, $wallet );

		if ( ! is_wp_error( $check_lock ) && hexdec( $check_lock['result'] ) == 1 ) {
			set_transient( $pmproup_transient_name, true, $transient_expiration ); 
			$has_lock_access = true;
		} else {
			set_transient( $pmproup_transient_name, false, $transient_expiration ); 
			$has_lock_access = false;
		}

	} else {
		// Get the lock access from the transient which will be either true or false.
		$has_lock_access = get_transient( $pmproup_transient_name );
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

/**
 * For users who purchased a membership using an NFT, verify they still have access to the NFT when
 * trying to view restricted content.
 * 
 * @since 0.1
 * 
 * @param int $user_id The user's ID to check wallet and lock access.
 * @param int|array[int] $levels The level ID or array of level IDs to check.
 *
 * @return bool $hasaccess Returns true or false based on whether the user has access or not.
 */
function pmproup_should_have_access( $user_id, $levels ) {

	// If multiple levels are passed in, check each one individually and return if any have access.
	if ( is_array( $levels ) ) {
		foreach ( $levels as $level) {
			if ( pmproup_should_have_access( $user_id, $level ) ) {
				return true;
			}
		}
	}

	// If the level ID is not positive, then we are checking if a user does not have a level.
	// We should always return true here.
	if ( (int) $levels <= 0 ) {
		return true;
	}

	// We have a real level to check for. Let's first get a list of all levels for the user.
	$user_levels = pmpro_getMembershipLevelsForUser( $user_id );

	// And then let's pull the level IDs to simplify our checks.
	$user_level_ids = wp_list_pluck( $user_levels, 'id' );

	// If the user does not have the level, then we should return false.
	if ( ! in_array( $levels, $user_level_ids ) ) {
		return false;
	}

	// The user does have the level. Let's check if they purchased it with a NFT.
	if ( empty( get_user_meta( $user_id, 'pmproup_claimed_nft_' . $levels, true ) ) ) {
		// If the user did not purchase the level with a NFT, we don't need to check if they still have it.
		return true;
	}

	// Get the user's wallet so that we can see if they still have the NFT for this level.
	$wallet = pmproup_try_to_get_wallet( $user_id );
	
	// If no wallet is found, then we can't confirm that they still have the NFT.
	if ( empty( $wallet ) ) {
		return false;
	}

	// We have a wallet. Let's get the lock address for this level and check if the user has access.
	$level_lock_options = get_option( 'pmproup_' . $levels, true );
	return pmproup_has_lock_access( $level_lock_options['network_rpc'], $level_lock_options['lock_address'], $wallet );
}

/**
 * Function to clear transients.
 *
 * @param string $lock The NFT Lock address.
 * @param string $wallet The user's wallet address.
 */
function pmproup_clear_transients( $lock, $wallet ) {
	// Last 8 digits of the lock and wallet for the transient, for reference.
	$ref_lock = substr( $lock, -8 );
	$ref_wallet = substr( $wallet, -8 );

	$pmproup_transient_name = 'pmproup_has_lock_' . $ref_lock . '_' . $ref_wallet;

	delete_transient( $pmproup_transient_name );
}