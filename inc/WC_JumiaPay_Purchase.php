<?php
/**
 * Cart handler.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once JPAY_DIR . 'inc/helpers/WC_JumiaPay_Cleaner.php';

class WC_JumiaPay_Purchase {

    public $order;

    public $baseUrl;

    public $currency;

    public $language;

    public $countryCode;

    public function __construct(
        $order,
        $countryCode,
        $language,
        $baseUrl,
        $currency
    ) {
        $this->order = $order;
        $this->baseUrl = $baseUrl;
        $this->currency = $currency;
        $this->countryCode = $countryCode;
        $this->language = $language;
    }


    /**
     * It generates a unique reference for the order.
     * 
     * @return a string.
     */
    private function generateMerchantReference() {
        $date = date_format(date_create(), 'U');
        $merchantReferenceId="wcpurchase".$this->order->get_id().$date;

        // Just Because
        $merchantReferenceId = str_replace(' ', '', $merchantReferenceId); // Replaces all spaces.
        $merchantReferenceId = preg_replace('/[^A-Za-z0-9]/', '', $merchantReferenceId); // Removes special chars.
        if(strlen($merchantReferenceId) > 255){
            $merchantReferenceId= substr($merchantReferenceId,0,255);
        }

        return sanitize_text_field($merchantReferenceId);
    }

    /**
     * It returns the basket items.
     * 
     * @return An array of basket items
     */
    private function getBasket() {

        $items = $this->order->get_items();

        $basketItems=array();

        /*
         * the basket array
         */
        foreach ( $items as $item ) {
            $basketItem=[
                "name"=> sanitize_text_field($item->get_name()),
                "amount"=> sanitize_text_field($item->get_subtotal()),
                "quantity"=> sanitize_text_field($item->get_quantity()),
                "currency"=> sanitize_text_field($this->currency)
            ];
            array_push($basketItems,$basketItem);

        }

        return $basketItems;
    }

    /**
     * It generates the data to be sent to the JumiaPay API
     * 
     * @return an array of data that will be used to create the payment request.
     */
    public function generateData() {
        $merchantReferenceId = $this->generateMerchantReference();
        $data = [
            "description" => sanitize_text_field(substr("Payment for order " . $merchantReferenceId, 0, 250)),
            "amount" => [
              "value" => sanitize_text_field($this->order->get_total()),
              "currency" => $this->currency
            ],
            "merchant" => [
              "referenceId" => sanitize_text_field($merchantReferenceId),
              "callbackUrl" => esc_url_raw($this->baseUrl."/wc-api/payment_callback/?orderid=".$this->order->get_id()),
              "returnUrl" => esc_url_raw($this->baseUrl."/wc-api/payment_return/?orderid=".$this->order->get_id())
            ],
            "consumer" => [
              "emailAddress" => sanitize_email($this->order->get_billing_email()),
              "ipAddress" => sanitize_text_field($this->order->get_customer_ip_address()),
              "country" => sanitize_text_field($this->countryCode),
              "mobilePhoneNumber" => wc_sanitize_phone_number($this->order->get_billing_phone()),
              "language" => $this->language,
              "name" => sanitize_text_field(substr($this->order->get_billing_first_name()." ".$this->order->get_billing_last_name(), 0, 100)),
              "firstName" => sanitize_text_field(substr($this->order->get_billing_last_name(), 0, 50)),
              "lastName" => sanitize_text_field(substr($this->order->get_billing_first_name(), 0, 50))
            ],
            "basket"=> [
                "shippingAmount"=>sanitize_text_field($this->order->get_shipping_tax()),
                "currency"=>$this->currency,
                "items"=>$this->getBasket(),
            ],
            "shippingAddress"=> [
                "addressPrimary"=> sanitize_text_field(substr($this->order->get_shipping_address_1(), 0, 512)),
                "addressSecondary"=> sanitize_text_field(substr($this->order->get_shipping_address_2(), 0, 512)),
                "city"=> sanitize_text_field(substr($this->order->get_shipping_city(), 0, 50)),
                "district"=> sanitize_text_field(substr($this->order->get_shipping_state(), 0, 50)),
                "province"=> sanitize_text_field(substr($this->order->get_shipping_state(), 0, 50)),
                "zip"=> sanitize_text_field(substr($this->order->get_shipping_postcode(), 0, 10)),
                "country"=> sanitize_text_field($this->order->get_shipping_country()),
                "name"=> sanitize_text_field(substr($this->order->get_shipping_first_name()." ".$this->order->get_shipping_last_name(), 0, 100)),
                "firstName"=> sanitize_text_field(substr($this->order->get_shipping_first_name(), 0, 50)),
                "lastName"=> sanitize_text_field(substr($this->order->get_shipping_last_name(), 0, 50)),
                "mobilePhoneNumber"=> wc_sanitize_phone_number($this->order->get_billing_phone())
            ],
            "billingAddress"=> [
                "addressPrimary"=> sanitize_text_field(substr($this->order->get_billing_address_1(), 0, 512)),
                "addressSecondary"=> sanitize_text_field(substr($this->order->get_billing_address_2(), 0, 512)),
                "city"=> sanitize_text_field(substr($this->order->get_billing_city(), 0, 50)),
                "district"=> sanitize_text_field(substr($this->order->get_billing_state(), 0, 50)),
                "province"=> sanitize_text_field(substr($this->order->get_billing_state(), 0, 50)),
                "zip"=> sanitize_text_field(substr($this->order->get_billing_postcode(), 0, 10)),
                "country"=> sanitize_text_field($this->order->get_billing_country()),
                "name"=> sanitize_text_field(substr($this->order->get_billing_first_name()." ".$this->order->get_billing_last_name(), 0, 100)),
                "firstName"=> sanitize_text_field(substr($this->order->get_billing_first_name(), 0, 50)),
                "lastName"=> sanitize_text_field(substr($this->order->get_billing_last_name(), 0, 50)),
                "mobilePhoneNumber"=> wc_sanitize_phone_number($this->order->get_billing_phone())
            ]
        ];
        
        return WC_JumiaPay_Cleaner::filterNotNull($data);
    }
}
