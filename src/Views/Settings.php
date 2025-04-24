<div class="wrap">
    <form method="post" action="">
        <?php wp_nonce_field('smbk_save_settings', 'smbk_nonce'); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="smbk_company"><?php esc_html_e('Company', 'smbk'); ?></label>
                </th>
                <td>
                    <input name="company"
                        type="text"
                        id="smbk_company"
                        value="<?php echo esc_attr($company); ?>"
                        class="regular-text" />
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="smbk_login"><?php esc_html_e('Login', 'smbk'); ?></label>
                </th>
                <td>
                    <input name="login"
                        type="text"
                        id="smbk_login"
                        value="<?php echo esc_attr($login); ?>"
                        class="regular-text" />
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="smbk_password"><?php esc_html_e('Password', 'smbk'); ?></label>
                </th>
                <td>
                    <div style="display: flex; align-items: center; gap: .5em;">
                        <input
                            name="password"
                            type="password"
                            id="smbk_password"
                            value="<?php echo esc_attr($password); ?>"
                            class="regular-text" />
                        <button
                            type="button"
                            id="toggle-password-btn"
                            class="button-secondary"
                            aria-label="<?php esc_attr_e('Show password', 'smbk'); ?>">
                            <?php esc_html_e('Show', 'smbk'); ?>
                        </button>
                    </div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="smbk_partyid"><?php esc_html_e('Party Id', 'smbk'); ?></label>
                </th>
                <td>
                    <input name="partyid"
                        type="text"
                        id="smbk_partyid"
                        value="<?php echo esc_attr($partyid); ?>"
                        class="regular-text" />
                </td>
            </tr>
        </table>

        <?= submit_button() ?>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pwd = document.getElementById('smbk_password');
            const btn = document.getElementById('toggle-password-btn');
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const isPwd = pwd.type === 'password';
                pwd.type = isPwd ? 'text' : 'password';
                this.textContent = isPwd ?
                    '<?php echo esc_js(__("Hide", "smbk")); ?>' :
                    '<?php echo esc_js(__("Show", "smbk")); ?>';
                this.setAttribute(
                    'aria-label',
                    isPwd ?
                    '<?php echo esc_js(__("Hide password", "smbk")); ?>' :
                    '<?php echo esc_js(__("Show password", "smbk")); ?>'
                );
            });
        });
    </script>
</div>
