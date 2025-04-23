<?php

/**
 * Plugin Name: SimplyBook.me Integration
 * Plugin URI: https://yourwebsite.com
 * Description: WordPress plugin for SimplyBook.me integration using JSON-RPC API.
 * Version: 1.0.0
 * Author: Josh Dowling
 * Author URI: https://yourwebsite.com
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simply-book
 */

// Initialize Plugin
use Jkdow\SimplyBook\SimplyBook;

if (!defined('ABSPATH') || !is_admin()) {
    exit; // Prevent direct access
}

// Load Composer Autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class SmbkRoot {
    protected static $file;
    protected static $dir;

    public static function init() {
        self::$file = __FILE__;
        self::$dir = __DIR__;
    }

    public static function getDir() {
        return self::$dir;
    }

    public static function getFile() {
        return self::$file;
    }
}

if (!function_exists('smbk_root_dir')) {
    SmbkRoot::init();
    function smbk_root_dir($path = '') {
        return SmbkRoot::getDir() . $path;
    }

    function smbk_root_file() {
        return SmbkRoot::getFile();
    }
}

SimplyBook::init();
