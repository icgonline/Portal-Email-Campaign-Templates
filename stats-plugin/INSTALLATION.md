# Installation & Setup Guide

## Prerequisites

Before installing this plugin, ensure you have:

- WordPress 5.0 or higher
- Jetpack CRM (formerly Zero BS CRM) plugin installed and activated
- PHP 7.2 or higher
- Administrator access to WordPress

## Installation Steps

### Method 1: Manual Installation

1. **Download the plugin** to your computer or clone from your repository

2. **Upload to WordPress**:
   - Connect to your WordPress server via FTP or use File Manager
   - Navigate to `/wp-content/plugins/`
   - Upload the `jetpack-crm-stats-exporter` folder

3. **Activate the plugin**:
   - Log in to WordPress admin panel
   - Go to **Plugins** → **Installed Plugins**
   - Find "Jetpack CRM Email Stats Exporter" and click **Activate**

4. **Verify installation**:
   - Look for a new menu item "Email Stats Exporter" in the WordPress admin sidebar
   - Click it to open the export interface

### Method 2: WordPress Admin Upload

1. In WordPress admin, go to **Plugins** → **Add New**
2. Click **Upload Plugin**
3. Choose the plugin ZIP file
4. Click **Install Now**
5. Click **Activate Plugin**

## Configuration

The plugin requires no additional configuration. It automatically:

- Detects your Jetpack CRM database tables
- Lists all available broadcast campaigns
- Provides export options

## Troubleshooting

### Plugin doesn't appear in menu

**Problem**: "Email Stats Exporter" menu doesn't show up

**Solutions**:
- Ensure Jetpack CRM is installed and activated
- Check that you're logged in as an administrator
- Try deactivating and reactivating the plugin
- Check WordPress error logs for PHP errors

### "Jetpack CRM tables not found" error

**Problem**: The plugin can't find the database tables

**Solutions**:
- Verify Jetpack CRM is properly installed
- Check database table names (prefix may differ)
- Ensure database connection is working

### Campaign list is empty

**Problem**: No campaigns appear in the dropdown

**Solutions**:
- You may not have any broadcasts yet. Create a campaign in Jetpack CRM first
- Check that the campaigns have associated statistics
- Verify database permissions allow SELECT queries

### CSV file won't download

**Problem**: Export fails or no file downloads

**Solutions**:
- Check WordPress upload directory permissions
- Ensure memory limit is sufficient (`php.ini`: `memory_limit = 256M`)
- Try reducing the number of records to export
- Clear browser cache and try again
- Check browser downloads folder - file may be there

## Uninstallation

1. Go to **Plugins** → **Installed Plugins**
2. Find "Jetpack CRM Email Stats Exporter"
3. Click **Deactivate**
4. Click **Delete**
5. Choose to delete files when prompted

**Note**: Uninstalling does NOT delete any campaign data or statistics in your Jetpack CRM.

## Security Information

This plugin:
- Requires administrator privileges to access
- Uses WordPress nonce verification for all forms
- Sanitizes all database queries with prepared statements
- Escapes all output to prevent injection
- Does not modify any campaign or stat data (read-only operations)

## Performance Considerations

For large campaigns with thousands of recipients:

- Export may take a few seconds to complete
- Memory usage is proportional to number of recipients
- CSV file size: approximately 500 bytes per recipient (without email bodies)

## Support & Troubleshooting

If you encounter issues:

1. **Check the WordPress error log**: Usually at `/wp-content/debug.log`
2. **Verify file permissions**: Plugin folder should be readable by WordPress
3. **Test database connection**: Use phpMyAdmin or WP-CLI to verify ZBS tables
4. **Check system requirements**: Ensure PHP and MySQL versions meet requirements

## Upgrading

To upgrade to a newer version:

1. Download the latest version
2. Deactivate the old plugin
3. Delete the old plugin folder via FTP/File Manager
4. Upload the new version
5. Reactivate the plugin

All campaign data and previous exports remain untouched during upgrades.
