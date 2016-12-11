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

/**
 * Require PHP autoload
 */
require( __DIR__ . '/vendor/autoload.php' );

// use ItalyStrap\License_Manager\Client;

$licence_manager = new Client(
    'product_id',
    'Theme Name',
    'textdomain',
    'http://mylicenses.example.com/api/license-manager',
    'theme'
);

