<?php

namespace Jkdow\SimplyBook;

use Jkdow\SimplyBook\Support\Config;
use Jkdow\SimplyBook\Support\Logger;
use Jkdow\SimplyBook\Api\SimplyApi;
use Jkdow\SimplyBook\Support\CsvHelp;
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
        self::setup();
        new AdminController();
    }

    public static function loadHelperFuncions()
    {
        if (file_exists(__DIR__ . '/helpers.php')) {
            require_once __DIR__ . '/helpers.php';
        }
    }

    public static function setup()
    {
        Logger::init(self::$storageDir);
        Logger::clear();
        Config::init();
        //SimplyApi::init(self::$storageDir);
        CsvHelp::init(self::$storageDir);
    }
}
