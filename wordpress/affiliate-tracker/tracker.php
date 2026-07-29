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

define('MONEVIBE_PATH', plugin_dir_path(__FILE__));

/**
 * Get base API URL from settings or return fallback default
 */
function mv_get_api_url(): string
{
    $saved_url = get_option('monevibe_api_url');
    if (!empty($saved_url)) {
        return untrailingslashit(esc_url_raw($saved_url));
    }
    return MONEVIBE_API_URL;
}

require_once MONEVIBE_PATH . 'includes/tracking.php';
require_once MONEVIBE_PATH . 'includes/api.php';
require_once MONEVIBE_PATH . 'includes/admin-order.php';
require_once MONEVIBE_PATH . 'includes/admin-settings.php';