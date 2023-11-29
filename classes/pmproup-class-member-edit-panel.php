<?php

class PMProup_Member_Edit_Panel extends PMPro_Member_Edit_Panel {
	/**
	 * Set up the panel.
	 */
	public function __construct() {
		$this->slug        = 'pmproup';
		$this->title       = __( 'Unlock Protocol Integration', 'pmpro-unlock' );
        $this->submit_text = __( 'Save', 'pmpro-unlock' );
	}

	/**
	 * Display the panel contents.
	 */
	protected function display_panel_contents() {
		pmproup_profile_connect_wallet( self::get_user() );
	}

    /**
     * Remove the wallet address if the remove option is selected.
     */
    public function save() {
        pmproup_profile_remove_wallet();
    }
}
