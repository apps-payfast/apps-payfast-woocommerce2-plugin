<?php

if (!defined('ABSPATH')) {
    exit;
}

return array(
    'enabled' => array(
        'title' => __('Enable/Disable', 'apps-payfast-woocommerce'),
        'type' => 'checkbox',
        'label' => __('Enable PayFast Payment Gateway', 'apps-payfast-woocommerce'),
        'description' => __('Enable or disable the gateway.', 'apps-payfast-woocommerce'),
        'desc_tip' => false,
        'default' => 'yes'
    ),
    'merchant_id' => array(
        'title' => __('Merchant ID', 'apps-payfast-woocommerce'),
        'type' => 'text',
        'label' => __('Registered Merchant ID at PayFast', 'apps-payfast-woocommerce'),
        'description' => __('Registered Merchant ID at PayFast.', 'apps-payfast-woocommerce'),
        'desc_tip' => true,
        'default' => '102'
    ),
    'security_key' => array(
        'title' => __('Merchant Secured Key', 'apps-payfast-woocommerce'),
        'type' => 'text',
        'description' => __('Merchant\'s security key.', 'apps-payfast-woocommerce'),
        'desc_tip' => true,
        'default' => 'zWHjBp2AlttNu1sK'
    ),
    'merchant_name' => array(
        'title' => __('Merchant Name', 'apps-payfast-woocommerce'),
        'type' => 'text',
        'description' => __('Merchant Name', 'apps-payfast-woocommerce'),
        'desc_tip' => true,
        'default' => 'Test Merchant'
    )
);
