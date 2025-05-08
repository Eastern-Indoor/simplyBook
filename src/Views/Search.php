<?php

/**
 * Template: Data.php
 * Variables passed in: $parties (array), $start (string), $end (string)
 */

use Jkdow\SimplyBook\Email;
?>

<div class="wrap">
    <h1><?php esc_html_e('Party Data', 'smbk'); ?></h1>

    <!-- Filter Form -->
    <form method="get" style="margin-bottom:1em;">
        <input type="hidden" name="page" value="simplybook-search" />
        <p class="description">
            <?php esc_html_e(
                'Please note that updating the dates can take a while and the page will refresh automatically.',
                'smbk'
            ); ?>
        </p>
        <table class="form-table">
            <tr>
                <th><label for="smbk_start"><?php esc_html_e('Start Date', 'smbk'); ?></label></th>
                <td><input type="date" id="smbk_start" name="start" value="<?php echo esc_attr($start); ?>" class="regular-text" /></td>
                <th><label for="smbk_end"><?php esc_html_e('End Date', 'smbk'); ?></label></th>
                <td><input type="date" id="smbk_end" name="end" value="<?php echo esc_attr($end); ?>" class="regular-text" /></td>
                <td><?php submit_button(__('Search', 'smbk'), 'secondary', '', false); ?></td>
            </tr>
        </table>
    </form>

    <?php if (empty($parties)): ?>
        <p><?php esc_html_e('No parties found for this period.', 'smbk'); ?></p>
    <?php else: ?>
        <p><?php printf(
                esc_html__('Found %d parties:', 'smbk'),
                count($parties)
            ); ?></p>

        <!-- Bulk Actions -->
        <button id="smbk_select_unsent" class="button-secondary" style="margin-bottom:1em;">
            <?php esc_html_e('Select All Unsent', 'smbk'); ?>
        </button>

        <form method="post">
            <?php wp_nonce_field('smbk_email_send', 'smbk_email_nonce'); ?>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="smbk_select_all" /></th>
                        <th><?php esc_html_e('Booking ID', 'smbk'); ?></th>
                        <th><?php esc_html_e('Start Date', 'smbk'); ?></th>
                        <th><?php esc_html_e('Record Date', 'smbk'); ?></th>
                        <th><?php esc_html_e('Client Name', 'smbk'); ?></th>
                        <th><?php esc_html_e('Unit', 'smbk'); ?></th>
                        <th><?php esc_html_e('Client Email', 'smbk'); ?></th>
                        <th><?php esc_html_e('Child\'s Name', 'smbk'); ?></th>
                        <th><?php esc_html_e('Emailed?', 'smbk'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($parties as $party): ?>
                        <?php $sent = (bool)$party['email_sent']; ?>
                        <tr>
                            <td>
                                <?php $classes = $sent ? '' : 'smbk-unsent'; ?>
                                <input
                                    type="checkbox"
                                    name="send[]"
                                    value="<?php echo esc_attr($party['id']); ?>"
                                    class="<?php echo esc_attr($classes); ?>" />
                            </td>
                            <td><?php echo esc_html($party['booking_id']); ?></td>
                            <td><?php echo esc_html(date_i18n(
                                    get_option('date_format') . ' ' . get_option('time_format'),
                                    strtotime($party['start_date'])
                                )); ?></td>
                            <td><?php echo esc_html(date_i18n(
                                    get_option('date_format') . ' ' . get_option('time_format'),
                                    strtotime($party['record_date'])
                                )); ?></td>
                            <td><?php echo esc_html($party['client']); ?></td>
                            <td><?php echo esc_html($party['unit'] ?? '&mdash;'); ?></td>
                            <td><a href="mailto:<?php echo esc_attr($party['client_email']); ?>"><?php echo esc_html($party['client_email']); ?></a></td>
                            <td><?php echo esc_html($party['child_name'] ?? '&mdash;'); ?></td>
                            <td>
                                <?php if ($sent): ?>
                                    <span class="dashicons dashicons-yes"></span>
                                <?php else: ?>
                                    <span class="dashicons dashicons-no-alt"></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php submit_button(__('Send Emails', 'smbk'), 'primary', 'send_parties'); ?>
        </form>
    <?php endif; ?>

    <?php
    // Dev preview
    $email = Email::getPreview();
    if ($email) {
        echo '<h2>' . esc_html__('Email Preview', 'smbk') . '</h2>';
        echo '<h3>' . esc_html($email['subject']) . '</h3>';
        echo wp_kses_post($email['body']);
    }
    ?>
</div>

<script>
    (function($) {
        // select all rows
        $('#smbk_select_all').on('change', function() {
            $('input[name="send[]"]').prop('checked', this.checked);
        });
        // select only unsent
        $('#smbk_select_unsent').on('click', function(e) {
            e.preventDefault();
            $('.smbk-unsent').prop('checked', true);
            $('input:not(.smbk-unsent)[name="send[]"]').prop('checked', false);
        });
    })(jQuery);
</script>
