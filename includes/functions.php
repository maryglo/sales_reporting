<?php

function getDefaults(){
    $defaults = require DD_SALES_REPORTING_DIR . '/includes/config/default_settings.php';
    return $defaults;
}

function dd_sales_reporting_get_options(){
    static $options;

    if (!$options) {
        $defaults = require DD_SALES_REPORTING_DIR . '/includes/config/default_settings.php';
        $options = (array)get_option('wp_dd_sales_reporting', array());
        $options = array_merge($defaults, $options);
    }

    return apply_filters('wp_dd_sales_reporting_settings', $options);
}

function getGaCode(){
    $name = "_ga";
    if (!isset($_COOKIE[$name])) {
        return null;
    }

    return explode(".", $_COOKIE[$name])[2];
}