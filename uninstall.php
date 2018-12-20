<?php
/**
 * Copyright (C) 2018 Out of the box solutions
 *
 */

if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();
global $wpdb;
$table = $wpdb->prefix . "dd_sales_data";
$wpdb->query( "DROP TABLE IF EXISTS $table" );
delete_option("dd_sales_reporting_version");