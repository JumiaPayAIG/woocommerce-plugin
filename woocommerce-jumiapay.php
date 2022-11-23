<?php

/**
 * Plugin Name: JumiaPay For Woocommerce
 * Plugin URI: https://github.com/JumiaPayAIG/woocommerce-plugin
 * Author Name: Pharaoh Soft
 * Author URI: http://www.pharaohsoft.com/
 * Description: JumiaPay for WooCommerce - Payment Gateway Get additional business with JumiaPay. JumiaPay does not only avail local and international payments methods but also bring you millions of users in your country
 * Version: 2.0.3
 * Licence: Apache-2.0 License
 * Licence URI: https://github.com/JumiaPayAIG/woocommerce-plugin/blob/master/LICENSE
 */

/**
 * check if the plugin from access from the admin or external method
 */
if (!defined('ABSPATH')) {
  exit;
}

if (!defined('JPAY_DIR')) {
  define('JPAY_DIR', plugin_dir_path(__FILE__));
}

// Get the plugin data, by parsing the metadata in this file header.
// The performance impacts should be negligible for such a small plugin and considering everything that WP already does for other requests.
// If this becomes a problem, we should simply move this to a static constant on the code.
// See: https://wordpress.stackexchange.com/questions/361/is-there-a-way-for-a-plug-in-to-get-its-own-version-number/367#367
$pluginData = get_file_data(__FILE__, array('Version' => 'Version'), false);

define('JPAY_PLUGIN_VERSION', $pluginData['Version']);

/**
 * check for the woocommerce plugin
 */
$muplugins = get_site_option('active_sitewide_plugins');
$plugins = get_option('active_plugins');
$mandatoryplugin = 'woocommerce/woocommerce.php';

$mandatoryplugin_exist = in_array($mandatoryplugin, apply_filters('active_plugins', $plugins));
$mandatoryplugin_exist_network = isset($muplugins[$mandatoryplugin]);

if (!$mandatoryplugin_exist && !$mandatoryplugin_exist_network) return;

//initiate plugin action
add_action('plugins_loaded', 'init_jumiaPay_gateway_class', 0);

//plugin main class
function init_jumiaPay_gateway_class()
{
  if (!class_exists('WC_Payment_Gateway')) {
    return;
  }

  require_once JPAY_DIR . 'inc/WC_JumiaPay_Gateway.php';

  $wc = new WC_JumiaPay_Gateway();

  function add_jumiaPay_gateway_class($methods)
  {
    $methods[] = 'WC_JumiaPay_Gateway';
    return $methods;
  }

  function customer_order_cancelled($orderId, $oldStatus, $newStatus)
  {
    $gateway = new WC_JumiaPay_Gateway();
    $gateway->order_cancelled($orderId, $oldStatus, $newStatus);
  }

  add_filter('woocommerce_payment_gateways', 'add_jumiaPay_gateway_class');
  add_filter('woocommerce_order_status_changed', 'customer_order_cancelled', 10, 3);
}
