<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$pluginFields=array(
    'enabled' => array(
        'title' => esc_html('Enable/Disable'),
        'type' => 'checkbox',
        'label' => esc_html('Enable or Disable JumiaPay'),
        'default' => 'no',

    ),
    "environment"=> array(
        'title' => esc_html('Environment'),
        'type' => 'select',
        'default' => '',
        'desc_tip' => true,
        'options' => array(
            'Live' => 'Live',
            'Sandbox' => 'Sandbox',
        ),
    ),
    "live_title"=> array(
        'title' => esc_html('Live Settings'),
        'type'  => 'title',
    ),
    "country_list"=> array(
        'title' => esc_html('Country List'),
        'type' => 'select',
        'default' => '',
        'desc_tip' => true,
        'description' => esc_html('Note that the currency of your WooCommerce store, under "General settings", must be the one used on the country you are operating and selecting here.'),
        'default' => '',
        'options' => array(
            'EG' => 'Egypt',
            'NG' => 'Nigeria',
        ),
    ),
    "shop_config_key"=> array(
        'title' => esc_html('Shop Key'),
        'type' => 'textarea',
        'default' => '',
    ),
    "api_key"=> array(
        'title' => esc_html('Merchant Api Key'),
        'type' => 'textarea',
        'default' => '',
    ),
    "sandbox_title"=> array(
        'title' => esc_html('Sandbox Settings'),
        'type'  => 'title',
    ),
    "sandbox_country_list"=> array(
        'title' =>  esc_html('Country List'),
        'type' => 'select',
        'default' => '',
        'desc_tip' => true,
        'description' => esc_html('Note that the currency of your WooCommerce store, under "General settings", must be the one used on the country you are operating and selecting here.'),
        'default' => '',
        'options' => array(
            'EG' => 'Egypt',
            'NG' => 'Nigeria',
        ),
    ),
    "sandbox_shop_config_key"=> array(
        'title' => esc_html('Shop Key'),
        'type' => 'textarea',
        'default' => '',
    ),
    "sandbox_api_key"=> array(
        'title' => esc_html('Merchant Api Key'),
        'type' => 'textarea',
        'default' => '',
    ),
);

return apply_filters( 'woo_jumiaPay_fields', $pluginFields );
