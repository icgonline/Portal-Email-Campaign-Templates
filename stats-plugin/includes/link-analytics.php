<?php
/**
 * Link Analytics - Jetpack CRM Stats Exporter
 * 
 * Advanced link click tracking and analytics
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get link click analytics for a campaign
 * Shows which URLs were clicked, how many times, and by how many unique recipients
 * 
 * @param int $campaign_id Campaign ID (mail ID)
 * @return array Link analytics data
 */
function jetpack_crm_stats_exporter_get_link_analytics($campaign_id) {
    global $wpdb;
    
    $links_table = $wpdb->prefix . 'zbs_mail_links';
    $stats_table = $wpdb->prefix . 'zbs_mail_stats';
    
    // Check if tables exist
    if ($wpdb->get_var("SHOW TABLES LIKE '{$links_table}'") !== $links_table) {
        return array();
    }
    
    // Get all links for this campaign
    $links = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$links_table} WHERE zbsmaillink_mailid = %d ORDER BY ID ASC",
            $campaign_id
        )
    );
    
    if (empty($links)) {
        return array();
    }
    
    $analytics = array();
    
    foreach ($links as $link) {
        // Count how many recipients clicked this link
        $click_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$stats_table} 
                 WHERE zbsmailstat_mailid = %d 
                 AND FIND_IN_SET(%d, zbsmailstat_clickedids)",
                $campaign_id,
                $link->ID
            )
        );
        
        if ($click_count > 0) {
            $analytics[] = array(
                'link_id' => (int) $link->ID,
                'link_url' => $link->zbsmaillink_linkurl,
                'unique_clicks' => (int) $click_count,
                'created' => (int) $link->zbsmaillink_created
            );
        }
    }
    
    // Sort by clicks descending
    usort($analytics, function($a, $b) {
        return $b['unique_clicks'] - $a['unique_clicks'];
    });
    
    return $analytics;
}

/**
 * Export link click report for a campaign
 * 
 * @param int $campaign_id Campaign ID (mail ID)
 * @return string CSV content with link analytics
 */
function jetpack_crm_stats_exporter_export_link_report($campaign_id) {
    $campaign = jetpack_crm_stats_exporter_get_campaign_data($campaign_id);
    $analytics = jetpack_crm_stats_exporter_get_link_analytics($campaign_id);
    
    if (!$campaign) {
        return '';
    }
    
    $output = fopen('php://memory', 'w');
    
    // Header
    fputcsv($output, array('LINK CLICK ANALYTICS REPORT'));
    fputcsv($output, array('Campaign ID', $campaign->ID));
    fputcsv($output, array('Campaign Name', $campaign->zbsmailcamp_title));
    fputcsv($output, array('Report Generated', current_time('mysql')));
    fputcsv($output, array(''));
    
    // Column headers
    fputcsv($output, array('Link ID', 'URL', 'Unique Clicks'));
    
    // Data rows
    if (empty($analytics)) {
        fputcsv($output, array('No link clicks recorded'));
    } else {
        foreach ($analytics as $link) {
            fputcsv($output, array(
                $link['link_id'],
                $link['link_url'],
                $link['unique_clicks']
            ));
        }
    }
    
    rewind($output);
    $csv_content = stream_get_contents($output);
    fclose($output);
    
    return $csv_content;
}

/**
 * Get detailed recipient click history for a campaign
 * Shows each recipient, which links they clicked, and when
 * 
 * @param int $campaign_id Campaign ID (mail ID)
 * @return array Recipient click history
 */
function jetpack_crm_stats_exporter_get_recipient_click_history($campaign_id) {
    global $wpdb;
    
    $stats_table = $wpdb->prefix . 'zbs_mail_stats';
    
    $recipients = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$stats_table} 
             WHERE zbsmailstat_mailid = %d 
             AND zbsmailstat_click > 0
             ORDER BY zbsmailstat_uemail ASC",
            $campaign_id
        )
    );
    
    if (empty($recipients)) {
        return array();
    }
    
    $history = array();
    
    foreach ($recipients as $recipient) {
        $clicked_ids = $recipient->zbsmailstat_clickedids;
        if (!$clicked_ids) {
            continue;
        }
        
        $link_ids = array_map('intval', explode(',', $clicked_ids));
        $link_ids = array_filter($link_ids);
        
        $urls = array();
        foreach ($link_ids as $link_id) {
            $url = jetpack_crm_stats_exporter_get_link_url($link_id);
            if ($url) {
                $urls[] = $url;
            }
        }
        
        $history[] = array(
            'email' => $recipient->zbsmailstat_uemail,
            'link_count' => count($urls),
            'links' => $urls,
            'first_click' => jetpack_crm_stats_exporter_format_timestamp($recipient->zbsmailstat_click)
        );
    }
    
    return $history;
}
