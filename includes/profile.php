<?php
/**
 * Profile page.
 */

function pmpro_up_profile_connect_wallet( $user ) {

    // Let's try to check and save it on page load.
    $wallet_address = pmpro_up_check_save_wallet();

    if ( empty( $wallet_address ) || is_wp_error( $wallet_address ) ) {
        pmpro_up_connect_wallet_button(); //Show the button, let's try to save it.
    }

    if ( ! is_wp_error( $wallet_address ) && ! empty( $wallet_address ) ) {
        echo "<p>Your Wallet Address is: " . $wallet_address . "</p>";
    }
}
add_action( 'edit_user_profile', 'pmpro_up_profile_connect_wallet', 10, 1 );
add_action( 'pmpro_show_user_profile', 'pmpro_up_profile_connect_wallet', 10, 1 );
