<?php

namespace Jkdow\SimplyBook\Controllers;

use Jkdow\SimplyBook\Support\Config;
use Jkdow\SimplyBook\Support\Logger;

class AdminController
{
    public function __construct()
    {
        add_action('admin_menu', [self::class, 'setupAdminPage']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdminAssets']);

        new SearchController();
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
        //wp_enqueue_media('simplybook-admin-media', smbk_asset('images/parties.css'));
    }

    public static function adminPage()
    {
        Logger::clear();
        smbk_render('Dashboard', [
            'a' => 'testing',
            'login' => smbk_config('api.login'),
            //'parties' => $parties,
        ]);
    }

    public static function settingsPage()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['smbk_nonce'])) {
            if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['smbk_nonce'])), 'smbk_save_settings')) {
                // sanitize and save each field
                Config::set('api.company',  sanitize_text_field(wp_unslash($_POST['company'])));
                Config::set('api.login',    sanitize_text_field(wp_unslash($_POST['login'])));
                Config::set('api.password', sanitize_text_field(wp_unslash($_POST['password'])));
                Config::set('bookings.partyid', sanitize_text_field(wp_unslash($_POST['partyid'])));

                smbk_flash('Settings saved successfully.', 'success');
            } else {
                smbk_flash('Security check failed, your settings were not saved.', 'error');
            }
        }

        smbk_render('Settings', [
            'company' => smbk_config('api.company'),
            'login' => smbk_config('api.login'),
            'password' => smbk_config('api.password'),
            'partyid' => smbk_config('bookings.partyid'),
        ]);
    }

}
