<?php
if (!defined('ABSPATH')) exit;

/**
 * Send order payload to Monevibe API on new order creation
 */
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

        wp_remote_post(mv_get_api_url() . '/wordpress/order', [
            'method'   => 'POST',
            'blocking' => false,
            'headers'  => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
            'body' => json_encode([
                'external_id'  => $order_id,
                'click_id'     => $click_id,
                'total_amount' => $order->get_total(),
                'currency'     => $order->get_currency(),
            ])
        ]);
    }
}

/**
 * Fire order status webhooks
 */
add_action('woocommerce_order_status_changed', 'mv_status_webhook_trigger', 10, 3);
function mv_status_webhook_trigger($order_id, $old_status, $new_status): void
{
    $target_statuses = ['completed', 'cancelled', 'refunded'];

    if (in_array($new_status, $target_statuses)) {

        $order    = wc_get_order($order_id);
        $order_id = $order->get_id();
        $click_id = get_post_meta($order_id, MONEVIBE_META_KEY, true);

        if ($click_id) {
            wp_remote_post(mv_get_api_url() . '/wordpress/webhook', [
                'method'  => 'POST',
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => json_encode([
                    'external_id'  => $order_id,
                    'click_id'     => $click_id,
                    'old_status'   => $old_status,
                    'new_status'   => $new_status,
                    'total_amount' => $order->get_total(),
                    'currency'     => $order->get_currency(),
                ])
            ]);
        }
    }
}