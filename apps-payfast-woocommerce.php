<?php

/**
 * The plugin bootstrap file
 *
 * @link              https://www.apps.net.pk
 * @since             1.0
 * @package           apps_payfast_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       APPS PayFast Gateway for Woocommerce - Sandbox
 * Plugin URI:        https://github.com/apps-payfast/apps-payfast-wordpress-plugin
 * Description:       PayFast let's customer pay via local Pakistani banks
 * Version:           1.0
 * Author:            APPS Tech Team
 * Author URI:        https://www.apps.net.pk
 * Text Domain:       apps-payfast-woocommerce
 * Domain Path:       /languages
 * Requires at least: 4.1
 * Tested up to:      4.7.4
 * License: Freeware
 *
 */
/**
 * Abort if the file is called directly
 */
if (!defined('WPINC')) {
    exit;
}

add_action('plugins_loaded', 'init_apps_payfast_gateway', 0);

function init_apps_payfast_gateway() {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    require plugin_dir_path(__FILE__) . 'includes/class-apps-payfast-woocommerce.php';

    function run_apps_payfast_woocommerce() {
        $plugin = new apps_payfast_Woocommerce();
        $plugin->run();
    }

    run_apps_payfast_woocommerce();
}