<?php
/**
 * zaito経由の応募と採用を数える（企業への報告・採用課金の根拠に使う）。
 *
 * zaitoは応募を受け付けず、学生は各企業の募集ページで応募する。そのため企業の画面だけでは
 * 「zaitoから来た応募」が分からない。そこで、応募ページへ進んだ学生に、あとで本人に聞く。
 *
 * 1. 応募ページへ進んだ（/remote/{slug}/go/）2日後に「応募しましたか？」とメールで聞く。
 * 2. 「応募した」と答えた人には、21日後に「選考の結果は？」と聞く。
 * 3. 回答は /apply-check/ のページで受ける（メール内のリンクは本人専用の署名つき・30日有効）。
 *    メールのリンクを開いただけでは記録しない（迷惑メール対策のソフトがリンクを先に開くことがあるため）。
 *    ページのボタンを押したときだけ記録する。
 * 4. 結果は管理画面「求人 > まとめ求人」に、求人ごとの「応募した」「採用された」の人数として出す。
 *
 * メールは、メールアドレスの確認が済み、配信を止めていない人にだけ送る。1回のメールで最大5件まで聞く。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ZAITO_AC_ASK_AFTER', 2 * DAY_IN_SECONDS );      // 応募ページへ進んでから「応募しましたか」を聞くまで
define( 'ZAITO_AC_ASK_UNTIL', 14 * DAY_IN_SECONDS );     // これより前に進んだものは聞かない
define( 'ZAITO_AC_RESULT_AFTER', 21 * DAY_IN_SECONDS );  // 「応募した」から「結果は？」を聞くまで

/**
 * 回答の選択肢。stage 1 は応募したか、stage 2 は選考の結果。
 */
function zaito_ac_choices( $stage ) {
    return 1 === $stage
        ? array( 'applied' => '応募した', 'later' => 'これから応募する', 'no' => '応募しない' )
        : array( 'hired' => '採用された', 'waiting' => 'まだ選考中', 'rejected' => '不採用だった', 'declined' => '辞退した' );
}

/* ---------- 本人専用のリンク ---------- */

function zaito_ac_sig( $id, $exp ) {
    return hash_hmac( 'sha256', 'zaito_apply_check|' . (int) $id . '|' . (int) $exp, wp_salt( 'auth' ) );
}

function zaito_ac_url( $id ) {
    $exp = time() + 30 * DAY_IN_SECONDS;
    return add_query_arg( 'k', (int) $id . '.' . $exp . '.' . zaito_ac_sig( $id, $exp ), home_url( '/apply-check/' ) );
}

/**
 * リンクの k を確かめて、登録IDを返す。だめなら 0。
 */
function zaito_ac_member( $k ) {
    if ( ! preg_match( '/^(\d+)\.(\d+)\.([a-f0-9]{64})$/', (string) $k, $m ) ) {
        return 0;
    }
    if ( (int) $m[2] < time() || 'zaito_interest' !== get_post_type( (int) $m[1] ) ) {
        return 0;
    }
    return hash_equals( zaito_ac_sig( $m[1], $m[2] ), $m[3] ) ? (int) $m[1] : 0;
}

/* ---------- 記録 ---------- */

/**
 * その人の、求人ごとの状態（applied / hired など）と日付。
 *
 * @return array<string, array{status: string, date: string}>
 */
function zaito_ac_status( $member ) {
    $s = get_post_meta( $member, 'apply_status', true );
    return is_array( $s ) ? $s : array();
}

function zaito_ac_save( $member, $slug, $status ) {
    $all = zaito_ac_status( $member );
    $all[ $slug ] = array( 'status' => $status, 'date' => current_time( 'Y-m-d' ) );
    update_post_meta( $member, 'apply_status', $all );

    // 求人ごとの集計（管理画面に出す）。同じ人の答えは最新の1つだけ数える。
    $stats = get_option( 'zaito_apply_stats', array() );
    $stats = is_array( $stats ) ? $stats : array();
    if ( ! isset( $stats[ $slug ] ) ) {
        $stats[ $slug ] = array();
    }
    $stats[ $slug ][ $member ] = array( 'status' => $status, 'date' => current_time( 'Y-m-d' ) );
    update_option( 'zaito_apply_stats', $stats, false );
}

/**
 * 求人ごとの「応募した」「採用された」の人数。応募したあとに結果を答えた人も「応募した」に数える。
 *
 * @return array<string, array{applied: int, hired: int}>
 */
function zaito_ac_counts() {
    $stats = get_option( 'zaito_apply_stats', array() );
    $out   = array();
    foreach ( is_array( $stats ) ? $stats : array() as $slug => $rows ) {
        $applied = 0;
        $hired   = 0;
        foreach ( $rows as $r ) {
            if ( in_array( $r['status'], array( 'applied', 'hired', 'waiting', 'rejected', 'declined' ), true ) ) {
                $applied++;
            }
            if ( 'hired' === $r['status'] ) {
                $hired++;
            }
        }
        $out[ $slug ] = array( 'applied' => $applied, 'hired' => $hired );
    }
    return $out;
}

/* ---------- 聞く相手を選ぶ ---------- */

/**
 * その人にいま聞く求人。stage 1（応募したか）と stage 2（結果は）に分けて返す。
 *
 * @return array{1: string[], 2: string[]}
 */
function zaito_ac_due( $member, $now ) {
    $clicks = get_post_meta( $member, 'remote_clicks', true );
    $clicks = is_array( $clicks ) ? $clicks : array();
    $status = zaito_ac_status( $member );
    $asked  = get_post_meta( $member, 'apply_asked', true );
    $asked  = is_array( $asked ) ? $asked : array();
    $due    = array( 1 => array(), 2 => array() );
    foreach ( $clicks as $slug => $date ) {
        $job = zaito_remote_job( $slug );
        if ( ! $job ) {
            continue;
        }
        $ago = $now - strtotime( $date );
        $st  = isset( $status[ $slug ] ) ? $status[ $slug ]['status'] : '';
        if ( ( '' === $st || 'later' === $st ) && empty( $asked[ $slug . ':1' ] )
            && $ago >= ZAITO_AC_ASK_AFTER && $ago <= ZAITO_AC_ASK_UNTIL && empty( $job['closed'] ) ) {
            $due[1][] = $slug;
        }
        if ( 'applied' === $st && empty( $asked[ $slug . ':2' ] )
            && ( $now - strtotime( $status[ $slug ]['date'] ) ) >= ZAITO_AC_RESULT_AFTER ) {
            $due[2][] = $slug;
        }
    }
    return $due;
}

/**
 * 1日1回：聞く相手にメールを送る。
 */
function zaito_ac_run() {
    $ids = get_posts( array(
        'post_type'      => 'zaito_interest',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_key'       => 'remote_clicks', // phpcs:ignore WordPress.DB.SlowDBQuery
    ) );
    $now = current_time( 'timestamp' );
    foreach ( $ids as $id ) {
        if ( get_post_meta( $id, 'unsubscribed', true ) || ! zaito_interest_verified( $id ) ) {
            continue;
        }
        $due = zaito_ac_due( $id, $now );
        if ( ! $due[1] && ! $due[2] ) {
            continue;
        }
        if ( zaito_ac_send( $id, $due ) ) {
            $asked = get_post_meta( $id, 'apply_asked', true );
            $asked = is_array( $asked ) ? $asked : array();
            foreach ( array( 1, 2 ) as $stage ) {
                foreach ( array_slice( $due[ $stage ], 0, 5 ) as $slug ) {
                    $asked[ $slug . ':' . $stage ] = current_time( 'Y-m-d' );
                }
            }
            update_post_meta( $id, 'apply_asked', $asked );
        }
    }
}
add_action( 'zaito_apply_check_daily', 'zaito_ac_run' );

function zaito_ac_schedule() {
    if ( ! wp_next_scheduled( 'zaito_apply_check_daily' ) ) {
        // 日本時間の朝10時ごろに送る。
        $next = strtotime( 'tomorrow 01:00 UTC' );
        wp_schedule_event( $next, 'daily', 'zaito_apply_check_daily' );
    }
}
add_action( 'init', 'zaito_ac_schedule' );

function zaito_ac_send( $id, $due ) {
    $email = (string) get_post_meta( $id, 'email', true );
    if ( ! is_email( $email ) ) {
        return false;
    }
    $names = array();
    foreach ( array_slice( array_merge( $due[1], $due[2] ), 0, 5 ) as $slug ) {
        $job = zaito_remote_job( $slug );
        if ( $job ) {
            $names[] = $job['company'] . '「' . $job['title'] . '」';
        }
    }
    if ( ! $names ) {
        return false;
    }
    $result = ! $due[1];
    $host   = preg_replace( '/^www\./', '', (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
    return zaito_mail_button(
        $email,
        $result ? '【zaito】選考の結果はいかがでしたか？' : '【zaito】応募はできましたか？',
        $result ? '選考の結果はいかがでしたか？' : '応募はできましたか？',
        ( $result ? '以前「応募した」と教えていただいた求人です。' : 'zaitoから募集ページを見た求人です。' )
            . "\n" . implode( "\n", $names ) . "\n"
            . 'ボタンを押して、当てはまるものを選んでください（10秒で終わります）。教えていただいた内容は、掲載する求人を選ぶのに使います。',
        '回答する',
        zaito_ac_url( $id ),
        'リンクは30日間有効です。',
        'このメールが不要な場合は、このメールに「配信停止」と返信してください。',
        'zaito <info@' . $host . '>'
    );
}

/* ---------- 回答ページ（/apply-check/） ---------- */

function zaito_ac_rewrite() {
    add_rewrite_rule( '^apply-check/?$', 'index.php?zaito_page=apply_check', 'top' );
}
add_action( 'init', 'zaito_ac_rewrite' );

function zaito_ac_template_redirect() {
    if ( 'apply_check' !== get_query_var( 'zaito_page' ) ) {
        return;
    }
    nocache_headers();
    header( 'X-Robots-Tag: noindex, nofollow' );
    $k      = isset( $_REQUEST['k'] ) ? (string) wp_unslash( $_REQUEST['k'] ) : '';
    $member = zaito_ac_member( $k );
    $saved  = false;
    // 回答はボタンを押したとき（POST）だけ記録する。
    if ( $member && 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['a'] ) && is_array( $_POST['a'] ) ) {
        $all = zaito_ac_choices( 1 ) + zaito_ac_choices( 2 );
        foreach ( wp_unslash( $_POST['a'] ) as $slug => $status ) {
            $slug   = sanitize_key( $slug );
            $status = sanitize_key( $status );
            if ( isset( $all[ $status ] ) && zaito_remote_job( $slug ) ) {
                zaito_ac_save( $member, $slug, $status );
                $saved = true;
            }
        }
    }
    $rows = array();
    if ( $member ) {
        // 応募ページへ進んだ求人（最近のもの）を、いまの答えといっしょに出す。
        $clicks = get_post_meta( $member, 'remote_clicks', true );
        $clicks = is_array( $clicks ) ? $clicks : array();
        arsort( $clicks );
        $status = zaito_ac_status( $member );
        foreach ( array_slice( $clicks, 0, 10, true ) as $slug => $date ) {
            $job = zaito_remote_job( $slug );
            if ( ! $job ) {
                continue;
            }
            $st     = isset( $status[ $slug ] ) ? $status[ $slug ]['status'] : '';
            $stage  = in_array( $st, array( 'applied', 'hired', 'waiting', 'rejected', 'declined' ), true ) ? 2 : 1;
            $rows[] = array( 'job' => $job, 'stage' => $stage, 'status' => $st );
        }
    }
    status_header( $member ? 200 : 403 );
    header( 'Content-Type: text/html; charset=UTF-8' );
    $zaito_ac = array( 'k' => $k, 'ok' => (bool) $member, 'saved' => $saved, 'rows' => $rows );
    include get_template_directory() . '/lp/apply-check.php';
    exit;
}
add_action( 'template_redirect', 'zaito_ac_template_redirect', -1 );
