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

if (!defined('ABSPATH')) {
    //exit; // Prevent direct access
}

// Load Composer Autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

$pluginRoot = dirname(__FILE__);

// Setup Config
use Jkdow\SimplyBook\Support\Config;

if (!function_exists('config')) {
    function config($key, $default = null) {
        return Config::get($key, $default);
    }
}

// Initialize Plugin
use Jkdow\SimplyBook\SimplyBook;

SimplyBook::init($pluginRoot);
SimplyBook::runTest();
