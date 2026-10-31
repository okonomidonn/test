<?php
/**
 * 運営用の集計API（GET /wp-admin/admin-ajax.php?action=zaito_stats）。登録者数などの「数字だけ」を返す。
 * XServer の国外IPアクセス制限で /wp-json/ は海外から開けないため、admin-ajax.php を使う。
 *
 * 名前・メールアドレス・学校名など、個人を特定できる情報は返さない（人数の集計だけ）。
 * 開けるのは、リクエストの X-Zaito-Token ヘッダーが wp-config.php の ZAITO_STATS_TOKEN と一致するときだけ。
 * ZAITO_STATS_TOKEN が設定されていない（または短すぎる）あいだは、誰も開けない。
 * 合言葉は .github/workflows/configure-stats-token.yml で GitHub の Secrets から wp-config.php に書き込む。
 *
 * ?days=30 で、日ごとの新規登録を何日分返すかを決める（1〜180）。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function zaito_stats_handle() {
    nocache_headers();
    if ( ! defined( 'ZAITO_STATS_TOKEN' ) || strlen( (string) ZAITO_STATS_TOKEN ) < 32 ) {
        wp_send_json( array( 'code' => 'zaito_stats_off' ), 404 );
    }
    $given = isset( $_SERVER['HTTP_X_ZAITO_TOKEN'] ) ? (string) wp_unslash( $_SERVER['HTTP_X_ZAITO_TOKEN'] ) : '';
    if ( '' === $given || ! hash_equals( (string) ZAITO_STATS_TOKEN, $given ) ) {
        wp_send_json( array( 'code' => 'zaito_stats_forbidden' ), 403 );
    }
    $days = isset( $_GET['days'] ) ? absint( $_GET['days'] ) : 30;
    wp_send_json( zaito_stats_data( $days ? $days : 30 ) );
}
add_action( 'wp_ajax_zaito_stats', 'zaito_stats_handle' );
add_action( 'wp_ajax_nopriv_zaito_stats', 'zaito_stats_handle' );

/** 登録（zaito_interest）のうち、あるメタがある件数。$value を渡すとその値だけ。 */
function zaito_stats_meta_count( $key, $value = null ) {
    global $wpdb;
    $sql = "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
        WHERE p.post_type = 'zaito_interest' AND p.post_status <> 'trash' AND m.meta_key = %s AND m.meta_value <> ''";
    $args = array( $key );
    if ( null !== $value ) {
        $sql   .= ' AND m.meta_value = %s';
        $args[] = $value;
    }
    return (int) $wpdb->get_var( $wpdb->prepare( $sql, $args ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

/** 登録のメタの値ごとの件数（上位 $limit 件）。空の値は「（なし）」にまとめる。 */
function zaito_stats_meta_breakdown( $key, $limit = 15 ) {
    global $wpdb;
    $rows = $wpdb->get_results( $wpdb->prepare(
        "SELECT COALESCE(NULLIF(m.meta_value, ''), '（なし）') AS v, COUNT(*) AS n FROM {$wpdb->posts} p
         LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = %s
         WHERE p.post_type = 'zaito_interest' AND p.post_status <> 'trash'
         GROUP BY v ORDER BY n DESC LIMIT %d",
        $key,
        $limit
    ) );
    $out = array();
    foreach ( $rows as $r ) {
        $out[ $r->v ] = (int) $r->n;
    }
    return $out;
}

function zaito_stats_data( $days ) {
    global $wpdb;
    $days = min( 180, max( 1, (int) $days ) );

    $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'zaito_interest' AND post_status <> 'trash'" );

    // 日ごとの新規登録（サイトの時刻＝日本時間）。登録がない日も0で返す。
    $since = wp_date( 'Y-m-d', time() - ( $days - 1 ) * DAY_IN_SECONDS );
    $rows  = $wpdb->get_results( $wpdb->prepare(
        "SELECT DATE(post_date) AS d, COUNT(*) AS n FROM {$wpdb->posts}
         WHERE post_type = 'zaito_interest' AND post_status <> 'trash' AND post_date >= %s GROUP BY d",
        $since . ' 00:00:00'
    ) );
    $by_day = array();
    for ( $i = $days - 1; $i >= 0; $i-- ) {
        $by_day[ wp_date( 'Y-m-d', time() - $i * DAY_IN_SECONDS ) ] = 0;
    }
    foreach ( $rows as $r ) {
        if ( isset( $by_day[ $r->d ] ) ) {
            $by_day[ $r->d ] = (int) $r->n;
        }
    }

    // 直接掲載：企業の審査状況、求人の審査状況、応募の状況。
    $companies = array();
    foreach ( array( 'pending', 'approved', 'suspended' ) as $s ) {
        $companies[ $s ] = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'zaito_company_status' AND meta_value = %s",
            $s
        ) );
    }
    $jobs = array();
    foreach ( $wpdb->get_results(
        "SELECT m2.meta_value AS v, COUNT(*) AS n FROM {$wpdb->posts} p
         JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_zaito_direct' AND m.meta_value = '1'
         LEFT JOIN {$wpdb->postmeta} m2 ON m2.post_id = p.ID AND m2.meta_key = '_zaito_review'
         WHERE p.post_type = 'job_listing' AND p.post_status <> 'trash' GROUP BY v"
    ) as $r ) {
        $jobs[ $r->v ? $r->v : 'draft' ] = (int) $r->n;
    }
    $apps = array();
    foreach ( $wpdb->get_results(
        "SELECT m.meta_value AS v, COUNT(*) AS n FROM {$wpdb->posts} p
         LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = 'status'
         WHERE p.post_type = 'zaito_application' AND p.post_status <> 'trash' GROUP BY v"
    ) as $r ) {
        $apps[ $r->v ? $r->v : 'new' ] = (int) $r->n;
    }

    return array(
        'generated_at' => wp_date( 'c' ),
        'registrations' => array(
            'total'          => $total,
            'verified'       => zaito_stats_meta_count( 'verified' ),
            'unsubscribed'   => zaito_stats_meta_count( 'unsubscribed' ),
            'with_name'      => zaito_stats_meta_count( 'name' ),
            'with_school'    => zaito_stats_meta_count( 'school' ),
            'via_google'     => zaito_stats_meta_count( 'login_google' ),
            'via_line'       => zaito_stats_meta_count( 'login_line' ),
            'by_role'        => zaito_stats_meta_breakdown( 'role' ),
            'by_grade'       => zaito_stats_meta_breakdown( 'grade' ),
            'by_utm_source'  => zaito_stats_meta_breakdown( 'utm_source' ),
            'new_by_day'     => $by_day,
        ),
        'direct' => array(
            'companies'    => $companies,
            'jobs'         => $jobs,
            'applications' => $apps,
            'offers'       => zaito_stats_post_meta_count( 'zaito_application', 'offer_at' ),
        ),
    );
}

/** 投稿タイプ $type のうち、メタ $key が入っている件数。 */
function zaito_stats_post_meta_count( $type, $key ) {
    global $wpdb;
    return (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
         WHERE p.post_type = %s AND p.post_status <> 'trash' AND m.meta_key = %s AND m.meta_value <> ''",
        $type,
        $key
    ) );
}
