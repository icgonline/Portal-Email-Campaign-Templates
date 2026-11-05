# Jetpack CRM Email Stats Exporter

A WordPress plugin that enables exporting detailed email campaign statistics from Jetpack CRM (also known as Zero BS CRM) broadcasts.

## Features

- **Campaign Selection**: Browse and select from all your email broadcasts
- **Full Statistics Export**: Export comprehensive recipient-level data including:
  - Recipient name and email address
  - Send timestamps
  - Email open status and timestamp
  - Click tracking (status, count, timestamps, link IDs)
  - **Link URL Resolution** - See actual clicked URLs instead of just IDs (NEW)
  - Unsubscribe information and timestamp
- **Flexible Field Selection**: Choose which fields to include in your export
- **CSV Format**: Export to standard CSV format for use in Excel, Google Sheets, or other applications
- **Easy Integration**: Seamlessly integrates with existing Jetpack CRM installations

## Requirements

- WordPress 5.0 or higher
- Jetpack CRM (Zero BS CRM) plugin installed and activated
- PHP 7.2 or higher
- MySQL database with proper permissions

## Installation

1. Upload the plugin files to your WordPress plugins directory:
   ```
   wp-content/plugins/jetpack-crm-stats-exporter/
   ```

2. Activate the plugin through the WordPress admin panel

3. Navigate to **Email Stats Exporter** in the main WordPress admin menu

## Usage

1. Go to **Email Stats Exporter** in the WordPress admin menu
2. Select a campaign from the dropdown list
3. Choose which data fields to include in your export:
   - Recipient Name
   - Email Address
   - Sent Date/Time
   - Opened (Yes/No)
   - Open Date/Time
   - Clicked (Yes/No)
   - Click Count
   - First Click Date/Time
   - Clicked Link IDs
   - **Clicked URLs** - Actual URLs from clicked links (NEW)
   - Unsubscribed (Yes/No)
   - Unsubscribe Date/Time
4. Click "Export to CSV"
5. A CSV file will download to your computer

## File Structure

```
jetpack-crm-stats-exporter/
├── jetpack-crm-stats-exporter.php    # Main plugin file
├── includes/
│   ├── admin-menu.php                # Admin interface and menu
│   ├── export-handler.php            # CSV generation logic
│   ├── database-queries.php          # Database query functions
│   ├── advanced-features.php         # Optional advanced analytics
│   └── link-analytics.php            # Link tracking and analytics
├── README.md                         # This file
├── DEVELOPER.md                      # Technical documentation
├── QUICK_START.md                    # Quick reference guide
├── LINK_TRACKING.md                  # Link tracking feature guide
└── LICENSE                           # GPL-2.0-or-later license
```

## Link Tracking Feature

**NEW**: The plugin now includes comprehensive link tracking that automatically resolves clicked link IDs to their actual URLs.

### How It Works:
- When recipients click links in your emails, Jetpack CRM records the link ID
- The plugin automatically looks up the actual URL from the link database
- Export includes both "Clicked Link IDs" and "Clicked URLs" fields
- Advanced analytics available to see which links were most popular

### Use Cases:
- Identify which CTAs (Calls-To-Action) drive the most engagement
- See which links recipients clicked instead of just IDs
- Track multi-link clicks per recipient
- Analyze URL performance across campaigns

For detailed information, see [LINK_TRACKING.md](LINK_TRACKING.md)

## Database Tables

The plugin queries the following Jetpack CRM database tables:

- `{prefix}_zbs_mail_campaigns` - Campaign information
- `{prefix}_zbs_mail_stats` - Individual recipient statistics
- `{prefix}_zbs_mail_links` - Link tracking data (for URL resolution)
- `{prefix}_zbs_contacts` - Contact/recipient names (optional)

## Output Format

The exported CSV includes:
- Campaign ID and name
- Export date/time
- Column headers with all selected fields
- One row per recipient with complete statistics

Example output:
```
Campaign ID: 1,Campaign: Welcome Email,Export Date: 2024-11-05 10:30:00
Recipient Name,Email Address,Sent Date,Opened,Open Date,Clicked,Click Count,First Click Date,Clicked Link IDs,Unsubscribed,Unsubscribe Date
John Doe,john@example.com,2024-11-05 09:00:00,Yes,2024-11-05 09:15:00,Yes,2,2024-11-05 09:20:00,"1,2",No,
Jane Smith,jane@example.com,2024-11-05 09:00:00,No,,No,0,,,No,
```

## Security

- The plugin implements WordPress nonce verification for all forms
- User capability checks ensure only administrators can access the export functionality
- Database queries use prepared statements to prevent SQL injection
- All user input is properly sanitized and escaped

## Support

For issues, feature requests, or contributions, please contact the development team.

## License

This plugin is licensed under the GPL-2.0-or-later license. See the LICENSE file for details.

## Changelog

### Version 1.0.0
- Initial release
- Campaign selection and full statistics export
- Flexible field selection
- CSV export format
