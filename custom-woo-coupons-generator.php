<?php
/*
Plugin Name: Custom WooCommerce Coupons Generator
Description: Generates custom discount coupons for WooCommerce
Version: 2.0
Author: Husson Aurelien husson-aurelien.tech
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

class Custom_Woo_Coupons_Generator {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_generate_coupons', array($this, 'generate_coupons'));
    }

    // Add admin menu item
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            'Coupon Generator',
            'Coupon Generator',
            'manage_woocommerce',
            'custom-woo-coupons-generator',
            array($this, 'render_admin_page')
        );
    }

    // Render admin page
    public function render_admin_page() {
        include plugin_dir_path(__FILE__) . 'admin-page.php';
    }

    // Enqueue admin scripts
    public function enqueue_admin_scripts($hook) {
        if ('woocommerce_page_custom-woo-coupons-generator' !== $hook) {
            return;
        }
        wp_enqueue_script('custom-woo-coupons-generator', plugins_url('admin-script.js', __FILE__), array('jquery'), '2.0', true);
        wp_localize_script('custom-woo-coupons-generator', 'cwcg_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cwcg-nonce')
        ));
    }

    // Generate coupons
    public function generate_coupons() {
        check_ajax_referer('cwcg-nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Insufficient permissions.');
        }

        $discount = intval($_POST['discount']);
        $count = intval($_POST['count']);
        $expiry_date = sanitize_text_field($_POST['expiry_date']);
        $usage_limit = intval($_POST['usage_limit']);
        $usage_limit_per_user = intval($_POST['usage_limit_per_user']);

        if ($discount <= 0 || $discount > 100 || $count <= 0) {
            wp_send_json_error('Invalid parameters.');
        }

        $generated_coupons = $this->generate_coupons_batch($discount, $count, $expiry_date, $usage_limit, $usage_limit_per_user);

        if (empty($generated_coupons)) {
            wp_send_json_error('An error occurred while generating coupons.');
        } else {
            wp_send_json_success(array(
                'message' => sprintf('%d coupons generated successfully.', count($generated_coupons)),
                'coupons' => $generated_coupons
            ));
        }
    }

    // Generate a batch of coupons
    private function generate_coupons_batch($discount, $count, $expiry_date, $usage_limit, $usage_limit_per_user) {
        $generated_coupons = array();
        $existing_coupons = $this->get_existing_coupons();

        for ($i = 0; $i < $count; $i++) {
            $code = $this->generate_unique_code($existing_coupons, $discount);
            $coupon_id = $this->create_coupon($code, $discount, $expiry_date, $usage_limit, $usage_limit_per_user);
            if ($coupon_id) {
                $generated_coupons[] = $code;
                $existing_coupons[] = $code;
            }
        }

        return $generated_coupons;
    }

    // Get existing coupon codes
    private function get_existing_coupons() {
        global $wpdb;
        return $wpdb->get_col("SELECT post_title FROM {$wpdb->posts} WHERE post_type = 'shop_coupon'");
    }

    // Generate a unique coupon code
    private function generate_unique_code($existing_coupons, $discount) {
        do {
            $letters = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 4);
            $code = "OPPO{$discount}_{$letters}";
        } while (in_array($code, $existing_coupons));
        return $code;
    }

    // Create a new coupon
    private function create_coupon($code, $discount, $expiry_date, $usage_limit, $usage_limit_per_user) {
        $coupon = array(
            'post_title' => $code,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
            'post_type' => 'shop_coupon'
        );

        $coupon_id = wp_insert_post($coupon);

        if ($coupon_id) {
            update_post_meta($coupon_id, 'discount_type', 'percent');
            update_post_meta($coupon_id, 'coupon_amount', $discount);
            update_post_meta($coupon_id, 'individual_use', 'yes');
            update_post_meta($coupon_id, 'usage_limit', $usage_limit);
            update_post_meta($coupon_id, 'usage_limit_per_user', $usage_limit_per_user);
            update_post_meta($coupon_id, 'date_expires', strtotime($expiry_date));
        }

        return $coupon_id;
    }
}

new Custom_Woo_Coupons_Generator();
