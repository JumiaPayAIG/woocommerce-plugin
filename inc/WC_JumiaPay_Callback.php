<?php
/**
 * Callback handler.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_JumiaPay_Callback {

    public $order;

    public function __construct($order) {
        $this->order = $order;
    }

    public function handle($paymentStatus) {
        if($this->order->get_status() == 'pending'){

            $this->order->add_order_note('Payment ' . $paymentStatus, true);

            switch ($paymentStatus) {
            case "Created":
            case "Pending":
            case "Committed":
                $this->order->update_status('Pending');
                break;
            case "Failed":
            case "Expired":
                break;
            case "Cancelled":
                $this->order->update_status('cancelled');
                break;
            case "Completed":
                $this->order->payment_complete();
                break;
            }

            return true;

        }

        return false;
    }
}
