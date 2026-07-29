<?php
if (!defined('ABSPATH')) exit;

/**
 * Capture Click ID from URL parameter
 */
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