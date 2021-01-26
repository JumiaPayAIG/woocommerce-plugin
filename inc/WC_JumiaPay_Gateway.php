<?php

/*
 * JumiaPay Gateway
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once JPAY_DIR . 'inc/WC_JumiaPay_Callback.php';
require_once JPAY_DIR . 'inc/WC_JumiaPay_Client.php';
require_once JPAY_DIR . 'inc/WC_JumiaPay_Purchase.php';
require_once JPAY_DIR . 'inc/WC_JumiaPay_Refund.php';

class WC_JumiaPay_Gateway extends WC_Payment_Gateway {


    public $JpayClient;

    /*
     * plugin construct which contain :
     * main (variablues , methods , functions, settings in the admin , web hook)
     */
    public function __construct() {

        //plugin main settings for the admin and check out page
        $this->id   = 'jumia-pay';
        $this->icon = apply_filters( 'woocommerce_jumiaPay_icon', plugins_url('/assets/image/Jumia-pay-logo-vertival.svg', dirname( __FILE__ ) ) );
        $this->has_fields = true;

        $this->method_title = 'JumiaPay';
        $this->method_description = 'JumiaPay payment gateway for WooCommerce';

        $this->title = 'JumiaPay';
        $this->description = 'Pay with your JumiaPay account and your preferred payment options';
        $this->instructions = $this->get_option( 'instructions', $this->description );

        $JpayClient = new WC_JumiaPay_Client(
            $this->get_option('environment'),
            $this->get_option('country_list'),
            $this->get_option('shop_config_key'),
            $this->get_option('api_key'),
            $this->get_option('sandbox_country_list'),
            $this->get_option('sandbox_shop_config_key'),
            $this->get_option('sandbox_api_key'),
        );

        $this->JpayClient = $JpayClient;

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
        add_action( 'woocommerce_api_payment_return', array($this, 'payment_return'));

        //action hook for the payment callback
        add_action( 'woocommerce_api_payment_callback', array($this, 'payment_callback'));
    }

    //settings fields function
    public function init_form_fields() {
		    $this->form_fields = include dirname( __FILE__ ) . '/settings/settings.php';
    }

    public function process_payment($orderId)
    {
        $purchase = new WC_JumiaPay_Purchase(
            wc_get_order($orderId),
            $this->JpayClient->getCountryCode(),
            get_bloginfo('language'),
            get_home_url(),
            get_woocommerce_currency(),
            $this->JpayClient->getShopConfig()
        );

        return $this->JpayClient->createPurchase($purchase->generateData(), $orderId);
    }

    public function payment_callback() {
        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            $orderId= isset($_GET['orderid']) ? $_GET['orderid'] : '';
            if ($orderId == '') {
              return;
            }

            $body = file_get_contents('php://input');
            $DecodeBody=urldecode($body);
            parse_str($DecodeBody,$bodyArray);
            $JsonDecodeBody = json_decode($bodyArray['transactionEvents'], true);

            $callbackHandler = new WC_JumiaPay_Callback(wc_get_order($orderId));
            $success = $callbackHandler->handle($JsonDecodeBody[0]['newStatus']);

            if ($success) {
                wp_send_json(['success' => true], 200);
            } else {
                wp_send_json(['success' => false, 'payload' => 'Wrong Order Status for this callback'], 400);
            }
        }
    }


    public function payment_return() {

        $orderId= isset($_GET['orderid']) ? $_GET['orderid'] : '';
        if ($orderId == '') {
            return;
        }

        $paymentStatus= isset($_GET['paymentStatus']) ? $_GET['paymentStatus'] : '';
        $order = wc_get_order($orderId);

        if($paymentStatus=='failure'){
            wc_add_notice('Payment Cancelled', 'error');
            if (wp_safe_redirect(wc_get_page_permalink('cart'))) {
              exit;
            }
        }

        if($paymentStatus=='success'){
            if (wp_safe_redirect($this->get_return_url($order))) {
              exit;
            }
        }
    }

    public function process_refund($orderId, $amount = null, $reason = '' ) {

        $order = wc_get_order($orderId);

        $refund = new WC_JumiaPay_Refund(
            $order,
            $amount,
            get_woocommerce_currency(),
            $this->JpayClient->getShopConfig()
        );

        $result = $this->JpayClient->createRefund($refund->generateData());

        if (isset($result['note'])) {
          $order->add_order_note($result['note'], true);
        }

        return $result['success'] ;
    }

    public function order_cancelled($orderId, $oldStatus, $newStatus){
        $order = wc_get_order($orderId);

        if($newStatus == 'cancelled'){

            $merchantReferenceId=get_post_meta($orderId, '_purchaseId',true);
            if ($merchantReferenceId != '') {
                $result = $this->JpayClient->cancelPurchase($merchantReferenceId, $order);

                if (isset($result['note'])) {
                  $order->add_order_note($result['note'], true);
                }

                return $result['success'] ;
            }

        }

        return false;
    }
}
