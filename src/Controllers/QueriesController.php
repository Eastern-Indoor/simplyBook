<?php

namespace Jkdow\SimplyBook\Controllers;

use Jkdow\SimplyBook\Parties;

class QueriesController
{
    public static function setupPage()
    {
        add_submenu_page(
            'simplybook',
            'SimplyBook Queries',
            'Queries',
            'manage_options',
            'simplybook-queries',
            [__CLASS__, 'page']
        );
    }

    public static function setupActions() {
        add_action( 'admin_post_smbk_delete_query', [ __CLASS__, 'handle_delete_query' ] );
    }

    public static function page()
    {
        $rows = Parties::getQueries();

        smbk_render('Queries', [
            'rows' => $rows,
        ]);
    }

    public static function handle_delete_query() {
        $id = isset($_POST['entry_id']) ? (int) $_POST['entry_id'] : 0;
        $nonce_action = "smbk_delete_query_{$id}";
        check_admin_referer( $nonce_action, 'smbk_delete_query_nonce' );
        if ( ! current_user_can('manage_options') ) {
            wp_die(__('Cheatin’ huh?','smbk'));
        }

        // 2) delete from database
        global $wpdb;
        $table = $wpdb->prefix . Parties::TABLE_QUERIES;
        $deleted = $wpdb->delete(
            $table,
            [ 'id' => $id ],
            [ '%d' ]
        );

        if ( false === $deleted ) {
            smbk_flash( __('Could not delete entry.','smbk'), 'error' );
        } else {
            smbk_flash( __('Entry deleted.','smbk'), 'success' );
        }

        // 3) redirect back to logs page
        wp_safe_redirect( wp_get_referer() ?: admin_url('admin.php?page=simplybook-queries') );
        exit;
    }
}
