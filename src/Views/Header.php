<?php

use Jkdow\SimplyBook\Support\Flash;

$tabs = [
    'simplybook' => __('Dashboard', 'smbk'),
    'simplybook-settings'  => __('Settings',  'smbk'),
    'simplybook-search'  => __('Search',  'smbk'),
    'simplybook-logs'      => __('Logs',      'smbk'),
    'simplybook-emails'      => __('Email History',      'smbk'),
    'simplybook-queries'      => __('Party Queries',      'smbk'),
];

$current_page = isset($_GET['page'])
    ? sanitize_text_field(wp_unslash($_GET['page']))
    : '';
?>
<div class="wrap">
    <?php Flash::render() ?>
    <h1 class="wp-heading-inline"><?= esc_html_e($smbkPageHeader, 'smbk') ?></h1>
    <hr class="wp-header-end">
    <h2 class="nav-tab-wrapper">
        <?php foreach ($tabs as $slug => $label):
            // build the URL to this sub-page
            $url = menu_page_url($slug, false);
            // active class?
            $active = ($current_page === $slug) ? ' nav-tab-active' : '';
        ?>
            <a href="<?= esc_url($url); ?>"
                class="nav-tab<?= $active; ?>">
                <?= esc_html($label); ?>
            </a>
        <?php endforeach; ?>
    </h2>
</div>
