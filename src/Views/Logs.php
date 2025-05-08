<?php

/**
 * Template: Logs.php
 * Variables passed in: $logs (array of structured log entries)
 */
$logsReverse = array_reverse($logs);
?>
<style>
    .smbk-logs-container {
        max-height: 600px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #ccc;
        padding: 1em;
    }

    .smbk-log-entry {
        padding: 6px 8px;
        margin-bottom: 4px;
        font-family: monospace;
        line-height: 1.3;
    }

    .smbk-log-meta {
        font-size: 0.85em;
        color: #555;
        margin-bottom: 2px;
    }

    .smbk-log-message {
        margin-left: 8px;
    }

    .smbk-log-context {
        margin-top: 2px;
        margin-left: 16px;
        font-family: monospace;
        color: #333;
        font-size: 0.85em;
    }

    /* level colors */
    .smbk-level-ERROR {
        background-color: #fdecea;
        border-left: 4px solid #f44336;
    }

    .smbk-level-WARNING {
        background-color: #fff8e1;
        border-left: 4px solid #ffeb3b;
    }

    .smbk-level-INFO {
        background-color: #e7f3fe;
        border-left: 4px solid #2196f3;
    }

    .smbk-level-DEBUG {
        background-color: #e8f5e9;
        border-left: 4px solid #4caf50;
    }
</style>
<div class="wrap">
    <h1><?php esc_html_e('Plugin Logs', 'smbk'); ?></h1>

    <!-- Clear Logs Button -->
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom: 1em">
        <input type="hidden" name="action" value="smbk_clear_logs">
        <?php wp_nonce_field('smbk_clear_logs', 'smbk_clear_logs_nonce'); ?>
        <?php submit_button('Clear Logs', 'delete', 'submit', false); ?>
    </form>

    <?php if (empty($logsReverse)): ?>
        <p><?php esc_html_e('No log entries found.', 'smbk'); ?></p>
    <?php else: ?>
        <div class="smbk-logs-container">
            <?php foreach ($logsReverse as $entry): ?>
                <?php $level = $entry['level']; ?>
                <div class="smbk-log-entry smbk-level-<?php echo esc_attr($level); ?>">
                    <div class="smbk-log-meta">
                        [<?php echo esc_html(date_i18n('Y-m-d H:i:s', strtotime($entry['datetime']))); ?>]
                        <strong><?php echo esc_html($level); ?></strong>
                        <?php if (!empty($entry['channel'])): ?>
                            &mdash; <?php echo esc_html($entry['channel']); ?>
                        <?php endif; ?>
                    </div>
                    <div class="smbk-log-message">
                        <?php echo esc_html($entry['message']); ?>
                    </div>
                    <?php if (!empty($entry['context'])): ?>
                        <div class="smbk-log-context">
                            <?php echo esc_html($entry['context']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
