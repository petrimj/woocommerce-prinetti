<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class WooCommerce_Prinetti_Order_Page {

    protected $version;
    protected $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'woocommerce_prinetti';

    }

    public function enqueue_styles() {
        wp_enqueue_style(
            'pmj_prinetti_admin_styles',
            plugins_url('woocommerce-prinetti/css/admin-style.css'));

        wp_enqueue_script('pmj-prinetti-ajax', plugins_url('woocommerce-prinetti/js/woocommerce-prinetti-ajax.js'), array('jquery'));
        wp_localize_script('pmj-prinetti-ajax', 'pmj_vars', array(
                'pmj_nonce' => wp_create_nonce('pmj-nonce')
            )
        );

    }

    public function add_meta_box() {

        add_meta_box(
            'prinetti_integraatio',
            'Prinetti',
            array($this, 'render_meta_box'),
            'shop_order',
            'side',
            'default');
    }

    public function render_meta_box() {

        global $wpdb;
        $order_id = $_GET['post'];

        $tilaus = new WC_Order($order_id);
        $tracking_codes = $wpdb->get_results("SELECT * FROM $this->table_name WHERE order_id = $order_id ORDER BY created DESC LIMIT 5");
        require_once plugin_dir_path(dirname(__FILE__)) . 'views/order-page-wrapper.php';
    }

    public function generate_created_tracking_codes($tracking_codes) {
        foreach ($tracking_codes as $tracking_code) {


            $output = '<div class="created_label"><span><strong>' . $tracking_code->trackingcode . '</strong></span><br>';
            $output .= '<span class="label_created_notification">' . __('Label created: ', 'woocommerce-prinetti') . date('j.n.Y G.i', strtotime($tracking_code->created)) . '</span><br>';
            $output .= '<span class="label_created_notification">' . __('Tracking code sent: ', 'woocommerce-prinetti');
            if ($tracking_code->sent == null) {
                $output .= __('no', 'woocommerce-prinetti');
            } else {
                $output .= date('j.n.Y G.i', strtotime($tracking_code->sent));
            }

            $output .= '</span></div>';

            echo $output;
        }
    }

    public function refresh_created_labels_ajax() {

        if (!isset($_POST['pmj_nonce']) || !wp_verify_nonce($_POST['pmj_nonce'], 'pmj-nonce'))
            return;

        global $wpdb;

        $params = array();

        parse_str($_POST['pmj_data'], $params);
        $order_id = $params['post_ID'];

        $tracking_codes = $wpdb->get_results("SELECT * FROM $this->table_name WHERE order_id = $order_id ORDER BY created DESC LIMIT 5");
        $this->generate_created_tracking_codes($tracking_codes);

        die();
    }
}