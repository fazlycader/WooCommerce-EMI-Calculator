<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Extend WC_Payment_Gateway class
class WC_EMI_Payment_Gateway extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id = 'wc_emi_gateway';
        $this->method_title = __('EMI Payment', 'wc-emi');
        $this->method_description = __('Pay via EMI plans available for selected banks.', 'wc-emi');
        $this->has_fields = true;

        // Load the settings
        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');

        // Save settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        // Add custom checkout fields for EMI plans
        add_action('woocommerce_before_order_notes', array($this, 'display_emi_options'));
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'wc-emi'),
                'type' => 'checkbox',
                'label' => __('Enable EMI Payment Gateway', 'wc-emi'),
                'default' => 'yes',
            ),
            'title' => array(
                'title' => __('Title', 'wc-emi'),
                'type' => 'text',
                'default' => __('EMI Payment', 'wc-emi'),
            ),
            'description' => array(
                'title' => __('Description', 'wc-emi'),
                'type' => 'textarea',
                'default' => __('Select an EMI plan to proceed.', 'wc-emi'),
            ),
        );
    }

    public function display_emi_options($checkout)
    {
        $bank_plans = get_option('wc_emi_bank_plans', []);

        if (!empty($bank_plans)) {
            echo '<h3>' . __('Choose Your EMI Plan', 'wc-emi') . '</h3>';
            echo '<select name="wc_emi_selected_plan" required>';
            echo '<option value="">' . __('Select an EMI plan', 'wc-emi') . '</option>';

            foreach ($bank_plans as $plan) {
                $emi = ($plan['percentage'] > 0) 
                    ? ($checkout->cart->subtotal + ($checkout->cart->subtotal * $plan['percentage'] / 100)) / $plan['duration']
                    : ($checkout->cart->subtotal + $plan['fee_fixed']) / $plan['duration'];

                echo '<option value="' . esc_attr($plan['id']) . '">';
                echo esc_html($plan['bank_name'] . ' - ' . $plan['duration'] . ' months (' . wc_price($emi) . '/month)');
                echo '</option>';
            }

            echo '</select>';
        }
    }

    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);
        $order->payment_complete();
        wc_reduce_stock_levels($order_id);

        // Redirect to the order received page
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order),
        );
    }
}


add_action('woocommerce_checkout_update_order_meta', 'wc_emi_save_selected_plan');
function wc_emi_save_selected_plan($order_id)
{
    if (!empty($_POST['wc_emi_selected_plan'])) {
        update_post_meta($order_id, '_wc_emi_selected_plan', sanitize_text_field($_POST['wc_emi_selected_plan']));
    }
}
