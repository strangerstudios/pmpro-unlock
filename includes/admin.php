<?php
/**
 * Runs only when the plugin is activated.
 *
 * @since 0.1.0
 */
function pmproup_admin_notice_activation_hook() {
	// Create transient data.
	set_transient( 'pmproup-admin-notice', true, 5 );
}
register_activation_hook( PMPROUP_BASENAME, 'pmproup_admin_notice_activation_hook' );

/**
 * Admin Notice on Activation.
 *
 * @since 0.1.0
 */
function pmproup_admin_notice() {
	// Check transient, if available display notice.
	if ( get_transient( 'pmproup-admin-notice' ) ) { ?>
		<div class="updated notice is-dismissible">
			<p>
			<?php 
				esc_html_e( 'Thank you for activating the Unlock Protocol Add On.', 'pmpro-unlock' );
				if ( function_exists( 'pmpro_url' ) ) {
                    echo ' <a href="' . esc_url( get_admin_url( null, 'admin.php?page=pmpro-membershiplevels' ) ) . '">';
				    esc_html_e( 'Edit a level to set up Unlock.', 'pmpro-unlock' );
                    echo '</a>';
                }
			?>
			</p>
		</div>
		<?php
		// Delete transient, only display this notice once.
		delete_transient( 'pmproup-admin-notice' );
	}
}
add_action( 'admin_notices', 'pmproup_admin_notice' );

/**
 * Function to add links to the plugin action links
 *
 * @param array $links Array of links to be shown in plugin action links.
 */
function pmproup_plugin_action_links( $links ) {
	if ( current_user_can( 'manage_options' ) && function_exists( 'pmpro_url' ) ) {
		$new_links = array(
			'<a href="' . esc_url( get_admin_url( null, 'admin.php?page=pmpro-membershiplevels' ) ) . '">' . esc_html__( 'Level Settings', 'pmpro-unlock' ) . '</a>',
		);

		$links = array_merge( $new_links, $links );
	}
	return $links;
}
add_filter( 'plugin_action_links_' . PMPROUP_BASENAME, 'pmproup_plugin_action_links' );

/**
 * Function to add links to the plugin row meta
 *
 * @param array  $links Array of links to be shown in plugin meta.
 * @param string $file Filename of the plugin meta is being shown for.
 */
function pmproup_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'pmpro-unlock.php' ) !== false ) {
		$new_links = array(
			'<a href="' . esc_url( 'https://www.paidmembershipspro.com/add-ons/unlock-protocol-integration/' ) . '" title="' . esc_attr__( 'View Documentation', 'pmpro-unlock' ) . '">' . esc_html__( 'Docs', 'pmpro-unlock' ) . '</a>',
			'<a href="' . esc_url( 'https://www.paidmembershipspro.com/support/' ) . '" title="' . esc_attr__( 'Visit Customer Support Forum', 'pmpro-unlock' ) . '">' . esc_html__( 'Support', 'pmpro-unlock' ) . '</a>',
		);
		$links = array_merge( $links, $new_links );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'pmproup_plugin_row_meta', 10, 2 );