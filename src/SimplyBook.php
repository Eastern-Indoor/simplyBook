<?php

namespace Jkdow\SimplyBook;

use Jkdow\SimplyBook\Support\Config;
use Jkdow\SimplyBook\Support\Logger;
use Jkdow\SimplyBook\Api\SimplyApi;
use Jkdow\SimplyBook\Support\CsvHelp;

class SimplyBook
{
    protected static $pluginRoot;

    public static function init($pluginRoot)
    {
        self::$pluginRoot = $pluginRoot;
        //add_action('plugins_loaded', [self::class, 'setup']);
        self::setup();
    }

    public static function setup()
    {
        Config::init(self::$pluginRoot);
        Logger::init(self::$pluginRoot);
        SimplyApi::init(self::$pluginRoot);
        CsvHelp::init(self::$pluginRoot);
        Logger::info("Initialized");
    }

    public static function runTest()
    {
        $start = '2024-03-23';
        //$end = '2024-04-28';
        $end = '2024-03-28';
        $parties = SimplyApi::previousParties($start, $end);
        if (CsvHelp::exportToCSV($parties->toArray(), 'parties.csv')) {
            Logger::info("Exported parties");
        } else {
            Logger::error('Failed to export parties');
        }
    }
}
