<?php
/**
 * Profile page.
 */

/**
 * Functionality for user profile page and connected/removing wallet address.
 * 
 * @param object $user User object.
 *  @since 1.0
 */
function pmproup_profile_connect_wallet( $user ) {

    // Let's try to check and save it on page load.
    $wallet_address = pmproup_check_save_wallet();

    if ( empty( $wallet_address ) || is_wp_error( $wallet_address ) ) {
        pmproup_connect_wallet_button(); //Show the button, let's try to save it.
    }

    if ( ! is_wp_error( $wallet_address ) && ! empty( $wallet_address ) ) {
        echo "<p>" . sprintf( esc_html__( "Your Wallet Address is: %s", 'pmpro-unlock' ), '<span id="pmpro_unlock_wallet_address">' . esc_html( $wallet_address ) . '</span>' ) . ' ';
        echo '<a style="color: red;" id="pmpro_unlock_remove_wallet" class="pmpro_unlock_remove" href="javascript:void(0);">' . esc_html__( 'remove', 'pmpro-addon-packages' ) . '</a>';
        echo '<input type="hidden" id="pmpro_unlock_delete_wallet" name="pmpro_unlock_delete_wallet" value="" />';
        echo "</p>";

        ?>
        <script>
            jQuery(document).ready(function(){
                jQuery('#pmpro_unlock_remove_wallet').on('click', function(){
                    jQuery('#pmpro_unlock_remove_wallet').css('text-decoration', 'line-through');
                    jQuery('#pmpro_unlock_wallet_address').css('text-decoration', 'line-through');

                    jQuery('#pmpro_unlock_delete_wallet').val('1');
                });
            });
        </script>
        <?php
    }
}
add_action( 'pmpro_show_user_profile', 'pmproup_profile_connect_wallet', 10, 1 );
add_action( 'pmpro_after_membership_level_profile_fields', 'pmproup_profile_connect_wallet', 10, 1 );

/**
 * Delete wallet address if remove option is selected.
 * 
 * @since TBD
 */
function pmproup_profile_remove_wallet() {
    if ( isset( $_REQUEST['pmpro_unlock_delete_wallet'] ) && ! empty( $_REQUEST['pmpro_unlock_delete_wallet'] ) ) {

        if ( ! empty( $_REQUEST['user_id'] ) ) {
			$user_id = intval( $_REQUEST['user_id'] );
		}

        delete_user_meta( $user_id, 'pmproup_wallet' );
    }

}
add_action( 'profile_update', 'pmproup_profile_remove_wallet' );
