<?php
/**
 * Bootstrap file
 *
 * This file init the License Manager Client API
 *
 * @link http://italystrap.com
 * @since 1.0.0
 *
 * @package ItalyStrap\License_Manager
 */

namespace ItalyStrap\License_Manager;

if ( ! is_admin() ) {
	return;
}

/**
 * Require PHP autoload
 */
require( __DIR__ . '/vendor/autoload.php' );

// use ItalyStrap\License_Manager\Client;

$type = 'theme';
// $type = 'plugin';

$licence_manager = new Client(
	'product_id',
	'Theme Name',
	'textdomain',
	'http://mylicenses.example.com/api/license-manager',
	$type
);

add_filter( "pre_set_site_transient_update_{$type}s", array( $licence_manager, 'check_for_update' ) );

if ( 'plugin' === $type ) {

	// Showing plugin information
	add_filter( 'plugins_api', array( $licence_manager, 'plugins_api_handler' ), 10, 3 );
}

$settings = new Settings(
	'product_id',
	'Theme Name',
	'textdomain',
	'http://mylicenses.example.com/api/license-manager',
	$type
);
