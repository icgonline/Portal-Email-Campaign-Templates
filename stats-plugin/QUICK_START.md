# Quick Reference Guide

## Basic Usage

### Exporting Campaign Stats (5 Steps)

1. **Log into WordPress admin** → Look for "Email Stats Exporter" in the sidebar
2. **Select a campaign** from the dropdown
3. **Choose which data to include** (or leave defaults checked for all fields)
4. **Click "Export to CSV"**
5. **Open the downloaded file** in Excel, Google Sheets, or your preferred application

## CSV Column Definitions

| Column | Description | Example |
|--------|-------------|---------|
| Recipient Name | Full name from contact record | John Doe |
| Email Address | Recipient's email | john@example.com |
| Sent Date | When email was delivered | 2024-11-05 09:00:00 |
| Opened | Whether email was opened | Yes / No |
| Open Date | When email was first opened | 2024-11-05 09:15:00 |
| Clicked | Whether any links were clicked | Yes / No |
| Click Count | Total number of link clicks | 2 |
| First Click Date | When first link was clicked | 2024-11-05 09:20:00 |
| Clicked Link IDs | IDs of clicked links (comma-separated) | 1,2,5 |
| Unsubscribed | Whether recipient unsubscribed | Yes / No |
| Unsubscribe Date | When unsubscribe occurred | 2024-11-05 10:00:00 |

## Common Tasks

### Find Top Engaged Recipients

1. Export campaign stats
2. Sort by "Click Count" (descending)
3. Top rows show most engaged recipients

### Identify Non-Openers

1. Export campaign stats
2. Filter rows where "Opened" = "No"
3. Consider re-engagement campaign

### Track Unsubscribes

1. Export campaign stats
2. Filter rows where "Unsubscribed" = "Yes"
3. Review unsubscribe reasons if available

### Analyze Email Performance

Export stats and calculate:
- **Open Rate**: (Total Opened / Total Sent) × 100
- **Click Rate**: (Total Clicked / Total Sent) × 100
- **Unsubscribe Rate**: (Total Unsubscribed / Total Sent) × 100

### Segment Audience for Next Campaign

1. Export stats from previous campaign
2. Create segments based on engagement:
   - **Highly Engaged**: Opened AND Clicked
   - **Engaged**: Opened only
   - **Not Engaged**: No Open
3. Import segments into Jetpack CRM for targeted campaigns

## File Format & Compatibility

### Supported Applications

| Application | Format | Notes |
|-------------|--------|-------|
| Microsoft Excel | CSV | Open directly, data imports cleanly |
| Google Sheets | CSV | Import via File → Import |
| Numbers (Mac) | CSV | Import via File → Import |
| LibreOffice Calc | CSV | Open directly |
| Notepad/Text Editor | CSV | Text format, not formatted |
| SQL Database | CSV | Can import for analysis |

### Best Practices for CSV Handling

1. **Column Width**: Auto-fit all columns for readability
2. **Date Format**: Displayed as YYYY-MM-DD HH:MM:SS (sortable)
3. **Special Characters**: UTF-8 encoded, supports international characters
4. **Large Files**: Files with 100k+ rows may open slowly in Excel
5. **Backup**: Save your exported files for record-keeping

## Troubleshooting Quick Reference

| Issue | Solution |
|-------|----------|
| File won't download | Clear browser cache, try different browser |
| Campaign list empty | Create a broadcast first, or check if sent |
| Incorrect recipient count | Some emails may not have stats recorded |
| Timestamps are wrong | Timestamps are in server timezone, check WP settings |
| Special characters corrupted | Open in Excel, select UTF-8 encoding |
| "Campaign not found" error | Campaign may have been deleted or ID incorrect |

## Keyboard Shortcuts

- **Alt + E**: Jump to "Email Stats Exporter" menu (browser dependent)
- In Excel: **Ctrl + Home** (Go to cell A1)
- In Excel: **Ctrl + End** (Go to last cell with data)
- In Excel: **Ctrl + F** (Find/search)

## Menu Navigation

From WordPress admin:
1. Main menu → **Email Stats Exporter**
2. Or navigate directly to: `/wp-admin/admin.php?page=jetpack-crm-stats-exporter`

## Data Retention

- **Database**: Stats remain in Jetpack CRM indefinitely
- **Exports**: Saved CSV files are your responsibility to manage
- **Frequency**: Export as often as needed - no limits
- **Deletion**: Data is never deleted by this plugin

## Permissions

- **Who can access**: WordPress Administrators only
- **Read-only**: Plugin only exports data, never modifies it
- **Security**: Uses WordPress security tokens (nonces)

## Performance Tips

- **Large Campaigns**: Exports of 100k+ recipients may take 10-30 seconds
- **System Load**: Exports use minimal server resources
- **Browser**: Use a stable internet connection for large downloads
- **Memory**: Most systems handle exports up to 500k recipients

## Advanced Usage

### Using Exported Data in Marketing Automation

1. Export stats from Jetpack CRM
2. Import CSV into:
   - MailChimp
   - ConvertKit
   - ActiveCampaign
   - HubSpot
3. Match email addresses to create suppression lists or segments

### Creating Custom Reports

Use exported CSV with Excel formulas:

```
Open Rate: =COUNTIF(D:D,"Yes")/COUNTA(D:D)
Click Rate: =COUNTIF(F:F,"Yes")/COUNTA(F:F)
Avg Clicks: =AVERAGE(G:G)
```

### Monthly Reporting

1. Export stats for each campaign monthly
2. Store in organized folder structure
3. Create summary in spreadsheet
4. Track trends over time

## Support

For issues or questions:
1. Check INSTALLATION.md for setup help
2. Review DEVELOPER.md for technical details
3. Check WordPress debug.log (`/wp-content/debug.log`)
4. Verify Jetpack CRM is properly installed

## Version Information

Current Version: 1.0.0
Requires: WordPress 5.0+, Jetpack CRM, PHP 7.2+
