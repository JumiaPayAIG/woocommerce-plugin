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
        // start write to woocommerce log file
        //extend woocommerce functions
        class WC_Gateway_jumiaPay_Gateway extends WC_Payment_Gateway {

            /*
             * plugin construct which contain :
             * main (variablues , methods , functions, settings in the admin , web hook)
             */
            public function __construct() {

                //plugin main settings for the admin and check out page
                $this->id   = 'jumia-pay';
                $this->icon = apply_filters( 'woocommerce_jumiaPay_icon', plugins_url('/assets/image/Jumia-pay-logo-vertival.svg', __FILE__ ) );
                $this->has_fields = true;
                $this->method_title = __( 'JumiaPay', 'jumia-pay-woo');
                $this->method_description = __( 'JumiaPay local content payment systems.', 'jumia-pay-woo');

                $this->title = 'JumiaPay';
                $this->description = 'Pay with your JumiaPay account and your preferred payment options';
                $this->instructions = $this->get_option( 'instructions', $this->description );

                //get the main fields from plugin settings
                $this->environment=$this->get_option( 'environment' ) ;

                $this->country_code = $this->get_option( 'country_list' );
                $this->shop_config_key=$this->get_option( 'shop_config_key' ) ;
                $this->api_key=$this->get_option( 'api_key' ) ;

                $this->sandbox_country_code = $this->get_option( 'sandbox_country_list' );
                $this->sandbox_shop_config_key=$this->get_option( 'sandbox_shop_config_key' ) ;
                $this->sandbox_api_key=$this->get_option( 'sandbox_api_key' ) ;

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
                        'label' => __( 'Enable or Disable JumiaPay', 'jumia-pay-woo'),
                        'default' => 'no',

                    ),
                    "environment"=> array(
                        'title' => __( 'Environment', 'jumia-pay-woo'),
                        'type' => 'select',
                        'default' => __( 'Please remit your payment to the shop to allow for the delivery to be made', 'jumia-pay-woo'),
                        'desc_tip' => true,
                        'options' => array(
                            'Live' => 'Live',
                            'Sandbox' => 'Sandbox',
                        ),
                    ),
                    "live_title"=> array(
                        'title' => esc_html__( 'Live Settings', 'jumia-pay-woo' ),
                        'type'  => 'title',
                    ),
                    "country_list"=> array(
                        'title' => __( 'Country List', 'jumia-pay-woo'),
                        'type' => 'select',
                        'default' => __( 'Please remit your payment to the shop to allow for the delivery to be made', 'jumia-pay-woo'),
                        'desc_tip' => true,
                        'description' => __( 'Note that the currency of your WooCommerce store, under "General settings", must be the one used on the country you are operating and selecting here.', 'woocommerce' ),
                        'default' => __( 'Cheque Payment', 'woocommerce' ),
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
                        'title' => __( 'Shop Api Key', 'jumia-pay-woo'),
                        'type' => 'textarea',
                        'default' => __( '', 'jumia-pay-woo'),
                    ),
                    "api_key"=> array(
                        'title' => __( 'Merchant Api Key', 'jumia-pay-woo'),
                        'type' => 'textarea',
                        'default' => __( '', 'jumia-pay-woo'),
                    ),
                    "sandbox_title"=> array(
                        'title' => esc_html__( 'Sandbox Settings', 'jumia-pay-woo' ),
                        'type'  => 'title',
                    ),
                    "sandbox_country_list"=> array(
                        'title' => __( 'Country List', 'jumia-pay-woo'),
                        'type' => 'select',
                        'default' => __( 'Please remit your payment to the shop to allow for the delivery to be made', 'jumia-pay-woo'),
                        'desc_tip' => true,
                        'description' => __( 'Note that the currency of your WooCommerce store, under "General settings", must be the one used on the country you are operating and selecting here.', 'woocommerce' ),
                        'default' => __( 'Cheque Payment', 'woocommerce' ),
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
                    "sandbox_shop_config_key"=> array(
                        'title' => __( 'Shop Api Key', 'jumia-pay-woo'),
                        'type' => 'textarea',
                        'default' => __( '', 'jumia-pay-woo'),
                    ),
                    "sandbox_api_key"=> array(
                        'title' => __( 'Merchant Api Key', 'jumia-pay-woo'),
                        'type' => 'textarea',
                        'default' => __( '', 'jumia-pay-woo'),
                    ),



                );
                $this->form_fields = apply_filters( 'woo_jumiaPay_fields', $pluginFields );



            }

            private function getJumiaPayUrl() {
                switch ($this->country_code) {
                    case "Egypt":
                        $tld='.jumia.com.ng';
                        break;
                    case "Ghana":
                        $tld='.jumia.com.gh';
                        break;
                    case "Ivory-Coast":
                        $tld='.jumia.ci';
                        break;
                    case "Kenya":
                        $tld='.jumia.co.ke';
                        break;
                    case "Morocco":
                        $tld='.jumia.ma';
                        break;
                    case "Nigeria":
                        $tld='.jumia.com.ng';
                        break;
                    case "Tunisia":
                        $tld='.jumia.com.tn';
                        break;
                    case "Uganda":
                        $tld='.jumia.ug';
                        break;
                }

                if($this->environment=="Live"){
                    return 'https://api-pay'.$tld;
                }
                if($this->environment=="Sandbox"){
                    return 'https://api-sandbox-pay'.$tld;
                }
                return '';
            }

            private function getJumiaPayCountryCode() {
                switch ($this->country_code) {
                    case "Egypt":
                        $countryCode='EG';
                        break;
                    case "Ghana":
                        $countryCode='GH';
                        break;
                    case "Ivory-Coast":
                        $countryCode='CI';
                        break;
                    case "Kenya":
                        $countryCode='KE';
                        break;
                    case "Morocco":
                        $countryCode='MA';
                        break;
                    case "Nigeria":
                        $countryCode='NG';
                        break;
                    case "Tunisia":
                        $countryCode='TN';
                        break;
                    case "Uganda":
                        $countryCode='UG';
                        break;
                }

                return $countryCode;
            }

            private function getJumiaPayShopKey() {
                if($this->environment=="Live"){
                    return $this->shop_config_key;
                }

                if($this->environment=="Sandbox"){
                    return $this->sandbox_shop_config_key;
                }
            }

            private function getJumiaPayApiKey() {
                if($this->environment=="Live"){
                    return $this->api_key;
                }

                if($this->environment=="Sandbox"){
                    return $this->sandbox_api_key;
                }
            }


            private function makeRequest($method, $url, $data=null) {
                if ($data != null) {
                    $data = wp_json_encode( $data );
                }

                /*
                * the options array contain the header for the api
                */
                $options = [
                    'body'        => $data,
                    'headers'     => [
                        "apikey"=> $this->getJumiaPayApiKey(),
                        "Content-Type"=> "application/json",
                    ],
                    'timeout'     => 60,
                    'redirection' => 5,
                    'blocking'    => true,
                    'httpversion' => '1.1',
                    'sslverify'   => false,
                    'data_format' => 'body',
                ];


                if ($method == 'post') {
                    return wp_remote_post( $url, $options );
                }
            }

            /*
             * create the payment order function
             * contain the create api
            */
            public function process_payment($order_id)
            {
                global $woocommerce;

                $logger = wc_get_logger();

                $date = date_create();
                $newDate=date_format($date, 'U');
                $merchantReferenceId="purchasereferenceId".$order_id.$newDate;

                $merchantReferenceId = str_replace(' ', '', $merchantReferenceId); // Replaces all spaces with hyphens.
                $merchantReferenceId = preg_replace('/[^A-Za-z0-9\-]/', '', $merchantReferenceId); // Removes special chars.

                if(strlen($merchantReferenceId) > 255){
                    $merchantReferenceId= substr($merchantReferenceId,0,250);
                }


                // we need it to get any order detailes
                $order = wc_get_order($order_id);

                $countryEndPoint=$this->getJumiaPayUrl();
                $countryCode=$this->getJumiaPayCountryCode();
                $shop_config_key=$this->getJumiaPayShopKey();

                // start the create api
                $shop_currency =get_woocommerce_currency();

                $create_endpoint = $countryEndPoint.'/merchant/create';

                $items = $order->get_items();

                $basketItems=array();
                $home_url=get_home_url();

                /*
                * the basket array
                */
                foreach ( $items as $item ) {
                    $product_id = $item['product_id'];
                    if(get_the_post_thumbnail_url($product_id, 'full')!=""){
                        $featured_img_url = get_the_post_thumbnail_url($product_id, 'full');
                    }else{
                        $featured_img_url="";
                    }
                    $basketItem=[
                        "name"=> $item->get_name(),
                        "imageUrl"=>$featured_img_url ,
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
                    "shopConfig" => $shop_config_key,
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
                        "country"=> $countryCode,
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
                    "purchaseCallbackUrl"=>  $home_url."/wc-api/payment_update/?orderid=".$order_id,
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

                $response = $this->makeRequest('post', $create_endpoint, $data);


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


                        //redirect to jumiaPay gateway
                        return array(
                            'result' => 'success',
                            'redirect' => $checkoutUrl
                        );
                    }
                    else {

                        //error for the bad requests like 400 and so on
                        wc_add_notice('Error payment failed case '.$body['payload'][0]['description'].' code-'.$body['payload'][0]['code'],'error');

                        WC()->cart->empty_cart();
                        wp_delete_post( $order_id, false );
                        return;
                    }

                }
                else {

                    $logger = wc_get_logger();

                    $logger->info( wc_print_r( 'Error On Request Jpay Merchant Create', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( 'Error Message = '.$response->get_error_message(), true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '==========================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                    //error for the connection with jumiaPay api that mean the api not trigger at all
                    wc_add_notice('Connection error please try again later.', 'error');

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
                $logger = wc_get_logger();


                //get the hooked information using get from the callback function
                $orderid= isset($_GET['orderid']) ? $_GET['orderid'] : '';
                $paymentStatus= isset($_GET['paymentStatus']) ? $_GET['paymentStatus'] : '';
                $order = wc_get_order($orderid);

                // RETURN URL HANDLE
                if($paymentStatus=='failure'){

                    wc_add_notice('Payment Cancelled.', 'error');
                    wp_safe_redirect(wc_get_page_permalink('cart'));
                }
                if($paymentStatus=='success'){
                    wp_safe_redirect($this->get_return_url( $order ));
                }

                // CALLBACK HANDLE
                if($_SERVER['REQUEST_METHOD'] === 'POST' && $paymentStatus!='failure'){

                    $body = file_get_contents('php://input');
                    $DecodeBody=urldecode($body);
                    parse_str($DecodeBody,$bodyArray);
                    $JsonDecodeBody = json_decode($bodyArray['transactionEvents'], true);

                    // save the transactionEvents for debugging purposes ( this will be removed in the production version )
                    update_post_meta($orderid,'bodyArray',$bodyArray);

                    $order = wc_get_order( $orderid );

                    if($order->get_status() == 'pending'){
                        if($JsonDecodeBody[0]['newStatus']=="Created"){
                            $order->update_status('Pending');
                            $order->add_order_note( 'Payment Created.', true );
                        }
                        if($JsonDecodeBody[0]['newStatus']=="Confirmed"){
                            $order->update_status('Pending');
                            $order->add_order_note( 'Payment Confirmed.', true );
                        }
                        if($JsonDecodeBody[0]['newStatus']=="Committed"){
                            $order->update_status('Pending');
                            $order->add_order_note( 'Payment Committed.', true );
                        }
                        if($JsonDecodeBody[0]['newStatus']=="Completed"){
                            $order->payment_complete();
                            $order->add_order_note( 'Payment Completed.', true );
                        }
                        if($JsonDecodeBody[0]['newStatus']=="Failed"){
                            $order->update_status('Cancelled');
                            $order->add_order_note( 'Payment Failed.', true );
                        }
                        if($JsonDecodeBody[0]['newStatus']=="Cancelled"){
                            $order->update_status('Cancelled');
                            $order->add_order_note( 'Payment Cancelled.', true );
                        }
                        if($JsonDecodeBody[0]['newStatus']=="Expired"){
                            $order->update_status('Cancelled');
                            $order->add_order_note( 'Payment Expired.', true );
                        }

                        wp_send_json(['success' => true], 200);
                    }
                    $logger->info( wc_print_r( 'Callback out of order', true ), array( 'source' => 'jumiaPay log file -'.$orderid ) );
                    $logger->info( wc_print_r( 'Order Status = '.$order->get_status(), true ), array( 'source' => 'jumiaPay log file -'.$orderid ) );
                    $logger->info( wc_print_r( 'CallBack Status = '.$JsonDecodeBody[0]['newStatus'], true ), array( 'source' => 'jumiaPay log file -'.$orderid ) );
                    $logger->info( wc_print_r( '==================================', true ), array( 'source' => 'jumiaPay log file -'.$orderid ) );

                    wp_send_json(['success' => false, 'payload' => 'Wrong Order Status for this callback'], 400);
                }
            }


            /*
            * refund function
            * contain the refund api
            */
            public function process_refund( $order_id,  $amount = null, $reason = '' ) {

                $logger = wc_get_logger();

                // refunded order
                $order = wc_get_order( $order_id );
                $date = date_create();
                $newDate=date_format($date, 'U');
                $refund_merchantReferenceId="refundreferenceId".$order_id.$newDate;

                $refund_merchantReferenceId = str_replace(' ', '', $refund_merchantReferenceId); // Replaces all spaces with hyphens.
                $refund_merchantReferenceId = preg_replace('/[^A-Za-z0-9\-]/', '', $refund_merchantReferenceId); // Removes special chars.

                if(strlen($refund_merchantReferenceId) > 255){
                    $refund_merchantReferenceId= substr($refund_merchantReferenceId,0,250);
                }

                // shop currency
                $shop_currency =get_woocommerce_currency();
                $countryEndPoint=$this->getJumiaPayUrl();
                $shop_config_key=$this->getJumiaPayShopKey();

                $refund_endpoint = $countryEndPoint.'/merchant/refund';

                // get the merchantReferenceId with saved before in the database
                $order_merchantReferenceId=get_post_meta( $order_id, '_merchantReferenceId',true );

                // the data array
                $data = [
                    "shopConfig" => $shop_config_key,
                    "refundAmount" => $amount,
                    "refundCurrency" => $shop_currency,
                    "description" =>  "Refund for order #".$order_merchantReferenceId,
                    "purchaseReferenceId" => $order_merchantReferenceId,
                    "referenceId"=> $refund_merchantReferenceId
                ];

                $refund_response = $this->makeRequest('post', $refund_endpoint, $data);
                //check response for errors
                if (!is_wp_error($refund_response)) {

                    $refund_body = json_decode($refund_response['body'], true);

                    //check if the refund return success
                    if ($refund_body['success'] == 'true') {

                        // refund success
                        $order->add_order_note( 'Order refunded via JumiaPay successfully', true );

                        return true;
                    }
                    else{
                        $order->add_order_note("Order Refund via JumiaPay Failed - Reason: ".$refund_body['payload'][0]['description'], true);
                        return false;
                    }

                }
                else {

                    $logger->info( wc_print_r( 'Error On Request Jpay Merchat Refund', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( 'Error Message = '.$refund_response->get_error_message(), true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '====================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                    $order->add_order_note("Order Refund via JumiaPay Failed - Reason: Connection Failed", true);

                    return;
                }
            }


            /*
            * cancel function
            * contain the cancel api
            */
            public function order_cancelled($order_id, $old_status, $new_status){

                $logger = wc_get_logger();

                $order = wc_get_order( $order_id );

                // We can only cancel an order if payment is not completed
                if($new_status == 'cancelled'){

                    $countryEndPoint=$this->getJumiaPayUrl();
                    $shop_config_key=$this->getJumiaPayShopKey();

                    $cancel_endpoint = $countryEndPoint.'/merchant/cancel';

                    $order_merchantReferenceId=get_post_meta( $order_id, '_purchaseId',true );
                    $data = [
                        "shopConfig" => $shop_config_key,
                        "purchaseId" => $order_merchantReferenceId,
                    ];

                    $cancel_response = $this->makeRequest('post', $cancel_endpoint, $data);

                    if (!is_wp_error($cancel_response)) {

                        $cancel_body = json_decode($cancel_response['body'], true);

                        if ($cancel_body['success'] == 'true') {

                            $order->add_order_note( 'JumiaPay Payment successfully cancelled', true );
                            return true;
                        } else {
                            $order->add_order_note("JumiaPay Payment cancellation failed - Reason: ".$cancel_body['payload'][0]['description'], true);
                            return false;
                        }
                    }
                    else{
                        $logger->info( wc_print_r( 'Error On Request Jpay Merchat Cancel', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                        $logger->info( wc_print_r( 'Error Message = '.$cancel_response->get_error_message(), true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );                                $logger->info( wc_print_r( '==================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                        $order->add_order_note("JumiaPay Payment cancellation failed - Reason: Connection Failed", true);
                        return false;
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
