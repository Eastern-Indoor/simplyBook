<?php

namespace Jkdow\SimplyBook\Support;

class Config
{
    /** in-memory cache of DB values after init() */
    protected static array $config = [];
    protected static array $dbValues = [];
    protected static array $foundKeys = [];
    protected static $isActivation = false;

    const TABLE_NAME = 'smbk_config';

    public static function init()
    {
        register_activation_hook(smbk_root_file(), [__CLASS__, 'activate']);
        //register_deactivation_hook(smbk_root_file(), [__CLASS__, 'deactivate']);
        self::$isActivation = false;

        self::load();
    }

    public static function activate()
    {
        global $wpdb;

        $table = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            config_key   varchar(191) NOT NULL,
            config_value longtext      NOT NULL,
            PRIMARY KEY (config_key)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        self::$isActivation = true;
        self::load();
    }

    public static function deactivate()
    {
        global $wpdb;
        $table = $wpdb->prefix . SELF::TABLE_NAME;

        $wpdb->query("DROP TABLE IF EXISTS {$table}");
    }

    public static function load()
    {
        // Load Db table entries into memory
        $directory = smbk_root_dir('/src/Config');
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_NAME;
        $results = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);
        foreach ($results as $row) {
            self::$dbValues[$row['config_key']] = $row['config_value'];
        }
        foreach (glob($directory . '/*.php') as $file) {
            $key = basename($file, '.php');
            self::$config[$key] = require $file;
        }
    }

    public static function setupVal($key, $default)
    {
        if (!isset(self::$dbValues[$key]) && self::$isActivation) {
            self::dbSet($key, $default);
        }
        self::$foundKeys[] = $key;
        return ['key' => $key, 'val' => self::$dbValues[$key] ?? $default];
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
        return $value['val'] ?? $default;
    }

    public static function set($key, $val)
    {
        // check if key or val is empty
        if (empty($key) || empty($val)) {
            return;
        }
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $segment) {
            if (!isset($value[$segment])) {
                return null;
            }
            $value = $value[$segment];
        }


        $dbKey = $value['key'];
        self::dbSet($dbKey, $val);
        self::$config[$keys[0]][$keys[1]]['val'] = $val;
    }

    protected static function dbSet($key, $val)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_NAME;
        $res = $wpdb->replace(
            $table,
            ['config_key' => $key, 'config_value' => $val],
            ['%s', '%s']
        );
        if ($res !== false) {
            self::$dbValues[$key] = $val;
        } else {
            Logger::error('Failed to set value for ' . $key);
        }
    }
}
