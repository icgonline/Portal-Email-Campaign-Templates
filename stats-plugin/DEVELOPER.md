# Developer Documentation

## Overview

This document provides technical details for developers who want to extend, customize, or integrate the Jetpack CRM Email Stats Exporter plugin.

## Plugin Architecture

### File Structure

```
jetpack-crm-stats-exporter/
├── jetpack-crm-stats-exporter.php     # Main plugin entry point
├── includes/
│   ├── admin-menu.php                 # Admin UI and form handling
│   ├── export-handler.php             # CSV generation and download
│   ├── database-queries.php           # Database abstraction layer
│   └── advanced-features.php          # Optional advanced functions
├── README.md                          # User documentation
├── INSTALLATION.md                    # Installation guide
├── DEVELOPER.md                       # This file
└── LICENSE                            # GPL-2.0-or-later
```

## Hooks & Filters

The plugin defines no custom hooks or filters currently, but is structured to allow for future extensibility.

### Adding Custom Hooks

To add a custom hook for extending functionality:

```php
// In export-handler.php, before CSV generation:
do_action('jetpack_crm_stats_exporter_before_export', $campaign_id, $export_fields);

// In admin-menu.php, after form submission:
apply_filters('jetpack_crm_stats_exporter_campaign_list', $campaigns);
```

## Database Schema

### Tables Used

**`{prefix}_zbs_mail_campaigns`**
- `ID` (int): Campaign ID
- `zbsmailcamp_title` (varchar): Campaign name
- `zbsmailcamp_mailid` (int): Associated mail ID
- `zbsmailcamp_status` (int): Campaign status
- `zbsmailcamp_created` (int): Unix timestamp of creation

**`{prefix}_zbs_mail_stats`**
- `ID` (int): Stat record ID
- `zbsmailstat_mailid` (int): Mail ID (links to campaign)
- `zbsmailstat_uemail` (varchar): Recipient email
- `zbsmailstat_uid` (int): Contact ID
- `zbsmailstat_sent` (int): Unix timestamp when sent
- `zbsmailstat_open` (int): Unix timestamp when opened (NULL if not opened)
- `zbsmailstat_click` (int): Unix timestamp when first clicked (NULL if not clicked)
- `zbsmailstat_clickcount` (int): Number of clicks
- `zbsmailstat_unsub` (int): Unix timestamp when unsubscribed (NULL if not)
- `zbsmailstat_clickedids` (varchar): Comma-separated link IDs clicked

**`{prefix}_zbs_contacts`**
- `ID` (int): Contact ID
- `zbsc_fname` (varchar): First name
- `zbsc_lname` (varchar): Last name

## Key Functions

### Database Functions (database-queries.php)

```php
// Get campaign by ID
jetpack_crm_stats_exporter_get_campaign_data($campaign_id)

// Get stats for a campaign
jetpack_crm_stats_exporter_get_campaign_stats($campaign_id)

// Get contact name
jetpack_crm_stats_exporter_get_contact_name($contact_id)

// Get mail ID for campaign
jetpack_crm_stats_exporter_get_mail_id($campaign_id)

// NEW: Get link URL from link ID
jetpack_crm_stats_exporter_get_link_url($link_id)

// NEW: Resolve comma-separated link IDs to URLs
jetpack_crm_stats_exporter_resolve_link_ids($clicked_ids)
```

### Export Functions (export-handler.php)

```php
// Main export handler
jetpack_crm_stats_exporter_export_campaign($campaign_id, $export_fields)

// Generate CSV content
jetpack_crm_stats_exporter_generate_csv($campaign_data, $stats, $export_fields)

// Get field value for a stat record
jetpack_crm_stats_exporter_get_field_value($stat, $field)

// Format Unix timestamp
jetpack_crm_stats_exporter_format_timestamp($timestamp)
```

### Advanced Functions (advanced-features.php & link-analytics.php)

```php
// Get campaigns with summary stats
jetpack_crm_stats_exporter_get_campaigns_summary()

// Calculate engagement metrics
jetpack_crm_stats_exporter_get_engagement_metrics($campaign_id)

// Get engagement timeline
jetpack_crm_stats_exporter_get_engagement_timeline($campaign_id)

// Export with metrics included
jetpack_crm_stats_exporter_export_with_metrics($campaign_id, $export_fields)

// Filter recipients by engagement status
jetpack_crm_stats_exporter_filter_by_status($campaign_id, $status)

// NEW: Get link click analytics (which links were clicked, how many times)
jetpack_crm_stats_exporter_get_link_analytics($campaign_id)

// NEW: Export link click report as CSV
jetpack_crm_stats_exporter_export_link_report($campaign_id)

// NEW: Get recipient click history
jetpack_crm_stats_exporter_get_recipient_click_history($campaign_id)
```

## Customization Examples

### Example 1: Add Custom Export Format (JSON)

Create a new file `includes/export-json.php`:

```php
<?php
function jetpack_crm_stats_exporter_export_json($campaign_id, $stats) {
    $campaign = jetpack_crm_stats_exporter_get_campaign_data($campaign_id);
    
    $json_data = array(
        'campaign' => array(
            'id' => $campaign->ID,
            'title' => $campaign->zbsmailcamp_title,
            'exported' => current_time('mysql')
        ),
        'statistics' => array()
    );
    
    foreach ($stats as $stat) {
        $json_data['statistics'][] = array(
            'email' => $stat->zbsmailstat_uemail,
            'sent' => (int) $stat->zbsmailstat_sent,
            'opened' => $stat->zbsmailstat_open > 0,
            'clicked' => $stat->zbsmailstat_click > 0
        );
    }
    
    return json_encode($json_data, JSON_PRETTY_PRINT);
}
```

Then modify `admin-menu.php` to add JSON export option:

```php
// Add to form
<input type="radio" name="export_format" value="csv" checked> CSV
<input type="radio" name="export_format" value="json"> JSON

// Handle in export logic
if ($_POST['export_format'] === 'json') {
    $json_content = jetpack_crm_stats_exporter_export_json($campaign_id, $stats);
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="campaign-' . $campaign_id . '-stats.json"');
    echo $json_content;
    die();
}
```

### Example 2: Add Engagement Metrics to Export

Modify `export-handler.php` to include metrics:

```php
function jetpack_crm_stats_exporter_generate_csv($campaign_data, $stats, $export_fields) {
    $output = fopen('php://memory', 'w');
    
    // Add metrics at top
    $metrics = jetpack_crm_stats_exporter_get_engagement_metrics($campaign_data->ID);
    
    fputcsv($output, array('Open Rate: ' . $metrics['open_rate'] . '%'));
    fputcsv($output, array('Click Rate: ' . $metrics['click_rate'] . '%'));
    fputcsv($output, array('Unsubscribe Rate: ' . $metrics['unsub_rate'] . '%'));
    fputcsv($output, array(''));
    
    // Continue with existing logic...
}
```

### Example 3: Add Scheduled Exports

Create `includes/scheduled-exports.php`:

```php
<?php
// Schedule daily exports
add_action('init', function() {
    if (!wp_next_scheduled('jetpack_crm_stats_daily_export')) {
        wp_schedule_event(time(), 'daily', 'jetpack_crm_stats_daily_export');
    }
});

// Handler for scheduled export
add_action('jetpack_crm_stats_daily_export', function() {
    $campaigns = jetpack_crm_stats_exporter_get_campaigns_summary();
    
    foreach ($campaigns as $campaign) {
        // Generate exports
        $stats = jetpack_crm_stats_exporter_get_campaign_stats($campaign->ID);
        $csv = jetpack_crm_stats_exporter_generate_csv($campaign, $stats, array(...));
        
        // Save to uploads directory
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/campaign-' . $campaign->ID . '-stats.csv';
        file_put_contents($file_path, $csv);
    }
});
```

## Testing

### Manual Testing Checklist

- [ ] Campaign dropdown populates correctly
- [ ] All field checkboxes work
- [ ] CSV exports with correct headers
- [ ] Timestamps format correctly
- [ ] Open/Click/Unsub status shows Yes/No
- [ ] File downloads with correct name
- [ ] File opens in Excel without corruption
- [ ] Uncheck fields removes columns from export
- [ ] Nonce validation prevents CSRF attacks
- [ ] Non-admin users can't access exporter

### Automated Testing Setup

```php
// Example PHPUnit test
class TestStatsExporter extends WP_UnitTestCase {
    
    public function test_campaign_data_retrieval() {
        $campaign_id = 1;
        $campaign = jetpack_crm_stats_exporter_get_campaign_data($campaign_id);
        $this->assertNotNull($campaign);
        $this->assertEquals(1, $campaign->ID);
    }
    
    public function test_csv_generation() {
        $campaign_data = (object) array(
            'ID' => 1,
            'zbsmailcamp_title' => 'Test Campaign'
        );
        $stats = array();
        $fields = array('recipient_email', 'opened');
        
        $csv = jetpack_crm_stats_exporter_generate_csv(
            $campaign_data, 
            $stats, 
            $fields
        );
        
        $this->assertStringContainsString('Email Address', $csv);
        $this->assertStringContainsString('Opened', $csv);
    }
}
```

## Performance Optimization

### Query Optimization

For large databases, consider adding indexes:

```sql
ALTER TABLE `wp_zbs_mail_stats` 
  ADD INDEX `idx_mailid` (`zbsmailstat_mailid`),
  ADD INDEX `idx_uemail` (`zbsmailstat_uemail`),
  ADD INDEX `idx_uid` (`zbsmailstat_uid`);
```

### Memory Usage

For exports with 100,000+ recipients:

```php
// Use streaming instead of loading all into memory
function jetpack_crm_stats_exporter_export_large_campaign($campaign_id, $batch_size = 1000) {
    $offset = 0;
    $output = fopen('php://output', 'w');
    
    while (true) {
        $stats = jetpack_crm_stats_exporter_get_campaign_stats_paginated($campaign_id, $batch_size, $offset);
        if (empty($stats)) break;
        
        foreach ($stats as $stat) {
            fputcsv($output, array(...));
        }
        
        $offset += $batch_size;
    }
    
    fclose($output);
}
```

## Security Considerations

### SQL Injection Prevention

All database queries use `$wpdb->prepare()`:

```php
// ✓ Safe
$result = $wpdb->get_row(
    $wpdb->prepare("SELECT * FROM {$table} WHERE ID = %d", $id)
);

// ✗ Unsafe - never do this
$result = $wpdb->get_row("SELECT * FROM {$table} WHERE ID = {$id}");
```

### CSRF Protection

All forms use nonces:

```php
// Generate nonce in form
wp_nonce_field('jetpack_crm_stats_export', 'jetpack_crm_stats_nonce');

// Verify in handler
if (!wp_verify_nonce($_POST['jetpack_crm_stats_nonce'], 'jetpack_crm_stats_export')) {
    wp_die('Security check failed');
}
```

### Capability Checks

Always verify user permissions:

```php
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}
```

## Debugging

Enable debug logging in `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Log custom messages
error_log('Campaign stats export: ' . $campaign_id);
```

View logs at `/wp-content/debug.log`

## Contributing

When extending this plugin:

1. Follow WordPress coding standards
2. Use prepared statements for all queries
3. Sanitize input, escape output
4. Add comprehensive comments
5. Test with multiple campaigns
6. Ensure backward compatibility

## Resources

- [WordPress Plugin Development](https://developer.wordpress.org/plugins/)
- [WordPress Security](https://developer.wordpress.org/plugins/security/)
- [Jetpack CRM Documentation](https://jetpack.com/crm/)
- [Zero BS CRM Database Structure](https://docs.zbs-crm.com/)
