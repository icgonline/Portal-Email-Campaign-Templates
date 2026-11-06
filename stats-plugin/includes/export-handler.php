<?php
/**
 * Export Handler - Jetpack CRM Stats Exporter
 * 
 * Handles the CSV export functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Export campaign statistics to CSV
 * 
 * @param int $campaign_id The campaign ID to export
 * @param array $export_fields Fields to include in export
 */
function jetpack_crm_stats_exporter_export_campaign($campaign_id, $export_fields) {
    // Get campaign data
    $campaign_data = jetpack_crm_stats_exporter_get_campaign_data($campaign_id);
    
    if (!$campaign_data) {
        wp_die('Campaign not found');
    }
    
    // Get stats for campaign
    $stats = jetpack_crm_stats_exporter_get_campaign_stats($campaign_id);
    
    if (empty($stats)) {
        wp_die('No statistics found for this campaign');
    }
    
    // Generate CSV
    $csv_content = jetpack_crm_stats_exporter_generate_csv($campaign_data, $stats, $export_fields);
    
    // Send file download headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="campaign-' . $campaign_id . '-stats.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output CSV content
    echo $csv_content;
    
    die();
}

/**
 * Generate CSV content
 * 
 * @param array $campaign_data Campaign information
 * @param array $stats Stats records
 * @param array $export_fields Fields to include
 * @return string CSV content
 */
function jetpack_crm_stats_exporter_generate_csv($campaign_data, $stats, $export_fields) {
    $output = fopen('php://memory', 'w');
    
    // Define all available fields and their headers
    $field_headers = array(
        'recipient_name' => 'Recipient Name',
        'recipient_email' => 'Email Address',
        'sent_date' => 'Sent Date',
        'opened' => 'Opened',
        'open_date' => 'Open Date',
        'clicked' => 'Clicked',
        'click_count' => 'Click Count',
        'first_click_date' => 'First Click Date',
        'clicked_ids' => 'Clicked Link IDs',
        'clicked_urls' => 'Clicked URLs',
        'unsubscribed' => 'Unsubscribed',
        'unsub_date' => 'Unsubscribe Date'
    );
    
    // Build header row based on selected fields
    $headers = array(
        'Campaign ID: ' . $campaign_data->ID,
        'Campaign: ' . $campaign_data->zbsmailcamp_title,
        'Export Date: ' . current_time('mysql')
    );
    
    fputcsv($output, $headers);
    fputcsv($output, array()); // Empty row for spacing
    
    // Add column headers
    $column_headers = array();
    foreach ($export_fields as $field) {
        if (isset($field_headers[$field])) {
            $column_headers[] = $field_headers[$field];
        }
    }
    fputcsv($output, $column_headers);
    
    // Add data rows
    foreach ($stats as $stat) {
        $row = array();
        
        foreach ($export_fields as $field) {
            $value = jetpack_crm_stats_exporter_get_field_value($stat, $field);
            $row[] = $value;
        }
        
        fputcsv($output, $row);
    }
    
    // Get CSV content
    rewind($output);
    $csv_content = stream_get_contents($output);
    fclose($output);
    
    return $csv_content;
}

/**
 * Get a field value from a stat record
 * 
 * @param object $stat The stat record
 * @param string $field The field name
 * @return string The formatted field value
 */
function jetpack_crm_stats_exporter_get_field_value($stat, $field) {
    switch ($field) {
        case 'recipient_name':
            return isset($stat->contact_name) ? $stat->contact_name : '';
        
        case 'recipient_email':
            return $stat->zbsmailstat_uemail;
        
        case 'sent_date':
            return jetpack_crm_stats_exporter_format_timestamp($stat->zbsmailstat_sent);
        
        case 'opened':
            return ($stat->zbsmailstat_open !== null && $stat->zbsmailstat_open > 0) ? 'Yes' : 'No';
        
        case 'open_date':
            return ($stat->zbsmailstat_open !== null && $stat->zbsmailstat_open > 0) ? 
                   jetpack_crm_stats_exporter_format_timestamp($stat->zbsmailstat_open) : '';
        
        case 'clicked':
            return ($stat->zbsmailstat_click !== null && $stat->zbsmailstat_click > 0) ? 'Yes' : 'No';
        
        case 'click_count':
            return ($stat->zbsmailstat_clickcount !== null && $stat->zbsmailstat_clickcount > 0) ? 
                   (int) $stat->zbsmailstat_clickcount : '0';
        
        case 'first_click_date':
            return ($stat->zbsmailstat_click !== null && $stat->zbsmailstat_click > 0) ? 
                   jetpack_crm_stats_exporter_format_timestamp($stat->zbsmailstat_click) : '';
        
        case 'clicked_ids':
            $ids = isset($stat->zbsmailstat_clickedids) ? $stat->zbsmailstat_clickedids : '';
            return $ids;
        
        case 'clicked_urls':
            $ids = isset($stat->zbsmailstat_clickedids) ? $stat->zbsmailstat_clickedids : '';
            return jetpack_crm_stats_exporter_resolve_link_ids($ids);
        
        case 'unsubscribed':
            return ($stat->zbsmailstat_unsub !== null && $stat->zbsmailstat_unsub > 0) ? 'Yes' : 'No';
        
        case 'unsub_date':
            return ($stat->zbsmailstat_unsub !== null && $stat->zbsmailstat_unsub > 0) ? 
                   jetpack_crm_stats_exporter_format_timestamp($stat->zbsmailstat_unsub) : '';
        
        default:
            return '';
    }
}

/**
 * Format Unix timestamp to readable date
 * Replicates how Jetpack CRM plugin displays timestamps
 * 
 * @param int $timestamp Unix timestamp (seconds since epoch, UTC)
 * @return string Formatted date using WordPress date/time format
 */
function jetpack_crm_stats_exporter_format_timestamp($timestamp) {
    if (!$timestamp || $timestamp <= 0) {
        return '';
    }
    
    $timestamp = (int) $timestamp;
    
    if (function_exists('zeroBSCRM_locale_utsToDatetimeWP')) {
        return zeroBSCRM_locale_utsToDatetimeWP($timestamp);
    }

    return wp_date(
        get_option('date_format') . ' ' . get_option('time_format'),
        $timestamp
    );
}
