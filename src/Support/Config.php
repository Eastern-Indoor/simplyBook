<?php

namespace Jkdow\SimplyBook\Support;

use Dotenv\Dotenv;

class Config
{
    protected static $config = [];

    public static function load($directory)
    {
        foreach (glob($directory . '/*.php') as $file) {
            $key = basename($file, '.php');
            self::$config[$key] = require $file;
        }
    }

    public static function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $segment) {
            if (!isset($value[$segment])) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public static function init($pluginRoot)
    {
        if (file_exists($pluginRoot . '/.env')) {
            $dotenv = Dotenv::createImmutable($pluginRoot);
            $dotenv->load(); // Use `load()` instead of `safeLoad()`

            // Manually set getenv() values (required on some PHP setups)
            foreach ($_ENV as $key => $value) {
                putenv("$key=$value");
            }
        }

        // Load Config
        self::load($pluginRoot . '/src/Config');
    }
}
