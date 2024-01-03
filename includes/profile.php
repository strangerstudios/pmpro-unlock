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
    $wallet_address = empty( $user->ID ) ? pmproup_check_save_wallet() : pmproup_check_save_wallet( $user->ID );

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

/**
 * Delete wallet address if remove option is selected.
 * 
 * @since 1.0
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

/**
 * Add a panel to the Edit Member dashboard page.
 *
 * @since 1.2
 *
 * @param array $panels Array of panels.
 * @return array
 */
function pmproup_pmpro_member_edit_panels( $panels ) {
	// If the class doesn't exist and the abstract class does, require the class.
	if ( ! class_exists( 'PMProup_Member_Edit_Panel' ) && class_exists( 'PMPro_Member_Edit_Panel' ) ) {
		require_once( PMPROUP_DIR . '/classes/pmproup-class-member-edit-panel.php' );
	}

	// If the class exists, add a panel.
	if ( class_exists( 'PMProup_Member_Edit_Panel' ) ) {
		$panels[] = new PMProup_Member_Edit_Panel();
	}

	return $panels;
}

/**
 * Hook the correct function for admins editing a member's profile.
 *
 * @since 1.2
 */
function pmproup_hook_edit_member_profile() {
	// If the `pmpro_member_edit_get_panels()` function exists, add a panel.
	// Otherwise, use the legacy hook.
	if ( function_exists( 'pmpro_member_edit_get_panels' ) ) {
		add_filter( 'pmpro_member_edit_panels', 'pmproup_pmpro_member_edit_panels' );
	} else {
		add_action( 'pmpro_after_membership_level_profile_fields', 'pmproup_profile_connect_wallet', 10, 1 );
		add_action( 'profile_update', 'pmproup_profile_remove_wallet' );
	}
}
add_action( 'admin_init', 'pmproup_hook_edit_member_profile', 0 );
