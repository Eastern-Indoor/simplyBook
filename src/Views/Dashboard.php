<?php

/**
 * Template: Dashboard.php
 * Variables passed in:
 *   $totalParties    => int
 *   $emailCounts     => ['today'=>int,'week'=>int,'all'=>int]
 *   $recentEmails    => array of last 5 ['id','party_id','client_email','sent_at']
 *   $recentLogs      => array of last 5 ['level','datetime','message']
 *   $smbkPageHeader  => string
 */

use Jkdow\SimplyBook\Email;
?>
<style>
    .smbk-stats-card {
        display: inline-block;
        width: 23%;
        margin: 0 1% 1em;
        padding: 1em;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-align: center;
    }

    .smbk-stats-card h3 {
        margin: .5em 0;
        font-size: 1.5em;
    }

    .smbk-table-small {
        width: 100%;
        margin-bottom: 1.5em;
    }

    .smbk-table-small th,
    .smbk-table-small td {
        padding: .25em .5em;
        font-size: .9em;
    }
</style>

<div class="wrap">
    <!-- Quick Stats -->
    <div>
        <div class="smbk-stats-card">
            <h3><?= number_format_i18n($totalParties); ?></h3>
            <div><?= __('Total Parties', 'smbk'); ?></div>
        </div>
        <div class="smbk-stats-card">
            <h3><?= number_format_i18n($emailCounts['today']); ?></h3>
            <div><?= __('Emails Today', 'smbk'); ?></div>
        </div>
        <div class="smbk-stats-card">
            <h3><?= number_format_i18n($emailCounts['week']); ?></h3>
            <div><?= __('Emails This Week', 'smbk'); ?></div>
        </div>
        <div class="smbk-stats-card">
            <h3><?= number_format_i18n($emailCounts['all']); ?></h3>
            <div><?= __('All Emails Sent', 'smbk'); ?></div>
        </div>
    </div>

    <!-- Recent Emails -->
    <h2><?= __('Recent Emails', 'smbk'); ?></h2>
    <?php if (empty($recentEmails)): ?>
        <p><?= esc_html__('No emails sent yet.', 'smbk'); ?></p>
    <?php else: ?>
        <table class="widefat smbk-table-small">
            <thead>
                <tr>
                    <th><?= __('ID', 'smbk'); ?></th>
                    <th><?= __('Party', 'smbk'); ?></th>
                    <th><?= __('Client Email', 'smbk'); ?></th>
                    <th><?= __('Sent At', 'smbk'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentEmails as $e): ?>
                    <tr>
                        <td><?= esc_html($e['id']); ?></td>
                        <td><?= esc_html($e['party_id']); ?></td>
                        <td><?= esc_html($e['client_email']); ?></td>
                        <td><?= esc_html(date_i18n('Y-m-d H:i:s', strtotime($e['sent_at']))); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Recent Logs -->
    <h2><?= __('Recent Logs', 'smbk'); ?></h2>
    <?php if (empty($recentLogs)): ?>
        <p><?= esc_html__('No log entries yet.', 'smbk'); ?></p>
    <?php else: ?>
        <table class="widefat smbk-table-small">
            <thead>
                <tr>
                    <th><?= __('Level', 'smbk'); ?></th>
                    <th><?= __('Time', 'smbk'); ?></th>
                    <th><?= __('Message', 'smbk'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentLogs as $l): ?>
                    <tr>
                        <td><?= esc_html($l['level']); ?></td>
                        <td><?= esc_html(date_i18n('Y-m-d H:i:s', strtotime($l['datetime']))); ?></td>
                        <td><?= esc_html($l['message']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Quick Actions -->
    <h2><?= __('Quick Actions', 'smbk'); ?></h2>
    <p>
        <a href="<?= esc_url(menu_page_url('smbk_search', false)); ?>" class="button button-primary">
            <?= __('Search Parties', 'smbk'); ?>
        </a>
        <a href="<?= esc_url(menu_page_url('smbk_logs', false)); ?>" class="button">
            <?= __('Clear Logs', 'smbk'); ?>
        </a>
        <?php if (Email::DEV_PREVIEW): ?>
            <a href="<?= esc_url(add_query_arg('preview_email', '1')); ?>" class="button button-secondary">
                <?= __('Send Test Email', 'smbk'); ?>
            </a>
        <?php endif; ?>
    </p>
</div>
