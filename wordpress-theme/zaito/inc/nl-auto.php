<?php
/**
 * 一人ひとりに合わせた求人メールの自動配信（サーバーのCronで毎日。cron/send-mail.php）。
 *
 * - 送り先: 配信停止していない登録者（メールの確認がまだの人も含む）。1人に送るのは3日に1回まで（週2回ほど）。
 *   確認がまだの人がメールのリンクを開いたら、本人のアドレスだと分かったので確認済みにする（zaito_nl_returned）。
 *   1日に送るのは ZAITO_NLA_DAILY 人までにして、少しずつ順に送る（一度に大量に送って迷惑メール扱いされないように）。
 * - 中身: その人の希望（やってみたい仕事・働ける日数・希望時給・立場）と、保存した求人の職種に合う求人を最大4件。
 *   点数は「今日のおすすめ」と同じ（inc/reco.php の zaito_reco_score）。提携サイト（アルバイトEX）の求人は、
 *   その人の興味に合うときだけ少し上に出す（zaito_reco_affiliate_bonus）。前に送った求人・保存済みの求人・募集終了は除く。
 * - 合う求人が2件に満たない人には、その週は送らない。
 * - 計測・停止リンク・本文は手動の配信（inc/newsletter.php）と同じものを使う。配信の記録は管理画面の配信履歴に「自動」として残る。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ZAITO_NLA_DAILY', 40 );   // 1日に送る人数の上限
define( 'ZAITO_NLA_EVERY', 3 );    // 同じ人に送る間隔（日）。3なら週2回ほど
define( 'ZAITO_NLA_JOBS', 4 );     // 1通に載せる求人の数
define( 'ZAITO_NLA_MIN', 2 );      // これより少なければ送らない

/**
 * 1人分の求人を選ぶ。戻り値は slug の配列。
 */
function zaito_nla_pick( $id ) {
    $p     = zaito_reco_prefs_for( $id );
    $sent  = get_post_meta( $id, 'nla_sent', true );
    $sent  = is_array( $sent ) ? $sent : array();
    $favs  = $p ? (array) $p['favorites'] : array();
    $fav_g = array();
    foreach ( $favs as $fs ) {
        $fj = zaito_remote_job( $fs );
        if ( $fj ) {
            $fav_g[ zaito_remote_group( $fj['category'] )[0] ] = true;
        }
    }
    $has  = $p && ( $p['interests'] || $p['role'] || $p['days'] || $p['min_pay'] );
    $now  = time();
    $rows = array();
    foreach ( zaito_remote_jobs() as $i => $j ) {
        if ( ! empty( $j['closed'] ) || ! zaito_remote_student_ok( $j ) || in_array( $j['slug'], $sent, true ) || in_array( $j['slug'], $favs, true ) ) {
            continue;
        }
        if ( $p && function_exists( 'zaito_nl_job_matches' ) && ! zaito_nl_job_matches( zaito_nl_member_prefs( $id ), $j ) ) {
            continue; // 職種・時給・日数の希望に合わないものは送らない
        }
        list( $s, ) = zaito_reco_score( $j, $has ? $p : null, $now );
        $s += zaito_reco_affiliate_bonus( $j, $has ? $p : null );
        if ( isset( $fav_g[ zaito_remote_group( $j['category'] )[0] ] ) ) {
            $s += 3; // 保存した求人と同じ職種
        }
        $rows[] = array( 'slug' => $j['slug'], 's' => $s, 'i' => $i, 'co' => $j['company'] );
    }
    usort( $rows, function ( $a, $b ) {
        return $b['s'] - $a['s'] ?: $a['i'] - $b['i'];
    } );
    $out = array();
    $cos = array();
    $aff = 0;
    foreach ( $rows as $r ) {
        if ( isset( $cos[ $r['co'] ] ) ) {
            continue; // 1通に同じ会社は1件まで
        }
        $is_aff = zaito_remote_is_affiliate( zaito_remote_job( $r['slug'] ) );
        if ( $is_aff && $aff >= 2 ) {
            continue; // 提携サイトの求人は1通に2件まで（ほかの求人も見られるように）
        }
        $aff += $is_aff ? 1 : 0;
        $out[]          = $r['slug'];
        $cos[ $r['co'] ] = true;
        if ( count( $out ) >= ZAITO_NLA_JOBS ) {
            break;
        }
    }
    return $out;
}

/**
 * 自動配信の送り先。配信停止していない登録者全員（メールの確認がまだの人も含む。同じアドレスは1人として数える）。
 */
function zaito_nla_recipients() {
    $ids  = get_posts( array( 'post_type' => 'zaito_interest', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC' ) );
    $seen = array();
    $out  = array();
    foreach ( $ids as $id ) {
        if ( get_post_meta( $id, 'unsubscribed', true ) ) {
            continue;
        }
        $email = strtolower( trim( (string) get_post_meta( $id, 'email', true ) ) );
        if ( ! is_email( $email ) || isset( $seen[ $email ] ) ) {
            continue;
        }
        $seen[ $email ] = true;
        $out[]          = (int) $id;
    }
    return $out;
}

/**
 * メールの確認がまだの人が、自動配信のリンク（本人あての署名つき）を開いたら、確認済みにする。
 */
add_action( 'zaito_nl_returned', function ( $id ) {
    if ( get_post_meta( $id, 'needs_verify', true ) && ! get_post_meta( $id, 'verified', true ) ) {
        update_post_meta( $id, 'verified', 1 );
        update_post_meta( $id, 'verified_via', 'newsletter_link' );
    }
} );

/**
 * 今日送る人（ZAITO_NLA_EVERY 日以上送っていない人を、前回送った日が古い順に）。
 */
function zaito_nla_due( $limit ) {
    $due = array();
    foreach ( zaito_nla_recipients() as $id ) {
        $last = (int) get_post_meta( $id, 'nla_last', true );
        if ( $last && ( time() - $last ) < ZAITO_NLA_EVERY * DAY_IN_SECONDS ) {
            continue;
        }
        // 登録してすぐの人には送らない（登録の確認メールと重ならないように1日あける）。
        if ( ( time() - (int) get_post_time( 'U', true, $id ) ) < DAY_IN_SECONDS ) {
            continue;
        }
        $due[ $id ] = $last;
    }
    asort( $due );
    return array_slice( array_keys( $due ), 0, $limit );
}

/**
 * 自動配信を1回動かす。$dry が true なら送らずに、誰に何を送るかだけ返す。
 */
function zaito_nla_run( $dry = false, $limit = ZAITO_NLA_DAILY ) {
    $campaign = 'auto' . wp_date( 'Ymd' );
    $intro    = "zaitoです。登録のときに教えていただいた希望と、保存した求人をもとに、あなたに合いそうな出社なしの在宅バイトを選びました。";
    $res      = array( 'campaign' => $campaign, 'sent' => 0, 'skipped' => 0, 'failed' => 0, 'plan' => array() );
    foreach ( zaito_nla_due( $limit ) as $id ) {
        $slugs = zaito_nla_pick( $id );
        if ( count( $slugs ) < ZAITO_NLA_MIN ) {
            $res['skipped']++;
            if ( ! $dry ) {
                update_post_meta( $id, 'nla_last', time() - ( ZAITO_NLA_EVERY - 1 ) * DAY_IN_SECONDS ); // 明日もう一度だけ見直す
            }
            continue;
        }
        $res['plan'][ $id ] = $slugs;
        if ( $dry ) {
            continue;
        }
        $email   = (string) get_post_meta( $id, 'email', true );
        $subject = '【zaito】あなたに合いそうな在宅バイトが' . count( $slugs ) . '件あります';
        $body    = zaito_nl_build_body( $intro, $slugs, $campaign, $id );
        if ( zaito_nl_send_one( $email, $subject, $body, zaito_nl_unsub_url( $id, $campaign ) ) ) {
            $res['sent']++;
            $sent = get_post_meta( $id, 'nla_sent', true );
            $sent = array_slice( array_merge( is_array( $sent ) ? $sent : array(), $slugs ), -200 );
            update_post_meta( $id, 'nla_sent', $sent );
            update_post_meta( $id, 'nla_last', time() );
        } else {
            $res['failed']++;
        }
    }
    if ( ! $dry && ( $res['sent'] || $res['failed'] ) ) {
        $history   = get_option( 'zaito_nl_history', array() );
        $history   = is_array( $history ) ? $history : array();
        $history[] = array(
            'id'      => $campaign,
            'match'   => true,
            'skipped' => $res['skipped'],
            'date'    => current_time( 'Y-m-d H:i' ),
            'subject' => '（自動）あなたに合いそうな在宅バイト',
            'jobs'    => ZAITO_NLA_JOBS,
            'sent'    => $res['sent'],
            'failed'  => $res['failed'],
        );
        update_option( 'zaito_nl_history', array_slice( $history, -50 ), false );
    }
    update_option( 'zaito_nla_last', array( 'at' => wp_date( 'c' ), 'sent' => $res['sent'], 'skipped' => $res['skipped'], 'failed' => $res['failed'], 'dry' => $dry ), false );
    return $res;
}
