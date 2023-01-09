<?php
// All functions relating to checkout.

/**
 * Adds functionality to level page to show connect wallet button.
 */
function pmproup_add_wallet_to_checkout() {
    global $pmpro_level;
    $level_id = $pmpro_level->id;

    $wallet = pmproup_check_save_wallet(); // Get and save the wallet possibly.

    // Only show this if there are level lock options

    $level_lock_options = get_option( 'pmproup_' . $level_id );
    if ( empty( $level_lock_options ) ) {
        return;
    }


    // Check if we have a wallet ID or not.
    if ( is_wp_error( $wallet ) || ! $wallet ) {
        pmproup_connect_wallet_button();
    } else {
        
        // Let's check the validate lock status.
        $check_lock = pmproup_has_lock_access( $level_lock_options['network_rpc'], $level_lock_options['lock_address'], $wallet );

        if ( $check_lock ) {
            pmpro_setMessage( esc_html__( 'You hold a valid lock, you may claim this membership level for free.', 'pmpro-unlock' ), 'pmpro_success'); ///Change this later on.
        } else {
            $redirect_uri = get_permalink() . '?level=' . $level_id;
            $checkout_url = pmproup_get_checkout_url( $level_lock_options, $redirect_uri );
            echo esc_html__( 'You can purchase this NFT.', 'pmpro-unlock' ) .  '<a href="' . esc_url( $checkout_url ) . '">' . esc_html__( 'Click here to buy the NFT.', 'pmpro-unlock' ) . '</a>'; 
        }
    }
}
add_action( 'pmpro_checkout_after_pricing_fields', 'pmproup_add_wallet_to_checkout' );

/**
 * Save/update wallet after level's changed. If no $code parameter is found it will just retrieve their wallet. ///Todo, make this smarter when we call it.
 *
 */
function pmproup_save_wallet_after_level_change( $level_id, $user_id, $cancel_level ) {
    // Try to save user's wallet after they're given a level.
    pmproup_check_save_wallet( $user_id );
    pmpro_unset_session_var( 'pmproup_code' ); // Remove any session VAR that may be there.
}
add_action( 'pmpro_after_change_membership_level', 'pmproup_save_wallet_after_level_change', 10, 3 );

/**
 * Bypass level pricing if they have an NFT.
 *
 * @param object $checkout_level The membership level the user is about to purchase.
 */
function pmproup_checkout_level( $checkout_level ) {
    $level_id = $checkout_level->id;

    $level_lock_options = get_option( 'pmproup_' . $level_id, true );

    // Level doesn't have any Unlock Protocol Settings, just bail.
    if ( ! $level_lock_options || ! is_array( $level_lock_options ) ) {
        return $checkout_level;
    }

    $wallet = pmproup_try_to_get_wallet();
    // Figure out how to get the wallet address.
    if ( is_wp_error( $wallet ) || ! $wallet ) {
        return $checkout_level; /// Unable to authenticate wallet whatsoever - just bail.
    }
       
    // Let's see if they have access to the lock now.

    if ( ! pmproup_has_lock_access( $level_lock_options['network_rpc'], $level_lock_options['lock_address'], $wallet ) ) {
        return $checkout_level;
    }

    // Make sure this NFT is not already used.
    // TODO: Get an actual unique ID for the NFT to save and uncomment.
    /*
    $unique_lock_id = '1';
    $query_args = array(
        'meta_key' => 'pmproup_claimed_nft_' . $level_id,
        'meta_value' => $unique_lock_id,
    );
    $query = new WP_User_Query( $query_args );
    if ( $query->get_total() > 0 ) {
        // NFT is already used.
        return $checkout_level;
    }
    */

    // User has access to the lock, let's set the price to 0.
    $checkout_level->initial_payment = '0';
    $checkout_level->billing_amount = '0';
    $checkout_level->cycle_number = '0';
    $checkout_level->cycle_period = '';
    $checkout_level->billing_limit = '0';
    $checkout_level->trial_amount = '0';
    $checkout_level->trial_limit = '0';

    return $checkout_level;

}
add_filter( 'pmpro_checkout_level', 'pmproup_checkout_level', 10, 1 );

/**
 * Check if user has relevant lock access or not during checkout and based on settings.
 *
 * @param bool $continue Variable to continue or stop registration for checkout.
 * @return bool $continue Continue with Paid Memberships Pro checkout or not - based on NFT Status.
 */
function pmproup_registration_checks( $continue ) {
    global $pmpro_level;

    $level_id = $pmpro_level->id;

    if ( ! $continue ) {
        return $continue;
    }

    $level_lock_options = get_option( 'pmproup_' . $level_id, true );

    // Level doesn't require NFT, network not selected, just bail quietly.
    if ( empty( $level_lock_options ) || ! is_array( $level_lock_options ) || $level_lock_options['network_rpc'] === '' || $level_lock_options['nft_required'] === 'No' ) {
        return $continue;
    }

    $wallet = pmproup_try_to_get_wallet();
    
    // Let's see if they have access to the lock now.
    $continue = pmproup_has_lock_access( $level_lock_options['network_rpc'], $level_lock_options['lock_address'], $wallet );
    
    if ( ! $continue ) {
        pmpro_setMessage( esc_html__( 'You need an NFT to claim this membership', 'pmpro-unlock' ), 'pmpro_error' ); // Change this.
    } else {
        pmproup_clear_transients( $level_lock_options['lock_address'], $wallet );
    }

    return $continue;
}
add_filter( 'pmpro_registration_checks', 'pmproup_registration_checks' );

/**
 * After checkout, if an NFT was used, save the NFT ID to the user's metadata.
 *
 * @param int         $user_id The user ID.
 * @param MemberOrder $morder The order object.
 */
function pmproup_after_checkout( $user_id, $morder ) {
    // Get the level that was purchased.
    $level_id = $morder->membership_id;

    // Get the level's NFT settings.
    $level_lock_options = get_option( 'pmproup_' . $level_id, true );

    // If the level doesn't have any NFT settings, bail.
    if ( empty( $level_lock_options ) || ! is_array( $level_lock_options ) || empty( $level_lock_options['network_rpc'] ) || empty( $level_lock_options['lock_address'] ) ) {
        return;
    }

    // Get the user's wallet.
    $wallet = pmproup_try_to_get_wallet();

    // If user doesn't have a wallet, bail.
    if ( is_wp_error( $wallet ) || ! $wallet ) {
        return;
    }

    // Check if the user has access to the lock for this level.
    if ( pmproup_has_lock_access( $level_lock_options['network_rpc'], $level_lock_options['lock_address'], $wallet ) ) {
        // Save the ID for the NFT used to unlock this level in user meta.
        // TODO: Get an actual unique ID for the NFT to save.
        $unique_lock_id = '1';
        update_user_meta( $user_id, 'pmproup_claimed_nft_' . $level_id, $unique_lock_id );

        pmproup_clear_transients( $level_lock_options['lock_address'], $wallet );
    }
}

/**
 * After a membership level is cancelled, remove the NFT ID from the user's meta.
 *
 * @param int $level_id The level ID.
 * @param int $user_id The user ID.
 * @param int $cancel_level_id The level ID that was cancelled.
 */
function pmproup_after_cancel_membership_level( $level_id, $user_id, $cancel_level_id ) {
    // Remove the NFT ID from the user's meta.
    delete_user_meta( $user_id, 'pmproup_claimed_nft_' . $cancel_level_id );
}
add_action( 'pmpro_after_change_membership_level', 'pmproup_after_cancel_membership_level', 10, 3 );