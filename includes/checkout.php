<?php
// All functions relating to checkout.

/**
 * Adds functionality to level page to show connect wallet button.
 */
function pmpro_up_add_wallet_to_checkout() {
    global $pmpro_level;
    $level_id = $pmpro_level->id;

    $wallet = pmpro_up_check_save_wallet();
    // Check if we have a wallet ID or not.
    if ( ! $wallet ) {
        echo pmpro_up_connect_wallet_button();

        // Check codes and stuff and save to SESSION?
    } else {
        echo "We've got a wallet address" . $wallet; /// Remove this later.

        $level_lock_options = get_option( 'pmpro_unlock_protocol_' . $level_id, true );
        // Let's check the validate lock status.
        $check_lock = pmpro_up_validate_lock( $level_lock_options['network'], $level_lock_options['lock_address'], $wallet );
    
        // There was an error, don't adjust the level.
        if ( is_wp_error( $check_lock ) || ! isset( $check_lock['result'] ) ) {
            echo "There was a problem checking the lock right now";
            return;
        }

        $okay = hexdec( $check_lock['result'] ) == 1;

        if ( $okay ) {
            echo pmpro_setMessage( 'ALL GOOD', 'pmpro_success'); ///Change this later on.
        } else { ///Look at this too.
            $redirect_uri = get_permalink() . '?level=' . $level_id;
            $checkout_url = pmpro_up_get_checkout_url( $level_lock_options['lock_address'], $redirect_uri );
            echo "You can purchase this NFT <a href='" . $checkout_url . "'>Click here to buy the NFT</a>"; /// Build Checkout URL here, maybe check level options if NFT is totally required.
        }
    }

}
add_action( 'pmpro_checkout_after_pricing_fields', 'pmpro_up_add_wallet_to_checkout' );

/**
 * Save/update wallet after level's changed. If no $code parameter is found it will just retrieve their wallet. ///Todo, make this smarter when we call it.
 *
 */
function pmpro_up_save_wallet_after_level_change( $level_id, $user_id, $cancel_level ) {
    // Try to save user's wallet after they're given a level.
    pmpro_up_check_save_wallet( $user_id );
}
add_action( 'pmpro_after_change_membership_level', 'pmpro_up_save_wallet_after_level_change', 10, 3 );

/// Important Function
/**
 * Bypass level pricing if they have an NFT.
 *
 * @param object $checkout_level The membership level the user is about to purchase.
 */
function pmpro_up_checkout_level( $checkout_level ) {
    $level_id = $checkout_level->id;

    $level_lock_options = get_option( 'pmpro_unlock_protocol_' . $level_id, true );

    // Level doesn't have any Unlock Protocol Settings, just bail.
    if ( ! $level_lock_options || ! is_array( $level_lock_options ) ) {
        return $checkout_level;
    }

    // Figure out how to get the wallet address.
    if ( is_user_logged_in() ) {
        global $current_user;
        $wallet = pmpro_up_check_save_wallet( $current_user->ID, '' );
    } elseif ( isset( $_REQUEST['code'] ) ) {
        $wallet = pmpro_up_check_save_wallet( '', sanitize_text_field( $_REQUEST['code'] ) ); // Check the code value.
    } else {
        return $checkout_level; /// Unable to authenticate wallet whatsoever - just bail.
    }

    /// EXTRACT THIS functionality.
    // Let's see if they have access to the lock now.
    $check_lock = pmpro_up_validate_lock( $level_lock_options['network'], $level_lock_options['lock_address'], $wallet );
    
    // There was an error, don't adjust the level.
    if ( is_wp_error( $check_lock ) || ! isset( $check_lock['result'] ) ) {
        return $checkout_level;
    }

    $okay = hexdec( $check_lock['result'] ) == 1;

    if ( $okay ) {
        $checkout_level->initial_payment = '0';
        $checkout_level->billing_amount = '0';
        $checkout_level->cycle_number = '0';
        $checkout_level->cycle_period = '';
        $checkout_level->billing_limit = '0';
        $checkout_level->trial_amount = '0';
        $checkout_level->trial_limit = '0';
    }

    return $checkout_level;

}
add_filter( 'pmpro_checkout_level', 'pmpro_up_checkout_level', 10, 1 );

/**
 * Check if user has relevant lock access or not during checkout and based on settings.
 *
 * @param bool $continue Variable to continue or stop registration for checkout.
 * @return bool $continue Continue with Paid Memberships Pro checkout or not - based on NFT Status.
 */
function pmpro_up_registration_checks( $continue ) {
    global $pmpro_level;

    $level_id = $pmpro_level->id;

    if ( ! $continue ) {
        return $continue;
    }

    $level_lock_options = get_option( 'pmpro_unlock_protocol_' . $level_id, true );

    // Level doesn't require NFT, network not selected, just bail quietly.
    if ( empty( $level_lock_options ) || $level_lock_options['network'] === '' || $level_lock_options['nft_required'] === 'No' ) {
        return $continue;
    }

    $wallet = pmpro_up_try_to_get_wallet();
    
    // Let's see if they have access to the lock now.
    $check_lock = pmpro_up_validate_lock( $level_lock_options['network'], $level_lock_options['lock_address'], $wallet );
    if ( is_wp_error( $check_lock ) || ! isset( $check_lock['result'] ) ) {
        $continue = false;
    }

    $continue = hexdec( $check_lock['result'] ) == 1;

    if ( ! $continue ) {
        pmpro_setMessage( 'You need an NFT bro!', 'pmpro_error' );
    }

    return $continue;
}
add_filter( 'pmpro_registration_checks', 'pmpro_up_registration_checks' );