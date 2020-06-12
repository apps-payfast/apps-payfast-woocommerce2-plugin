<?php

if (!defined('ABSPATH')) {
    exit;
}

class apps_payfast_Gateway extends WC_Payment_Gateway {

    private $plugin_name;
    private $version;
    public $shipping_enabled;
    public static $log_enabled = false;
    public static $log = false;

    public function __construct() {

        $this->plugin_name = 'apps-payfast-woocommerce';
        $this->version = '0.9 Beta';
        $this->shipping_enabled = 'yes' === get_option('woocommerce_calc_shipping', 'no');

        $this->id = 'apps_payfast_payment';
        $this->method_title = __('PayFast', 'apps-payfast-woocommerce');
        $this->method_description = __('PayFast gateway sends customers to PayFast Web Checkout to enter their payment information and redirects back to shop when the payment was completed.', 'apps-payfast-woocommerce');
        $this->icon = apply_filters('apps-payfast-woocommerce_icon', plugins_url('assets/img/payfast.png', __FILE__));
        $this->has_fields = false;
        $this->credit_fields = false;

        $this->title = 'PayFast Web Checkout';
        $this->description = $this->get_option('description');
        $this->merchant_id = $this->get_option('merchant_id');
        $this->security_key = $this->get_option('security_key');
        $this->merchant_name = $this->get_option('merchant_name');

        if (!$this->is_enabled()) {
            $this->enabled = false;
        }

        $this->init_form_fields();
        $this->init_settings();
    }

    public function is_enabled() {

        return true;
    }

    public static function log($message) {
        
    }

    public function admin_options() {
        include('partials/views/apps-admin-settings-template.php');
    }

    public function init_form_fields() {
        $this->form_fields = include('partials/apps-admin-settings.php');
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);

        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        );
    }

    public function payfast_response_handler() {

        $redirect = isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : '';
        $basketid = isset($_REQUEST['basket_id']) ? $_REQUEST['basket_id'] : '';
        $apps_status_msg = isset($_REQUEST['err_msg']) ? $_REQUEST['err_msg'] : '';
        $apps_transactionid = $transaction_id = isset($_REQUEST['transaction_id']) ? $_REQUEST['transaction_id'] : '';
        $apps_statuscode = isset($_REQUEST['err_code']) ? $_REQUEST['err_code'] : '';
        $apps_rdv_key = isset($_REQUEST['Rdv_Message_Key']) ? $_REQUEST['Rdv_Message_Key'] : '';
        $signature = isset($_REQUEST['signature']) ? $_REQUEST['signature'] : '';

        $order_id = wc_get_order((int) $basketid);
        $order = wc_get_order($order_id);
        $current_status = $order->get_status();
        $meta_data_collection = $order->get_meta_data();
        $sign_data = '';

        foreach ($meta_data_collection as $meta_data) {
            $data = $meta_data->get_data();
            if ($data['key'] == 'payfast_meta_data') {
                $sign_data = json_decode($data['value'], true);
            }
        }

        if ($sign_data['signature'] != $signature && $sign_data['order_id'] != $basketid) {

            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Forbidden', true, 401);
            exit;
        }



        if ($current_status == 'processing' || $current_status == 'complete' || $current_status == 'on-hold') {
            if ($redirect == "Y") {
                wc_add_notice('Could not update this order. Its already in processing state.');
                wp_redirect(get_site_url());
                exit;
            }
            header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK', true, 200);
            exit;
        }



        if ($apps_statuscode == '000') {

            $order->update_status('processing', __('Payment received, your order is currently being processed.'));

            $message = __('Payment received.<br />Your order is currently being processed.', 'apps-payfast-woocommerce');
            $message_type = 'success';

            $order->add_order_note(__('Payment Received.<br />Your order is currently being processed.<br />PayFast Transaction ID: ', 'apps-payfast-woocommerce') . $apps_transactionid, 1);

            $order->add_order_note(__('Payment Via PayFast Payment Gateway<br />Transaction ID: ', 'apps-payfast-woocommerce') . $transaction_id);

            wc_reduce_stock_levels($order_id);


            WC()->cart->empty_cart();

            $order->update_meta_data('payfast_rdv_message_key', $apps_rdv_key);
            $order->update_meta_data('payfast_transaction_id', $apps_transactionid);
            $order->update_meta_data('payfast_status_message', $apps_status_msg);
            $order->update_meta_data('payfast_status_code', $apps_statuscode);
            $order->save();

            $payfast_message = array(
                'message' => $message,
                'message_type' => $message_type
            );

            if (version_compare(WOOCOMMERCE_VERSION, "2.2") >= 0) {
                add_post_meta($order_id, '_paid_date', current_time('mysql'), true);
                update_post_meta($order_id, '_transaction_id', $transaction_id);
            }

            update_post_meta($order_id, '_apps_payfast_message', $payfast_message);
            do_action('woocommerce_payment_complete', $order_id);
            if ($redirect == "Y") {
                wp_redirect($this->get_return_url($order));
                exit;
            }

            header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK', true, 200);
            exit;
        }

        $order->update_status('failed', __('Payment transaction failed. Your order was not successfull.'));

        $message = __('Payment transaction failed. Your order was not successfull.', 'apps-payfast-woocommerce');
        $message_type = 'failed';

        $order->add_order_note(__('Payment transaction failed. Your order was not successfull..<br />PayFast Transaction ID: ', 'apps-payfast-woocommerce') . $apps_transactionid, 1);

        $order->add_order_note(__('Payment transaction failed. <br>' . $apps_status_msg . ' <br />Transaction ID: ', 'apps-payfast-woocommerce') . $transaction_id);

        update_post_meta($order_id, '_apps_payfast_message', $payfast_message);

        $order->update_meta_data('payfast_rdv_message_key', $apps_rdv_key);
        $order->update_meta_data('payfast_transaction_id', $apps_transactionid);
        $order->update_meta_data('payfast_status_message', $apps_status_msg);
        $order->update_meta_data('payfast_status_code', $apps_statuscode);
        $order->save();

        if ($redirect == "Y") {
            wc_add_notice('Payment was unsuccessfull. Please contact merchant for more information.');

            wp_redirect($order->get_cancel_order_url());
            exit;
        }

        header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK', true, 200);

        exit;
    }

    public function set_wc_notice() {
        if (get_query_var('order-received')) {
            $order_id = absint(get_query_var('order-received'));
            $order = wc_get_order($order_id);
            $payment_method = $order->payment_method;

            if (is_order_received_page() && ('apps_payfast_payment' == $payment_method)) {
                $payfast_message = get_post_meta($order_id, '_apps_payfast_message', true);

                if (!empty($payfast_message)) {
                    $message = $payfast_message['message'];
                    $message_type = $payfast_message['message_type'];

                    delete_post_meta($order_id, '_apps_payfast_message');

                    wc_add_notice($message, $message_type);
                }
            }
        }
    }

    public function receipt_page($order_id) {
        include_once('partials/class-apps-payfast-gateway-request.php');

        $this->enqueue_scripts();

        $order = wc_get_order($order_id);
        $payfast_request = new apps_payfast_Gateway_Request($this);

        echo $payfast_request->generate_payfast_form($order, $this->testmode);
    }

    private function get_merchant_codes() {
        $merchant_codes = array();
        for ($i = 1; $i < 4; $i++) {
            $code = $this->get_option('api_merchant_code_' . $i);
            if (!empty($code)) {
                array_push($merchant_codes, $code);
            }
        }

        return $merchant_codes;
    }

    private function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'assets/js/apps-payfast-woocommerce.js', array('jquery'), $this->version, TRUE);
    }

    public function add_new_gateway($methods) {
        $methods[] = 'apps_payfast_Gateway';
        return $methods;
    }

}
