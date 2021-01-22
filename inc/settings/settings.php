<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$pluginFields=array(
    'enabled' => array(
        'title' => 'Enable/Disable',
        'type' => 'checkbox',
        'label' => 'Enable or Disable JumiaPay',
        'default' => 'no',

    ),
    "environment"=> array(
        'title' => 'Environment',
        'type' => 'select',
        'default' => '',
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
        'title' => 'Country List',
        'type' => 'select',
        'default' => '',
        'desc_tip' => true,
        'description' => 'Note that the currency of your WooCommerce store, under "General settings", must be the one used on the country you are operating and selecting here.',
        'default' => '',
        'options' => array(
            'EG' => 'Egypt',
            'NG' => 'Nigeria',
        ),
    ),
    "shop_config_key"=> array(
        'title' => 'Shop Key',
        'type' => 'textarea',
        'default' => '',
    ),
    "api_key"=> array(
        'title' => 'Merchant Api Key',
        'type' => 'textarea',
        'default' => '',
    ),
    "sandbox_title"=> array(
        'title' => esc_html__( 'Sandbox Settings', 'jumia-pay-woo' ),
        'type'  => 'title',
    ),
    "sandbox_country_list"=> array(
        'title' => 'Country List',
        'type' => 'select',
        'default' => '',
        'desc_tip' => true,
        'description' => 'Note that the currency of your WooCommerce store, under "General settings", must be the one used on the country you are operating and selecting here.',
        'default' => '',
        'options' => array(
            'EG' => 'Egypt',
            'NG' => 'Nigeria',
        ),
    ),
    "sandbox_shop_config_key"=> array(
        'title' => 'Shop Key',
        'type' => 'textarea',
        'default' => '',
    ),
    "sandbox_api_key"=> array(
        'title' => 'Merchant Api Key',
        'type' => 'textarea',
        'default' => '',
    ),
);

return apply_filters( 'woo_jumiaPay_fields', $pluginFields );
