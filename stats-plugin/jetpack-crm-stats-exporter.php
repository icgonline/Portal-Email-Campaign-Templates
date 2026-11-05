<?php
/**
 * Plugin Name: Jetpack CRM Email Stats Exporter
 * Description: Export email campaign statistics from Jetpack CRM broadcasts
 * Version: 1.0.0
 * Author: ICG Agency
 * License: GPL-2.0-or-later
 * Text Domain: jetpack-crm-stats-exporter
 *
 * This plugin provides functionality to export detailed email campaign statistics
 * (including sent status, opens, clicks, unsubscribes) from Jetpack CRM broadcasts.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

define('JETPACK_CRM_STATS_EXPORTER_VERSION', '1.0.0');
define('JETPACK_CRM_STATS_EXPORTER_PATH', plugin_dir_path(__FILE__));
define('JETPACK_CRM_STATS_EXPORTER_URL', plugin_dir_url(__FILE__));

// Load plugin files
require_once JETPACK_CRM_STATS_EXPORTER_PATH . 'includes/admin-menu.php';
require_once JETPACK_CRM_STATS_EXPORTER_PATH . 'includes/export-handler.php';
require_once JETPACK_CRM_STATS_EXPORTER_PATH . 'includes/database-queries.php';
require_once JETPACK_CRM_STATS_EXPORTER_PATH . 'includes/advanced-features.php';
require_once JETPACK_CRM_STATS_EXPORTER_PATH . 'includes/link-analytics.php';

/**
 * Plugin activation hook
 */
function jetpack_crm_stats_exporter_activate() {
    // Flush rewrite rules if needed
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'jetpack_crm_stats_exporter_activate');

/**
 * Plugin deactivation hook
 */
function jetpack_crm_stats_exporter_deactivate() {
    // Cleanup if needed
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'jetpack_crm_stats_exporter_deactivate');
