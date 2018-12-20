<?php
/**
 * Plugin Name: DD Sales Reporting
 * Plugin URI:
 * Description: Doggy Dan Sales tracking plugin
 * Version: 1.0.0
 * Author: Out of the box solutions
 * Author URI:
 * Text Domain: dd_sales_reporting
 *
 *
 * Copyright Out of the box solutions
 */


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Currently plugin version.
 */
define( 'DD_SALES_REPORTING_VERSION', '1.0.0' );

if( !defined( 'DD_SALES_REPORTING_DIR' ) ) {
    define( 'DD_SALES_REPORTING_DIR', dirname( __FILE__ ) ); // plugin dir
}

if( !defined( 'DD_SALES_REPORTING_URL' ) ) {
    define( 'DD_SALES_REPORTING_URL', plugin_dir_url( __FILE__ ) ); // plugin url
}

/**
 * Activation hook
 *
 */
register_activation_hook( __FILE__, 'activate_dd_sales_reporting' );

/**
 * Plugin Setup (On Activation)
 *
 */
function activate_dd_sales_reporting() {


    $dd_sales_reporting_version = get_option( 'dd_sales_reporting_version' );
    if( empty($dd_sales_reporting_version) ) {
        update_option( 'dd_sales_reporting_version', DD_SALES_REPORTING_VERSION );
    }

    $dd_sales_reporting_version = get_option( 'dd_sales_reporting_version' );
    if( $dd_sales_reporting_version == '1.0.0' ) {
        // feature update
    }

    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $dd_sales_data = $wpdb->prefix . "dd_sales_data";

    $sql1 = "CREATE TABLE IF NOT EXISTS $dd_sales_data (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `productID` int(100),
			  `receipt` varchar (50),
			  `email` varchar (50),
			  `ref_url` varchar (250),
			  `contactID` int(11),
			  `date_time`  datetime,
			  PRIMARY KEY (`id`)
			) $charset_collate AUTO_INCREMENT=1 ;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql1 );
}

/**
 * Plugin Deactivation Hook
 *
 */
register_deactivation_hook( __FILE__, 'deactivate_dd_sales_reporting' );

/**
 * Deactivation function
 */
function deactivate_dd_sales_reporting(){

}

add_action( 'load_plugins', 'dd_sales_reporting_load_plugin_textdomain' );
/**
 * Load Text Domain
 *
 * This gets the plugin ready for translation.
 *
 * @package Ardex Widget
 * @since 1.0.0
 */
function dd_sales_reporting_load_plugin_textdomain() {
    load_plugin_textdomain( 'dd_sales_reporting', false, DD_SALES_REPORTING_DIR . '/languages/' );
}

add_action( 'plugins_loaded', 'load_required_dd_files', 999 );

/**
 * load required plugin files
 */
function load_required_dd_files(){

    require_once( DD_SALES_REPORTING_DIR. '/includes/functions.php' );
    //admin functions file
    require_once DD_SALES_REPORTING_DIR . '/admin/class-dd-sales-reporting-admin.php';
    /**
     * The class responsible for defining all actions that occur in the public-facing
     * side of the site.
     */
    require_once DD_SALES_REPORTING_DIR . '/public/class-dd-sales-reporting-public.php';
}


