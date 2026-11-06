<?php

define('ABSPATH', __DIR__);
date_default_timezone_set('UTC');

$options = array(
    'date_format' => 'd/m/Y',
    'time_format' => 'g:i a',
    'timezone_string' => 'Europe/London',
    'gmt_offset' => 0,
);
$crm_calls = array();
$use_crm = isset($argv[1]) && $argv[1] === 'crm';

function get_option($name) {
    global $options;
    return $options[$name];
}

function wp_date($format, $timestamp) {
    $timezone = get_option('timezone_string');
    if (!$timezone) {
        $minutes = (int) round(get_option('gmt_offset') * 60);
        $timezone = sprintf('%s%02d:%02d', $minutes < 0 ? '-' : '+', floor(abs($minutes) / 60), abs($minutes) % 60);
    }
    $date = new DateTimeImmutable('@' . $timestamp);
    return $date->setTimezone(new DateTimeZone($timezone))->format($format);
}

function current_time($format) {
    return '2026-11-06 12:00:00';
}

if ($use_crm) {
    function zeroBSCRM_locale_utsToDatetimeWP($timestamp) {
        global $crm_calls;
        $crm_calls[] = $timestamp;
        return wp_date(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);
    }
}

function expect_same($expected, $actual, $label) {
    if ($expected !== $actual) {
        throw new RuntimeException($label . ': expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

require __DIR__ . '/../includes/export-handler.php';
require __DIR__ . '/../includes/database-queries.php';

expect_same('28/10/2026 1:57 pm', jetpack_crm_stats_exporter_format_timestamp('1793195822'), 'Supplied epoch');
if ($use_crm) {
    expect_same(array(1793195822), $crm_calls, 'Delegate integer epoch to CRM');
}

$summer_timestamp = (new DateTimeImmutable('2026-07-01 13:57:02 UTC'))->getTimestamp();
expect_same('01/07/2026 2:57 pm', jetpack_crm_stats_exporter_format_timestamp($summer_timestamp), 'UK daylight saving');
$options['timezone_string'] = '';
$options['gmt_offset'] = 5.5;
expect_same('28/10/2026 7:27 pm', jetpack_crm_stats_exporter_format_timestamp(1793195822), 'Positive fractional offset');
$options['gmt_offset'] = -3.5;
expect_same('28/10/2026 10:27 am', jetpack_crm_stats_exporter_format_timestamp(1793195822), 'Negative fractional offset');
$options['gmt_offset'] = 0;
expect_same('28/10/2026 1:57 pm', jetpack_crm_stats_exporter_format_timestamp(1793195822), 'UTC fallback');
foreach (array(null, '', 0, -1) as $timestamp) {
    expect_same('', jetpack_crm_stats_exporter_format_timestamp($timestamp), 'Absent timestamp');
}

class ExportRegressionDatabase {
    public $prefix = 'test_';
    public $stats_queries = 0;

    public function prepare($query, $value) {
        return array('query' => $query, 'value' => $value);
    }

    public function get_row($prepared) {
        return $prepared['value'] === 31 ? (object) array('zbsmailcamp_mailid' => 34) : null;
    }

    public function get_var($query) {
        return null;
    }

    public function get_results($prepared) {
        expect_same(34, $prepared['value'], 'Campaign 31 selects mail 34');
        $this->stats_queries++;
        return array((object) array(
            'zbsmailstat_uid' => 475,
            'zbsmailstat_uemail' => 'simon.egan@wjftechnicalsupport.co.uk',
            'zbsmailstat_sent' => 1793195822,
            'zbsmailstat_open' => 1793195822,
            'zbsmailstat_click' => 1793195822,
            'zbsmailstat_unsub' => 1793195822,
        ));
    }
}

$wpdb = new ExportRegressionDatabase();
$stats = jetpack_crm_stats_exporter_get_campaign_stats(31);
expect_same(475, $stats[0]->zbsmailstat_uid, 'Recipient contact');
$fields = array('recipient_email', 'sent_date', 'open_date', 'first_click_date', 'unsub_date');
$campaign = (object) array('ID' => 31, 'zbsmailcamp_title' => 'Regression');
$csv = jetpack_crm_stats_exporter_generate_csv($campaign, $stats, $fields);
$rows = explode("\n", trim($csv));
$recipient = str_getcsv($rows[3]);
expect_same(array(
    'simon.egan@wjftechnicalsupport.co.uk',
    '28/10/2026 1:57 pm',
    '28/10/2026 1:57 pm',
    '28/10/2026 1:57 pm',
    '28/10/2026 1:57 pm',
), $recipient, 'CSV event dates');

foreach (array('open_date', 'first_click_date', 'unsub_date') as $field) {
    $stat = (object) array('zbsmailstat_open' => -1, 'zbsmailstat_click' => -1, 'zbsmailstat_unsub' => -1);
    expect_same('', jetpack_crm_stats_exporter_get_field_value($stat, $field), 'Absent event date');
}
expect_same(array(), jetpack_crm_stats_exporter_get_campaign_stats(999), 'Unknown campaign');
expect_same(1, $wpdb->stats_queries, 'No stats query without a mail ID');

echo 'PASS: ' . ($use_crm ? 'CRM formatter' : 'WordPress fallback') . " export regressions\n";