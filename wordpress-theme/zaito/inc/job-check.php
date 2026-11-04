<?php
/**
 * まとめ求人の募集終了チェック（サーバーのCronで毎日動かす。cron/check-closed.php）。
 *
 * 求人ごとに元の募集ページを開き、募集が終わっていれば「自動で募集終了」として記録する（オプション zaito_auto_closed）。
 * 記録された求人は、一覧・おすすめ・配信から外れ、求人ページには「募集終了」と出る（コードの 'closed' => true と同じ扱い）。
 * 判定は ops/check_closed_jobs.py と同じ：
 * - 404・410が2回続いた
 * - 403で「この求人は現在非公開」と出た（ハープキャリア）
 * - ログイン画面（/careers/login）に転送された
 * - 画面の文字に「募集を終了」などがある
 * 迷うもの（一覧への転送・エラー）は閉じずに、メールの報告にだけ載せる。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ZAITO_JOB_CHECK_OPTION', 'zaito_auto_closed' );

/**
 * 自動で募集終了にした求人を、求人の一覧に反映する（直接掲載の求人は対象外）。
 */
add_filter( 'zaito_remote_jobs', function ( $jobs ) {
    $closed = get_option( ZAITO_JOB_CHECK_OPTION, array() );
    if ( ! is_array( $closed ) || ! $closed ) {
        return $jobs;
    }
    foreach ( $jobs as $i => $j ) {
        if ( empty( $j['direct'] ) && isset( $closed[ $j['slug'] ] ) ) {
            $jobs[ $i ]['closed'] = true;
        }
    }
    return $jobs;
}, 20 );

function zaito_job_check_words() {
    return array(
        '募集を終了', '募集は終了', '募集終了しました', '現在募集を停止', '募集を停止しています', '掲載を終了',
        '掲載は終了', '掲載期間が終了', 'この求人は現在募集しておりません', '現在、募集していません', '現在募集していません',
        'お探しのページは見つかりません', 'ページが見つかりません', 'This job is no longer available', 'no longer accepting',
        'この求人は現在非公開',
    );
}

function zaito_job_check_fetch( $url ) {
    $res = wp_remote_get( $url, array(
        'timeout'     => 25,
        'redirection' => 5,
        'user-agent'  => 'Mozilla/5.0 (zaito job check)',
    ) );
    if ( is_wp_error( $res ) ) {
        return array( 0, '', $url, $res->get_error_message() );
    }
    $final = $url;
    if ( isset( $res['http_response'] ) && is_object( $res['http_response'] ) && method_exists( $res['http_response'], 'get_response_object' ) ) {
        $obj = $res['http_response']->get_response_object();
        if ( $obj && ! empty( $obj->url ) ) {
            $final = $obj->url;
        }
    }
    return array( (int) wp_remote_retrieve_response_code( $res ), (string) wp_remote_retrieve_body( $res ), $final, '' );
}

/**
 * 1件の求人を判定する。戻り値: array( 'closed' | 'maybe' | 'error' | 'ok', 理由 )
 */
function zaito_job_check_one( $url ) {
    list( $code, $body, $final, $err ) = zaito_job_check_fetch( $url );
    if ( 404 === $code || 410 === $code ) {
        sleep( 5 ); // ハープキャリアは、公開中の求人にもときどき一時的に404を返すため、取り直す。
        list( $code, $body, $final, $err ) = zaito_job_check_fetch( $url );
    }
    if ( ! $code ) {
        return array( 'error', $err ? $err : '接続できない' );
    }
    if ( 404 === $code || 410 === $code ) {
        return array( 'closed', 'HTTP ' . $code . '（2回とも）' );
    }
    if ( 403 === $code && false !== strpos( $body, 'この求人は現在非公開' ) ) {
        return array( 'closed', '「この求人は現在非公開」（HTTP 403）' );
    }
    if ( $code < 200 || $code >= 300 ) {
        return array( 'error', 'HTTP ' . $code );
    }
    if ( false !== strpos( $final, '/careers/login' ) ) {
        return array( 'closed', 'ログイン画面に転送（非公開）' );
    }
    $text = preg_replace( '#<(script|style|noscript|template)\b.*?</\1>#is', ' ', $body );
    $text = preg_replace( '#<[^>]+>#', ' ', (string) $text );
    foreach ( zaito_job_check_words() as $w ) {
        if ( false !== strpos( (string) $text, $w ) ) {
            return array( 'closed', '「' . $w . '」' );
        }
    }
    if ( substr_count( rtrim( $final, '/' ), '/' ) < substr_count( rtrim( $url, '/' ), '/' ) - 1 ) {
        return array( 'maybe', '転送先 ' . $final );
    }
    return array( 'ok', '' );
}

/**
 * 掲載中のまとめ求人をすべて確かめ、募集が終わったものを記録して、新しく閉じたものがあれば運営にメールで知らせる。
 */
function zaito_job_check_run() {
    $closed = get_option( ZAITO_JOB_CHECK_OPTION, array() );
    $closed = is_array( $closed ) ? $closed : array();
    $new    = array();
    $notes  = array();
    $n      = 0;
    foreach ( zaito_remote_jobs() as $j ) {
        if ( ! empty( $j['closed'] ) || ! empty( $j['direct'] ) || empty( $j['url'] ) ) {
            continue;
        }
        $n++;
        list( $st, $why ) = zaito_job_check_one( $j['url'] );
        if ( 'closed' === $st ) {
            $closed[ $j['slug'] ] = array( 'date' => wp_date( 'Y-m-d' ), 'reason' => $why );
            $new[]                = $j['company'] . '「' . $j['title'] . '」：' . $why . "\n  " . $j['url'];
        } elseif ( 'ok' !== $st ) {
            $notes[] = $j['company'] . '「' . $j['title'] . '」：' . $why . "\n  " . $j['url'];
        }
    }
    update_option( ZAITO_JOB_CHECK_OPTION, $closed, false );
    update_option( 'zaito_job_check_last', array( 'at' => wp_date( 'c' ), 'checked' => $n, 'new_closed' => count( $new ), 'notes' => count( $notes ) ), false );
    if ( $new || $notes ) {
        $body = "zaitoの募集終了チェック（" . wp_date( 'Y-m-d' ) . "）\n確かめた求人：{$n}件\n\n";
        if ( $new ) {
            $body .= "■募集終了にした求人（一覧から自動で外しました）\n" . implode( "\n", $new ) . "\n\n";
        }
        if ( $notes ) {
            $body .= "■確かめてほしい求人（閉じていません）\n" . implode( "\n", $notes ) . "\n\n";
        }
        $body .= "コードの inc/remote-jobs.php にも 'closed' => true を付けておくと、記録がそろいます。";
        wp_mail( 'info@zaito-work.com', '【zaito】募集終了チェック：' . count( $new ) . '件を募集終了にしました', $body );
    }
    return array( 'checked' => $n, 'new_closed' => $new, 'notes' => $notes );
}
