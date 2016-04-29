<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Plugin Name:     WooCommerce Prinetti
 * Plugin URI:      https://github.com/petrimj/woocommerce-prinetti
 * Description:     Postin Prinetti-toiminnallisuus WooCommerce-verkkokauppaan
 * Version:         0.1.0
 * Author:          Petri Mäki-Jaakkola
 * Author URI:      http://www.makijaakkola.fi
 * Text Domain:     woocommerce-prinetti
 * Domain Path:     /languages
 */


require_once plugin_dir_path(__FILE__) . 'classes/class-woocommerce-prinetti.php';

/**
 * Begins the plugin execution
 */
function run_woocommerce_prinetti()
{

    $wcprinetti = new WooCommerce_Prinetti;
    $wcprinetti->run();
}

run_woocommerce_prinetti();

// This will be executed when the plugin is activated in the admin panel
function woocommerce_prinetti_activate_plugin()
{
    // Changes to the database
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'woocommerce_prinetti';

    $sql = "CREATE TABLE $table_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  order_id mediumint(9),
  created datetime NOT NULL,
  trackingcode varchar(50) NOT NULL,
  sent datetime,
  UNIQUE KEY id (id)
) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook( __FILE__, "woocommerce_prinetti_activate_plugin");

function woocommerce_prinetti_deactivate_plugin() {

// Undo all database changes
    global $wpdb;

    $table_name = $wpdb->prefix . 'woocommerce_prinetti';
    $sql = "DROP TABLE $table_name;";

   // $wpdb->query($sql);
   
   delete_option('woocommerce_woocommerce-prinetti_settings');

}
register_deactivation_hook( __FILE__, 'woocommerce_prinetti_deactivate_plugin' );

register_uninstall_hook( 'uninstall.php', "woocommerce_prinetti_uninstall_plugin");

load_plugin_textdomain('woocommerce-prinetti', false, basename( dirname( __FILE__ ) ) . '/languages' );