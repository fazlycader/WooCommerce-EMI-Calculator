<?php
add_action('woocommerce_admin_order_data_after_billing_address', 'wc_emi_display_selected_plan_in_admin', 10, 1);
function wc_emi_display_selected_plan_in_admin($order) {
    $selected_plan_index = get_post_meta($order->get_id(), '_wc_emi_selected_plan', true);
    $banks = get_option('wc_emi_banks', []);
    $payment_plans = get_option('wc_emi_payment_plans', []);

    if (!isset($payment_plans[$selected_plan_index])) {
        return;
    }

    $plan = $payment_plans[$selected_plan_index];
    $bank_name = isset($banks[$plan['bank_id']]) ? $banks[$plan['bank_id']]['name'] : __('Unknown Bank', 'wc-emi-calculator');

    echo '<p><strong>' . __('Selected EMI Plan:', 'wc-emi-calculator') . '</strong> ' . esc_html($bank_name . ' - ' . $plan['duration'] . ' Months') . '</p>';
}
?>