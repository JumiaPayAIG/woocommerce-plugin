<?php
/**
 *  Validators.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_JumiaPay_Validator {

        public static function ValidateEnvironment($env) {
                $validEnviroments = ['Live', 'Sandbox'];
                return in_array($env, $validEnviroments) ? $env : "";
        }

        public static function ValidateCountryCode($countryCode) {
                $validCountryCode = ['NG', 'EG'];
                return in_array($countryCode, $validCountryCode) ? $countryCode : "";
        }

        public static function ValidatePaymentStatus($paymentStatus) {
                $validPaymentStatus = ['success', 'failure'];
                return in_array($paymentStatus, $validPaymentStatus) ? $paymentStatus : "";
        }
}
