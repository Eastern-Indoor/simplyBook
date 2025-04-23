<?php

namespace Jkdow\SimplyBook\Controllers;

use Jkdow\SimplyBook\Api\SimplyApi;
use Jkdow\SimplyBook\Support\CsvHelp;
use Jkdow\SimplyBook\Support\Logger;

class AdminController
{
    public function __construct()
    {
        add_action('admin_menu', [self::class, 'setupAdminPage']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdminAssets']);
    }

    public static function setupAdminPage()
    {
        add_menu_page(
            'SimplyBook',
            'SimplyBook',
            'manage_options',
            'simplybook',
            [self::class, 'adminPage'],
            'dashicons-calendar-alt'
        );

        add_submenu_page(
            'simplybook',
            'SimplyBook Settings',
            'Settings',
            'manage_options',
            'simplybook-settings',
            [self::class, 'settingsPage']
        );
    }

    public static function enqueueAdminAssets()
    {
        wp_enqueue_style('simplybook-admin-css', smbk_asset('css/admin.css'));
    }

    public static function adminPage()
    {
        //$parties = self::runData();
        smbk_render('Dashboard', [
            'a' => 'testing',
            //'parties' => $parties,
        ]);
    }

    private static function runData()
    {
        $start = '2024-03-23';
        //$end = '2024-04-28';
        $end = '2024-03-28';
        if (CsvHelp::fileExists('parties.csv')) {
            echo 'File already exists.';
            $parties = CsvHelp::importFromCSV('parties.csv');
        } else {
            $parties = SimplyApi::previousParties($start, $end);
            if (CsvHelp::exportToCSV($parties->toArray(), 'parties.csv')) {
                Logger::info("Exported parties");
            } else {
                Logger::error('Failed to export parties');
            }
        }
        return $parties;
    }
}
