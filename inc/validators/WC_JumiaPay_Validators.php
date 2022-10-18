<?php
/**
 *  Validators.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_JumiaPay_Validator {

        /**
         * It checks if the environment is valid.
         * 
         * @param env The environment you want to use. Valid values are "Live" and "Sandbox".
         * 
         * @return The environment that was passed in.
         */
        public static function ValidateEnvironment($env) {
                $validEnviroments = ['Live', 'Sandbox'];
                return in_array($env, $validEnviroments) ? $env : "";
        }

        /**
         * It validates the country code.
         * 
         * @param countryCode The country code of the country you want to send to.
         * 
         * @return The country code is being returned.
         */
        public static function ValidateCountryCode($countryCode) {
                $validCountryCode = ["NG", "EG", "KE", "CI", "MA", "TN","UG", "GH", "DZ", "SN"];
                return in_array($countryCode, $validCountryCode) ? $countryCode : "";
        }

        /**
         * It checks if the payment status is valid or not.
         * 
         * @param paymentStatus This is the status of the payment. It can be either success or failure.
         * 
         * @return The payment status is being returned.
         */
        public static function ValidatePaymentStatus($paymentStatus) {
                $validPaymentStatus = ['success', 'failure'];
                return in_array($paymentStatus, $validPaymentStatus) ? $paymentStatus : "";
        }
}
