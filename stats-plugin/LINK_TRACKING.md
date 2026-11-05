# Link Tracking Guide

## Overview

The Jetpack CRM Email Stats Exporter now includes comprehensive link tracking that resolves clicked link IDs to their actual URLs, allowing you to see exactly which CTAs (Calls-To-Action) in your emails were most effective.

## How Link Tracking Works

### Database Structure

Jetpack CRM stores link information across three related tables:

**1. zbs_mail_campaigns** (Campaign info)
```
ID → Campaign ID
zbsmailcamp_mailid → Links to zbs_mail records
```

**2. zbs_mail_links** (URL tracking)
```
ID → Unique link ID within the campaign
zbsmaillink_mailid → Mail ID
zbsmaillink_linkurl → Actual URL (https://example.com or mailto:user@example.com)
zbsmaillink_hash → Unique tracking hash
```

**3. zbs_mail_stats** (Recipient activity)
```
zbsmailstat_clickedids → Comma-separated link IDs (e.g., "5,82,83,85")
zbsmailstat_click → Timestamp of first click
zbsmailstat_clickcount → Total number of clicks
```

### Link Resolution Flow

When a recipient clicks a link in your email:
1. Jetpack CRM records the link ID in `zbs_mail_stats.zbsmailstat_clickedids`
2. The plugin looks up the link ID in `zbs_mail_links` table
3. The actual URL is retrieved via `zbsmaillink_linkurl`
4. Both ID and URL are available in your export

## Using Link Tracking in Exports

### Export Options

The plugin provides two link-related export fields:

#### 1. Clicked Link IDs
- **Column Name**: "Clicked Link IDs"
- **Shows**: Comma-separated link IDs
- **Example**: `5,82,83`
- **Use case**: Cross-reference with your email HTML to find link positions

#### 2. Clicked URLs (NEW)
- **Column Name**: "Clicked URLs"
- **Shows**: Actual URLs separated by semicolons
- **Example**: `https://example.com; https://example.com/promo; mailto:contact@example.com`
- **Use case**: See exactly which links were clicked without reference lookups

### Example Export

**Row Data:**
| Email | Link IDs | URLs |
|-------|----------|------|
| john@example.com | 1,5,8 | https://www.icg.agency; https://www.icg.agency/belfast; https://www.prolificnorth.co.uk/festival |
| jane@example.com | 5,12 | https://www.icg.agency/belfast; https://www.icg.agency |

## Advanced Link Analytics

### Link Click Report

Use the advanced analytics function to see which links were most popular:

```php
$analytics = jetpack_crm_stats_exporter_get_link_analytics($campaign_id);
// Returns: Array of links sorted by unique click count
```

**Output Example:**
```
Link ID | URL | Unique Clicks
1       | https://www.icg.agency | 45
5       | https://www.icg.agency/belfast | 38
2       | mailto:contact@example.com | 22
```

### Recipient Click History

Track which links each recipient clicked:

```php
$history = jetpack_crm_stats_exporter_get_recipient_click_history($campaign_id);
// Returns: Per-recipient link click data
```

## Common Use Cases

### 1. Identify Top-Performing CTAs

**Goal**: Find which links drove the most engagement

**Steps**:
1. Export campaign with "Clicked URLs" field
2. Sort by "Click Count" (descending)
3. Top recipients show most active links
4. Use advanced analytics for aggregate link performance

### 2. A/B Test Link Placement

**Goal**: Compare engagement for different link placements

**Steps**:
1. Create two campaigns with same content but different link order
2. Export both with "Clicked Link IDs" and "Clicked URLs"
3. Compare link position (ID) with URL and click patterns
4. Highest ID = usually link at bottom; lower IDs = higher in email

### 3. Map URLs Back to Email Sections

**Goal**: Understand which email sections engaged readers most

**Steps**:
1. Note link IDs when building email templates
2. Export stats with "Clicked Link IDs"
3. Map IDs to email sections:
   - IDs 1-5 = Header section
   - IDs 6-10 = Body section
   - IDs 11-15 = Footer section
4. See which sections got most clicks

### 4. Track Multi-Link Clicks

**Goal**: See recipients who engaged multiple times

**Steps**:
1. Export with "Click Count" field
2. Filter rows where "Click Count" > 1
3. These are highly engaged recipients
4. Use "Clicked URLs" to see which links they clicked
5. Note if they clicked same link multiple times or different links

## FAQ

### Q: Why do I see semicolons between URLs in the Clicked URLs field?
**A**: Semicolons separate multiple URLs when a recipient clicked multiple links. This prevents confusion with URLs that may contain commas.

### Q: What if the link ID doesn't match?
**A**: If a link ID in `zbsmailstat_clickedids` isn't found in the `zbs_mail_links` table, it will show blank in the URL field. This is rare and usually indicates data inconsistency.

### Q: Can I see which link was clicked first?
**A**: The "First Click Date" field shows when the first click occurred. To see which specific link was clicked first, you'd need to use Jetpack CRM's detailed activity log or a custom script.

### Q: Do mailto: links get tracked?
**A**: Yes! Email links appear as `mailto:email@example.com` in the Clicked URLs field.

### Q: How are duplicate links handled?
**A**: If the same URL appears multiple times in an email, each instance gets a unique link ID. The export will show the URL multiple times if each link was clicked.

## Technical Details

### Database Query for Link Lookup

```sql
SELECT zbsmaillink_linkurl 
FROM wp_zbs_mail_links 
WHERE ID = [link_id] 
AND zbsmaillink_mailid = [mail_id]
```

### Link Resolution Function

```php
jetpack_crm_stats_exporter_resolve_link_ids($clicked_ids)
// Input: "5,82,83"
// Output: "https://example.com; https://example.com/page2; mailto:contact@example.com"
```

### Performance Notes

- Link lookups are cached per export to minimize database queries
- Large campaigns (100k+ clicks) may take 10-30 seconds for full URL resolution
- Individual link queries use prepared statements to prevent SQL injection

## Troubleshooting

### Links appear blank in export

**Cause**: Link IDs exist but no corresponding URL in `zbs_mail_links`

**Solution**:
1. Verify email was sent through Jetpack CRM (not imported)
2. Check if table exists: `SHOW TABLES LIKE '%zbs_mail_links%'`
3. Contact Jetpack CRM support if data is corrupted

### All recipients show same links

**Cause**: Email template links are standardized (expected behavior)

**Solution**: This is normal. All recipients receive the same email, so all have access to the same links. Differences appear in which links they clicked and how many times.

### Link count doesn't match click count

**Cause**: Click count includes repeated clicks of same link, unique links shows distinct URLs

**Solution**: This is correct. A recipient might click the same link 3 times (click count = 3, unique links = 1).

## Related Metrics

| Metric | Meaning | Use |
|--------|---------|-----|
| Link Click Count | Total clicks across all links | Overall engagement |
| Unique Link Clicks | Number of distinct links clicked per recipient | Click diversity |
| First Click Date | When first link was clicked | Engagement timing |
| Clicked URLs | Actual URLs that were clicked | Content analysis |

## Advanced Customization

### Custom Link Analytics Export

```php
// Generate link report with highest-clicked links first
$link_report = jetpack_crm_stats_exporter_export_link_report($campaign_id);
```

### Filter Recipients by Specific Link

```php
// Get all recipients who clicked link ID 5
// Requires custom SQL query on zbs_mail_stats.zbsmailstat_clickedids
```

## Best Practices

1. **Include both ID and URL fields** when first analyzing campaigns for reference
2. **Sort by Click Count** to quickly identify engaged recipients
3. **Use link analytics** to optimize future campaigns
4. **Track URL patterns** to understand which types of content resonate
5. **Export regularly** to build historical engagement data

## See Also

- [README.md](README.md) - Main plugin documentation
- [DEVELOPER.md](DEVELOPER.md) - Technical development guide
- [QUICK_START.md](QUICK_START.md) - Quick reference guide
