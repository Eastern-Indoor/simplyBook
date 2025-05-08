<?php

/**
 * Plugin Name: SimplyBook.me Integration
 * Description: WordPress plugin for SimplyBook.me integration using JSON-RPC API.
 * Version: 1.1.1
 * Author: Josh Dowling
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smbk
 */

// Initialize Plugin
use Jkdow\SimplyBook\SimplyBook;

if (!function_exists('smvk_load_plugin')) {
    function smbk_load_plugin()
    {
        // Load Composer Autoloader
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            require_once __DIR__ . '/vendor/autoload.php';
        }

        class SmbkRoot
        {
            protected static $file;
            protected static $dir;

            public static function init()
            {
                self::$file = __FILE__;
                self::$dir = __DIR__;
            }

            public static function getDir()
            {
                return self::$dir;
            }

            public static function getFile()
            {
                return self::$file;
            }
        }

        if (!function_exists('smbk_root_dir')) {
            SmbkRoot::init();
            function smbk_root_dir($path = '')
            {
                return SmbkRoot::getDir() . $path;
            }

            function smbk_root_file()
            {
                return SmbkRoot::getFile();
            }
        }

        SimplyBook::init();
    }
}

if (defined('ABSPATH') && is_admin()) {
    smbk_load_plugin();
}
