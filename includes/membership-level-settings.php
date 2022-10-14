<?php
/**
 * Code to add settings to the edit membership level page and save those settings.
*/

/**
 * Display Settings for Unlock Protocol Integration.
 */
function pmproup_level_settings() {

    // Unlock Protocol settings.
    $networks = pmproup_networks_list();
    array_unshift( $networks, array( 'network_name' => '' ) ); // Insert first option as "-" to array.
    
    if ( isset( $_REQUEST['edit'] ) ) {
		$level_id = intval( $_REQUEST['edit'] );
    }

    // Get level settings and configure variables to be used.
    $pmproup_settings = get_option( 'pmproup_' . $level_id );
    if ( is_array( $pmproup_settings ) ) {
        $network_value = $pmproup_settings['network_name'];
        $lock_address_value = $pmproup_settings['lock_address'];
        $nft_required = $pmproup_settings['nft_required'];
    } else {
        $network_value = '';
        $lock_address_value = '';
        $nft_required = 'No';
    }

    ?>
    <hr/>
    <h2><?php esc_html_e( 'Unlock Protocol Settings', 'pmpro-unlock' ); ?></h2>
    <table class="form-table">
        <tbody>
            <tr>
                <p>Configure settings below if an NFT is required to purchase this membership level. If a valid NFT is detected for the member, they will be able to claim this membership for free.</p>
                <th scope="row" valign="top">   
                    <label for="pmproup-network"><?php esc_html_e( 'Choose a Network:', 'pmpro-unlock' ); ?></label>
                </th>   
                <td>
                    <select name="pmproup-network" id="pmproup-network">
                    <?php
                        foreach ( $networks as $network ) {
                            echo "<option value='" . esc_attr( $network['network_name'] ) . "' " . selected( $network_value, $network['network_name'], false ) . ">" . esc_html( $network['network_name'] ) . "</option>";
                        }
                    ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><?php esc_html_e( 'Lock Address:', 'pmpro-unlock' ); ?></th>
                <td>
                    <input type="text" name="pmproup-lock" id="pmproup-lock" class="regular-text" value="<?php echo esc_attr( $lock_address_value ); ?>"/>
                    <p class="description"><a href="https://app.unlock-protocol.com/dashboard" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Deploy a lock', 'pmpro-unlock' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><?php esc_html_e( 'Require NFT to checkout?', 'pmpro-unlock' ); ?></th>
                <td>
                    <select name="pmproup-nft-required" id="pmproup-nft-required">
                        <option value="No" <?php selected( $nft_required, "No", true ); ?>>No. People who don't have an NFT may checkout for this level.</option>
                        <option value="Yes" <?php selected( $nft_required, "Yes", true ); ?> >Yes. People are required to have an NFT to checkout for this level.</option>
                    </select>
                </td>
        </tbody>
    </table>
    <?php
}
add_action( 'pmpro_membership_level_after_other_settings', 'pmproup_level_settings' );

/**
 * Save settings for Unlock Protocol.
 */
function pmproup_save_membership_level( $level_id ) {
    
    if ( $level_id <= 0 ) {
		return;
	}

    $available_networks = pmproup_networks_list();
    $network = sanitize_text_field( $_REQUEST['pmproup-network'] );
    $lock_address = sanitize_text_field( $_REQUEST['pmproup-lock' ] );
    $nft_required = sanitize_text_field( $_REQUEST['pmproup-nft-required'] );

    // Save the entire network details for this lock.
    $pmproup_settings = array( 'network_name' => $network, 'lock_address' => $lock_address, 'nft_required' => $nft_required );

    // Make sure the network settings actually exist and add them to the array.
    if ( ! empty( $network ) && isset( $available_networks[$network] ) ) {
        $pmproup_settings['network_rpc'] = $available_networks[$network]['network_rpc_endpoint'];
        $pmproup_settings['network_id'] = $available_networks[$network]['network_id'];
    }

    // Save or delete options during level save.
    if ( $network ) {
        update_option( 'pmproup_' . $level_id, $pmproup_settings, 'no' );
    } else {
        delete_option( 'pmproup_' . $level_id );
    }

}
add_action( 'pmpro_save_membership_level', 'pmproup_save_membership_level', 10, 1 );
