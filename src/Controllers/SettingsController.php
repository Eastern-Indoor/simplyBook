<?php

namespace Jkdow\SimplyBook\Controllers;

use Jkdow\SimplyBook\Support\Config;

class SettingsController
{
    public static function setupPage()
    {
        add_submenu_page(
            'simplybook',
            'SimplyBook Settings',
            'Settings',
            'manage_options',
            'simplybook-settings',
            [__CLASS__, 'page']
        );
    }

    public static function page()
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
