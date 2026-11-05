<?php
/**
 * Admin Menu - Jetpack CRM Stats Exporter
 * 
 * Registers the admin menu page for the stats exporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hook into WordPress admin_menu to register our menu
 */
add_action('admin_menu', 'jetpack_crm_stats_exporter_register_menu');

function jetpack_crm_stats_exporter_register_menu() {
    add_menu_page(
        'Email Campaign Stats',
        'Email Stats Exporter',
        'manage_options',
        'jetpack-crm-stats-exporter',
        'jetpack_crm_stats_exporter_render_page',
        'dashicons-chart-bar',
        76 // Position after other Jetpack CRM menus
    );
}

/**
 * Render the admin page
 */
function jetpack_crm_stats_exporter_render_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <div class="jetpack-crm-stats-exporter-container">
            <form method="post" action="" class="jetpack-crm-stats-exporter-form">
                <?php wp_nonce_field('jetpack_crm_stats_export', 'jetpack_crm_stats_nonce'); ?>
                
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="campaign_id">Select Campaign:</label>
                            </th>
                            <td>
                                <select 
                                    name="campaign_id" 
                                    id="campaign_id" 
                                    required
                                    style="min-width: 300px;"
                                >
                                    <option value="">-- Choose a Campaign --</option>
                                    <?php jetpack_crm_stats_exporter_list_campaigns(); ?>
                                </select>
                                <p class="description">Select a broadcast campaign to export statistics for.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label>Export Data:</label>
                            </th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="checkbox" name="export_fields[]" value="recipient_name" checked>
                                        Recipient Name
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="export_fields[]" value="recipient_email" checked>
                                        Email Address
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="export_fields[]" value="sent_date" checked>
                                        Sent Date/Time
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="export_fields[]" value="opened" checked>
                                        Opened (Yes/No)
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="export_fields[]" value="open_date" checked>
                                        Open Date/Time
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="export_fields[]" value="clicked" checked>
                                        Clicked (Yes/No)
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="export_fields[]" value="click_count" checked>
                                        Click Count
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="export_fields[]" value="first_click_date" checked>
                                        First Click Date/Time
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="export_fields[]" value="clicked_ids" checked>
                                        Clicked Link IDs
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="export_fields[]" value="clicked_urls" checked>
                                        Clicked URLs
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="export_fields[]" value="unsubscribed" checked>
                                        Unsubscribed (Yes/No)
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="export_fields[]" value="unsub_date" checked>
                                        Unsubscribe Date/Time
                                    </label>
                                </fieldset>
                                <p class="description">Choose which fields to include in the CSV export.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <?php submit_button('Export to CSV', 'primary', 'export_stats', true); ?>
            </form>
        </div>
        
        <?php if (isset($_GET['jetpack_crm_stats_exported']) && $_GET['jetpack_crm_stats_exported'] === '1'): ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Success!</strong> Your campaign statistics have been exported to CSV.</p>
            </div>
        <?php endif; ?>
        
        <div class="jetpack-crm-stats-exporter-info">
            <h2>About This Tool</h2>
            <p>This plugin allows you to export detailed statistics from your Jetpack CRM broadcast campaigns.</p>
            <h3>What's Included:</h3>
            <ul>
                <li>Recipient contact information</li>
                <li>Send timestamps</li>
                <li>Email open status and timestamps</li>
                <li>Click tracking data (status, count, timestamps, link IDs)</li>
                <li>Unsubscribe information</li>
            </ul>
            <h3>Export Format:</h3>
            <p>Data is exported as a CSV (comma-separated values) file that can be opened in Excel, Google Sheets, or any text editor.</p>
        </div>
        
        <style>
            .jetpack-crm-stats-exporter-container {
                background: white;
                border: 1px solid #ccc;
                border-radius: 5px;
                padding: 20px;
                margin: 20px 0;
                max-width: 600px;
            }
            
            .jetpack-crm-stats-exporter-form .form-table {
                margin: 0;
            }
            
            .jetpack-crm-stats-exporter-info {
                background: #f5f5f5;
                border: 1px solid #ddd;
                border-radius: 5px;
                padding: 20px;
                margin-top: 20px;
                max-width: 600px;
            }
            
            .jetpack-crm-stats-exporter-info h2 {
                margin-top: 0;
            }
            
            .jetpack-crm-stats-exporter-info ul {
                margin-left: 20px;
            }
        </style>
    </div>
    <?php
}

/**
 * Get list of campaigns and render as select options
 */
function jetpack_crm_stats_exporter_list_campaigns() {
    global $wpdb;
    
    // Get the table name prefix from ZBS CRM
    $table_name = $wpdb->prefix . 'zbs_mail_campaigns';
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
        echo '<option value="">-- Jetpack CRM tables not found --</option>';
        return;
    }
    
    $campaigns = $wpdb->get_results(
        "SELECT ID, zbsmailcamp_title FROM {$table_name} ORDER BY zbsmailcamp_created DESC"
    );
    
    if (empty($campaigns)) {
        echo '<option value="">-- No campaigns found --</option>';
        return;
    }
    
    foreach ($campaigns as $campaign) {
        $campaign_id = absint($campaign->ID);
        $campaign_name = esc_html($campaign->zbsmailcamp_title);
        echo "<option value='{$campaign_id}'>{$campaign_name} (ID: {$campaign_id})</option>";
    }
}

/**
 * Handle the export request
 */
add_action('admin_init', 'jetpack_crm_stats_exporter_handle_export');

function jetpack_crm_stats_exporter_handle_export() {
    // Check if export button was clicked
    if (!isset($_POST['export_stats'])) {
        return;
    }
    
    // Verify nonce
    if (!isset($_POST['jetpack_crm_stats_nonce']) || 
        !wp_verify_nonce($_POST['jetpack_crm_stats_nonce'], 'jetpack_crm_stats_export')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to perform this action');
    }
    
    // Get campaign ID
    $campaign_id = isset($_POST['campaign_id']) ? absint($_POST['campaign_id']) : 0;
    if (!$campaign_id) {
        return;
    }
    
    // Get selected fields
    $export_fields = isset($_POST['export_fields']) ? (array) $_POST['export_fields'] : array();
    if (empty($export_fields)) {
        $export_fields = array(
            'recipient_name', 'recipient_email', 'sent_date', 'opened', 
            'open_date', 'clicked', 'click_count', 'first_click_date',
            'clicked_ids', 'unsubscribed', 'unsub_date'
        );
    }
    
    // Call the export handler
    jetpack_crm_stats_exporter_export_campaign($campaign_id, $export_fields);
}
