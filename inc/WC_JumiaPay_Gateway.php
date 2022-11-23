<?php

/*
 * JumiaPay Gateway
 */
if (!defined('ABSPATH')) {
  exit;
}

require_once JPAY_DIR . 'inc/WC_JumiaPay_Callback.php';
require_once JPAY_DIR . 'inc/WC_JumiaPay_Client.php';
require_once JPAY_DIR . 'inc/WC_JumiaPay_Purchase.php';
require_once JPAY_DIR . 'inc/WC_JumiaPay_Refund.php';
require_once JPAY_DIR . 'inc/validators/WC_JumiaPay_Validators.php';

class WC_JumiaPay_Gateway extends WC_Payment_Gateway
{


  public $JpayClient;

  /*
     * plugin construct which contain :
     * main (variablues , methods , functions, settings in the admin , web hook)
     */
  public function __construct()
  {
    //plugin main settings for the admin and check out page
    $this->id   = 'jumia-pay';

    $country = $this->get_option('environment') == 'Live' ? $this->get_option('country_list') : $this->get_option('sandbox_country_list');
    $image_url = '';
    
    switch ($country) {
      case 'EG':
        $image_url = '/assets/image/secured_payfac_eg.png';
        break;
      case 'KE':
        $image_url = '/assets/image/secured_payfac_kenya.png';
        break;
      case 'NG':
        $image_url = '/assets/image/secured_payfac_ng.png';
        break;
      case 'CI':
        $image_url = '/assets/image/secured_payfac_ic.png';
        break;
      default:
        $image_url = '/assets/image/secured_payfac_not-country-specific.png';
    }

    $this->icon = apply_filters('woocommerce_jumiaPay_icon', plugins_url($image_url, dirname(__FILE__)));
    $this->has_fields = true;

    $this->method_title =  esc_html('JumiaPay');
    $this->method_description = esc_html('JumiaPay for WooCommerce - Payment Gateway Get additional business with JumiaPay. JumiaPay does not only avail local and international payments methods but also bring you millions of users in your country');

    $this->title = esc_html('JumiaPay');
    $this->description = esc_html('Pay securely with JumiaPay');
    $this->instructions = $this->get_option('instructions', $this->description);

    $JpayClient = new WC_JumiaPay_Client(
      $this->get_option('environment'),
      $this->get_option('country_list'),
      $this->get_option('shop_config_key'),
      $this->get_option('shop_config_id'),
      $this->get_option('api_key'),
      $this->get_option('sandbox_country_list'),
      $this->get_option('sandbox_shop_config_key'),
      $this->get_option('sandbox_shop_config_id'),
      $this->get_option('sandbox_api_key'),
      JPAY_PLUGIN_VERSION
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
    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

    //action hook for the payment return
    add_action('woocommerce_api_payment_return', array($this, 'payment_return'));

    //action hook for the payment callback
    add_action('woocommerce_api_payment_callback', array($this, 'payment_callback'));
  }

  //settings fields function
  public function init_form_fields()
  {
    $this->form_fields = include dirname(__FILE__) . '/settings/settings.php';
  }

  /**
   * It creates a purchase object and sends it to the JumiaPay API.
   * 
   * @param orderId The order ID of the order that is being processed.
   * 
   * @return The return value is an array with the following structure:
   * ```
   * array(
   *   'result' => 'success',
   *   'redirect' => 'https://pay.jumia.com/checkout/{checkoutId}'
   * )
   * ```
   */
  public function process_payment($orderId)
  {
    $lang = explode('-', get_bloginfo('language'));
    $lang = $lang[0];
    $purchase = new WC_JumiaPay_Purchase(
      wc_get_order($orderId),
      $this->JpayClient->getCountryCode(),
      $lang,
      get_home_url(),
      get_woocommerce_currency()
    );

    return $this->JpayClient->createPurchase($purchase->generateData(), $orderId);
  }

  /**
   * It receives a POST request from JumiaPay, extracts the order ID from the request, and then uses
   * the order ID to get the order from the database
   * 
   * @return The callback is returning a json object with a success property.
   */
  public function payment_callback()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $orderId = filter_input(INPUT_GET, 'orderid', FILTER_SANITIZE_ENCODED);
      if ($orderId == '' || $orderId == false || $orderId == null) {
        return;
      }

      $body = file_get_contents('php://input');
      $DecodeBody = urldecode($body);
      parse_str($DecodeBody, $bodyArray);
      $JsonDecodeBody = json_decode($bodyArray['transactionEvents'], true);
      
      if (!isset($JsonDecodeBody[0]['newStatus'])) {
        wp_send_json(['success' => false, 'payload' => 'Wrong Paylod received'], 400);
        return;
      }

      $callbackHandler = new WC_JumiaPay_Callback(wc_get_order($orderId));
      $success = $callbackHandler->handle($JsonDecodeBody[0]['newStatus']);

      if ($success) {
        wp_send_json(['success' => true], 200);
      } else {
        wp_send_json(['success' => false, 'payload' => 'Wrong Order Status for this callback'], 400);
      }
    }
  }


  /**
   * It checks if the payment status is success, failure or empty. If it's empty, it redirects to the
   * cart page. If it's failure, it redirects to the cart page. If it's success, it redirects to the
   * order received page
   * 
   * @return The payment status is being returned.
   */
  public function payment_return()
  {
    $orderId = filter_input(INPUT_GET, 'orderid', FILTER_SANITIZE_ENCODED);
    if ($orderId == '' || $orderId == false || $orderId == null) {
      return;
    }

    $paymentStatus = WC_JumiaPay_Validator::ValidatePaymentStatus(filter_input(INPUT_GET, 'paymentStatus', FILTER_SANITIZE_ENCODED));
    $order = wc_get_order($orderId);

    if ($paymentStatus == '' || $paymentStatus == false || $paymentStatus == null) {
      wc_add_notice('Payment Failed', 'error');
      if (wp_safe_redirect(wc_get_page_permalink('cart'))) {
        exit;
      }
    }

    if ($paymentStatus == 'failure') {
      wc_add_notice('Payment Cancelled', 'error');
      if (wp_safe_redirect(wc_get_page_permalink('cart'))) {
        exit;
      }
    }

    if ($paymentStatus == 'success') {
      if (wp_safe_redirect($this->get_return_url($order))) {
        exit;
      }
    }
  }

  /**
   * A function that is called when a refund is made on the order.
   * 
   * @param orderId The order ID to refund.
   * @param amount The amount to refund. If not set, the entire order will be refunded.
   * @param reason The reason for the refund.
   * 
   * @return The return value is a boolean value.
   */
  public function process_refund($orderId, $amount = null, $reason = '')
  {

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

    return $result['success'];
  }

  /**
   * If the order status is changed to cancelled, then cancel the purchase in JPay
   * 
   * @param orderId The order ID
   * @param oldStatus The status of the order before the change.
   * @param newStatus The new status of the order.
   * 
   * @return The return value is a boolean.
   */
  public function order_cancelled($orderId, $oldStatus, $newStatus)
  {
    $order = wc_get_order($orderId);

    if ($newStatus == 'cancelled') {

      $merchantReferenceId = get_post_meta($orderId, '_purchaseId', true);
      if ($merchantReferenceId != '') {
        $result = $this->JpayClient->cancelPurchase($merchantReferenceId, $order);

        if (isset($result['note'])) {
          $order->add_order_note($result['note'], true);
        }

        return $result['success'];
      }
    }

    return false;
  }
}
