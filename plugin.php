<?php

/*
 * Plugin Name: Two-factor Authentication
 * Description: Two-factor Authentication for WordPress.
 * Author: Fredrik Forsmo
 * Author URI: https://frozzare.com
 * Version: 1.0.1
 * Plugin URI: https://github.com/wpup/2fa
 * Textdomain: 2fa
 * Domain Path: /languages/
 */

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Bootstrap plugin.
 */
add_action( 'plugins_loaded', function () {
    WPUP\TwoFactor\Plugin::instance();
} );
