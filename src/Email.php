<?php

namespace Jkdow\SimplyBook;

use Jkdow\SimplyBook\Support\Logger;

class Email
{
    // Toggle preview mode in dev without SMTP
    const DEV_PREVIEW = false;
    const TABLE = 'smbk_emails';
    const PREVIEW_TRANSIENT = 'smbk_email_preview';

    /**
     * Register activation/deactivation hooks. Call from plugin bootstrap.
     */
    public static function init(): void
    {
        register_activation_hook(smbk_root_file(),   [__CLASS__, 'activate']);
        register_deactivation_hook(smbk_root_file(), [__CLASS__, 'deactivate']);
    }

    /**
     * On activation, create the emails log table with FK to parties table.
     */
    public static function activate(): void
    {
        global $wpdb;
        $etable  = $wpdb->prefix . self::TABLE;
        $ptable  = $wpdb->prefix . Parties::TABLE_PARTIES;
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$etable} (
            id         BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            party_id   BIGINT(20) UNSIGNED NOT NULL,
            sent_at    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_party (party_id),
            CONSTRAINT fk_email_party
                FOREIGN KEY (party_id)
                REFERENCES {$ptable}(id)
                ON DELETE CASCADE
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * On deactivation, drop the emails table.
     */
    public static function deactivate(): void
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . self::TABLE);
    }

    /**
     * Build the email content for a party (subject and HTML body).
     *
     * @param array $party  ['client','child_name','unit','client_email','start_date',…]
     * @return array        ['to'=>string,'subject'=>string,'body'=>string]
     */
    public static function buildEmail(array $party): array
    {
        // sanitize inputs
        $to = sanitize_email($party['client_email']);
        $full_name = sanitize_text_field($party['client']);
        $parts = explode(' ', $full_name);
        $first_name = $parts[0] ?? $full_name;
        $client = $first_name;
        $childName = sanitize_text_field($party['child_name'] ?? '');
        $dateFmt   = date_i18n(
            get_option('date_format') . ' ' . get_option('time_format'),
            strtotime($party['start_date'])
        );

        // subject
        $subject = __("Have you booked your kids party in 2025?!", "smbk");

        // image URLs (you'll need to place these files in assets/images/)
        $img1 = smbk_asset('images/parties.png');
        $img2 = smbk_asset('images/sports.png');
        $img3 = smbk_asset('images/eisc_footer.png');

        // build body
        $body = <<<HTML
            <p>Hey {$client},</p>

            <p>Hope you have had an amazing start to 2025!</p>

            <p>With {$childName}'s birthday coming up soon, we'd love to host your group here at Eastern Indoor Sports Centre & Slides Play Centre!</p>

            <p>We have a full range of activities we would love to have you back for...! 😀</p>

            <img src="{$img1}" alt="Parties Image" width="500">

            <ul>
              <li><strong>Disco Parties:</strong> Boogie on down in our Disco Tent - Dance & play games to the latest music</li>
              <li><strong>Archery Parties:</strong> Draw, aim and let those arrows loose in a fast paced game of bow tag!</li>
              <li><strong>Inflatables Parties:</strong> Bounce, run, race & shoot in our Giant Inflatable Zone - WIPE OUT, NINJA WARRIOR & INFLATABLE CONNECT-4 BASKETBALL</li>
              <li><strong>Nerf Parties:</strong> Team up with Nerf guns in hand. Games great for kids and parents alike!</li>
            </ul>

            <br>

            <img src="{$img2}" alt="Sports Image" width="500">

            <ul>
              <li><strong>AFL:</strong> Practise your footy skills and have fun with goal posts, handball target & drills</li>
              <li><strong>Soccer:</strong> Kick off the fun with dribbling, passing & shooting, for the soccer loving kid!</li>
              <li><strong>Netball:</strong> Shoot, side-step, attack or defend with game play and drills!</li>
              <li><strong>Pickleball:</strong> Learn to play the coolest game in town on a smaller court with lots of hitting, game play, running & fun!</li>
            </ul>

            <p>To speak to us & make a booking send us an email at <a href="mailto:info@easternindoor.com.au">info@easternindoor.com.au</a> or call on (03) 9763 5589</p>

            <br>

            <p>Regards,</p>
            <p>Eastern Indoor Sports Centre & Slides Playcentre</p>

            <img src="{$img3}" alt="EISC Footer" width="700">
        HTML;

        return compact('to', 'subject', 'body');
    }

    /**
     * Send an email to the party and record it in the DB.
     * In DEV_PREVIEW mode, saves content in a transient for preview instead.
     *
     * @param array $party
     * @return bool
     */
    public static function emailParty(array $party): bool
    {
        global $wpdb;
        $etable = $wpdb->prefix . self::TABLE;

        $email = self::buildEmail($party);
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        if (self::DEV_PREVIEW) {
            // store preview for dev
            set_transient(self::PREVIEW_TRANSIENT, $email, HOUR_IN_SECONDS);
            Logger::info('Email preview saved', [$party['id']]);
        } else {
            // send actual mail
            if (! wp_mail($email['to'], $email['subject'], $email['body'], $headers)) {
                Logger::error('Failed to send email', [$party['id'], $email['to']]);
                return false;
            }
            Logger::info('Email sent', [$party['id']]);
        }

        // record in DB regardless
        $inserted = $wpdb->insert(
            $etable,
            ['party_id' => (int)$party['id']],
            ['%d']
        );
        if ($inserted === false) {
            Logger::error('Failed to log email', [$party['id']]);
            return false;
        }

        return true;
    }

    /**
     * Retrieve the last previewed email (for dev).
     * @return array|null ['to','subject','body'] or null
     */
    public static function getPreview(): ?array
    {
        return get_transient(self::PREVIEW_TRANSIENT) ?: null;
    }

    public static function emailCount()
    {
        global $wpdb;
        return [
            'today' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}smbk_emails WHERE DATE(sent_at)=CURDATE()"),
            'week'  => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}smbk_emails WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"),
            'all'   => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}smbk_emails")
        ];
    }

    public static function recentEmails()
    {
        global $wpdb;
        $etable = $wpdb->prefix . self::TABLE;
        $ptable = $wpdb->prefix . Parties::TABLE_PARTIES;
        return $wpdb->get_results("
            SELECT e.id, e.party_id, e.sent_at, p.client_email
            FROM {$etable} AS e
            LEFT JOIN {$ptable} AS p ON p.id = e.party_id
            ORDER BY e.sent_at DESC
            LIMIT 5
        ", ARRAY_A);
    }
}
