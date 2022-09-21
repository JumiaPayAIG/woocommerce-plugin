<?php
/**
 * Cart handler.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_JumiaPay_Refund {
    public $order;

    public $amount;

    public $currency;

    public $shopConfig;

    public function __construct(
        $order,
        $amount,
        $currency,
        $shopConfig
    ) {
        $this->order = $order;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->shopConfig = $shopConfig;
    }

    private function generateMerchantReference() {
        $date = date_format(date_create(), 'U');
        $merchantReferenceId="wcrefund".$this->order->get_id().$date;

        // Just Because
        $merchantReferenceId = str_replace(' ', '', $merchantReferenceId); // Replaces all spaces.
        $merchantReferenceId = preg_replace('/[^A-Za-z0-9]/', '', $merchantReferenceId); // Removes special chars.
        if(strlen($merchantReferenceId) > 255){
            $merchantReferenceId= substr($merchantReferenceId,0,255);
        }

        return sanitize_text_field($merchantReferenceId);
    }

    public function generateData() {
        $merchantReferenceId=get_post_meta($this->order->get_id(), '_merchantReferenceId', true );
        $purchaseId = get_post_meta($this->order->get_id(), '_purchaseId', true);
        
        return [
            "shopConfig" => $this->shopConfig,
            "refundAmount" => sanitize_text_field($this->amount),
            "refundCurrency" => $this->currency,
            "description" => sanitize_text_field("Refund for order #".$merchantReferenceId),
            "purchaseReferenceId" => $merchantReferenceId,
            "purchaseId" => $purchaseId,
            "referenceId"=> sanitize_text_field($this->generateMerchantReference())
        ];
    }
}
