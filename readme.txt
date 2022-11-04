=== Paid Memberships Pro - Unlock Protocol Integration ===
Contributors: strangerstudios, paidmembershipspro
Tags: nft, pmpro-unlock, crypto, nft-membership
Requires at least: 5.0
Tested up to: 6.1
Requires PHP: 7.2
Stable tag: 0.1

Allow people to purchase an NFT during checkout or claim a Paid Memberships Pro membership should they already hold a valid NFT.

== Description ==

### Offer NFT memberships with Unlock Protocol and Paid Memberships Pro
Allow NFT holders to claim a Paid Memberships Pro level for free. As long as the member has a valid NFT, they may retain access until their membership expiration date or transferred away their NFT to another wallet.

### Log in to WordPress using your crypto wallet
Once a wallet address is linked to the user's WordPress account, they may login with a Single Signon via their crypto wallet and Unlock Protocol. This is only available on the default WordPress login screen.

== Installation ==
Note: You must have [Paid Memberships Pro](https://wordpress.org/plugins/paid-memberships-pro/) installed and activated on your site and an account with [Unlock Protocol](https://unlock-protocol.com).

### Install PMPro Unlock Protocol from within WordPress

1. Visit the plugins page within your dashboard and select "Add New"
1. Search for "PMPro Unlock Protocol"
1. Locate this plugin and click "Install"
1. Activate "Paid Memberships Pro - Unlock Protocol Integration" through the "Plugins" menu in WordPress
1. Go to "after activation" below.

### Install PMPro Unlock Protocol Manually

1. Upload the `pmpro-unlock` folder to the `/wp-content/plugins/` directory
1. Activate "Paid Memberships Pro - Unlock Protocol Integration" through the "Plugins" menu in WordPress
1. Go to "after activation" below.

### After Activation: Configure Plugin Settings
Inside each membership level setting, under "Other Settings", you may configure a link between an NFT address for that particular memebership level.
1. Choose a Network - The network of your crypto currency that is used for the NFT.
1. Lock Address - The NFT address that is used to validate if the crypto wallet has this NFT.
1. Require NFT to checkout - This option may be set if you would like to only allow NFT holders to claim the level, or let non-NFT holders purchase the level using a regular payment method.

== Screenshots ==
1. Checkout screen with the option to purchase the NFT
2. Allow valid NFT holders to claim the membership level for free
3. Membership level settings to link an NFT to a membership level

== Changelog ==
= 0.1 -TBD =
* Initial Release