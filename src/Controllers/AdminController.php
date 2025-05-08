<?php

namespace Jkdow\SimplyBook\Controllers;

use Jkdow\SimplyBook\Email;
use Jkdow\SimplyBook\Parties;
use Jkdow\SimplyBook\Support\Logger;

class AdminController
{
    public static function setup()
    {
        add_action('admin_menu', [self::class, 'setupAdminPage']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdminAssets']);
        // Setup page actions
        LogsController::setupActions();
        QueriesController::setupActions();
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

        SettingsController::setupPage();
        SearchController::setupPage();
        LogsController::setupPage();
        EmailController::setupPage();
        QueriesController::setupPage();
    }

    public static function enqueueAdminAssets()
    {
        wp_enqueue_style('simplybook-admin-css', smbk_asset('css/admin.css'));
    }

    public static function adminPage()
    {
        smbk_render('Dashboard', [
            'emailCounts' => Email::emailCount(),
            'recentEmails' => Email::recentEmails(),
            'recentLogs' => Logger::getLogs(5),
            'totalParties' => Parties::totalParties(),
        ]);
    }
}
