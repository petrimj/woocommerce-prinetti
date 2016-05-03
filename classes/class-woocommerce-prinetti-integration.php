<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly
/**
 * Prinetti WooCommerce Integration.
 *
 * @author   Petri Mäki-Jaakkola
 */

if (!class_exists('WooCommerce_Prinetti_Integration')) :

    class WooCommerce_Prinetti_Integration extends WC_Integration {

        /**
         * Init and hook in the integration.
         */
        public function __construct() {
            global $woocommerce;

            $this->id = 'woocommerce-prinetti';
            $this->method_title = __('Prinetti', 'woocommerce-prinetti');
            $this->method_description = __('Settings for WooCommerce Prinetti integration', 'woocommerce-prinetti');

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables.
            $this->api_key = $this->get_option('routing_account');
            $this->debug = $this->get_option('secret_key');

            // Actions.
            add_action('woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));

        }

        /**
         * Initialize integration settings form fields.
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'routing_account' => array(
                    'title' => __('Routing Account', 'woocommerce-prinetti'),
                    'type' => 'decimal',
                    'description' => __('Give the routing account number given by Posti', 'woocommerce-prinetti'),
                    'desc_tip' => true,
                    'default' => ''
                ),

                'secret_key' => array(
                    'title' => __('Secret Key', 'woocommerce-prinetti'),
                    'type' => 'password',
                    'default' => '',
                    'description' => __('Secret key', 'woocommerce-prinetti'),
                ),

                'routing_source' => array(
                    'title' => __('Routing Source', 'woocommerce-prinetti'),
                    'type' => 'decimal',
                    'description' => __('Give the routing source number given by Posti', 'woocommerce-prinetti'),
                    'desc_tip' => true,
                    'default' => ''
                ),

                'lahettaja_nimi_1' => array(
                    'title' => __('Sender name', 'woocommerce-prinetti'),
                    'type' => 'text',
                    'default' => '',
                ),

                'lahettaja_nimi_2' => array(
                    'title' => __('Sender name 2', 'woocommerce-prinetti'),
                    'type' => 'text',
                    'default' => '',
                ),

                'lahettaja_osoiterivi_1' => array(
                    'title' => __('Address 1', 'woocommerce-prinetti'),
                    'type' => 'text',
                    'default' => '',
                ),

                'lahettaja_osoiterivi_2' => array(
                    'title' => __('Address 2', 'woocommerce-prinetti'),
                    'type' => 'text',
                    'default' => '',
                ),

                'lahettaja_postinumero' => array(
                    'title' => __('Postalcode', 'woocommerce-prinetti'),
                    'type' => 'text',
                    'default' => '',
                ),

                'lahettaja_toimipaikka' => array(
                    'title' => __('City', 'woocommerce-prinetti'),
                    'type' => 'text',
                    'default' => '',
                ),

                'lahettaja_bic' => array(
                    'title' => __('BIC-code', 'woocommerce-prinetti'),
                    'type' => 'text',
                    'default' => '',
                    'description' => __('BIC-koodi postiennakoiden tilitystä varten', 'woocommerce-prinetti'),
                ),

                'lahettaja_iban' => array(
                    'title' => __('IBAN-number', 'woocommerce-prinetti'),
                    'type' => 'text',
                    'default' => '',
                    'description' => __('Tilinumero IBAN-muodossa postiennakoiden tilitystä varten', 'woocommerce-prinetti'),
                ),

                'testmode' => array(
                    'title' => __('Test mode', 'woocommerce-prinetti'),
                    'type' => 'checkbox',
                    'label' => __('Enable testing mode', 'woocommerce-prinetti'),
                    'default' => 'no',
                    'description' => __('If checked, test mode is active', 'woocommerce-prinetti'),
                ),
            );
        }

    }

endif;