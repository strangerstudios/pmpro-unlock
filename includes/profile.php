<?php
/**
 * Profile page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Functionality for user profile page and connected/removing wallet address.
 * 
 * @param object $user User object.
 *  @since 1.0
 */
function pmpro_unlock_profile_connect_wallet( $user ) {

    // Let's try to check and save it on page load.
    $wallet_address = empty( $user->ID ) ? pmpro_unlock_check_save_wallet() : pmpro_unlock_check_save_wallet( $user->ID );

    if ( empty( $wallet_address ) || is_wp_error( $wallet_address ) ) {
        pmpro_unlock_connect_wallet_button(); //Show the button, let's try to save it.
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
add_action( 'pmpro_show_user_profile', 'pmpro_unlock_profile_connect_wallet', 10, 1 );

/**
 * Delete wallet address if remove option is selected.
 * 
 * @since 1.0
 */
function pmpro_unlock_profile_remove_wallet( $user_id = 0 ) {
    if ( empty( $_REQUEST['pmpro_unlock_delete_wallet'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Runs inside the profile save (profile_update / Member Edit panel save), after the form's nonce is verified; capability is checked below.
        return;
    }

    $user_id = intval( $user_id );
    if ( $user_id <= 0 ) {
        return;
    }

    // Only the user themselves or someone who may edit them can unlink the wallet.
    if ( get_current_user_id() !== $user_id && ! current_user_can( 'edit_user', $user_id ) ) {
        return;
    }

    delete_user_meta( $user_id, 'pmproup_wallet' );
}
add_action( 'profile_update', 'pmpro_unlock_profile_remove_wallet', 10, 1 );

/**
 * Add a panel to the Edit Member dashboard page.
 *
 * @since 1.2
 *
 * @param array $panels Array of panels.
 * @return array
 */
function pmpro_unlock_pmpro_member_edit_panels( $panels ) {
	// If the class doesn't exist and the abstract class does, require the class.
	if ( ! class_exists( 'PMPro_Unlock_Member_Edit_Panel' ) && class_exists( 'PMPro_Member_Edit_Panel' ) ) {
		require_once( PMPRO_UNLOCK_DIR . '/classes/class-pmpro-unlock-member-edit-panel.php' );
	}

	// If the class exists, add a panel.
	if ( class_exists( 'PMPro_Unlock_Member_Edit_Panel' ) ) {
		$panels[] = new PMPro_Unlock_Member_Edit_Panel();
	}

	return $panels;
}

/**
 * Hook the correct function for admins editing a member's profile.
 *
 * @since 1.2
 */
function pmpro_unlock_hook_edit_member_profile() {
	// If the `pmpro_member_edit_get_panels()` function exists, add a panel.
	// Otherwise, use the legacy hook.
	if ( function_exists( 'pmpro_member_edit_get_panels' ) ) {
		add_filter( 'pmpro_member_edit_panels', 'pmpro_unlock_pmpro_member_edit_panels' );
	} else {
		add_action( 'pmpro_after_membership_level_profile_fields', 'pmpro_unlock_profile_connect_wallet', 10, 1 );
		add_action( 'profile_update', 'pmpro_unlock_profile_remove_wallet' );
	}
}
add_action( 'admin_init', 'pmpro_unlock_hook_edit_member_profile', 0 );
