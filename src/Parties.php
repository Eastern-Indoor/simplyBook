<?php

namespace Jkdow\SimplyBook;

use Jkdow\SimplyBook\Api\SimplyApi;
use Jkdow\SimplyBook\Support\Logger;

class Parties
{
    const TABLE_QUERIES       = 'smbk_queries';
    const TABLE_PARTIES = 'smbk_parties';

    public static function init()
    {
        register_activation_hook(smbk_root_file(), [__CLASS__, 'activate']);
        register_deactivation_hook(smbk_root_file(), [__CLASS__, 'deactivate']);
    }

    public static function activate()
    {
        global $wpdb;

        $charset = $wpdb->get_charset_collate();
        $qtable  = $wpdb->prefix . self::TABLE_QUERIES;
        $ptable  = $wpdb->prefix . self::TABLE_PARTIES;

        // 1) Create the queries‐log table
        $sql1 = "CREATE TABLE {$qtable} (
          id            bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          date_start    date        NOT NULL,
          date_end      date        NOT NULL,
          queried_at    datetime    NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY  (id)
        ) {$charset};";

        // 2) Create the bookings table, linked to queries
        $sql2 = "CREATE TABLE {$ptable} (
          id             bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          query_id       bigint(20) UNSIGNED NOT NULL,
          booking_id     bigint(20) UNSIGNED NOT NULL,
          start_date     datetime    NOT NULL,
          record_date    datetime    NOT NULL,
          client         varchar(255) NOT NULL,
          unit           varchar(255) NULL,
          client_email   varchar(255) NOT NULL,
          child_name     varchar(255) NULL,
          PRIMARY KEY   (id),
          KEY idx_query (query_id),
          CONSTRAINT fk_query
            FOREIGN KEY (query_id)
            REFERENCES {$qtable}(id)
            ON DELETE CASCADE
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql1);
        dbDelta($sql2);
    }

    public static function deactivate()
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}" . self::TABLE_PARTIES);
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}" . self::TABLE_QUERIES);
    }

    public static function getParties($start, $end)
    {
        Logger::info('getParties', [$start, $end]);
        global $wpdb;
        $qtable = $wpdb->prefix . self::TABLE_QUERIES;
        $ptable = $wpdb->prefix . self::TABLE_PARTIES;
        $etable     = $wpdb->prefix . Email::TABLE;

        // 1) Load all existing query rows that overlap [start,end]
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, date_start, date_end
                   FROM {$qtable}
                  WHERE date_start <= %s
                    AND date_end   >= %s",
                $end,
                $start
            ),
            ARRAY_A
        );

        $intervals = array_map(fn($r) => [$r['date_start'], $r['date_end']], $rows);
        //Logger::info('Loaded intervals', [$intervals]);
        $merged    = self::mergeIntervals($intervals);
        $missing = self::computeMissing($start, $end, $merged);
        Logger::info('Missing dates not in DB', [$missing]);

        foreach ($missing as [$ms, $me]) {
            self::uncheckedQuery($ms, $me);
            $merged = self::mergeIntervals(array_merge($merged, [[$ms, $me]]));
        }

        $full_start = $start . ' 00:00:00';
        $full_end   = $end   . ' 23:59:59';

        $sql = "
            SELECT
                p.*,
                CASE WHEN EXISTS(
                    SELECT 1 FROM {$etable} e
                    WHERE e.party_id = p.id
                ) THEN 1 ELSE 0 END AS email_sent
            FROM {$ptable} p
            WHERE p.start_date BETWEEN %s AND %s
            ORDER BY p.start_date ";
        return $wpdb->get_results(
            $wpdb->prepare($sql, $full_start, $full_end),
            ARRAY_A
        );
    }

    protected static function uncheckedQuery($start, $end)
    {
        $parties = SimplyApi::previousParties($start, $end);
        if ($parties === null) {
            return;
        }
        Logger::info('Got parties in date range', [$start, $end, $parties->count()]);
        // Create new query entry
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . self::TABLE_QUERIES,
            [
                'date_start' => $start,
                'date_end'   => $end,
            ]
        );
        $queryId = $wpdb->insert_id;
        //Logger::info('Inserted new query', [$queryId]);
        $ptable  = $wpdb->prefix . self::TABLE_PARTIES;

        // Insert each party into the parties table
        foreach ($parties as $party) {
            $booking_id = (int) $party['id'];

            // skip if this booking_id already exists
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT 1 FROM {$ptable} WHERE booking_id = %d",
                $booking_id
            ));

            if ($exists) {
                //Logger::info("Skipping duplicate booking", [$booking_id]);
                continue;
            }

            $wpdb->insert(
                $wpdb->prefix . self::TABLE_PARTIES,
                [
                    'query_id'     => $queryId,
                    'booking_id'   => $party['id'],
                    'start_date'   => $party['start_date'],
                    'record_date'  => $party['record_date'],
                    'client'       => $party['client'],
                    'unit'         => $party['unit'],
                    'client_email' => $party['client_email'],
                    'child_name'   => $party['child_name'],
                ],
                ['%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s']
            );
        }
    }

    /**
     * Merge a list of [start,end] intervals (YYYY-MM-DD) into non-overlapping,
     * sorted intervals.
     * @param array<array{0:string,1:string}> $intervals
     * @return array<array{0:string,1:string}>
     */
    private static function mergeIntervals(array $intervals): array
    {
        if (empty($intervals)) {
            return [];
        }
        // sort by start
        usort($intervals, fn($a, $b) => strcmp($a[0], $b[0]));

        $merged = [];
        foreach ($intervals as [$s, $e]) {
            if (empty($merged)) {
                $merged[] = [$s, $e];
                continue;
            }
            [$ls, $le] = $merged[count($merged) - 1];
            if ($s <= $le) {
                // overlap → extend
                $merged[count($merged) - 1][1] = max($le, $e);
            } else {
                $merged[] = [$s, $e];
            }
        }
        return $merged;
    }

    /**
     * Given a desired [start,end] and a set of merged intervals that lie inside it,
     * compute the sub-ranges of [start,end] that are NOT covered.
     *
     * @param string $start
     * @param string $end
     * @param array<array{0:string,1:string}> $merged
     * @return array<array{0:string,1:string}> list of missing [start,end] pairs
     */
    private static function computeMissing(string $start, string $end, array $merged): array
    {
        $missing = [];

        // Normalise to timestamps
        $cursor = strtotime($start);
        $endTs  = strtotime($end);

        foreach ($merged as [$ms, $me]) {
            $msTs = strtotime($ms);
            $meTs = strtotime($me);

            // If our cursor is before the next interval start, that's a gap
            if ($cursor < $msTs) {
                // gapEnd = day before this interval
                $gapEndTs = strtotime('-1 day', $msTs);

                if ($cursor <= $gapEndTs) {
                    $missing[] = [
                        date('Y-m-d', $cursor),
                        date('Y-m-d', $gapEndTs),
                    ];
                }
            }

            // Move cursor to day after this interval
            $cursor = strtotime('+1 day', $meTs);

            // If we've now passed the overall end, we can stop
            if ($cursor > $endTs) {
                return $missing;
            }
        }

        // Final tail: anything left between cursor and end
        if ($cursor <= $endTs) {
            $missing[] = [
                date('Y-m-d', $cursor),
                date('Y-m-d', $endTs),
            ];
        }

        return $missing;
    }

    public static function totalParties()
    {
        global $wpdb;
        $ptable = $wpdb->prefix . self::TABLE_PARTIES;
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$ptable}");
    }

    public static function getQueries() {
        /** @var wpdb $wpdb */
        global $wpdb;

        // Adjust to match your actual table name
        $table_name = $wpdb->prefix . self::TABLE_QUERIES;

        // Fetch up to 200 most recent queries
        return $wpdb->get_results("
            SELECT id, date_start, date_end, queried_at
            FROM {$table_name}
            ORDER BY queried_at DESC
            LIMIT 200
        ", ARRAY_A);
    }
}
