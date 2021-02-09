<?php
/**
 * Cart handler.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_JumiaPay_Purchase {

    public $order;

    public $baseUrl;

    public $currency;

    public $language;

    public $countryCode;

    public $shopConfig;

    public function __construct(
        $order,
        $countryCode,
        $language,
        $baseUrl,
        $currency,
        $shopConfig
    ) {
        $this->order = $order;
        $this->baseUrl = $baseUrl;
        $this->currency = $currency;
        $this->countryCode = $countryCode;
        $this->language = $language;
        $this->shopConfig = $shopConfig;
    }


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

    private function getBasket() {

        $items = $this->order->get_items();

        $basketItems=array();

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
                "name"=> sanitize_text_field($item->get_name()),
                "imageUrl"=>  esc_url_raw($featured_img_url),
                "amount"=> sanitize_text_field($item->get_subtotal()),
                "quantity"=> sanitize_text_field($item->get_quantity()),
                "discount"=> "",
                "currency"=> sanitize_text_field($this->currency))
            ];
            array_push($basketItems,$basketItem);

        }

        return $basketItems;
    }

    public function generateData() {

        return [
            "shopConfig" => $this->shopConfig,
            "basket"=> [
                "shipping"=>sanitize_text_field($this->order->get_shipping_tax()),
                "currency"=>$this->currency,
                "basketItems"=>$this->getBasket(),
                "totalAmount"=> sanitize_text_field($this->order->get_total()),
                "discount"=>sanitize_text_field($this->order->get_discount_total()),
            ],
            "consumerData"=> [
                "emailAddress"=> sanitize_email($this->order->get_billing_email()),
                "mobilePhoneNumber"=> wc_sanitize_phone_number($this->order->get_billing_phone()),
                "country"=> sanitize_text_field($this->countryCode),
                "firstName"=> sanitize_text_field($this->order->get_billing_first_name()),
                "lastName"=> sanitize_text_field($this->order->get_billing_last_name()),
                "ipAddress"=> sanitize_text_field($this->order->get_customer_ip_address()),
                "dateOfBirth"=> "",
                "language"=> $this->language,
                "name"=> sanitize_text_field($this->order->get_billing_first_name()." ".$this->order->get_billing_last_name())
            ],
            "priceCurrency"=> $this->currency,
            "priceAmount"=> sanitize_text_field($this->order->get_total()),
            "purchaseReturnUrl"=>  esc_url_raw($this->baseUrl."/wc-api/payment_return/?orderid=".$this->order->get_id()),
            "purchaseCallbackUrl"=> esc_url_raw($this->baseUrl."/wc-api/payment_callback/?orderid=".$this->order->get_id()),
            "shippingAddress"=> [
                "addressLine1"=> sanitize_text_field($this->order->get_shipping_address_1()),
                "addressLine2"=> sanitize_text_field($this->order->get_shipping_address_2()),
                "city"=> sanitize_text_field($this->order->get_shipping_city()),
                "district"=> sanitize_text_field($this->order->get_shipping_state()),
                "province"=> sanitize_text_field($this->order->get_shipping_state()),
                "zip"=> sanitize_text_field($this->order->get_shipping_postcode()),
                "country"=> sanitize_text_field($this->order->get_shipping_country()),
                "name"=> sanitize_text_field($this->order->get_shipping_first_name()." ".$this->order->get_shipping_last_name()),
                "firstName"=> sanitize_text_field($this->order->get_shipping_first_name()),
                "lastName"=> sanitize_text_field($this->order->get_shipping_last_name()),
                "mobilePhoneNumber"=> wc_sanitize_phone_number($this->order->get_billing_phone())
            ],
            "billingAddress"=> [
                "addressLine1"=> sanitize_text_field($this->order->get_billing_address_1()),
                "addressLine2"=> sanitize_text_field($this->order->get_billing_address_2()),
                "city"=> sanitize_text_field($this->order->get_billing_city()),
                "district"=> sanitize_text_field($this->order->get_billing_state()),
                "province"=> sanitize_text_field($this->order->get_billing_state()),
                "zip"=> sanitize_text_field($this->order->get_billing_postcode()),
                "country"=> sanitize_text_field($this->order->get_billing_country()),
                "name"=> sanitize_text_field($this->order->get_billing_first_name()." ".$this->order->get_billing_last_name()),
                "firstName"=> sanitize_text_field($this->order->get_billing_first_name()),
                "lastName"=> sanitize_text_field($this->order->get_billing_last_name()),
                "mobilePhoneNumber"=> wc_sanitize_phone_number($this->order->get_billing_phone())
            ],
            "merchantReferenceId"=> $this->generateMerchantReference()
        ];
    }
}
