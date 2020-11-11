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


            /*
             * create the payment order function
             * contain the create api
            */
            public function process_payment($order_id)
            {
                global $woocommerce;

                $logger = wc_get_logger();

                // add to woocommerce log file the order id
                $logger->info( wc_print_r( 'order id = '.$order_id, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );


                // add to woocommerce log file the order id
                $logger->info( wc_print_r( 'site url = '.get_site_url(), true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );


                // add to woocommerce log file the order id
                $logger->info( wc_print_r( 'home url = '.get_home_url(), true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                $date = date_create();
                $newDate=date_format($date, 'U');
                $merchantReferenceId="purchasereferenceId".$order_id.$newDate;

                // add to woocommerce log file the merchant Reference Id
                $logger->info( wc_print_r( 'merchant Reference Id before the preg function ( this id contain fix string + the order id )= '.$merchantReferenceId, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                $merchantReferenceId = str_replace(' ', '', $merchantReferenceId); // Replaces all spaces with hyphens.
                $merchantReferenceId = preg_replace('/[^A-Za-z0-9\-]/', '', $merchantReferenceId); // Removes special chars.

                if(strlen($merchantReferenceId) > 255){
                    $merchantReferenceId= substr($merchantReferenceId,0,250);
                }

                // add to woocommerce log file the merchant Reference Id
                $logger->info( wc_print_r( 'the last merchant Reference Id ( this id generate after remove any special characters and after the length check to be less 255 characters )= '.$merchantReferenceId, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );


                // we need it to get any order detailes
                $order = wc_get_order($order_id);

                // add to woocommerce log file the order array before trigger the api
                $logger->info( wc_print_r( 'the order array before trigger the api = '.$order, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );


                /*
                 * switch case to get country code and country api
                 */
                if($this->environment=="Live"){
                    // add to woocommerce log file the live environment
                    $logger->info( wc_print_r( 'the environment ( live ) = '.$this->environment, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                    switch ($this->country_code) {
                        case "Egypt":
                            $countryCode='EG';
                            $countryEndPoint='https://api-pay.jumia.com.eg/';
                            // add to woocommerce log file the country code ( EG ) ( Live )
                            $logger->info( wc_print_r( 'the country code ( EG ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( EG ) ( Live )
                            $logger->info( wc_print_r( 'the country end point ( EG ) ( Live ) = '.$countryEndPoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Ghana":
                            $countryCode='GH';
                            $countryEndPoint='https://api-pay.jumia.com.gh/';

                            // add to woocommerce log file the country code ( GH ) ( Live )
                            $logger->info( wc_print_r( 'the country code ( GH ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( GH ) ( Live )
                            $logger->info( wc_print_r( 'the country end point ( GH ) ( Live ) = '.$countryEndPoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Ivory-Coast":
                            $countryCode='CI';
                            $countryEndPoint='https://api-pay.jumia.ci/';

                            // add to woocommerce log file the country code ( EG ) ( Live )
                            $logger->info( wc_print_r( 'the country code ( CI ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( CI ) ( Live )
                            $logger->info( wc_print_r( 'the country end point ( CI ) ( Live ) = '.$countryEndPoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Kenya":
                            $countryCode='KE';
                            $countryEndPoint='https://api-pay.jumia.co.ke/';

                            // add to woocommerce log file the country code ( KE ) ( Live )
                            $logger->info( wc_print_r( 'the country code ( KE ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( KE ) ( Live )
                            $logger->info( wc_print_r( 'the country end point ( KE ) ( Live ) = '.$countryEndPoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Morocco":
                            $countryCode='MA';
                            $countryEndPoint='https://api-pay.jumia.ma/';

                            // add to woocommerce log file the country code ( MA ) ( Live )
                            $logger->info( wc_print_r( 'the country code ( MA ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( MA ) ( Live )
                            $logger->info( wc_print_r( 'the country end point ( MA ) ( Live ) = '.$countryEndPoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Nigeria":
                            $countryCode='NG';
                            $countryEndPoint='https://api-pay.jumia.com.ng/';

                            // add to woocommerce log file the country code ( NG ) ( Live )
                            $logger->info( wc_print_r( 'the country code ( NG ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( NG ) ( Live )
                            $logger->info( wc_print_r( 'the country end point ( NG ) ( Live ) = '.$countryEndPoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Tunisia":
                            $countryCode='TN';
                            $countryEndPoint='https://api-pay.jumia.com.tn/';

                            // add to woocommerce log file the country code ( TN ) ( Live )
                            $logger->info( wc_print_r( 'the country code ( TN ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( TN ) ( Live )
                            $logger->info( wc_print_r( 'the country end point ( TN ) ( Live ) = '.$countryEndPoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Uganda":
                            $countryCode='UG';
                            $countryEndPoint='https://api-pay.jumia.ug/';

                            // add to woocommerce log file the country code ( UG ) ( Live )
                            $logger->info( wc_print_r( 'the country code ( UG ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( UG ) ( Live )
                            $logger->info( wc_print_r( 'the country end point ( UG ) ( Live ) = '.$countryEndPoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;

                    }
                    $shop_config_key=$this->shop_config_key;
                    $api_key=$this->api_key;

                    // add to woocommerce log file the Shop Api Key = $shop_config_key ( my Shop Api Key )
                    $logger->info( wc_print_r( 'the Shop Api Key = $shop_config_key ( my Shop Api Key ) ( live )  = '.$shop_config_key, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                    // add to woocommerce log file the Shop Api Key = $api_key ( my Merchant Api Key )
                    $logger->info( wc_print_r( 'the the Merchant Api Key = $api_key ( my Merchant Api Key ) ( live )  = '.$api_key, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                }
                if($this->environment=="Sandbox"){
                    // add to woocommerce log file the live environment
                    $logger->info( wc_print_r( 'the environment ( Sandbox ) = '.$this->environment, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                    switch ($this->sandbox_country_code) {
                        case "Egypt":
                            $countryCode='EG';
                            $countryEndPoint='https://api-sandbox-pay.jumia.com.eg/';
                            // add to woocommerce log file the country code ( EG ) ( sandbox )
                            $logger->info( wc_print_r( 'the country code ( EG ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( EG ) ( sandbox )
                            $logger->info( wc_print_r( 'the country end point ( EG ) ( sandbox ) = '.$countryEndPoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Ghana":
                            $countryCode='GH';
                            $countryEndPoint='https://api-sandbox-pay.jumia.com.gh/';

                            // add to woocommerce log file the country code ( GH ) ( sandbox )
                            $logger->info( wc_print_r( 'the country code ( GH ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( GH ) ( sandbox )
                            $logger->info( wc_print_r( 'the country end point ( GH ) ( sandbox ) = '.$countryEndPoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Ivory-Coast":
                            $countryCode='CI';
                            $countryEndPoint='https://api-sandbox-pay.jumia.ci/';

                            // add to woocommerce log file the country code ( EG ) ( sandbox )
                            $logger->info( wc_print_r( 'the country code ( CI ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( CI ) ( sandbox )
                            $logger->info( wc_print_r( 'the country end point ( CI ) ( sandbox ) = '.$countryEndPoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Kenya":
                            $countryCode='KE';
                            $countryEndPoint='https://api-sandbox-pay.jumia.co.ke/';

                            // add to woocommerce log file the country code ( KE ) ( sandbox )
                            $logger->info( wc_print_r( 'the country code ( KE ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( KE ) ( sandbox )
                            $logger->info( wc_print_r( 'the country end point ( KE ) ( sandbox ) = '.$countryEndPoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Morocco":
                            $countryCode='MA';
                            $countryEndPoint='https://api-sandbox-pay.jumia.ma/';

                            // add to woocommerce log file the country code ( MA ) ( sandbox )
                            $logger->info( wc_print_r( 'the country code ( MA ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( MA ) ( sandbox )
                            $logger->info( wc_print_r( 'the country end point ( MA ) ( sandbox ) = '.$countryEndPoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Nigeria":
                            $countryCode='NG';
                            $countryEndPoint='https://api-sandbox-pay.jumia.com.ng/';

                            // add to woocommerce log file the country code ( NG ) ( sandbox )
                            $logger->info( wc_print_r( 'the country code ( NG ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( NG ) ( sandbox )
                            $logger->info( wc_print_r( 'the country end point ( NG ) ( sandbox ) = '.$countryEndPoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Tunisia":
                            $countryCode='TN';
                            $countryEndPoint='https://api-sandbox-pay.jumia.com.tn/';

                            // add to woocommerce log file the country code ( TN ) ( sandbox )
                            $logger->info( wc_print_r( 'the country code ( TN ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( TN ) ( sandbox )
                            $logger->info( wc_print_r( 'the country end point ( TN ) ( sandbox ) = '.$countryEndPoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Uganda":
                            $countryCode='UG';
                            $countryEndPoint='https://api-sandbox-pay.jumia.ug/';

                            // add to woocommerce log file the country code ( UG ) ( sandbox )
                            $logger->info( wc_print_r( 'the country code ( UG ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( UG ) ( sandbox )
                            $logger->info( wc_print_r( 'the country end point ( UG ) ( sandbox ) = '.$countryEndPoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;

                    }
                    $shop_config_key=$this->sandbox_shop_config_key;
                    $api_key=$this->sandbox_api_key;

                    // add to woocommerce log file the Shop Api Key = $shop_config_key ( my Shop Api Key ) ( sandbox )
                    $logger->info( wc_print_r( 'the Shop Api Key = $shop_config_key ( my Shop Api Key ) ( sandbox )  = '.$shop_config_key, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                    // add to woocommerce log file the Shop Api Key = $api_key ( my Merchant Api Key )( sandbox )
                    $logger->info( wc_print_r( 'the Merchant Api Key = $api_key ( my Merchant Api Key ) ( sandbox )  = '.$api_key, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                }


                // start the create api
                $shop_currency =get_woocommerce_currency();

                // add to woocommerce log file the shop currency
                $logger->info( wc_print_r( 'the shop currency = '.$shop_currency, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );


                $create_endpoint = $countryEndPoint.'/merchant/create';

                // add to woocommerce log file the create end point
                $logger->info( wc_print_r( 'the create end point = '.$create_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );


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



                $data = wp_json_encode( $data );

                // add to woocommerce log file the data array
                $logger->info( wc_print_r( 'the data array = '.$data, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );


                /*
                * the options array contain the header for the api
                */
                $options = [
                    'body'        => $data,
                    'headers'     => [
                        "apikey"=> $api_key,
                        "Content-Type"=> "application/json",
                    ],
                    'timeout'     => 0,
                    'redirection' => 5,
                    'blocking'    => true,
                    'httpversion' => '1.1',
                    'sslverify'   => false,
                    'data_format' => 'body',
                ];


                $myTestOptions = wp_json_encode( $options );

                // add to woocommerce log file the options array
                $logger->info( wc_print_r( 'the options array = '.$myTestOptions, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );


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

                        // add to woocommerce log file the response response status
                        $logger->info( wc_print_r( 'the response status = success', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                        // add to woocommerce log file the checkout Url
                        $logger->info( wc_print_r( 'the checkout Url = '.$checkoutUrl, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                        // add to woocommerce log file the purchase Id
                        $logger->info( wc_print_r( 'the purchasedId = '.$purchaseId, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );


                        //redirect to jumiaPay gateway
                        return array(
                            'result' => 'success',
                            'redirect' => $checkoutUrl
                        );
                    }
                    else {

                        //error for the bad requests like 400 and so on
                        wc_add_notice('Error payment failed case '.$body['payload'][0]['description'].' code-'.$body['payload'][0]['code'],'error');

                        // add to woocommerce log file the response response status
                        $logger->info( wc_print_r( 'the response status = fail', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                        // add to woocommerce log file the fail description
                        $logger->info( wc_print_r( 'the fail description = '.$body['payload'][0]['description'], true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                        // add to woocommerce log file the fail code
                        $logger->info( wc_print_r( 'the fail code = '.$body['payload'][0]['code'], true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );


                        WC()->cart->empty_cart();
                        return;
                    }

                }
                else {

                    //error for the connection with jumiaPay api that mean the api not trigger at all
                    wc_add_notice('Connection error please try again later.', 'error');

                    // add to woocommerce log file the fail description
                    $logger->info( wc_print_r( 'the fail description = Internal Server Error', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                    // add to woocommerce log file the fail code
                    $logger->info( wc_print_r( 'the fail code = 500', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );


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
                $orderid=$_GET['orderid'];
                $paymentStatus=$_GET['paymentStatus'];
                $order = wc_get_order($orderid);
                if($paymentStatus=='failure'){
                    $order->update_status('cancelled', 'woocommerce' );
                    wc_add_notice('Order Cancelled.', 'error');
                    wp_safe_redirect(wc_get_page_permalink('cart'));

                    // add to woocommerce log file the payment status ( failure ) ( Cancelled )
                    $logger->info( wc_print_r( 'the payment status ( failure ) ( Cancelled )', true ), array( 'source' => 'jumiaPay log file -'.$orderid ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$orderid ) );

                }
                if($paymentStatus=='success'){
                    $logger->info( wc_print_r( 'paymentStatus = '.$paymentStatus, true ), array( 'source' => 'jumiaPay log file -'.$orderid ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$orderid ) );

                    wp_safe_redirect($this->get_return_url( $order ));
                }
                if($_SERVER['REQUEST_METHOD'] === 'POST' && $paymentStatus!='failure'){

                    $body = file_get_contents('php://input');
                    $DecodeBody=urldecode($body);
                    parse_str($DecodeBody,$bodyArray);
                    $JsonDecodeBody = json_decode($bodyArray['transactionEvents'], true);

                    // save the transactionEvents for debugging purposes ( this will be removed in the production version )
                    update_post_meta($orderid,'bodyArray',$bodyArray);


                    $order = wc_get_order( $orderid );

                    if($order->get_status()!= 'cancelled'  &&  $order->get_status() !=  'processing' || $order->get_status() !='failed '){
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

                        $logger->info( wc_print_r( '$orderid'.$orderid, true ), array( 'source' => 'jumiaPay log file -'.$orderid ) );
                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$orderid ) );


                        $logger->info( wc_print_r( 'Decode Body array ='.$DecodeBody, true ), array( 'source' => 'jumiaPay log file -'.$orderid ) );
                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$orderid ) );
                        wp_send_json(['success' => true], 200);
                    }



                }

                if($paymentStatus!='failure' || $paymentStatus!='success'){
                    $order->update_status('cancelled', 'woocommerce' );
                    wc_add_notice('Order Cancelled.', 'error');
                    wp_safe_redirect(wc_get_page_permalink('cart'));

                    // add to woocommerce log file the payment status ( failure ) ( Cancelled )
                    $logger->info( wc_print_r( 'the payment status ( failure ) ( Cancelled )', true ), array( 'source' => 'jumiaPay log file -'.$orderid ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$orderid ) );

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

                // add to woocommerce log file the $refund_merchantReferenceId
                $logger->info( wc_print_r( 'the refund Reference Id = '.$refund_merchantReferenceId, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );


                // shop currency
                $shop_currency =get_woocommerce_currency();

                // add to woocommerce log file the refund shop currency
                $logger->info( wc_print_r( 'the refund shop currency = '.$shop_currency, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );


                // get the merchantReferenceId with saved before in the database
                $order_merchantReferenceId=get_post_meta( $order_id, '_merchantReferenceId',true );

                if($this->environment=="Live"){
                    // add to woocommerce log file the live environment
                    $logger->info( wc_print_r( 'the environment ( live ) = '.$this->environment, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                    switch ($this->country_code) {
                        case "Egypt":
                            $countryCode='EG';
                            $refund_endpoint='https://api-pay.jumia.com.eg/merchant/refund';
                            // add to woocommerce log file the country code ( EG ) ( Live )
                            $logger->info( wc_print_r( 'the country code ( EG ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( EG ) ( Live )
                            $logger->info( wc_print_r( 'the country end point ( EG ) ( Live ) = '.$refund_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Ghana":
                            $countryCode='GH';
                            $refund_endpoint='https://api-pay.jumia.com.gh/merchant/refund';

                            // add to woocommerce log file the country code ( GH ) ( Live )
                            $logger->info( wc_print_r( 'the country code ( GH ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( GH ) ( Live )
                            $logger->info( wc_print_r( 'the country end point ( GH ) ( Live ) = '.$refund_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Ivory-Coast":
                            $countryCode='CI';
                            $refund_endpoint='https://api-pay.jumia.ci/merchant/refund';

                            // add to woocommerce log file the country code ( EG ) ( Live )
                            $logger->info( wc_print_r( 'the country code ( CI ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( CI ) ( Live )
                            $logger->info( wc_print_r( 'the country end point ( CI ) ( Live ) = '.$refund_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Kenya":
                            $countryCode='KE';
                            $refund_endpoint='https://api-pay.jumia.co.ke/merchant/refund';

                            // add to woocommerce log file the country code ( KE ) ( Live )
                            $logger->info( wc_print_r( 'the country code ( KE ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( KE ) ( Live )
                            $logger->info( wc_print_r( 'the country end point ( KE ) ( Live ) = '.$refund_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Morocco":
                            $countryCode='MA';
                            $refund_endpoint='https://api-pay.jumia.ma/merchant/refund';

                            // add to woocommerce log file the country code ( MA ) ( Live )
                            $logger->info( wc_print_r( 'the country code ( MA ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( MA ) ( Live )
                            $logger->info( wc_print_r( 'the country end point ( MA ) ( Live ) = '.$refund_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Nigeria":
                            $countryCode='NG';
                            $refund_endpoint='https://api-pay.jumia.com.ng/merchant/refund';

                            // add to woocommerce log file the country code ( NG ) ( Live )
                            $logger->info( wc_print_r( 'the country code ( NG ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( NG ) ( Live )
                            $logger->info( wc_print_r( 'the country end point ( NG ) ( Live ) = '.$refund_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Tunisia":
                            $countryCode='TN';
                            $refund_endpoint='https://api-pay.jumia.com.tn/merchant/refund';

                            // add to woocommerce log file the country code ( TN ) ( Live )
                            $logger->info( wc_print_r( 'the country code ( TN ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( TN ) ( Live )
                            $logger->info( wc_print_r( 'the country end point ( TN ) ( Live ) = '.$refund_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Uganda":
                            $countryCode='UG';
                            $refund_endpoint='https://api-pay.jumia.ug/merchant/refund';

                            // add to woocommerce log file the country code ( UG ) ( Live )
                            $logger->info( wc_print_r( 'the country code ( UG ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( UG ) ( Live )
                            $logger->info( wc_print_r( 'the country end point ( UG ) ( Live ) = '.$refund_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;

                    }
                    $shop_config_key=$this->shop_config_key;
                    $api_key=$this->api_key;

                    // add to woocommerce log file the Shop Api Key = $shop_config_key ( my Shop Api Key )
                    $logger->info( wc_print_r( 'the Shop Api Key = $shop_config_key ( my Shop Api Key ) ( live )  = '.$shop_config_key, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                    // add to woocommerce log file the Shop Api Key = $api_key ( my Merchant Api Key )
                    $logger->info( wc_print_r( 'the the Merchant Api Key = $api_key ( my Merchant Api Key ) ( live )  = '.$api_key, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                }
                if($this->environment=="Sandbox"){
                    // add to woocommerce log file the live environment
                    $logger->info( wc_print_r( 'the environment ( Sandbox ) = '.$this->environment, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                    switch ($this->sandbox_country_code) {
                        case "Egypt":
                            $countryCode='EG';
                            $refund_endpoint='https://api-sandbox-pay.jumia.com.eg/merchant/refund';
                            // add to woocommerce log file the country code ( EG ) ( sandbox )
                            $logger->info( wc_print_r( 'the country code ( EG ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( EG ) ( sandbox )
                            $logger->info( wc_print_r( 'the country end point ( EG ) ( sandbox ) = '.$refund_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Ghana":
                            $countryCode='GH';
                            $refund_endpoint='https://api-sandbox-pay.jumia.com.gh/merchant/refund';

                            // add to woocommerce log file the country code ( GH ) ( sandbox )
                            $logger->info( wc_print_r( 'the country code ( GH ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( GH ) ( sandbox )
                            $logger->info( wc_print_r( 'the country end point ( GH ) ( sandbox ) = '.$refund_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Ivory-Coast":
                            $countryCode='CI';
                            $refund_endpoint='https://api-sandbox-pay.jumia.ci/merchant/refund';

                            // add to woocommerce log file the country code ( EG ) ( sandbox )
                            $logger->info( wc_print_r( 'the country code ( CI ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( CI ) ( sandbox )
                            $logger->info( wc_print_r( 'the country end point ( CI ) ( sandbox ) = '.$refund_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Kenya":
                            $countryCode='KE';
                            $refund_endpoint='https://api-sandbox-pay.jumia.co.ke/merchant/refund';

                            // add to woocommerce log file the country code ( KE ) ( sandbox )
                            $logger->info( wc_print_r( 'the country code ( KE ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( KE ) ( sandbox )
                            $logger->info( wc_print_r( 'the country end point ( KE ) ( sandbox ) = '.$refund_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Morocco":
                            $countryCode='MA';
                            $refund_endpoint='https://api-sandbox-pay.jumia.ma/merchant/refund';

                            // add to woocommerce log file the country code ( MA ) ( sandbox )
                            $logger->info( wc_print_r( 'the country code ( MA ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( MA ) ( sandbox )
                            $logger->info( wc_print_r( 'the country end point ( MA ) ( sandbox ) = '.$refund_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Nigeria":
                            $countryCode='NG';
                            $refund_endpoint='https://api-sandbox-pay.jumia.com.ng/merchant/refund';

                            // add to woocommerce log file the country code ( NG ) ( sandbox )
                            $logger->info( wc_print_r( 'the country code ( NG ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( NG ) ( sandbox )
                            $logger->info( wc_print_r( 'the country end point ( NG ) ( sandbox ) = '.$refund_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Tunisia":
                            $countryCode='TN';
                            $refund_endpoint='https://api-sandbox-pay.jumia.com.tn/merchant/refund';

                            // add to woocommerce log file the country code ( TN ) ( sandbox )
                            $logger->info( wc_print_r( 'the country code ( TN ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( TN ) ( sandbox )
                            $logger->info( wc_print_r( 'the country end point ( TN ) ( sandbox ) = '.$refund_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;
                        case "Uganda":
                            $countryCode='UG';
                            $refund_endpoint='https://api-sandbox-pay.jumia.ug/merchant/refund';

                            // add to woocommerce log file the country code ( UG ) ( sandbox )
                            $logger->info( wc_print_r( 'the country code ( UG ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            // add to woocommerce log file the country end point ( UG ) ( sandbox )
                            $logger->info( wc_print_r( 'the country end point ( UG ) ( sandbox ) = '.$refund_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            break;

                    }
                    $shop_config_key=$this->sandbox_shop_config_key;
                    $api_key=$this->sandbox_api_key;

                    // add to woocommerce log file the Shop Api Key = $shop_config_key ( my Shop Api Key ) ( sandbox )
                    $logger->info( wc_print_r( 'the Shop Api Key = $shop_config_key ( my Shop Api Key ) ( sandbox )  = '.$shop_config_key, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                    // add to woocommerce log file the Shop Api Key = $api_key ( my Merchant Api Key )( sandbox )
                    $logger->info( wc_print_r( 'the Merchant Api Key = $api_key ( my Merchant Api Key ) ( sandbox )  = '.$api_key, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                }
                
              
                
                // the data array
                $data = [
                    "shopConfig" => $shop_config_key,
                    "refundAmount" => $amount,
                    "refundCurrency" => $shop_currency,
                    "description" =>  "Refund for order #".$order_merchantReferenceId,
                    "purchaseReferenceId" => $order_merchantReferenceId,
                    "referenceId"=> $refund_merchantReferenceId
                ];

                $data = wp_json_encode( $data );

                // add to woocommerce log file the refund endpoint
                $logger->info( wc_print_r( 'the refund data= '.$data, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                // the options array
                $options = [
                    'body'        => $data,
                    'headers'     => [
                        "apikey"=> $api_key,
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
                        // add to woocommerce log file the refund endpoint
                        $logger->info( wc_print_r( 'the refund success ', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                        return true;


                    }
                    else{

                        // refund failed case bad request like 400 and so on
                        $order->add_order_note( 'order refunded failed', true );

                        // add to woocommerce log file the refund endpoint
                        $logger->info( wc_print_r( 'the refund failed ', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                        return false;


                    }

                }
                else {

                    // refund failed case connection error the api not trigger at all
                    $order->add_order_note( 'order refunded failed case connection error pls try again later', true );

                    // add to woocommerce log file the refund endpoint
                    $logger->info( wc_print_r( 'the refund failed the api not trigger at all', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                    return false;

                }
            }


            /*
            * cancel function
            * contain the cancel api
            */
            public function order_cancelled($order_id, $old_status, $new_status){

                $logger = wc_get_logger();

                $order = wc_get_order( $order_id );

                if($new_status=="cancelled"){

                    if($this->environment=="Live"){
                        // add to woocommerce log file the live environment
                        $logger->info( wc_print_r( 'the environment ( live ) = '.$this->environment, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                        switch ($this->country_code) {
                            case "Egypt":
                                $countryCode='EG';
                                $verify_endpoint='https://api-pay.jumia.com.eg/merchant/transaction-events';
                                // add to woocommerce log file the country code ( EG ) ( Live )
                                $logger->info( wc_print_r( 'the country code ( EG ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                // add to woocommerce log file the country end point ( EG ) ( Live )
                                $logger->info( wc_print_r( 'the country end point ( EG ) ( Live ) = '.$verify_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                break;
                            case "Ghana":
                                $countryCode='GH';
                                $verify_endpoint='https://api-pay.jumia.com.gh/merchant/transaction-events';

                                // add to woocommerce log file the country code ( GH ) ( Live )
                                $logger->info( wc_print_r( 'the country code ( GH ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                // add to woocommerce log file the country end point ( GH ) ( Live )
                                $logger->info( wc_print_r( 'the country end point ( GH ) ( Live ) = '.$verify_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                break;
                            case "Ivory-Coast":
                                $countryCode='CI';
                                $verify_endpoint='https://api-pay.jumia.ci/merchant/transaction-events';

                                // add to woocommerce log file the country code ( EG ) ( Live )
                                $logger->info( wc_print_r( 'the country code ( CI ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                // add to woocommerce log file the country end point ( CI ) ( Live )
                                $logger->info( wc_print_r( 'the country end point ( CI ) ( Live ) = '.$verify_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                break;
                            case "Kenya":
                                $countryCode='KE';
                                $verify_endpoint='https://api-pay.jumia.co.ke/merchant/transaction-events';

                                // add to woocommerce log file the country code ( KE ) ( Live )
                                $logger->info( wc_print_r( 'the country code ( KE ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                // add to woocommerce log file the country end point ( KE ) ( Live )
                                $logger->info( wc_print_r( 'the country end point ( KE ) ( Live ) = '.$verify_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                break;
                            case "Morocco":
                                $countryCode='MA';
                                $verify_endpoint='https://api-pay.jumia.ma/merchant/transaction-events';

                                // add to woocommerce log file the country code ( MA ) ( Live )
                                $logger->info( wc_print_r( 'the country code ( MA ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                // add to woocommerce log file the country end point ( MA ) ( Live )
                                $logger->info( wc_print_r( 'the country end point ( MA ) ( Live ) = '.$verify_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                break;
                            case "Nigeria":
                                $countryCode='NG';
                                $verify_endpoint='https://api-pay.jumia.com.ng/merchant/transaction-events';

                                // add to woocommerce log file the country code ( NG ) ( Live )
                                $logger->info( wc_print_r( 'the country code ( NG ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                // add to woocommerce log file the country end point ( NG ) ( Live )
                                $logger->info( wc_print_r( 'the country end point ( NG ) ( Live ) = '.$verify_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                break;
                            case "Tunisia":
                                $countryCode='TN';
                                $verify_endpoint='https://api-pay.jumia.com.tn/merchant/transaction-events';

                                // add to woocommerce log file the country code ( TN ) ( Live )
                                $logger->info( wc_print_r( 'the country code ( TN ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                // add to woocommerce log file the country end point ( TN ) ( Live )
                                $logger->info( wc_print_r( 'the country end point ( TN ) ( Live ) = '.$verify_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                break;
                            case "Uganda":
                                $countryCode='UG';
                                $verify_endpoint='https://api-pay.jumia.ug/merchant/transaction-events';

                                // add to woocommerce log file the country code ( UG ) ( Live )
                                $logger->info( wc_print_r( 'the country code ( UG ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                // add to woocommerce log file the country end point ( UG ) ( Live )
                                $logger->info( wc_print_r( 'the country end point ( UG ) ( Live ) = '.$verify_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                break;

                        }
                        $shop_config_key=$this->shop_config_key;
                        $api_key=$this->api_key;

                        // add to woocommerce log file the Shop Api Key = $shop_config_key ( my Shop Api Key )
                        $logger->info( wc_print_r( 'the Shop Api Key = $shop_config_key ( my Shop Api Key ) ( live )  = '.$shop_config_key, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                        // add to woocommerce log file the Shop Api Key = $api_key ( my Merchant Api Key )
                        $logger->info( wc_print_r( 'the the Merchant Api Key = $api_key ( my Merchant Api Key ) ( live )  = '.$api_key, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                    }
                    if($this->environment=="Sandbox"){
                        // add to woocommerce log file the live environment
                        $logger->info( wc_print_r( 'the environment ( Sandbox ) = '.$this->environment, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                        switch ($this->sandbox_country_code) {
                            case "Egypt":
                                $countryCode='EG';
                                $verify_endpoint='https://api-sandbox-pay.jumia.com.eg/merchant/transaction-events';
                                // add to woocommerce log file the country code ( EG ) ( sandbox )
                                $logger->info( wc_print_r( 'the country code ( EG ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                // add to woocommerce log file the country end point ( EG ) ( sandbox )
                                $logger->info( wc_print_r( 'the country end point ( EG ) ( sandbox ) = '.$verify_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                break;
                            case "Ghana":
                                $countryCode='GH';
                                $verify_endpoint='https://api-sandbox-pay.jumia.com.gh/merchant/transaction-events';

                                // add to woocommerce log file the country code ( GH ) ( sandbox )
                                $logger->info( wc_print_r( 'the country code ( GH ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                // add to woocommerce log file the country end point ( GH ) ( sandbox )
                                $logger->info( wc_print_r( 'the country end point ( GH ) ( sandbox ) = '.$verify_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                break;
                            case "Ivory-Coast":
                                $countryCode='CI';
                                $verify_endpoint='https://api-sandbox-pay.jumia.ci/merchant/transaction-events';

                                // add to woocommerce log file the country code ( EG ) ( sandbox )
                                $logger->info( wc_print_r( 'the country code ( CI ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                // add to woocommerce log file the country end point ( CI ) ( sandbox )
                                $logger->info( wc_print_r( 'the country end point ( CI ) ( sandbox ) = '.$verify_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                break;
                            case "Kenya":
                                $countryCode='KE';
                                $verify_endpoint='https://api-sandbox-pay.jumia.co.ke/merchant/transaction-events';

                                // add to woocommerce log file the country code ( KE ) ( sandbox )
                                $logger->info( wc_print_r( 'the country code ( KE ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                // add to woocommerce log file the country end point ( KE ) ( sandbox )
                                $logger->info( wc_print_r( 'the country end point ( KE ) ( sandbox ) = '.$verify_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                break;
                            case "Morocco":
                                $countryCode='MA';
                                $verify_endpoint='https://api-sandbox-pay.jumia.ma/merchant/transaction-events';

                                // add to woocommerce log file the country code ( MA ) ( sandbox )
                                $logger->info( wc_print_r( 'the country code ( MA ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                // add to woocommerce log file the country end point ( MA ) ( sandbox )
                                $logger->info( wc_print_r( 'the country end point ( MA ) ( sandbox ) = '.$verify_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                break;
                            case "Nigeria":
                                $countryCode='NG';
                                $verify_endpoint='https://api-sandbox-pay.jumia.com.ng/merchant/transaction-events';

                                // add to woocommerce log file the country code ( NG ) ( sandbox )
                                $logger->info( wc_print_r( 'the country code ( NG ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                // add to woocommerce log file the country end point ( NG ) ( sandbox )
                                $logger->info( wc_print_r( 'the country end point ( NG ) ( sandbox ) = '.$verify_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                break;
                            case "Tunisia":
                                $countryCode='TN';
                                $verify_endpoint='https://api-sandbox-pay.jumia.com.tn/merchant/transaction-events';

                                // add to woocommerce log file the country code ( TN ) ( sandbox )
                                $logger->info( wc_print_r( 'the country code ( TN ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                // add to woocommerce log file the country end point ( TN ) ( sandbox )
                                $logger->info( wc_print_r( 'the country end point ( TN ) ( sandbox ) = '.$verify_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                break;
                            case "Uganda":
                                $countryCode='UG';
                                $verify_endpoint='https://api-sandbox-pay.jumia.ug/merchant/transaction-events';

                                // add to woocommerce log file the country code ( UG ) ( sandbox )
                                $logger->info( wc_print_r( 'the country code ( UG ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                // add to woocommerce log file the country end point ( UG ) ( sandbox )
                                $logger->info( wc_print_r( 'the country end point ( UG ) ( sandbox ) = '.$verify_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                break;

                        }
                        $shop_config_key=$this->sandbox_shop_config_key;
                        $api_key=$this->sandbox_api_key;

                        // add to woocommerce log file the Shop Api Key = $shop_config_key ( my Shop Api Key ) ( sandbox )
                        $logger->info( wc_print_r( 'the Shop Api Key = $shop_config_key ( my Shop Api Key ) ( sandbox )  = '.$shop_config_key, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                        // add to woocommerce log file the Shop Api Key = $api_key ( my Merchant Api Key )( sandbox )
                        $logger->info( wc_print_r( 'the Merchant Api Key = $api_key ( my Merchant Api Key ) ( sandbox )  = '.$api_key, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                    }
                   
                    $order_merchantReferenceId=get_post_meta( $order_id, '_purchaseId',true );
                    $data = [
                        "shopConfig" => $shop_config_key,
                        "transactionId" => $order_merchantReferenceId,
                        "transactionType" => "Purchase",

                    ];

                    $data = wp_json_encode( $data );

                    // add to woocommerce log file the refund endpoint
                    $logger->info( wc_print_r( 'the verify data= '.$data, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                    $options = [
                        'body'        => $data,
                        'headers'     => [
                            "apikey"=> $api_key,
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

                            // add to woocommerce log file the refund endpoint
                            $logger->info( wc_print_r( 'the verify status = success', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            $purchase_status=$verify_body['payload'][0]['newStatus'];

                            // add to woocommerce log file the refund endpoint
                            $logger->info( wc_print_r( 'the verify purchase status = '.$purchase_status, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            if($purchase_status=="Created" || $purchase_status=="Confirmed" || $purchase_status=="Committed"){


                                if($this->environment=="Live"){
                                    // add to woocommerce log file the live environment
                                    $logger->info( wc_print_r( 'the environment ( live ) = '.$this->environment, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                    switch ($this->country_code) {
                                        case "Egypt":
                                            $countryCode='EG';
                                            $cancel_endpoint='https://api-pay.jumia.com.eg/merchant/cancel';
                                            // add to woocommerce log file the country code ( EG ) ( Live )
                                            $logger->info( wc_print_r( 'the country code ( EG ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            // add to woocommerce log file the country end point ( EG ) ( Live )
                                            $logger->info( wc_print_r( 'the country end point ( EG ) ( Live ) = '.$cancel_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            break;
                                        case "Ghana":
                                            $countryCode='GH';
                                            $cancel_endpoint='https://api-pay.jumia.com.gh/merchant/cancel';

                                            // add to woocommerce log file the country code ( GH ) ( Live )
                                            $logger->info( wc_print_r( 'the country code ( GH ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            // add to woocommerce log file the country end point ( GH ) ( Live )
                                            $logger->info( wc_print_r( 'the country end point ( GH ) ( Live ) = '.$cancel_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            break;
                                        case "Ivory-Coast":
                                            $countryCode='CI';
                                            $cancel_endpoint='https://api-pay.jumia.ci/merchant/cancel';

                                            // add to woocommerce log file the country code ( EG ) ( Live )
                                            $logger->info( wc_print_r( 'the country code ( CI ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            // add to woocommerce log file the country end point ( CI ) ( Live )
                                            $logger->info( wc_print_r( 'the country end point ( CI ) ( Live ) = '.$cancel_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            break;
                                        case "Kenya":
                                            $countryCode='KE';
                                            $cancel_endpoint='https://api-pay.jumia.co.ke/merchant/cancel';

                                            // add to woocommerce log file the country code ( KE ) ( Live )
                                            $logger->info( wc_print_r( 'the country code ( KE ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            // add to woocommerce log file the country end point ( KE ) ( Live )
                                            $logger->info( wc_print_r( 'the country end point ( KE ) ( Live ) = '.$cancel_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            break;
                                        case "Morocco":
                                            $countryCode='MA';
                                            $cancel_endpoint='https://api-pay.jumia.ma/merchant/cancel';

                                            // add to woocommerce log file the country code ( MA ) ( Live )
                                            $logger->info( wc_print_r( 'the country code ( MA ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            // add to woocommerce log file the country end point ( MA ) ( Live )
                                            $logger->info( wc_print_r( 'the country end point ( MA ) ( Live ) = '.$cancel_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            break;
                                        case "Nigeria":
                                            $countryCode='NG';
                                            $cancel_endpoint='https://api-pay.jumia.com.ng/merchant/cancel';

                                            // add to woocommerce log file the country code ( NG ) ( Live )
                                            $logger->info( wc_print_r( 'the country code ( NG ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            // add to woocommerce log file the country end point ( NG ) ( Live )
                                            $logger->info( wc_print_r( 'the country end point ( NG ) ( Live ) = '.$cancel_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            break;
                                        case "Tunisia":
                                            $countryCode='TN';
                                            $cancel_endpoint='https://api-pay.jumia.com.tn/merchant/cancel';

                                            // add to woocommerce log file the country code ( TN ) ( Live )
                                            $logger->info( wc_print_r( 'the country code ( TN ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            // add to woocommerce log file the country end point ( TN ) ( Live )
                                            $logger->info( wc_print_r( 'the country end point ( TN ) ( Live ) = '.$cancel_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            break;
                                        case "Uganda":
                                            $countryCode='UG';
                                            $cancel_endpoint='https://api-pay.jumia.ug/merchant/cancel';

                                            // add to woocommerce log file the country code ( UG ) ( Live )
                                            $logger->info( wc_print_r( 'the country code ( UG ) ( Live ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            // add to woocommerce log file the country end point ( UG ) ( Live )
                                            $logger->info( wc_print_r( 'the country end point ( UG ) ( Live ) = '.$cancel_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            break;

                                    }
                                    $shop_config_key=$this->shop_config_key;
                                    $api_key=$this->api_key;

                                    // add to woocommerce log file the Shop Api Key = $shop_config_key ( my Shop Api Key )
                                    $logger->info( wc_print_r( 'the Shop Api Key = $shop_config_key ( my Shop Api Key ) ( live )  = '.$shop_config_key, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                    // add to woocommerce log file the Shop Api Key = $api_key ( my Merchant Api Key )
                                    $logger->info( wc_print_r( 'the the Merchant Api Key = $api_key ( my Merchant Api Key ) ( live )  = '.$api_key, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                }
                                if($this->environment=="Sandbox"){
                                    // add to woocommerce log file the live environment
                                    $logger->info( wc_print_r( 'the environment ( Sandbox ) = '.$this->environment, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                    switch ($this->sandbox_country_code) {
                                        case "Egypt":
                                            $countryCode='EG';
                                            $cancel_endpoint='https://api-sandbox-pay.jumia.com.eg/merchant/cancel';
                                            // add to woocommerce log file the country code ( EG ) ( sandbox )
                                            $logger->info( wc_print_r( 'the country code ( EG ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            // add to woocommerce log file the country end point ( EG ) ( sandbox )
                                            $logger->info( wc_print_r( 'the country end point ( EG ) ( sandbox ) = '.$cancel_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            break;
                                        case "Ghana":
                                            $countryCode='GH';
                                            $cancel_endpoint='https://api-sandbox-pay.jumia.com.gh/merchant/cancel';

                                            // add to woocommerce log file the country code ( GH ) ( sandbox )
                                            $logger->info( wc_print_r( 'the country code ( GH ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            // add to woocommerce log file the country end point ( GH ) ( sandbox )
                                            $logger->info( wc_print_r( 'the country end point ( GH ) ( sandbox ) = '.$cancel_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            break;
                                        case "Ivory-Coast":
                                            $countryCode='CI';
                                            $cancel_endpoint='https://api-sandbox-pay.jumia.ci/merchant/cancel';

                                            // add to woocommerce log file the country code ( EG ) ( sandbox )
                                            $logger->info( wc_print_r( 'the country code ( CI ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            // add to woocommerce log file the country end point ( CI ) ( sandbox )
                                            $logger->info( wc_print_r( 'the country end point ( CI ) ( sandbox ) = '.$cancel_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            break;
                                        case "Kenya":
                                            $countryCode='KE';
                                            $cancel_endpoint='https://api-sandbox-pay.jumia.co.ke/merchant/cancel';

                                            // add to woocommerce log file the country code ( KE ) ( sandbox )
                                            $logger->info( wc_print_r( 'the country code ( KE ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            // add to woocommerce log file the country end point ( KE ) ( sandbox )
                                            $logger->info( wc_print_r( 'the country end point ( KE ) ( sandbox ) = '.$cancel_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            break;
                                        case "Morocco":
                                            $countryCode='MA';
                                            $cancel_endpoint='https://api-sandbox-pay.jumia.ma/merchant/cancel';

                                            // add to woocommerce log file the country code ( MA ) ( sandbox )
                                            $logger->info( wc_print_r( 'the country code ( MA ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            // add to woocommerce log file the country end point ( MA ) ( sandbox )
                                            $logger->info( wc_print_r( 'the country end point ( MA ) ( sandbox ) = '.$cancel_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            break;
                                        case "Nigeria":
                                            $countryCode='NG';
                                            $cancel_endpoint='https://api-sandbox-pay.jumia.com.ng/merchant/cancel';

                                            // add to woocommerce log file the country code ( NG ) ( sandbox )
                                            $logger->info( wc_print_r( 'the country code ( NG ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            // add to woocommerce log file the country end point ( NG ) ( sandbox )
                                            $logger->info( wc_print_r( 'the country end point ( NG ) ( sandbox ) = '.$cancel_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            break;
                                        case "Tunisia":
                                            $countryCode='TN';
                                            $cancel_endpoint='https://api-sandbox-pay.jumia.com.tn/merchant/cancel';

                                            // add to woocommerce log file the country code ( TN ) ( sandbox )
                                            $logger->info( wc_print_r( 'the country code ( TN ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            // add to woocommerce log file the country end point ( TN ) ( sandbox )
                                            $logger->info( wc_print_r( 'the country end point ( TN ) ( sandbox ) = '.$cancel_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            break;
                                        case "Uganda":
                                            $countryCode='UG';
                                            $cancel_endpoint='https://api-sandbox-pay.jumia.ug/merchant/cancel';

                                            // add to woocommerce log file the country code ( UG ) ( sandbox )
                                            $logger->info( wc_print_r( 'the country code ( UG ) ( sandbox ) = '.$countryCode, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            // add to woocommerce log file the country end point ( UG ) ( sandbox )
                                            $logger->info( wc_print_r( 'the country end point ( UG ) ( sandbox ) = '.$cancel_endpoint, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                            $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                            break;

                                    }
                                    $shop_config_key=$this->sandbox_shop_config_key;
                                    $api_key=$this->sandbox_api_key;

                                    // add to woocommerce log file the Shop Api Key = $shop_config_key ( my Shop Api Key ) ( sandbox )
                                    $logger->info( wc_print_r( 'the Shop Api Key = $shop_config_key ( my Shop Api Key ) ( sandbox )  = '.$shop_config_key, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                    // add to woocommerce log file the Shop Api Key = $api_key ( my Merchant Api Key )( sandbox )
                                    $logger->info( wc_print_r( 'the Merchant Api Key = $api_key ( my Merchant Api Key ) ( sandbox )  = '.$api_key, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                }


                                // add to woocommerce log file the refund endpoint
                                $logger->info( wc_print_r( 'the verify purchase status = '.$purchase_status, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                $order_merchantReferenceId=get_post_meta( $order_id, '_purchaseId',true );
                                $data = [
                                    "shopConfig" => $this->shop_config_key,
                                    "purchaseId" => $order_merchantReferenceId,
                                ];

                                $data = wp_json_encode( $data );

                                // add to woocommerce log file the refund endpoint
                                $logger->info( wc_print_r( 'the cancel data = '.$data, true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

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

                                        if(!is_admin()){ wc_add_notice('Cancel complete', 'error');}
                                        // add to woocommerce log file the refund endpoint
                                        $logger->info( wc_print_r( 'the cancel status = success', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                        $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                    }

                                }
                                else{
                                    if(!is_admin()){wc_add_notice('Connection Error', 'error');}
                                    // add to woocommerce log file the refund endpoint
                                    $logger->info( wc_print_r( 'the cancel status = Fail', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                    $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                                }

                            }
                            elseif($purchase_status=="Cancelled" || $purchase_status=="Expired"){
                                if(!is_admin()){wc_add_notice('This order is already cancelled', 'error');}
                                // add to woocommerce log file the refund endpoint
                                $logger->info( wc_print_r( 'the cancel status = This order is already cancelled', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            }
                            elseif($purchase_status=="Completed"){
                                if(!is_admin()){ wc_add_notice('This order can not be canceled case the payment complete.', 'error');}
                                // add to woocommerce log file the refund endpoint
                                $logger->info( wc_print_r( 'the cancel status = This order can not be canceled case the payment complete.', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            }
                            elseif($purchase_status=="Failed"){
                                if(!is_admin()){wc_add_notice('This order can not be cancelled case it is failed in payment process.', 'error');}
                                // add to woocommerce log file the refund endpoint
                                $logger->info( wc_print_r( 'the cancel status = This order can not be cancelled case it is failed in payment process.', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

                            }
                            else{
                                if(!is_admin()){
                                    wc_add_notice('Connection error.', 'error');}
                                // add to woocommerce log file the refund endpoint
                                $logger->info( wc_print_r( 'the cancel status = This order can not be cancelled case Connection error', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );
                                $logger->info( wc_print_r( '===========================================================================================================================================', true ), array( 'source' => 'jumiaPay log file -'.$order_id ) );

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