=== Paid Memberships Pro - Unlock Protocol Integration ===
Contributors: strangerstudios, paidmembershipspro
Tags: nft, pmpro-unlock, crypto, nft-membership
Requires at least: 5.0
Tested up to: 6.1
Requires PHP: 7.2
Stable tag: 1.0

Connect PMPro with Unlock Protocol. Let users that own an NFT claim access to your WordPress membership site by connecting their crypto wallet for single sign-on.

== Description ==

### Offer NFT memberships with Unlock Protocol and Paid Memberships Pro
Allow NFT holders to claim a membership in your PMPro-powered membership site. As long as the member has a valid NFT in their crypto wallet, they get access to your membership site.

At membership checkout, users that own a valid NFT can join without any signup requirements or fees.

If the NFT is ever transferred out of their connected wallet, their membership and access to your site will be instantly removed. You can set membership to expire after a specific period of time or be offered for as long as they own the NFT.

If their NFT has a built-in expiration date, that information is used to block membership access. The user will still have the membership level in your members list, but they won't have access to restricted content.

### Log in to WordPress Using Your Crypto Wallet
Once a wallet address is linked to the user's WordPress account, they may use their crypto wallet for single sign-on (SSO) with Unlock Protocol. This is only available on the default WordPress login screen.

### Connects Membership Levels with NFT Ownership

This plugin adds additional settings to your membership levels in Paid Memberships Pro, including:

* Select a Network: Select the network of your crypto currency that is used for the NFT.
* Lock Address: Enter the NFT address used to validate if the crypto wallet has this NFT.
* Require NFT to checkout: Optionally require NFT ownership to claim the level or allow anyone to purchase the level using your regular payment gateway.

== Screenshots ==

== Installation ==

### Before Getting Started

You must install and activate [Paid Memberships Pro](https://wordpress.org/plugins/paid-memberships-pro/) and complete the [initial plugin setup steps here](https://www.paidmembershipspro.com/documentation/initial-plugin-setup/).

You should also create an account with [Unlock Protocol](https://unlock-protocol.com).

### Install and Activate PMPro Unlock Protocol through the WordPress dashboard

1. Log in to the WordPress dashboard for your site.
1. Go to Plugins > Add New.
1. Search for "PMPro Unlock Protocol".
1. Click "Install Now" then "Activate".

### After Activation: Configure Plugin Settings

Your membership level settings now include additional options for linking an NFT address to the level.

1. Navigate to Memberships > Settings > Levels.
1. Select a level to edit or create a new level.
1. Locate the "Other Settings" section.
1. Choose a Network: Select the network of your crypto currency that is used for the NFT.
1. Lock Address: Enter the NFT address that is used to validate if the crypto wallet has this NFT. This address is created within your Unlock Protocol account.
1. Require NFT to checkout: Select "Yes" to only allow NFT holders to claim the level. Select "No" to people without the NFT purchase the level using your regular payment gateway.

== Screenshots ==

1. Checkout screen with the option to purchase the NFT
2. Checkout screen where a valid NFT holder can claim the membership level for free
3. Membership level settings to link an NFT to a membership level

== Changelog ==
= 1.0 - 2011-11-04 =
* Initial Release
