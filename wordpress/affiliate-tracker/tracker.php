<?php
/**
 * Plugin Name: Monevibe Affiliate Tracker
 * Plugin URI:  https://monevibe.com
 * Description: Automatic Click ID tracking for Monevibe affiliate partners.
 * Version: 1.0.0
 * Author: Monevibe developer
 * Author URI: https://monevibe.com
 * Text Domain: monevibe-tracker
 */

if (!defined('ABSPATH')) exit;

const MONEVIBE_CLICK_ID_KEY = 'monevibe_click_id';
const MONEVIBE_META_KEY = '_monevibe_click_id';
const MONEVIBE_API_URL = 'https://addons.monevibe.com/api';

add_action('wp_loaded', 'mv_capture_click_id');
function mv_capture_click_id(): void
{
    if (isset($_GET['clickid'])) {
        $click_id = sanitize_text_field($_GET['clickid']);

        setcookie(MONEVIBE_CLICK_ID_KEY, $click_id, time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);

        if (class_exists('WooCommerce') && WC()->session) {
            WC()->session->set(MONEVIBE_CLICK_ID_KEY, $click_id);
        }
    }
}


add_action('woocommerce_new_order', 'mv_handle_new_order', 10, 1);
function mv_handle_new_order($order_id): void
{
    $click_id = '';

    if (class_exists('WooCommerce') && WC()->session) {
        $click_id = WC()->session->get(MONEVIBE_CLICK_ID_KEY);
    }

    if (!$click_id && isset($_COOKIE[MONEVIBE_CLICK_ID_KEY])) {
        $click_id = sanitize_text_field($_COOKIE[MONEVIBE_CLICK_ID_KEY]);
    }

    if ($click_id) {
        update_post_meta($order_id, MONEVIBE_META_KEY, $click_id);

        $order = wc_get_order($order_id);

        wp_remote_post(MONEVIBE_API_URL . '/wordpress/order', [
            'method' => 'POST',
            'blocking' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'body' => json_encode([
                'external_id' => $order_id,
                'click_id' => $click_id,
                'total_amount' => $order->get_total(),
                'currency' => $order->get_currency(),
            ])
        ]);
    }
}

add_action('woocommerce_order_status_changed', 'mv_status_webhook_trigger', 10, 3);

function mv_status_webhook_trigger($order_id, $old_status, $new_status): void
{

    $target_statuses = ['completed', 'cancelled', 'refunded'];

    if (in_array($new_status, $target_statuses)) {

        $order = wc_get_order($order_id);
        $order_id = $order->get_id();
        $click_id = get_post_meta($order_id, MONEVIBE_META_KEY, true);

        if ($click_id) {
            wp_remote_post(MONEVIBE_API_URL . '/wordpress/webhook', [
                'method' => 'POST',
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'external_id' => $order_id,
                    'click_id' => $click_id,
                    'old_status' => $old_status,
                    'new_status' => $new_status,
                    'total_amount' => $order->get_total(),
                    'currency' => $order->get_currency(),
                ])
            ]);
        }
    }
}

add_action('woocommerce_admin_order_data_after_billing_address', 'mv_display_order_click_id');
function mv_display_order_click_id($order): void {
    $order_id = $order->get_id();
    $click_id = get_post_meta($order_id, MONEVIBE_META_KEY, true);

    echo '<div style="background: #e7f5fe; padding: 12px; border-left: 4px solid #2096f3; margin-top: 20px; border-radius: 4px;">';
    if ($click_id) {
        echo '<h4 style="margin:0 0 5px 0;">' . esc_html__('Monevibe Integration', 'monevibe-tracker') . '</h4>';
        echo '<p style="margin:0;"><strong>' . esc_html__('Click ID:', 'monevibe-tracker') . '</strong> <code style="background:#fff; padding:2px 5px; border:1px solid #ccc;">' . esc_html($click_id) . '</code></p>';
    } else {
        echo '<p style="color:red; margin:0;"><strong>' . esc_html__('Monevibe Click ID:', 'monevibe-tracker') . '</strong> ' . esc_html__('Not found', 'monevibe-tracker') . '</p>';
    }
    echo '</div>';
}