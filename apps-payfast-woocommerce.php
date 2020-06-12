<?php

/**
 * The plugin bootstrap file
 *
 * @link              http://www.apps.ne.pk
 * @since             0.9 Beta 
 * @package           apps_payfast_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       APPS PayFast Gateway for Woocommerce
 * Plugin URI:        http://www.apps.net.pk
 * Description:       PayFast let's customer pay via local Pakistani banks
 * Version:           0.9 Beta Test
 * Author:            APPS Tech Team
 * Author URI:        http://www.apps.net.pk
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