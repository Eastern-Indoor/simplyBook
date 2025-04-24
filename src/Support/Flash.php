<?php

namespace Jkdow\SimplyBook\Support;

class Flash
{
    /** Option name where flashes are stored */
    const OPTION = 'smbk_flash_messages';

    /**
     * Queue up a one-time admin message.
     *
     * @param string $message The message to display.
     * @param string $level   One of 'error','warning','success','info'.
     */
    public static function flash(string $message, string $level = 'info'): void
    {
        // sanitize
        $text  = wp_kses_post($message);
        $level = in_array($level, ['error', 'warning', 'success', 'info'], true)
            ? $level
            : 'info';

        // grab existing
        $msgs = get_option(self::OPTION, []);
        if (! is_array($msgs)) {
            $msgs = [];
        }

        $msgs[] = compact('text', 'level');
        update_option(self::OPTION, $msgs);
    }

    /**
     * Retrieve and clear all queued messages.
     *
     * @return array<int,array{level:string,text:string}>
     */
    protected static function getMessages(): array
    {
        $msgs = get_option(self::OPTION, []);
        if (is_array($msgs) && ! empty($msgs)) {
            delete_option(self::OPTION);
            return $msgs;
        }
        return [];
    }

    /**
     * Echo out any flash messages as WP admin notices.
     * Call this in your page template (e.g. right under <div class="wrap">).
     */
    public static function render(): void
    {
        $msgs = self::getMessages();
        if (empty($msgs)) {
            return;
        }

        foreach ($msgs as $m) {
            // notice-error, notice-success, notice-warning, notice-info
            printf(
                '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
                esc_attr($m['level']),
                esc_html($m['text'])
            );
        }
    }
}
