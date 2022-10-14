<?php

/**
 * Get a list of crypto currency networks.
 *
 * @return array $networks A list of cryptocurrency networks, their names, chain ID's and RPC endpoints.
 */
function pmproup_networks_list() {
		$networks = array(
			'mainnet'  => array(
				'network_name'         => 'mainnet',
				'network_id'           => 1,
				'network_rpc_endpoint' => 'https://mainnet.infura.io/v3/9aa3d95b3bc440fa88ea12eaa4456161',
			),
			'goerli' => array(
				'network_name'         => 'goerli',
				'network_id'           => 5,
				'network_rpc_endpoint' => 'https://goerli.infura.io/v3/9aa3d95b3bc440fa88ea12eaa4456161',
			),
			'ropsten'  => array(
				'network_name'         => 'ropsten',
				'network_id'           => 3,
				'network_rpc_endpoint' => 'https://ropsten.infura.io/v3/9aa3d95b3bc440fa88ea12eaa4456161',
			),
			'rinkeby'  => array(
				'network_name'         => 'rinkeby',
				'network_id'           => 4,
				'network_rpc_endpoint' => 'https://rinkeby.infura.io/v3/9aa3d95b3bc440fa88ea12eaa4456161',
			),
			'kovan'    => array(
				'network_name'         => 'kovan',
				'network_id'           => 42,
				'network_rpc_endpoint' => 'https://kovan.infura.io/v3/9aa3d95b3bc440fa88ea12eaa4456161',
			),
			'xdai'     => array(
				'network_name'         => 'xdai',
				'network_id'           => 100,
				'network_rpc_endpoint' => 'https://rpc.xdaichain.com/',
			),
			'polygon'  => array(
				'network_name'         => 'polygon',
				'network_id'           => 137,
				'network_rpc_endpoint' => 'https://rpc-mainnet.maticvigil.com/',
			),
			'arbitrum' => array(
				'network_name'         => 'arbitrum',
				'network_id'           => 42161,
				'network_rpc_endpoint' => 'https://arb1.arbitrum.io/rpc',
			),
			'binance'  => array(
				'network_name'         => 'binance',
				'network_id'           => 56,
				'network_rpc_endpoint' => 'https://bsc-dataseed.binance.org/',
			),
		);

		return apply_filters( 'pmproup_network_list', $networks );
	}
