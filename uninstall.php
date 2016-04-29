<?php

//if uninstall is not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN'))
    exit();


function woocommerce_prinetti_uninstall_plugin()
{

// Undo all database changes
    global $wpdb;

    $table_name = $wpdb->prefix . 'woocommerce_prinetti';
    $sql = "DROP TABLE $table_name;";

    $wpdb->query($sql);

}