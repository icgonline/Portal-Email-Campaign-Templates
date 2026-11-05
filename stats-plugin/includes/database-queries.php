<?php
/**
 * Database Queries - Jetpack CRM Stats Exporter
 * 
 * Handles all database queries for the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get campaign data by ID
 * 
 * @param int $campaign_id Campaign ID
 * @return object|null Campaign data or null if not found
 */
function jetpack_crm_stats_exporter_get_campaign_data($campaign_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'zbs_mail_campaigns';
    
    $campaign = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE ID = %d",
            $campaign_id
        )
    );
    
    return $campaign;
}

/**
 * Get campaign statistics
 * 
 * @param int $campaign_id Campaign ID
 * @return array Array of stat records
 */
function jetpack_crm_stats_exporter_get_campaign_stats($campaign_id) {
    global $wpdb;
    
    $stats_table = $wpdb->prefix . 'zbs_mail_stats';
    
    // Get stats for this campaign
    $stats = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$stats_table} 
             WHERE zbsmailstat_mailid = %d 
             ORDER BY zbsmailstat_sent ASC",
            $campaign_id
        )
    );
    
    if (empty($stats)) {
        return array();
    }
    
    // Enhance stats with contact information
    $enhanced_stats = array();
    foreach ($stats as $stat) {
        $stat->contact_name = jetpack_crm_stats_exporter_get_contact_name($stat->zbsmailstat_uid);
        $enhanced_stats[] = $stat;
    }
    
    return $enhanced_stats;
}

/**
 * Get contact name by user ID
 * 
 * @param int $contact_id Contact ID
 * @return string Contact name or empty string
 */
function jetpack_crm_stats_exporter_get_contact_name($contact_id) {
    global $wpdb;
    
    // Try to get from ZBS contacts table
    $contacts_table = $wpdb->prefix . 'zbs_contacts';
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '{$contacts_table}'") !== $contacts_table) {
        return '';
    }
    
    $contact = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT zbsc_fname, zbsc_lname FROM {$contacts_table} WHERE ID = %d",
            $contact_id
        )
    );
    
    if (!$contact) {
        return '';
    }
    
    $first_name = isset($contact->zbsc_fname) ? $contact->zbsc_fname : '';
    $last_name = isset($contact->zbsc_lname) ? $contact->zbsc_lname : '';
    
    return trim($first_name . ' ' . $last_name);
}

/**
 * Get link URL by link ID
 * 
 * @param int $link_id Link ID
 * @return string Link URL or empty string
 */
function jetpack_crm_stats_exporter_get_link_url($link_id) {
    global $wpdb;
    
    $links_table = $wpdb->prefix . 'zbs_mail_links';
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '{$links_table}'") !== $links_table) {
        return '';
    }
    
    $link = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT zbsmaillink_linkurl FROM {$links_table} WHERE ID = %d",
            $link_id
        )
    );
    
    return $link ? $link->zbsmaillink_linkurl : '';
}

/**
 * Resolve clicked link IDs to actual URLs
 * 
 * @param string $clicked_ids Comma-separated link IDs (e.g., "5,82,83")
 * @return string Comma-separated URLs or empty string
 */
function jetpack_crm_stats_exporter_resolve_link_ids($clicked_ids) {
    if (!$clicked_ids || $clicked_ids === '') {
        return '';
    }
    
    $link_ids = array_map('intval', explode(',', $clicked_ids));
    $link_ids = array_filter($link_ids); // Remove 0 values
    
    if (empty($link_ids)) {
        return '';
    }
    
    $urls = array();
    foreach ($link_ids as $link_id) {
        $url = jetpack_crm_stats_exporter_get_link_url($link_id);
        if ($url) {
            $urls[] = $url;
        }
    }
    
    return implode('; ', $urls);
}

/**
 * Get campaign mail ID for reference
 * 
 * @param int $campaign_id Campaign ID
 * @return int Mail ID or 0 if not found
 */
function jetpack_crm_stats_exporter_get_mail_id($campaign_id) {
    global $wpdb;
    
    $campaigns_table = $wpdb->prefix . 'zbs_mail_campaigns';
    
    $campaign = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT zbsmailcamp_mailid FROM {$campaigns_table} WHERE ID = %d",
            $campaign_id
        )
    );
    
    return $campaign ? (int) $campaign->zbsmailcamp_mailid : 0;
}
