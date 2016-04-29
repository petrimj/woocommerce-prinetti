<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class WooCommerce_Prinetti
{

    protected $loader;
    protected $plugin_slug;
    protected $version;

    public function __construct()
    {
        $this->plugin_slug = 'woocommerce-prinetti';
        $this->version = '0.1.0';

        $this->load_dependencies();
        $this->define_admin_hooks();

    }

    /**
     * Load all dependencies
     *
     */
    private function load_dependencies()
    {

        require_once plugin_dir_path(dirname(__FILE__)) . 'classes/class-woocommerce-prinetti-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'classes/class-woocommerce-prinetti-order-page.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'classes/class-prinetti-shipment.php';

        $this->loader = new WooCommerce_Prinetti_Loader();

    }

    /**
     *
     */
    public function init_wc_integration()
    {

        // Checks if WooCommerce is installed.
        if (class_exists('WC_Integration')) {
            // Include integration class.
            include_once (dirname(__FILE__)) . '/class-woocommerce-prinetti-integration.php';

            // Register the integration.
            add_filter('woocommerce_integrations', array($this, 'add_integration'));
        } else {
            // throw an error
        }
    }


    /**
     * Adds the integration to WooCommerce settings
     *
     * @param $integrations
     * @return array
     */
    public function add_integration($integrations)
    {
        $integrations[] = 'WooCommerce_Prinetti_integration';
        return $integrations;
    }


    private function define_admin_hooks()
    {
        $orderpageForm = new WooCommerce_Prinetti_Order_Page();
        $this->loader->add_action('admin_enqueue_scripts', $orderpageForm, 'enqueue_styles');
        $this->loader->add_action('add_meta_boxes', $orderpageForm, 'add_meta_box');
        $this->loader->add_action('wp_ajax_pmj_get_results', $this, 'pmj_process_ajax');
        $this->loader->add_action('wp_ajax_refresh_created_labels_ajax', $orderpageForm, 'refresh_created_labels_ajax');
        $this->loader->add_action('woocommerce_email_before_order_table', $this, 'add_tracking_code');
        $this->loader->add_action('plugins_loaded', $this, 'init_wc_integration');
    }

    public function run()
    {
        $this->loader->run();


    }

    public function get_version()
    {
        return $this->version;
    }

    /**
     * Processes AJAX-call from woocommerce-prinetti-ajax.js
     * die() is mandatory, otherwise redirects to admin-ajax.php and returns 0
     *
     */
    function pmj_process_ajax()
    {

        if (!isset($_POST['pmj_nonce']) || !wp_verify_nonce($_POST['pmj_nonce'], 'pmj-nonce'))
            return;

        $params = array();


        parse_str($_POST['pmj_data'], $params);
        $order_id = $params['post_ID'];

        $options = get_option('woocommerce_woocommerce-prinetti_settings');
        

        $shipment = new Prinetti_Shipment($params, $options);
        $shipment->createXML();
        $result = $shipment->sendXML();

        if (!isset($result['error_status'])) {

            global $wpdb;

            $link = $shipment->getLabel();

            // Save the result to database
            $table_name = $wpdb->prefix . 'woocommerce_prinetti';

            $wpdb->insert(
                $table_name,
                array(
                    'order_id' => $order_id,
                    'trackingcode' => $result,
                    'created' => current_time('mysql'),
                )
            );
            print $link;


        } else {

            $error_message  = '<div class="error">';
            $error_message .= '<table class="prinetti_input_error_message">';
            $error_message .= '<tr><td colspan="2"><strong>' . __('Error on processing the request', 'woocommerce-prinetti') .'</strong></td></tr>';
            $error_message .= '<tr><td>' . $result['error_status'] . '</td><td>' . $result['error_message'] . '</td></tr>';
            $error_message .= '</table>';
            $error_message .= '</div>';

            print $error_message;

        }


        // die() is mandatory, otherwise redirects to admin-ajax.php and returns 0
        die();
    }

    function add_tracking_code()
    {

        global $wpdb, $post;
        $table_name = $wpdb->prefix . 'woocommerce_prinetti';

        if (isset($post->ID)) {
            $tracking_code = $wpdb->get_row("SELECT * FROM $table_name WHERE order_id = $post->ID ORDER BY created DESC LIMIT 1;");
            if ($tracking_code != null) {

                // If found, show most recent and mark as sent to the database
                $wpdb->update(
                    $table_name,
                    array(
                        'sent' => current_time('mysql')
                    ),
                    array(
                        'id' => $tracking_code->id
                    )
                );

                echo(_e('<br>Track your parcel by clicking the following link: ', 'woocommerce-prinetti') . '<br>');
                echo('<a href="http://www.posti.fi/itemtracking/posti/search_by_shipment_id?lang=fi&ShipmentId=' . $tracking_code->trackingcode . '"">' . $tracking_code->trackingcode . '</a>');



            }
        }

    }


}