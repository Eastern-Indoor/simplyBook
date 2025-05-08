<div class="wrap">
    <?php if (empty($rows)): ?>
        <p><?= esc_html_e('No emails have been sent yet.', 'smbk'); ?></p>
    <?php else: ?>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><?= esc_html__('ID', 'smbk'); ?></th>
                    <th><?= esc_html__('Party ID', 'smbk'); ?></th>
                    <th><?= esc_html__('Client Email', 'smbk'); ?></th>
                    <th><?= esc_html__('Sent At', 'smbk'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= esc_html($row['id']); ?></td>
                        <td><?= esc_html($row['party_id']); ?></td>
                        <td><?= esc_html($row['client_email'] ?? '—'); ?></td>
                        <td><?= esc_html(date_i18n('Y-m-d H:i:s', strtotime($row['sent_at']))); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
