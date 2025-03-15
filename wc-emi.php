<?php
/**
 * Plugin Name: WooCommerce EMI Calculator
 * Description: Custom WooCommerce plugin to calculate EMI for all products with bank and payment plan management.
 * Version: 2.0.0
 * Author: Fazly Cader
 * Author URI: https://linkedin.com/in/fazly-cader
 * License: GPL2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function wc_emi_plugin_init() {
    if (class_exists('WC_Payment_Gateway')) { // Ensure WooCommerce is loaded
        require_once plugin_dir_path(__FILE__) . 'includes/class-wc-emi-payment-gateway.php';
    }
}
add_action('plugins_loaded', 'wc_emi_plugin_init', 11);


class WC_EMI_Calculator {
    public function __construct() {
        // Admin menu and scripts
        add_action('admin_menu', [$this, 'create_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

        // Frontend scripts and display
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
        add_action('woocommerce_after_add_to_cart_form', [$this, 'display_emi_options']);

        // Save actions
        add_action('admin_post_save_bank', [$this, 'save_bank_handler']);
        add_action('admin_post_save_payment_plan', [$this, 'save_plan_handler']);
        add_action('admin_post_delete_bank', [$this, 'wc_emi_delete_bank']);
        add_action('admin_post_edit_bank', [$this, 'wc_emi_edit_bank']);
        add_action('admin_post_delete_payment_plan', [$this, 'wc_emi_delete_payment_plan']);
        add_action('admin_post_edit_payment_plan', [$this, 'wc_emi_edit_payment_plan']);
    }

    /**
     * Create Admin Menu for EMI Settings
     */
    public function create_admin_menu() {
        add_menu_page('EMI Settings', 'EMI Settings', 'manage_options', 'wc-emi-settings', [$this, 'render_admin_page'], 'dashicons-calculator', 56);
    }

    /**
     * Enqueue Admin Scripts and Styles
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'toplevel_page_wc-emi-settings') {
            wp_enqueue_style('wc-emi-admin-css', plugin_dir_url(__FILE__) . 'assets/css/admin.css', [], '1.0.0');
            wp_enqueue_script('wc-emi-admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin.js', ['jquery'], '1.0.0', true);
        }
    }

    /**
     * Enqueue Frontend Scripts
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style('wc-emi-frontend-css', plugin_dir_url(__FILE__) . 'assets/css/frontend.css', [], '1.0.0');
        wp_enqueue_script('wc-emi-frontend-js', plugin_dir_url(__FILE__) . 'assets/js/frontend.js', ['jquery'], '1.0.0', true);
    }

    /**
     * Render Admin Page
     */
    public function render_admin_page() {
        $banks = get_option('wc_emi_banks', []);
        $plans = get_option('wc_emi_payment_plans', []);

        include plugin_dir_path(__FILE__) . 'templates/admin-page.php';
    }

    /**
     * Save Bank Handler
     */
function save_bank_handler() {
    // Verify nonce
    if (!isset($_POST['save_bank_nonce']) || !wp_verify_nonce($_POST['save_bank_nonce'], 'save_bank_action')) {
        wp_die(__('Invalid nonce specified', 'wc-emi-calculator'), __('Error', 'wc-emi-calculator'), ['response' => 403]);
    }

    // Validate input
    $bank_index = isset($_POST['bank_index']) && is_numeric($_POST['bank_index']) ? intval($_POST['bank_index']) : null;
    $bank_name = isset($_POST['bank_name']) ? sanitize_text_field($_POST['bank_name']) : '';
    $bank_logo = isset($_POST['bank_logo']) ? esc_url_raw($_POST['bank_logo']) : '';
    $bank_note = isset($_POST['bank_note']) ? sanitize_textarea_field($_POST['bank_note']) : '';

    if (empty($bank_name)) {
        wp_die(__('Bank name is required.', 'wc-emi-calculator'), __('Error', 'wc-emi-calculator'), ['response' => 400]);
    }

    // Retrieve existing banks
    $banks = get_option('wc_emi_banks', []);

    // Update or create bank
    if ($bank_index !== null && isset($banks[$bank_index])) {
        // Update existing bank
        $banks[$bank_index] = [
            'name' => $bank_name,
            'logo' => $bank_logo,
            'note' => $bank_note,
        ];
    } else {
        // Create a new bank
        $banks[] = [
            'name' => $bank_name,
            'logo' => $bank_logo,
            'note' => $bank_note,
        ];
    }

    // Save updated banks to the database
    update_option('wc_emi_banks', $banks);

    // Redirect back to the settings page
    wp_safe_redirect(admin_url('admin.php?page=wc-emi-settings&tab=Banks'));
    exit;
}


    /**
     * Delete Bank Handler
     */
    function wc_emi_delete_bank() {
        check_admin_referer('delete_bank_action', 'delete_bank_nonce');

        $banks = get_option('wc_emi_banks', []);
        $index = intval($_POST['bank_index']);
        if (isset($banks[$index])) {
            unset($banks[$index]);
            update_option('wc_emi_banks', array_values($banks));
        }

        wp_redirect(admin_url('admin.php?page=wc-emi-settings'));
        exit;
    }

    /**
     * Edit Bank Handler
     */
    function wc_emi_edit_bank() {
        check_admin_referer('edit_bank_action', 'edit_bank_nonce');

        $banks = get_option('wc_emi_banks', []);
        $index = intval($_POST['bank_index']);
        if (isset($banks[$index])) {
            // Redirect to the edit form or process it here
        }

        wp_redirect(admin_url('admin.php?page=wc-emi-settings'));
        exit;
    }

    /**
     * Delete Payment Plan Handler
     */
    function wc_emi_delete_payment_plan() {
        check_admin_referer('delete_payment_plan_action', 'delete_payment_plan_nonce');

        $plans = get_option('wc_emi_payment_plans', []);
        $index = intval($_POST['plan_index']);
        if (isset($plans[$index])) {
            unset($plans[$index]);
            update_option('wc_emi_payment_plans', array_values($plans));
        }

        wp_redirect(admin_url('admin.php?page=wc-emi-settings'));
        exit;
    }

    /**
     * Edit Payment Plan Handler
     */
    function wc_emi_edit_payment_plan() {
        check_admin_referer('edit_payment_plan_action', 'edit_payment_plan_nonce');

        $plans = get_option('wc_emi_payment_plans', []);
        $index = intval($_POST['plan_index']);
        if (isset($plans[$index])) {
            // Redirect to the edit form or process it here
        }

        wp_redirect(admin_url('admin.php?page=wc-emi-settings'));
        exit;
    }

    /**
     * Display EMI Options on Product Page
     */
    public function display_emi_options() {
    if (!is_product()) {
        return;
    }

    global $product;
    $price = $product->get_price();
    $banks = get_option('wc_emi_banks', []);
    $plans = get_option('wc_emi_payment_plans', []);

    if (empty($plans)) {
        return;
    }

    // Group plans by bank
    $bank_plans = [];
    foreach ($plans as $plan) {
        if (!isset($bank_plans[$plan['bank_id']])) {
            $bank_plans[$plan['bank_id']] = [];
        }
        $bank_plans[$plan['bank_id']][] = $plan;
    }

    echo '<div id="woocommerce-emi-options" style="margin-top: 15px;">';
    
    foreach ($bank_plans as $bank_id => $bank_plan_list) {
        $bank = $banks[$bank_id] ?? null;
        if (!$bank) {
            continue;
        }
        
        $logo_url = $bank['logo'];
        $bank_name = esc_html($bank['name']);
        $bank_note = esc_html($bank['note']);
        $first_plan = $bank_plan_list[0]; // Show only the first plan in the main list

        echo '<div class="emi-option" style="display: inline-block; margin-right: 15px;">';
        echo '<a href="#" class="emi-popup-trigger" data-bank-id="' . esc_attr($bank_id) . '">';
        echo '<img src="' . esc_url($logo_url) . '" alt="' . $bank_name . '" style="width: 50px; height: auto;">';
        echo '<div>' . $bank_name . '</div>';
        echo '</a>';
        echo '</div>';

        // Hidden popup content
        echo '<div id="emi-popup-' . esc_attr($bank_id) . '" class="emi-popup" style="display: none;">';
        echo '<div class="emi-popup-header">';
        echo '<h2>' . $bank_name . '</h2>';
        echo '<img src="' . esc_url($logo_url) . '" alt="' . $bank_name . '">';
        echo '</div>';
        echo '<p>' . nl2br($bank_note) . '</p>';
        echo '<table>';
        echo '<tr><th class="emi-heading">Plan(Months)</th><th class="emi-heading">Handling Fee</th><th class="emi-heading">Installment</th><th class="emi-heading">Total</th></tr>';

        foreach ($bank_plan_list as $plan) {
            // Calculate EMI based on the plan
            if ($plan['percentage'] > 0) {
                // If percentage is greater than 0, calculate using percentage
                $emi = ($price + ($price * $plan['percentage'] / 100)) / $plan['duration'];
                $fee_type = $plan['percentage'] . '%';
            } elseif ($plan['fee_fixed'] > 0) {
                // If fee_fixed is greater than 0, calculate using fixed fee
                $emi = ($price + $plan['fee_fixed']) / $plan['duration'];
                $fee_type = wc_price($plan['fee_fixed']);
            } else {
                // If both percentage and fee_fixed are 0, fall back to a default calculation
                $emi = $price / $plan['duration'];
                $fee_type = 'N/A';
            }

            $total_amount = $emi * $plan['duration'];

            $current_date = date('Y-m-d'); // Get current date in YYYY-MM-DD format    
            if ($current_date >= $plan['start_date'] && $current_date <= $plan['end_date']) {
                echo '<tr>';
                echo '<td>' . esc_html($plan['duration']) . '</td>';
                echo '<td>' . esc_html(wp_strip_all_tags($fee_type)) . '</td>';
                echo '<td>' . wc_price($emi) . '</td>';
                echo '<td>' . wc_price($total_amount) . '</td>';
                echo '</tr>';
            }
            
        }

        echo '</table>';
        echo '<div class="emi-popup-footer">';
        echo '<button class="emi-popup-close">Close</button>';
        echo '</div>';
        echo '</div>';
    }

    echo '</div>';
}

}

function wc_emi_enqueue_admin_scripts($hook) {
    // Load only on the plugin's admin page
    if ($hook !== 'toplevel_page_wc-emi-settings') {
        return;
    }

    // Enqueue WordPress Media Uploader
    wp_enqueue_media();
    wp_enqueue_script('wc-emi-admin-script', plugins_url('assets/js/admin.js', __FILE__), ['jquery'], '1.0', true);
}
add_action('admin_enqueue_scripts', 'wc_emi_enqueue_admin_scripts');

add_action('admin_post_save_payment_plan', function () {
    if (!isset($_POST['save_payment_plan_nonce']) || !wp_verify_nonce($_POST['save_payment_plan_nonce'], 'save_payment_plan_action')) {
        wp_die(__('Nonce verification failed', 'wc-emi-calculator'));
    }

    // Debugging: Log form data to check for missing fields
    error_log(print_r($_POST, true));

    // Retrieve and sanitize inputs
    $plan_index = isset($_POST['plan_index']) && $_POST['plan_index'] !== '' ? intval($_POST['plan_index']) : false;
    $bank_id = isset($_POST['bank_id']) ? intval($_POST['bank_id']) : null;
    $plan_name = isset($_POST['plan_name']) ? sanitize_text_field($_POST['plan_name']) : '';
    $duration = isset($_POST['duration']) && $_POST['duration'] !== '' ? intval($_POST['duration']) : 0;
    $percentage = isset($_POST['percentage']) && $_POST['percentage'] !== '' ? floatval($_POST['percentage']) : 0;
    $fee_fixed = isset($_POST['fee_fixed']) && $_POST['fee_fixed'] !== '' ? floatval($_POST['fee_fixed']) : 0;
    $start_date = isset($_POST['plan_start_date']) ? sanitize_text_field($_POST['plan_start_date']) : '';
    $end_date = isset($_POST['plan_end_date']) ? sanitize_text_field($_POST['plan_end_date']) : '';

    // Validate required fields
    // if ($bank_id === null || empty($plan_name) || empty($duration) || empty($percentage) || empty($start_date) || empty($end_date)) {
    //     wp_die(__('All fields are required.', 'wc-emi-calculator'));
    // }

    // Retrieve existing payment plans
    $payment_plans = get_option('wc_emi_payment_plans', []);

    $plan_data = [
        'bank_id'     => $bank_id,
        'plan_name'   => $plan_name,
        'duration'    => $duration,
        'percentage'  => $percentage,
        'fee_fixed'   => $fee_fixed,
        'start_date'  => $start_date,
        'end_date'    => $end_date,
    ];

    if ($plan_index !== false && isset($payment_plans[$plan_index])) {
        $payment_plans[$plan_index] = $plan_data;
    } else {
        $payment_plans[] = $plan_data; // Add new plan
    }

    update_option('wc_emi_payment_plans', $payment_plans);

    wp_redirect(admin_url('admin.php?page=wc-emi-settings'));
    exit;
});



add_action('admin_post_delete_payment_plan', function () {
    // Verify nonce
    if (!isset($_POST['delete_plan_nonce']) || !wp_verify_nonce($_POST['delete_plan_nonce'], 'delete_plan_action')) {
        wp_die(__('Nonce verification failed', 'wc-emi-calculator'));
    }

    // Ensure the user has the appropriate capability
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to perform this action.', 'wc-emi-calculator'));
    }

    // Retrieve and sanitize input
    $plan_index = isset($_POST['plan_index']) ? intval($_POST['plan_index']) : -1;

    // Retrieve existing payment plans
    $payment_plans = get_option('wc_emi_payment_plans', []);

    // Check if the plan exists
    if ($plan_index >= 0 && isset($payment_plans[$plan_index])) {
        unset($payment_plans[$plan_index]);

        // Re-index the array
        $payment_plans = array_values($payment_plans);

        // Save updated payment plans
        update_option('wc_emi_payment_plans', $payment_plans);
    } else {
        wp_die(__('Invalid payment plan selected.', 'wc-emi-calculator'));
    }

    // Redirect back to the admin page
    wp_redirect(admin_url('admin.php?page=wc-emi-settings'));
    exit;
});


new WC_EMI_Calculator();

function wc_emi_add_gateway_class($gateways)
{
    $gateways[] = 'WC_EMI_Payment_Gateway';
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'wc_emi_add_gateway_class');

add_action('woocommerce_admin_order_data_after_billing_address', 'wc_emi_display_selected_plan_in_admin', 10, 1);
function wc_emi_display_selected_plan_in_admin($order)
{
    $emi_plan = get_post_meta($order->get_id(), '_wc_emi_selected_plan', true);
    if (!empty($emi_plan)) {
        echo '<p><strong>' . __('EMI Plan:', 'wc-emi') . '</strong> ' . esc_html($emi_plan) . '</p>';
    }
}
