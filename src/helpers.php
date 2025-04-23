<?php

// Setup Config
use Jkdow\SimplyBook\Support\Config;

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
        if (!empty($data)) {
            extract($data);
        }
        $file = $viewsPath . $template . '.php';
        if (file_exists($file)) {
            include $file;
        } else {
            // Optionally handle the error if the file isn't found.
            echo sprintf(__('View file "%s" not found at %s', 'simplybook'), $template, $file);
        }
    }
}

if(!function_exists('smbk_asset')) {
    function smbk_asset($path = '')
    {
        $path = 'src/assets/' . $path;
        return plugins_url($path, '/simplyBook/src');
    }
}
