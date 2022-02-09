<?php
/**
 * JumiaPay Api Client.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once JPAY_DIR . 'inc/validators/WC_JumiaPay_Validators.php';

class WC_JumiaPay_Client {

    /*
     * @var string
     */
    public $countryCode;

    /*
     * @var string
     */
    public $shopConfig;

    /*
     * @var string
     */
    public $apiKey;


    /*
     * @var string
     */
    public $sandboxCountryCode;

    /*
     * @var string
     */
    public $sandboxShopConfigKey;

    /*
     * @var string
     */
    public $sandboxApiKey;

    public function __construct($env,
        $countryCode,
        $shopConfig,
        $apikey,
        $sandboxCountryCode,
        $sandboxShopConfig,
        $sandboxApiKey
    ) {
        $this->environment= WC_JumiaPay_Validator::ValidateEnvironment($env);

        $this->countryCode = WC_JumiaPay_Validator::ValidateCountryCode($countryCode);
        $this->shopConfig = sanitize_text_field($shopConfig);
        $this->apiKey = sanitize_text_field($apikey);

        $this->sandboxCountryCode = WC_JumiaPay_Validator::ValidateCountryCode($sandboxCountryCode);
        $this->sandboxShopConfig=sanitize_text_field($sandboxShopConfig);
        $this->sandboxApiKey=sanitize_text_field($sandboxApiKey);
    }

    /**
     * @return string
     */
    public function getShopConfig() {
        return $this->isLiveEnv() ? $this->shopConfig : $this->sandboxShopConfig;
    }

    /**
     * @return string
     */
    public function getCountryCode() {
        return $this->isLiveEnv() ? $this->countryCode : $this->sandboxCountryCode;
    }

    public function createRefund($data) {
        $response = $this->makeRequest('post', $this->getBaseUrl().'/merchant/refund', $data);
        if (!is_wp_error($response)) {
            $body = json_decode($response['body'], true);

            //check if the refund return success
            if ($body['success'] == 'true') {
                return ['success' => true, 'note' => 'Order refunded via JumiaPay successfully'];
            }
            else{
                return ['success' => false, 'note' => "Order Refund via JumiaPay Failed - Reason: ".$body['payload'][0]['description']];
            }

        }
        else {
            return ['success' => false, 'note' => "Order Refund via JumiaPay Failed - Reason: Connection Failed"];
        }
    }

    public function createPurchase($data, $orderId) {

        $response = $this->makeRequest('post', $this->getBaseUrl().'/merchant/create', $data);

        // check for the respond errors
        if (!is_wp_error($response)) {

            $body = json_decode($response['body'], true);

            // check for the respond body
            if ($body['success'] == 'true') {

                //save the purchaseId and the merchantReferenceId in the database
                update_post_meta( $orderId, '_purchaseId', $body['payload']['purchaseId']);
                update_post_meta( $orderId, '_merchantReferenceId', $data['merchantReferenceId']);

                //redirect to jumiaPay gateway
                return array(
                    'result' => 'success',
                    'redirect' => $body['payload']['checkoutUrl']
                );
            }
            else {
                wc_add_notice('Error payment failed case '.$body['payload'][0]['description'].' code-'.$body['payload'][0]['code'],'error');
                WC()->cart->empty_cart();
                wp_delete_post( $orderId, false );
                return;
            }
        }
        else {
            wc_add_notice('Connection error please try again later.','error');
            WC()->cart->empty_cart();
            wp_delete_post( $orderId, false );
            return;
        }
    }

    public function cancelPurchase($merchantReferenceId, $order) {

        $data = [
            "shopConfig" => $this->getShopConfig(),
            "purchaseId" => $merchantReferenceId,
        ];

        $response = $this->makeRequest('post', $this->getBaseUrl().'/merchant/cancel', $data);

        if (!is_wp_error($response)) {

            $body = json_decode($response['body'], true);

            if ($body['success'] == 'true') {
                return ['success' => true, 'note' => 'JumiaPay Payment successfully cancelled'];
            } else {
                return ['success' => false, 'note' => "JumiaPay Payment cancellation failed - Reason: ".$body['payload'][0]['description']];
            }
        }
        else{
            return ['success' => false, 'note' => "JumiaPay Payment cancellation failed - Reason: Connection Failed"];
        }
    }

    /**
     * @return string
     */
    private function getApiKey() {
        return $this->isLiveEnv() ? $this->apiKey : $this->sandboxApiKey;
    }

    /**
     * @return boolean
     */
    private function isLiveEnv() {
        return $this->environment == "Live" ;
    }

    /**
     * @return string
     */
    private function getTld() {
        $tld = '';

        switch ($this->getCountryCode()) {
            case "EG":
                $tld='.jumia.com.eg';
                break;
            case "NG":
                $tld='.jumia.com.ng';
                break;
            case "KE":
                $tld='.jumia.co.ke';
                break; 
            case "MA":
                $tld='.jumia.ma';
                break;    
            case "CI":
                $tld='.jumia.ci';
                break;
            case "TN":
                $tld='.jumia.com.tn';
                break;
            case "GH":
                $tld='.jumia.com.gh';
                break;
            case "UG":
                $tld='.jumia.ug';
                break;  
            case "DZ":
                $tld='.jumia.dz';
                break;  
            case "SN":
                $tld='.jumia.sn';
                break;       
            }
    
        return $tld;
    }

    /**
     * @return string
     */
    private function getBaseUrl() {
        return $this->isLiveEnv() ?
            'https://api-pay'.$this->getTld() :
            'https://api-staging-pay'.$this->getTld();
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $data
     */
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
                "apikey"=> $this->getApiKey(),
                "Content-Type"=> "application/json",
            ],
            'timeout'     => 60,
            'redirection' => 5,
            'blocking'    => true,
            'sslverify'   => false,
            'data_format' => 'body',
        ];

        if ($method == 'post') {
            return wp_remote_post( $url, $options );
        }
    }
}
