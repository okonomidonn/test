<?php
/**
 * 一人ひとりに合わせた求人メールの自動配信（サーバーのCronで毎日。cron/send-mail.php）。
 *
 * - 送り先: 配信停止していない登録者（メールの確認がまだの人も含む）。1人に送るのは3日に1回まで（週2回ほど）。
 *   確認がまだの人がメールのリンクを開いたら、本人のアドレスだと分かったので確認済みにする（zaito_nl_returned）。
 *   1日に送るのは ZAITO_NLA_DAILY 人までにして、少しずつ順に送る（一度に大量に送って迷惑メール扱いされないように）。
 * - 送り先から外す人: 立場が「社会人」「主婦・主夫」の人（zaitoは学生向け）、動作確認用の登録。
 * - 中身: その人の希望（やってみたい仕事・働ける日数・希望時給・立場）と、保存した求人の職種に合う求人を最大4件。
 *   反応から学ぶ：応募ページへ進んだ求人・メールから開いた求人と同じ職種を、次の配信で上に出す。
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
    // 反応から学ぶ：応募ページへ進んだ求人（remote_clicks）と、メールから開いた求人（nla_viewed）の職種を上に出す。
    $clicked = get_post_meta( $id, 'remote_clicks', true );
    $clicked = is_array( $clicked ) ? array_keys( $clicked ) : array();
    $viewed  = get_post_meta( $id, 'nla_viewed', true );
    $viewed  = is_array( $viewed ) ? $viewed : array();
    $like_g  = array();
    foreach ( array( array( $clicked, 4 ), array( $viewed, 2 ) ) as $src ) {
        foreach ( $src[0] as $cs ) {
            $cj = zaito_remote_job( $cs );
            if ( $cj ) {
                $g            = zaito_remote_group( $cj['category'] )[0];
                $like_g[ $g ] = min( 8, ( isset( $like_g[ $g ] ) ? $like_g[ $g ] : 0 ) + $src[1] );
            }
        }
    }
    $has  = $p && ( $p['interests'] || $p['role'] || $p['days'] || $p['min_pay'] );
    $now  = time();
    $rows = array();
    foreach ( zaito_remote_jobs() as $i => $j ) {
        if ( ! empty( $j['closed'] ) || ! zaito_remote_student_ok( $j ) || in_array( $j['slug'], $sent, true ) || in_array( $j['slug'], $favs, true ) || in_array( $j['slug'], $clicked, true ) ) {
            continue;
        }
        if ( $p && function_exists( 'zaito_nl_job_matches' ) && ! zaito_nl_job_matches( zaito_nl_member_prefs( $id ), $j ) ) {
            continue; // 職種・時給・日数の希望に合わないものは送らない
        }
        list( $s, ) = zaito_reco_score( $j, $has ? $p : null, $now );
        $s += zaito_reco_affiliate_bonus( $j, $has ? $p : null );
        $jg = zaito_remote_group( $j['category'] )[0];
        if ( isset( $fav_g[ $jg ] ) ) {
            $s += 3; // 保存した求人と同じ職種
        }
        if ( isset( $like_g[ $jg ] ) ) {
            $s += $like_g[ $jg ]; // 応募ページへ進んだ・メールから開いた求人と同じ職種
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
        // 動作確認のために作った登録（名前やアドレスに「テスト」「test」「qa-」が入ったもの）には送らない。
        $name = (string) get_post_meta( $id, 'name', true );
        if ( preg_match( '/テスト|test|^qa-/iu', $name . ' ' . $email ) || false !== strpos( $email, '@zaito-work.com' ) ) {
            continue;
        }
        // zaitoは学生向けなので、立場が「社会人」「主婦・主夫」の人には送らない（学生・未回答・その他には送る）。
        if ( in_array( (string) get_post_meta( $id, 'role', true ), array( '社会人', '主婦・主夫' ), true ) ) {
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
 * メールのリンクから求人ページを開いたら、その求人を記録する（次の配信で、同じ職種を上に出すため）。
 */
add_action( 'zaito_nl_returned', function ( $member ) {
    $path = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH );
    if ( preg_match( '#/remote/([a-z0-9-]+)/?$#', $path, $m ) && zaito_remote_job( $m[1] ) ) {
        $viewed   = get_post_meta( $member, 'nla_viewed', true );
        $viewed   = is_array( $viewed ) ? $viewed : array();
        $viewed[] = $m[1];
        update_post_meta( $member, 'nla_viewed', array_slice( array_values( array_unique( $viewed ) ), -50 ) );
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
        $subject = zaito_nla_subject( $id, count( $slugs ) );
        $body    = zaito_nla_body( zaito_nla_intro( $id, count( $slugs ) ), $slugs, $campaign, $id );
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

/**
 * 宛名に使う名前（姓。なければ登録した名前の最初の部分）。分からなければ空。
 */
function zaito_nla_name( $id ) {
    $last = trim( (string) get_post_meta( $id, 'last_name', true ) );
    if ( '' === $last ) {
        $name = trim( (string) get_post_meta( $id, 'name', true ) );
        $last = '' !== $name ? preg_split( '/[\s　]+/u', $name )[0] : '';
    }
    return mb_substr( $last, 0, 10 );
}

function zaito_nla_subject( $id, $n ) {
    $name = zaito_nla_name( $id );
    return '【zaito】' . ( '' !== $name ? $name . 'さんに' : '' ) . 'おすすめの在宅バイト（' . $n . '件）';
}

function zaito_nla_intro( $id, $n ) {
    $name = zaito_nla_name( $id );
    return ( '' !== $name ? $name . "さん\n\n" : '' )
        . "こんにちは、zaitoです。\n"
        . ( '' !== $name ? $name . 'さん' : 'あなた' ) . 'におすすめの在宅バイトを' . $n . '件選びました。どれも出社なしで、家から働けます。';
}

/**
 * 自動配信の本文。手動の配信と同じ本文を使い、見出しと、おすすめの選び方の説明だけ変える。
 */
function zaito_nla_body( $intro, $slugs, $campaign, $id ) {
    $body = zaito_nl_build_body( $intro, $slugs, $campaign, $id );
    $body = str_replace( "新しく載った在宅求人\n", "今回のおすすめ\n", $body );
    return str_replace(
        "このメールは、zaitoに登録したメールアドレスに、登録時に答えていただいた希望に合う求人をお送りしています。\n",
        "このメールは、zaitoに登録したメールアドレスにお送りしています。おすすめは、登録のときに選んでいただいた「やってみたい仕事」や「働ける日数」をもとに選んでいます。希望はマイページからいつでも変えられます。\n",
        $body
    );
}
