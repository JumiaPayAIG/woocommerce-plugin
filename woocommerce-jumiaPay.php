<?php

/**
 * Plugin Name: woocommerce jumiaPay
 * Plugin URI: http://www.pharaohsoft.com/
 * Author Name: Pharaoh Soft
 * Author URI: http://www.pharaohsoft.com/
 * Description: This plugin allows for local content payment systems.
 * Version: 1.0.0
 * License: GPLv2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * text-domain: woocommerce-jumiaPay
 */

/**
 * check if the plugin from access from the admin or external method
 */
if(!defined('ABSPATH')){
    exit;
}

/**
 * check for the woocommerce plugin
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

/**
 * check if the plugin id unique
 */
if ( !class_exists( 'jumiaPayPlugin' ) ) {

    //initiate plugin action
    add_action( 'plugins_loaded', 'init_jumiaPay_gateway_class' );

    //plugin main class
    function init_jumiaPay_gateway_class() {

        //extend woocommerce functions
        class WC_Gateway_jumiaPay_Gateway extends WC_Payment_Gateway {


           /*
            * plugin construct which contain :
            * main (variablues , methods , functions, settings in the admin , web hook)
            */
            public function __construct() {

                //plugin main settings for the admin and check out page
                $this->id   = 'jumia-pay';
                $this->icon = apply_filters( 'woocommerce_jumiaPay_icon', plugins_url('/assets/image/jumiapay.jpg', __FILE__ ) );
                $this->has_fields = true;
                $this->method_title = __( 'Jumia Payment', 'jumia-pay-woo');
                $this->method_description = __( 'jumiaPay local content payment systems.', 'jumia-pay-woo');

                $this->title = 'JumiaPay';
                $this->description = 'JumiaPay';
                $this->instructions = $this->get_option( 'instructions', $this->description );

                //get the main fields from plugin settings
                $this->shop_config_key=$this->get_option( 'shop_config_key' ) ;
                $this->api_key=$this->get_option( 'api_key' ) ;
                $this->country_code = $this->get_option( 'country_list' );

                //plugin support for pay and refund
                $this->supports = array(
                    'products',
                    'refunds',
                );

                //initiate plugin fields hook
                $this->init_form_fields();

                //initiate plugin settings hook
                $this->init_settings();

                //action hook for the payment process
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

                //action hook for the payment return
                add_action( 'woocommerce_api_payment_update', array( $this, 'payment_update' ) );

                //action hook for the cancel the order
                add_action( 'woocommerce_order_status_changed',array( $this, 'order_cancelled' ),10,3);

            }

            //settings fields function
            public function init_form_fields() {

                $pluginFields=array(
                    'enabled' => array(
                        'title' => __( 'Enable/Disable', 'jumia-pay-woo'),
                        'type' => 'checkbox',
                        'label' => __( 'Enable or Disable Jumia Payments', 'jumia-pay-woo'),
                        'default' => 'no'
                    ),
                    "country_list"=> array(
                        'title' => __( 'Country List', 'jumia-pay-woo'),
                        'type' => 'select',
                        'default' => __( 'Please remit your payment to the shop to allow for the delivery to be made', 'jumia-pay-woo'),
                        'desc_tip' => true,
                        'options' => array(
                            'Egypt' => 'Egypt',
                            'Ghana' => 'Ghana',
                            'Ivory-Coast' => 'Ivory Coast',
                            'Kenya' => 'Kenya',
                            'Morocco' => 'Morocco',
                            'Nigeria' => 'Nigeria',
                            'Tunisia' => 'Tunisia',
                            'Uganda' => 'Uganda',
                        ),
                    ),
                    "shop_config_key"=> array(
                        'title' => __( 'Merchant Api Key', 'jumia-pay-woo'),
                        'type' => 'textarea',
                        'default' => __( '', 'jumia-pay-woo'),
                    ),
                    "api_key"=> array(
                        'title' => __( 'Shop Api Key', 'jumia-pay-woo'),
                        'type' => 'textarea',
                        'default' => __( '', 'jumia-pay-woo'),
                    ),




                );
                $this->form_fields = apply_filters( 'woo_jumiaPay_fields', $pluginFields );



            }


            /*
             * create the payment order function
             * contain the create api
            */
            public function process_payment($order_id)
            {
                global $woocommerce;

                //unique id for the order purchasereferenceId + website name + order id
                $oName=str_replace(' ','',get_bloginfo( 'name' ));
                $merchantReferenceId="purchasereferenceId".$oName.$order_id;

                // we need it to get any order detailes
                $order = wc_get_order($order_id);

                // start the create api
                $shop_currency =get_woocommerce_currency();
                $create_endpoint = 'https://api-staging-pay.jumia.com.ng/merchant/create';
                $items = $order->get_items();
                $basketItems=array();
                $home_url=get_home_url();
                /*
                * the basket array
                */
                foreach ( $items as $item ) {
                    $product_id = $item['product_id'];
                    $basketItem=[
                        "name"=> $item->get_name(),
                        "imageUrl"=>$featured_img_url = get_the_post_thumbnail_url($product_id, 'full'),
                        "amount"=> $item->get_subtotal(),
                        "quantity"=> $item->get_quantity(),
                        "discount"=>"",
                        "currency"=> $shop_currency
                    ];
                    array_push($basketItems,$basketItem);
                }

                /*
                * the data array
                */
                $data = [
                    "shopConfig" => $this->shop_config_key,
                    "basket"=> [
                        "shipping"=>$order->get_shipping_tax(),
                        "currency"=>$shop_currency,
                        "basketItems"=>$basketItems,
                        "totalAmount"=> $order->get_total(),
                        "discount"=>$order->get_discount_total(),
                    ],
                    "consumerData"=> [
                        "emailAddress"=> $order->get_billing_email(),
                        "mobilePhoneNumber"=> $order->get_billing_phone(),
                        "country"=> $order->get_billing_country(),
                        "firstName"=> $order->get_billing_first_name(),
                        "lastName"=> $order->get_billing_last_name(),
                        "ipAddress"=> $order->get_customer_ip_address(),
                        "dateOfBirth"=> "",
                        "language"=> get_bloginfo( 'language' ),
                        "name"=> $order->get_billing_first_name()." ".$order->get_billing_last_name()
                    ],
                    "priceCurrency"=> $shop_currency,
                    "priceAmount"=> $order->get_total(),
                    "purchaseReturnUrl"=>  $home_url."/wc-api/payment_update/?orderid=".$order_id,
                    "purchaseCallbackUrl"=>  $home_url."/wc-api/payment_update/?paymentStatus=success",
                    "shippingAddress"=> [
                        "addressLine1"=> $order->get_shipping_address_1(),
                        "addressLine2"=> $order->get_shipping_address_2(),
                        "city"=> $order->get_shipping_city(),
                        "district"=> $order->get_shipping_state(),
                        "province"=> $order->get_shipping_state(),
                        "zip"=> $order->get_shipping_postcode(),
                        "country"=> $order->get_shipping_country(),
                        "name"=> $order->get_shipping_first_name()." ".$order->get_shipping_last_name(),
                        "firstName"=> $order->get_shipping_first_name(),
                        "lastName"=> $order->get_shipping_last_name(),
                        "mobilePhoneNumber"=> $order->get_billing_phone()
                    ],
                    "billingAddress"=> [
                        "addressLine1"=> $order->get_billing_address_1(),
                        "addressLine2"=> $order->get_billing_address_2(),
                        "city"=> $order->get_billing_city(),
                        "district"=> $order->get_billing_state(),
                        "province"=> $order->get_billing_state(),
                        "zip"=> $order->get_billing_postcode(),
                        "country"=> $order->get_billing_country(),
                        "name"=> $order->get_billing_first_name()." ".$order->get_billing_last_name(),
                        "firstName"=> $order->get_billing_first_name(),
                        "lastName"=> $order->get_billing_last_name(),
                        "mobilePhoneNumber"=> $order->get_billing_phone()
                    ],
                    "merchantReferenceId"=> $merchantReferenceId
                ];



                $data = wp_json_encode( $data );

                /*
                * the options array contain the header for the api
                */
                $options = [
                    'body'        => $data,
                    'headers'     => [
                        "apikey"=> $this->api_key,
                        "Content-Type"=> "application/json",
                    ],
                    'timeout'     => 0,
                    'redirection' => 5,
                    'blocking'    => true,
                    'httpversion' => '1.1',
                    'sslverify'   => false,
                    'data_format' => 'body',
                ];



                /*
                * Your API interaction could be built with wp_remote_post()
                 * trigger the api and get the respond
                */
                $response = wp_remote_post( $create_endpoint, $options );

                // check for the respond errors
                if (!is_wp_error($response)) {


                    $body = json_decode($response['body'], true);


                    // check for the respond body
                    if ($body['success'] == 'true') {

                        //get the checkout url from the respond
                        $checkoutUrl= $body['payload']['checkoutUrl'];

                        //get the purchaseId url from the respond
                        $purchaseId= $body['payload']['purchaseId'];


                        //save the purchaseId and the merchantReferenceId in the database
                        update_post_meta( $order_id, '_purchaseId', $purchaseId );
                        update_post_meta( $order_id, '_merchantReferenceId', $merchantReferenceId );
                        WC()->cart->empty_cart();
                        //redirect to jumiaPay gateway
                        return array(
                            'result' => 'success',
                            'redirect' => $checkoutUrl
                        );
                    }
                    else {

                        //error for the bad requests like 400 and so on
                        wc_add_notice('Please try again.', 'error');
                        WC()->cart->empty_cart();
                        return;
                    }

                }
                else {

                    //error for the connection with jumiaPay api that mean the api not trigger at all
                    wc_add_notice('Connection error.', 'error');
                    WC()->cart->empty_cart();
                    wp_delete_post( $order_id, false );
                    return;
                }

            }


            /*
            * update the order payment status function
            * the return from the jumiapay gat way
            */
            public function payment_update() {
                //get the hooked information using get from the callback function
                $orderid=$_GET['orderid'];
                $paymentStatus=$_GET['paymentStatus'];

                //check the status then redirect to the order page
                if($paymentStatus=='failure'){
                    $order = wc_get_order( $orderid );
                    $order->update_status('cancelled', 'woocommerce' );
                    wc_add_notice('Order Cancelled.', 'error');
                    wp_safe_redirect(wc_get_page_permalink('cart'));

                }
                if($paymentStatus=='success'){
                    $order = wc_get_order( $orderid );
                    $order->payment_complete();
                    wp_safe_redirect($this->get_return_url( $order ));

                }

            }


           /*
           * refund function
           * contain the refund api
           */
            public function process_refund( $order_id,  $amount = null, $reason = '' ) {

                // refunded order
                $order = wc_get_order( $order_id );

                // refunded api
                $refund_endpoint = 'https://api-staging-pay.jumia.com.ng/merchant/refund';
                // create refund unique Id refundreferenceId + website name + order id
                $oName=str_replace(' ','',get_bloginfo( 'name' ));
                $refund_merchantReferenceId="refundreferenceId".$oName.$order_id;
                // shop currency
                $shop_currency =get_woocommerce_currency();
                // get the merchantReferenceId with saved before in the database
                $order_merchantReferenceId=get_post_meta( $order_id, '_merchantReferenceId',true );

                // the data array
                $data = [
                    "shopConfig" => $this->shop_config_key,
                    "refundAmount" => $amount,
                    "refundCurrency" => $shop_currency,
                    "description" =>  "Refund for order #".$order_merchantReferenceId,
                    "purchaseReferenceId" => $order_merchantReferenceId,
                    "referenceId"=> $refund_merchantReferenceId
                ];

                $data = wp_json_encode( $data );

                // the options array
                $options = [
                    'body'        => $data,
                    'headers'     => [
                        "apikey"=> $this->api_key,
                        "Content-Type"=> "application/json",
                    ],
                    'timeout'     => 0,
                    'redirection' => 5,
                    'blocking'    => true,
                    'httpversion' => '1.1',
                    'sslverify'   => false,
                    'data_format' => 'body',
                ];

                // start the api and get the response
                $refund_response = wp_remote_post( $refund_endpoint, $options );

                //check response for errors
                if (!is_wp_error($refund_response)) {

                    $refund_body = json_decode($refund_response['body'], true);

                    //check if the refund return success
                    if ($refund_body['success'] == 'true') {

                        // refund success
                        $order->add_order_note( 'order refunded successfully', true );
                        return true;
                    }
                    else{
                        // refund failed case bad request like 400 and so on
                        $order->add_order_note( 'order refunded failed', true );
                        return false;


                    }

                }
                else {

                    // refund failed case connection error the api not trigger at all
                    $order->add_order_note( 'order refunded failed case connection error pls try again later', true );
                    return false;

                }
            }

            /*
            * cancel function
            * contain the cancel api
            */
            public function order_cancelled($order_id, $old_status, $new_status){
                $order = wc_get_order( $order_id );

                if($new_status=="cancelled"){

                    $verify_endpoint = 'https://api-staging-pay.jumia.com.ng/merchant/transaction-events';
                    $order_merchantReferenceId=get_post_meta( $order_id, '_purchaseId',true );
                    $data = [
                        "shopConfig" => $this->shop_config_key,
                        "transactionId" => $order_merchantReferenceId,
                        "transactionType" => "Purchase",

                    ];

                    $data = wp_json_encode( $data );
                    $options = [
                        'body'        => $data,
                        'headers'     => [
                            "apikey"=> $this->api_key,
                            "Content-Type"=> "application/json",
                        ],
                        'timeout'     => 0,
                        'redirection' => 5,
                        'blocking'    => true,
                        'httpversion' => '1.1',
                        'sslverify'   => false,
                        'data_format' => 'body',
                    ];
                    $verify_response = wp_remote_post( $verify_endpoint, $options );

                    if (!is_wp_error($verify_response)) {

                        $verify_body = json_decode($verify_response['body'], true);
                        if ($verify_body['success'] == 'true') {
                            $purchase_status=$verify_body['payload'][0]['newStatus'];
                            if($purchase_status=="Created" || $purchase_status=="Confirmed" || $purchase_status=="Committed"){
                                $cancel_endpoint = 'https://api-staging-pay.jumia.com.ng/merchant/cancel';
                                $order_merchantReferenceId=get_post_meta( $order_id, '_purchaseId',true );
                                $data = [
                                    "shopConfig" => $this->shop_config_key,
                                    "purchaseId" => $order_merchantReferenceId,
                                ];

                                $data = wp_json_encode( $data );
                                $options = [
                                    'body'        => $data,
                                    'headers'     => [
                                        "apikey"=> $this->api_key,
                                        "Content-Type"=> "application/json",
                                    ],
                                    'timeout'     => 0,
                                    'redirection' => 5,
                                    'blocking'    => true,
                                    'httpversion' => '1.1',
                                    'sslverify'   => false,
                                    'data_format' => 'body',
                                ];
                                $cancel_response = wp_remote_post( $cancel_endpoint, $options );
                                if (!is_wp_error($cancel_response)) {
                                    $cancel_body = json_decode($cancel_response['body'], true);
                                    if ($cancel_body['success'] == 'true') {
                                        wc_add_notice('Cancel complete', 'error');
                                    }

                                }
                                else{
                                    wc_add_notice('Connection Error', 'error');
                                }

                            }
                            elseif($purchase_status=="Cancelled" || $purchase_status=="Expired"){
                                wc_add_notice('This order is already cancelled', 'error');
                            }
                            elseif($purchase_status=="Completed"){
                                wc_add_notice('This order can not be canceled case the payment complete.', 'error');
                            }
                            elseif($purchase_status=="Failed"){
                                wc_add_notice('This order can not be cancelled case it is failed in payment process.', 'error');
                            }
                            else{
                                wc_add_notice('Connection error.', 'error');
                            }

                            return ;
                        }
                        else{
                            return ;

                        }

                    }

                }

            }
        }
    }

    function add_jumiaPay_gateway_class( $methods ) {
        $methods[] = 'WC_Gateway_jumiaPay_Gateway';
        return $methods;
    }

    add_filter( 'woocommerce_payment_gateways', 'add_jumiaPay_gateway_class' );




}




