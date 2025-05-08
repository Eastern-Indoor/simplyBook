<?php

namespace Jkdow\SimplyBook\Controllers;

use Jkdow\SimplyBook\Api\SimplyApi;
use Jkdow\SimplyBook\Email;
use Jkdow\SimplyBook\Parties;

class SearchController
{
    public static function setupPage()
    {
        add_submenu_page(
            'simplybook',
            'SimplyBook Search',
            'Search',
            'manage_options',
            'simplybook-search',
            [self::class, 'searchPage']
        );
    }

    public static function searchPage()
    {
        SimplyApi::init();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_parties'])) {
            self::handleSendEmail();
        }
        self::handlePageLoad();
    }

    protected static function handlePageLoad()
    {
        $data = self::updateDate();
        smbk_render('Search', [
            'parties' => $data['parties'],
            'start'   => $data['start'],
            'end'     => $data['end'],
        ]);
    }

    protected static function updateDate()
    {
        // 1) Retrieve stored range (if any), or fall back to defaults:
        $stored_start = get_option('smbk_last_start');
        $stored_end   = get_option('smbk_last_end');

        // 2) Grab incoming GET (if you just submitted), else use stored, else default to this month
        $raw_start = $_GET['start'] ?? $stored_start  ?? null;
        $raw_end   = $_GET['end']   ?? $stored_end    ?? null;

        //Logger::info('raw_start' . $raw_start . ', raw_end' . $raw_end);

        if ($raw_start && $raw_end) {
            // 3) Sanitize & validate YYYY-MM-DD format
            $start = preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw_start)
                ? $raw_start
                : date('Y-m-01');

            $end   = preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw_end)
                ? $raw_end
                : date('Y-m-t');
            //Logger::info('Sanitized', [ 'start' => $start, 'end'   => $end, ]);

            // 4) If this was a fresh submit (i.e. via GET), store it for next time
            if (isset($_GET['start'], $_GET['end'])) {
                update_option('smbk_last_start', $start);
                update_option('smbk_last_end',   $end);
            }

            $parties = Parties::getParties($start, $end);

            return [
                'parties' => $parties,
                'start' => $start,
                'end' => $end,
            ];
        } else {
            return [
                'parties' => [],
                'start'   => null,
                'end'     => null,
            ];
        }
    }

    /**
     * Process email send requests from POST.
     */
    protected static function handleSendEmail(): void
    {
        check_admin_referer('smbk_email_send', 'smbk_email_nonce');
        $sentList = array_map('intval', $_POST['send'] ?? []);
        if (empty($sentList)) {
            return;
        }

        global $wpdb;
        $ptable = $wpdb->prefix . Parties::TABLE_PARTIES;

        // Build placeholders for IN()
        $placeholders = implode(',', array_fill(0, count($sentList), '%d'));
        $sql = "SELECT * FROM {$ptable} WHERE id IN ({$placeholders})";

        // Query parties by primary key IDs
        $parties = $wpdb->get_results(
            $wpdb->prepare($sql, ...$sentList),
            ARRAY_A
        );

        $count = 0;
        foreach ($parties as $party) {
            if (Email::emailParty($party)) {
                $count++;
            }
        }

        if ($count > 0) {
            smbk_flash(
                sprintf(
                    _n('%d email sent.', '%d emails sent.', $count, 'smbk'),
                    $count
                ),
                'success'
            );
        }
    }
}
