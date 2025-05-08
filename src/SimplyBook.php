<?php

namespace Jkdow\SimplyBook;

use Jkdow\SimplyBook\Support\Config;
use Jkdow\SimplyBook\Support\Logger;
use Jkdow\SimplyBook\Controllers\AdminController;

class SimplyBook
{
    protected static $storageDir;

    public static function init()
    {
        $upload_dir   = wp_upload_dir();
        self::$storageDir = trailingslashit($upload_dir['basedir']) . 'simplybook';
        if (!file_exists(self::$storageDir)) {
            wp_mkdir_p(self::$storageDir);
        }
        self::loadHelperFuncions();
        add_action('plugins_loaded', [__CLASS__, 'setup']);
    }

    public static function loadHelperFuncions()
    {
        if (file_exists(__DIR__ . '/helpers.php')) {
            require_once __DIR__ . '/helpers.php';
        }
    }

    public static function setup()
    {
        // Supports and Models
        Logger::init(self::$storageDir);
        Config::init();
        Parties::init();
        Email::init();
        // Controllers
        AdminController::setup();
    }
}
