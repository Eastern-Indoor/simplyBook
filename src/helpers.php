<?php

// Setup Config
use Jkdow\SimplyBook\Support\Config;
use Jkdow\SimplyBook\Support\Flash;

if (!function_exists('smbk_config')) {
    function smbk_config($key, $default = null)
    {
        return Config::get($key, $default);
    }
}

// render view template
if (!function_exists('smbk_render')) {
    function smbk_render($template, $data = [])
    {
        $viewsPath = plugin_dir_path(__FILE__) . 'Views/';
        $data['smbkPageHeader'] = $template;
        if (!empty($data)) {
            extract($data);
        }
        // Load Header
        $header = $viewsPath . 'Header.php';
        include $header;
        $file = $viewsPath . $template . '.php';
        if (file_exists($file)) {
            include $file;
        } else {
            // Optionally handle the error if the file isn't found.
            echo sprintf(__('View file "%s" not found at %s', 'simplybook'), $template, $file);
        }
    }
}

if (!function_exists('smbk_asset')) {
    function smbk_asset($path = '')
    {
        $path = 'src/assets/' . $path;
        return plugins_url($path, smbk_root_file());
    }
}

if (!function_exists('smbk_flash')) {
    if (!function_exists('add_settings_error') && is_admin()) {
        require_once ABSPATH . 'wp-admin/includes/misc.php';
    }
    /**
     * Level: ['error', 'warning', 'success', 'info']
     */
    function smbk_flash($message, $level = 'info')
    {
        Flash::flash($message, $level);
    }
}
