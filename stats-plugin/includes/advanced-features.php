<?php
/**
 * Advanced Features - Jetpack CRM Stats Exporter
 * 
 * Optional helper functions for advanced usage and customization
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all campaigns with their stats summary
 * 
 * @return array Array of campaigns with summary stats
 */
function jetpack_crm_stats_exporter_get_campaigns_summary() {
    global $wpdb;
    
    $campaigns_table = $wpdb->prefix . 'zbs_mail_campaigns';
    $stats_table = $wpdb->prefix . 'zbs_mail_stats';
    
    if ($wpdb->get_var("SHOW TABLES LIKE '{$campaigns_table}'") !== $campaigns_table) {
        return array();
    }
    
    $campaigns = $wpdb->get_results(
        "SELECT 
            c.ID,
            c.zbsmailcamp_title,
            c.zbsmailcamp_created,
            COUNT(s.ID) as total_recipients,
            SUM(CASE WHEN s.zbsmailstat_open > 0 THEN 1 ELSE 0 END) as total_opens,
            SUM(CASE WHEN s.zbsmailstat_click > 0 THEN 1 ELSE 0 END) as total_clicks,
            SUM(CASE WHEN s.zbsmailstat_unsub > 0 THEN 1 ELSE 0 END) as total_unsubscribes
        FROM {$campaigns_table} c
        LEFT JOIN {$stats_table} s ON c.zbsmailcamp_mailid = s.zbsmailstat_mailid
        GROUP BY c.ID
        ORDER BY c.zbsmailcamp_created DESC"
    );
    
    return $campaigns;
}

/**
 * Calculate engagement metrics for a campaign
 * 
 * @param int $campaign_id Campaign ID
 * @return array Engagement metrics
 */
function jetpack_crm_stats_exporter_get_engagement_metrics($campaign_id) {
    global $wpdb;
    
    $stats_table = $wpdb->prefix . 'zbs_mail_stats';
    
    $metrics = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT 
                COUNT(*) as total_sent,
                SUM(CASE WHEN zbsmailstat_open > 0 THEN 1 ELSE 0 END) as total_opens,
                SUM(CASE WHEN zbsmailstat_click > 0 THEN 1 ELSE 0 END) as total_clicks,
                SUM(CASE WHEN zbsmailstat_unsub > 0 THEN 1 ELSE 0 END) as total_unsubscribes,
                SUM(zbsmailstat_clickcount) as total_click_count,
                AVG(zbsmailstat_clickcount) as avg_clicks_per_recipient
            FROM {$stats_table}
            WHERE zbsmailstat_mailid = %d",
            $campaign_id
        )
    );
    
    if (!$metrics || $metrics->total_sent == 0) {
        return array();
    }
    
    // Calculate percentages
    $open_rate = round(($metrics->total_opens / $metrics->total_sent) * 100, 2);
    $click_rate = round(($metrics->total_clicks / $metrics->total_sent) * 100, 2);
    $unsub_rate = round(($metrics->total_unsubscribes / $metrics->total_sent) * 100, 2);
    
    return array(
        'total_sent' => (int) $metrics->total_sent,
        'total_opens' => (int) $metrics->total_opens,
        'total_clicks' => (int) $metrics->total_clicks,
        'total_unsubscribes' => (int) $metrics->total_unsubscribes,
        'open_rate' => $open_rate,
        'click_rate' => $click_rate,
        'unsub_rate' => $unsub_rate,
        'total_click_count' => (int) ($metrics->total_click_count ?? 0),
        'avg_clicks_per_recipient' => round($metrics->avg_clicks_per_recipient ?? 0, 2)
    );
}

/**
 * Get detailed recipient engagement timeline
 * 
 * @param int $campaign_id Campaign ID
 * @return array Timeline events
 */
function jetpack_crm_stats_exporter_get_engagement_timeline($campaign_id) {
    global $wpdb;
    
    $stats_table = $wpdb->prefix . 'zbs_mail_stats';
    
    $events = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT 
                zbsmailstat_uemail,
                zbsmailstat_sent,
                zbsmailstat_open,
                zbsmailstat_click,
                zbsmailstat_unsub,
                zbsmailstat_clickcount
            FROM {$stats_table}
            WHERE zbsmailstat_mailid = %d
            ORDER BY zbsmailstat_sent ASC",
            $campaign_id
        )
    );
    
    $timeline = array();
    
    foreach ($events as $event) {
        $recipient_events = array();
        
        if ($event->zbsmailstat_sent) {
            $date = function_exists('zeroBSCRM_locale_utsToDatetimeWP') ? zeroBSCRM_locale_utsToDatetimeWP((int) $event->zbsmailstat_sent) : date_i18n('Y-m-d H:i:s', (int) $event->zbsmailstat_sent);
            $recipient_events[] = array(
                'type' => 'sent',
                'timestamp' => $event->zbsmailstat_sent,
                'date' => $date
            );
        }
        
        if ($event->zbsmailstat_open) {
            $date = function_exists('zeroBSCRM_locale_utsToDatetimeWP') ? zeroBSCRM_locale_utsToDatetimeWP((int) $event->zbsmailstat_open) : date_i18n('Y-m-d H:i:s', (int) $event->zbsmailstat_open);
            $recipient_events[] = array(
                'type' => 'opened',
                'timestamp' => $event->zbsmailstat_open,
                'date' => $date
            );
        }
        
        if ($event->zbsmailstat_click) {
            $date = function_exists('zeroBSCRM_locale_utsToDatetimeWP') ? zeroBSCRM_locale_utsToDatetimeWP((int) $event->zbsmailstat_click) : date_i18n('Y-m-d H:i:s', (int) $event->zbsmailstat_click);
            $recipient_events[] = array(
                'type' => 'clicked',
                'timestamp' => $event->zbsmailstat_click,
                'date' => $date,
                'click_count' => (int) $event->zbsmailstat_clickcount
            );
        }
        
        if ($event->zbsmailstat_unsub) {
            $date = function_exists('zeroBSCRM_locale_utsToDatetimeWP') ? zeroBSCRM_locale_utsToDatetimeWP((int) $event->zbsmailstat_unsub) : date_i18n('Y-m-d H:i:s', (int) $event->zbsmailstat_unsub);
            $recipient_events[] = array(
                'type' => 'unsubscribed',
                'timestamp' => $event->zbsmailstat_unsub,
                'date' => $date
            );
        }
        
        $timeline[$event->zbsmailstat_uemail] = $recipient_events;
    }
    
    return $timeline;
}

/**
 * Export campaign stats with engagement metrics (extended format)
 * 
 * @param int $campaign_id Campaign ID
 * @param array $export_fields Fields to include
 * @return string CSV content with metrics section
 */
function jetpack_crm_stats_exporter_export_with_metrics($campaign_id, $export_fields = array()) {
    $campaign_data = jetpack_crm_stats_exporter_get_campaign_data($campaign_id);
    $metrics = jetpack_crm_stats_exporter_get_engagement_metrics($campaign_id);
    $stats = jetpack_crm_stats_exporter_get_campaign_stats($campaign_id);
    
    $output = fopen('php://memory', 'w');
    
    // Write metrics section
    fputcsv($output, array('CAMPAIGN METRICS'));
    fputcsv($output, array('Campaign ID', $campaign_data->ID));
    fputcsv($output, array('Campaign Name', $campaign_data->zbsmailcamp_title));
    fputcsv($output, array('Total Sent', $metrics['total_sent']));
    fputcsv($output, array('Total Opens', $metrics['total_opens']));
    fputcsv($output, array('Open Rate (%)', $metrics['open_rate']));
    fputcsv($output, array('Total Clicks', $metrics['total_clicks']));
    fputcsv($output, array('Click Rate (%)', $metrics['click_rate']));
    fputcsv($output, array('Total Unsubscribes', $metrics['total_unsubscribes']));
    fputcsv($output, array('Unsubscribe Rate (%)', $metrics['unsub_rate']));
    fputcsv($output, array(''));
    
    // Write detailed stats
    fputcsv($output, array('RECIPIENT DETAILS'));
    
    if (empty($export_fields)) {
        $export_fields = array(
            'recipient_name', 'recipient_email', 'sent_date', 'opened',
            'click_count', 'unsubscribed'
        );
    }
    
    // Generate CSV for detailed stats using existing function
    $csv_content = jetpack_crm_stats_exporter_generate_csv($campaign_data, $stats, $export_fields);
    
    rewind($output);
    $metrics_csv = stream_get_contents($output);
    fclose($output);
    
    return $metrics_csv . $csv_content;
}

/**
 * Get recipients with specific engagement status
 * 
 * @param int $campaign_id Campaign ID
 * @param string $status Status filter (opened, clicked, unsubscribed, unopened, etc)
 * @return array Filtered recipients
 */
function jetpack_crm_stats_exporter_filter_by_status($campaign_id, $status = '') {
    global $wpdb;
    
    $stats_table = $wpdb->prefix . 'zbs_mail_stats';
    
    $query = $wpdb->prepare(
        "SELECT * FROM {$stats_table} WHERE zbsmailstat_mailid = %d",
        $campaign_id
    );
    
    switch ($status) {
        case 'opened':
            $query .= " AND zbsmailstat_open > 0";
            break;
        case 'clicked':
            $query .= " AND zbsmailstat_click > 0";
            break;
        case 'unsubscribed':
            $query .= " AND zbsmailstat_unsub > 0";
            break;
        case 'unopened':
            $query .= " AND (zbsmailstat_open IS NULL OR zbsmailstat_open <= 0)";
            break;
        case 'not_clicked':
            $query .= " AND (zbsmailstat_click IS NULL OR zbsmailstat_click <= 0)";
            break;
    }
    
    $query .= " ORDER BY zbsmailstat_sent ASC";
    
    return $wpdb->get_results($query);
}
