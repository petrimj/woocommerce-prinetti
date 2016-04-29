<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly ?>
<div class="wrap">
	
    <form id="pmj_prinetti_form" action="" method="post">

        <input placeholder="<?php _e('Recipient name', 'woocommerce-prinetti'); ?>" type="text"
               name="prinetti_vastaanottaja" id="prinetti_vastaanottaja"
               value="<?php echo $tilaus->shipping_first_name . " " . $tilaus->shipping_last_name; ?>"
               class="required"><br>

        <input placeholder="<?php _e('Company', 'woocommerce-prinetti'); ?>" type="text"
               name="prinetti_vastaanottaja_yritys" id="prinetti_vastaanottaja_yritys"
               value="<?php echo $tilaus->shipping_company; ?>"><br>

        <input placeholder="<?php _e('Address 1', 'woocommerce-prinetti'); ?>" type="text" name="prinetti_osoite1"
               id="prinetti_osoite1"
               value="<?php echo $tilaus->shipping_address_1; ?>"
               class="required"><br>

        <input placeholder="<?php _e('Address 2', 'woocommerce-prinetti'); ?>" type="text" name="prinetti_osoite2"
               id="prinetti_osoite2"
               value="<?php echo $tilaus->shipping_address_2; ?>"><br>

        <input placeholder="<?php _e('Postalcode', 'woocommerce-prinetti'); ?>" type="text" name="prinetti_postinumero"
               id="prinetti_postinumero"
               value="<?php echo $tilaus->shipping_postcode; ?>"
               class="required"><br>


        <input placeholder="<?php _e('City', 'woocommerce-prinetti'); ?>" type="text" name="prinetti_postitoimipaikka"
               id="prinetti_postitoimipaikka" value="<?php echo $tilaus->shipping_city; ?>"
               class="required"><br>

        <input placeholder="<?php _e('Phone', 'woocommerce-prinetti'); ?>" type="text" name="prinetti_puhelinnumero"
               id="prinetti_puhelinnumero"
               value="<?php echo $tilaus->billing_phone; ?>"><br>

        <input placeholder="<?php _e('Email', 'woocommerce-prinetti'); ?>" type="text" name="prinetti_email"
               id="prinetti_email"
               value="<?php echo $tilaus->billing_email; ?>"><input type="hidden" name="prinetti_order_id"
                                                                    value="<?php $_GET['post']; ?>"><br>


        <div>
            <span class="service-header"><strong><?php _e('Service', 'woocommerce-prinetti'); ?></strong></span>

            <div class="prinetti_additional_service">
                <fieldset>
                    <label for="palvelu1">
                        <input type="radio" name="palvelu" value="2103" checked="checked" id="palvelu1"/>
                        <span><?php _e('Economy 16', 'woocommerce-prinetti'); ?></span>
                    </label>
                    <br>
                    <label for="palvelu2">
                        <input type="radio" name="palvelu" value="2144" id="palvelu2"/>
                        <span><?php _e('Express Business Day 14', 'woocommerce-prinetti'); ?></span>
                    </label>
                    <br>
                    <label for="palvelu3">
                        <input type="radio" name="palvelu" value="2145" id="palvelu3"/>
                        <span><?php _e('Express Flex 21', 'woocommerce-prinetti'); ?></span>
                    </label>
                </fieldset>
            </div>
        </div>

        <div><span
                class="service-header"><strong><?php _e('Additional services', 'woocommerce-prinetti'); ?></strong></span>

            <div class="prinetti_additional_service">
                <input type="checkbox" id="pe_checkbox" name="postiennakko" value="3101"/>


                <span><?php _e('Cash on delivery', 'woocommerce-prinetti'); ?></span><br/>

                <div id="pe_lisatiedot">
                    <input type="text" placeholder="<?php _e('Amount', 'woocommerce-prinetti'); ?>"
                           name="postiennakko_summa"
                           value="<?php echo $tilaus->order_total; ?>">
                </div>
                
                <input type="checkbox" id="mp_checkbox" name="monipaketti" value="3102"/>
                <span><?php _e('Multiple parcels', 'woocommerce-prinetti'); ?></span><br/>
                
                
                <div id="mp_lisatiedot">
                    <fieldset>
                        <label for="mpcount_1">
                            <input id="mpcount_1" type="radio" name="mp_count" value="2">
                            <span>2</span>
                        </label><br>
                        <label for="mpcount_2">
                            <input id="mpcount_2" type="radio" name="mp_count" value="3">
                            <span>3</span>
                        </label><br>
                        <label for="mpcount_3">
                            <input id="mpcount_3" type="radio" name="mp_count" value="4">
                            <span>4</span>
                        </label><br>
                    </fieldset>
                </div>
                
                
                <input type="checkbox" id="erilliskasiteltava_checkbox" name="erilliskasiteltava" value="3104"/>
                <span><?php _e('Erilliskäsiteltävä', 'woocommerce-prinetti'); ?></span>
                
                <div id="erilliskasiteltava_lisatiedot">
	                <fieldset>
		                <label for="ercount_1">
                            <input id="ercount_1" type="radio" name="er_count" value="1" checked="checked">
                            <span>1</span>
                        </label><br>
                        <label for="ercount_2">
                            <input id="ercount_2" type="radio" name="er_count" value="2">
                            <span>2</span>
                        </label><br>
                        <label for="ercount_3">
                            <input id="ercount_3" type="radio" name="er_count" value="3">
                            <span>3</span>
                        </label><br>
                        <label for="ercount_4">
                            <input id="ercount_4" type="radio" name="er_count" value="4">
                            <span>4</span>
                        </label><br>
                    </fieldset>
                </div>
                
            </div>
        </div>
    </form>
    <input class="button-primary" type="button" id="pmj_prinetti_submit" name="pmj-prinetti-submit"
           value="<?php _e('Create label', 'woocommerce-prinetti'); ?>"/>
    <img src="<?php echo admin_url('images/loading.gif'); ?>" class="waiting" id="pmj_prinetti_loading"
         style="display:none">

    <div id="results"></div>
    <div id="created_labels">

        <?php WooCommerce_Prinetti_Order_Page::generate_created_tracking_codes($tracking_codes); ?>

    </div>
</div>
