<?php

namespace Jkdow\SimplyBook\Controllers;

use Jkdow\SimplyBook\Support\Logger;

class LogsController {
    public static function setupPage() {
        add_submenu_page(
            'simplybook',                 // parent slug
            __('Plugin Logs', 'smbk'),    // page title
            __('Logs', 'smbk'),           // menu title
            'manage_options',             // capability
            'simplybook-logs',            // menu slug
            [__CLASS__, 'page']     // callback
        );
    }

    public static function setupActions() {
        add_action('admin_post_smbk_clear_logs', [__CLASS__, 'handle_clear_logs']);
    }

    public static function page() {
        $logs = Logger::getLogs(200);
        smbk_render('Logs', ['logs' => $logs]);
    }

    public static function handle_clear_logs()
    {
        check_admin_referer('smbk_clear_logs', 'smbk_clear_logs_nonce');
        Logger::clear();
        wp_safe_redirect(wp_get_referer() ?: admin_url('admin.php?page=simplybook-logs'));
        smbk_flash(esc_html__('Logs have been cleared.', 'smbk'), 'success');
        exit;
    }
}
