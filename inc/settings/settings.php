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
            'Live' => esc_html('Live'),
            'Sandbox' => esc_html('Sandbox'),
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
            'NG' => esc_html('Nigeria'),
            'EG' => esc_html('Egypt'),
            'KE' => esc_html('Kenya'),
            'CI' => esc_html("Cote d'ivoire"),
            'MA' => esc_html('Morroco'),
            'TN' => esc_html('Tunisia'),
            'UG' => esc_html('Uganda'),
            'GH' => esc_html('Ghana'),
            'DZ' => esc_html('Algeria'),
            'SN' => esc_html('Senegal'),
        ),
    ),
    "shop_config_key"=> array(
        'title' => esc_html('Shop Key'),
        'type' => 'textarea',
        'default' => '',
    ),
    "shop_config_id"=> array(
        'title' => esc_html('Shop Config ID'),
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
            'NG' => esc_html('Nigeria'),
            'EG' => esc_html('Egypt'),
            'KE' => esc_html('Kenya'),
            'CI' => esc_html("Cote d'ivoire"),
            'MA' => esc_html('Morroco'),
            'TN' => esc_html('Tunisia'),
            'UG' => esc_html('Uganda'),
            'GH' => esc_html('Ghana'),
            'DZ' => esc_html('Algeria'),
            'SN' => esc_html('Senegal'),
        ),
    ),
    "sandbox_shop_config_key"=> array(
        'title' => esc_html('Shop Key'),
        'type' => 'textarea',
        'default' => '',
    ),
    "sandbox_shop_config_id"=> array(
        'title' => esc_html('Shop Config ID'),
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
