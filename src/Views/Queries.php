<div class="wrap">
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('ID',         'simplybook'); ?></th>
                <th><?php esc_html_e('Start Date', 'simplybook'); ?></th>
                <th><?php esc_html_e('End Date',   'simplybook'); ?></th>
                <th><?php esc_html_e('Queried At', 'simplybook'); ?></th>
                <th><?php esc_html_e('Actions',    'simplybook'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows) : ?>
                <?php foreach ($rows as $row) :
                    $id = (int) $row['id'];
                    // unique nonce for each row:
                    $nonce_action = "smbk_delete_query_{$id}";
                    $nonce_name   = 'smbk_delete_query_nonce';
                ?>
                    <tr>
                        <td><?php echo esc_html($id); ?></td>
                        <td><?php echo esc_html($row['date_start']); ?></td>
                        <td><?php echo esc_html($row['date_end']); ?></td>
                        <td><?php echo esc_html($row['queried_at']); ?></td>
                        <td>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline">
                                <input type="hidden" name="action" value="smbk_delete_query">
                                <input type="hidden" name="entry_id" value="<?php echo esc_attr($id); ?>">
                                <?php wp_nonce_field($nonce_action, $nonce_name); ?>
                                <?php submit_button(__('Delete', 'simplybook'), 'delete small', '', false); ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">
                        <?php esc_html_e('No searches logged yet.', 'simplybook'); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
