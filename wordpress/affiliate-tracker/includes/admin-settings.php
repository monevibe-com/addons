<?php
if (!defined('ABSPATH')) exit;

/**
 * Register Admin Menu Item
 */
add_action('admin_menu', 'mv_add_admin_menu');
function mv_add_admin_menu(): void
{
    add_menu_page(
        'Monevibe Tracker',
        'Monevibe',
        'manage_options',
        'monevibe-tracker',
        'mv_render_admin_page',
        'dashicons-share-alt2',
        56
    );
}

/**
 * Render Admin Settings Page
 */
function mv_render_admin_page(): void
{
    if (isset($_POST['mv_save_settings']) && check_admin_referer('mv_settings_nonce')) {
        $input_url = sanitize_text_field($_POST['monevibe_api_url'] ?? '');

        if (empty($input_url)) {
            delete_option('monevibe_api_url');
        } else {
            update_option('monevibe_api_url', esc_url_raw($input_url));
        }

        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully!', 'monevibe-tracker') . '</p></div>';
    }

    $current_api_url = get_option('monevibe_api_url', '');

    $store_url  = get_site_url();
    $site_name  = get_bloginfo('name') ?: parse_url($store_url, PHP_URL_HOST);

    $currency   = function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : '';
    $wc_version = defined('WC_VERSION') ? WC_VERSION : '';

    $callback_url = add_query_arg([
        'store_url' => $store_url,
        'store_name' => $site_name,
        'currency'   => $currency,
        'wc_version' => $wc_version,
    ], mv_get_api_url() . '/wordpress/auth-callback');

    $auth_url = add_query_arg([
        'app_name'     => 'Monevibe Affiliate Tracker',
        'scope'        => 'read_write',
        'user_id'      => get_current_user_id(),
        'return_url'   => admin_url('admin.php?page=monevibe-tracker&status=connected'),
        'callback_url' => $callback_url,
    ], untrailingslashit($store_url) . '/wc-auth/v1/authorize');

    $is_connected = isset($_GET['status']) && $_GET['status'] === 'connected';

    echo '<div class="wrap">';
    echo '<h1>Monevibe Integration</h1>';

    if ($is_connected) {
        echo '<div class="notice notice-success"><p>' . esc_html__('Store successfully connected to Monevibe!', 'monevibe-tracker') . '</p></div>';
    }

    // Connect Box
    echo '<div style="background:#fff; padding:20px; border-radius:8px; max-width:600px; box-shadow:0 1px 3px rgba(0,0,0,0.1); margin-top:20px;">';
    echo '<h3>' . esc_html__('Catalog & Price Synchronization', 'monevibe-tracker') . '</h3>';
    echo '<p>' . esc_html__('Click the button below to authorize Monevibe to fetch products and track price changes in real-time.', 'monevibe-tracker') . '</p>';
    echo '<a href="' . esc_url($auth_url) . '" class="button button-primary button-hero">' . esc_html__('Connect Monevibe', 'monevibe-tracker') . '</a>';
    echo '</div>';

    // Settings Box
    echo '<div style="background:#fff; padding:20px; border-radius:8px; max-width:600px; box-shadow:0 1px 3px rgba(0,0,0,0.1); margin-top:20px;">';
    echo '<h3>' . esc_html__('Backend Settings (Developer Options)', 'monevibe-tracker') . '</h3>';
    echo '<form method="post" action="">';
    wp_nonce_field('mv_settings_nonce');

    echo '<table class="form-table" role="presentation">';
    echo '<tr>';
    echo '<th scope="row"><label for="monevibe_api_url">' . esc_html__('API Base URL', 'monevibe-tracker') . '</label></th>';
    echo '<td>';
    echo '<input name="monevibe_api_url" type="text" id="monevibe_api_url" value="' . esc_attr($current_api_url) . '" class="regular-text" placeholder="https://addons.monevibe.com/api" />';
    echo '<p class="description">' . esc_html__('Leave empty to use the default URL:', 'monevibe-tracker') . ' <code>' . MONEVIBE_API_URL . '</code></p>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';

    echo '<p class="submit"><input type="submit" name="mv_save_settings" id="submit" class="button button-secondary" value="' . esc_attr__('Save Settings', 'monevibe-tracker') . '"></p>';
    echo '</form>';
    echo '</div>';

    echo '</div>';
}