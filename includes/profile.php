<?php
/**
 * Profile page.
 */

function pmproup_profile_connect_wallet( $user ) {

    // Let's try to check and save it on page load.
    $wallet_address = pmproup_check_save_wallet();

    if ( empty( $wallet_address ) || is_wp_error( $wallet_address ) ) {
        pmproup_connect_wallet_button(); //Show the button, let's try to save it.
    }

    if ( ! is_wp_error( $wallet_address ) && ! empty( $wallet_address ) ) {
        echo "<p>Your Wallet Address is: " . esc_html( $wallet_address ) . "</p>";
    }
}
add_action( 'pmpro_show_user_profile', 'pmproup_profile_connect_wallet', 10, 1 );
add_action( 'pmpro_after_membership_level_profile_fields', 'pmproup_profile_connect_wallet', 10, 1 );
