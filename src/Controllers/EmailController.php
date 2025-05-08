<?php

namespace Jkdow\SimplyBook\Controllers;

use Jkdow\SimplyBook\Email;
use Jkdow\SimplyBook\Parties;

class EmailController {
    public static function setupPage() {
        add_submenu_page(
            'simplybook',                        // parent slug
            __('Email History', 'smbk'),     // page <title>
            __('Email History', 'smbk'),     // menu label
            'manage_options',                   // capability
            'simplybook-emails',               // menu slug
            [__CLASS__, 'page']  // callback
        );
    }

    public static function page() {
        global $wpdb;

        $etable = $wpdb->prefix . Email::TABLE;
        // Assuming your parties table constant lives in Parties
        $ptable = $wpdb->prefix . Parties::TABLE_PARTIES;

        // Join to pull in client email (adjust column names if your schema differs)
        $rows = $wpdb->get_results("
            SELECT e.id, e.party_id, e.sent_at, p.client_email
            FROM {$etable} AS e
            LEFT JOIN {$ptable} AS p ON p.id = e.party_id
            ORDER BY e.sent_at DESC
            LIMIT 100
        ", ARRAY_A);
        smbk_render('Emails', [
            'rows' => $rows,
        ]);
    }
}
