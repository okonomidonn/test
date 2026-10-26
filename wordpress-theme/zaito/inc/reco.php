<?php
/**
 * トップページの「あなたへのおすすめ」（5件）。lp/reco.js が admin-ajax（zaito_reco）から読み込む。
 *
 * - 本人確認済みのブラウザ（zaito_my_id）では、登録した希望条件（立場・やってみたい仕事・日数・希望時給）で
 *   掲載中の求人に点数をつけ、高い順に返す。理由（「SNS運用に興味がある」など）も添える。
 * - それ以外のブラウザには、未経験OK・高時給・新着・学生OKを重く見た共通のおすすめを返す。
 * - このブラウザで見終わった求人（seen）と、保存済みの求人は除く。全部見終わったら除かずに返す。
 * - 点数は決まったルールで計算する（外部のAIには何も送らない）。
 * - ページはキャッシュされるため、中身はここで作らず、JSが読み込んで並べる。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ZAITO_RECO_N', 5 );

/**
 * やってみたい仕事（登録の選択肢）と、求人の職種名・タイトルに出てくる言葉の対応。
 */
function zaito_reco_interest_words() {
    return array(
        'SNS運用'           => '/SNS|インスタ|Instagram|TikTok|ショート/u',
        'Webマーケティング'   => '/マーケ|広告|集客|PR|広報/u',
        'ライティング・編集'   => '/ライティング|ライター|編集|記事|校正/u',
        '動画編集'           => '/動画|映像|YouTube|ショート/u',
        'デザイン'           => '/デザイン|デザイナー|バナー|サムネ/u',
        'エンジニア'         => '/エンジニア|開発|プログラ|データ分析|AI/u',
        '事務・アシスタント'   => '/事務|アシスタント|秘書|経理|バックオフィス/u',
        '教育・学習サポート'   => '/教育|講師|学習|指導|コーチ/u',
        'カスタマーサポート'   => '/カスタマー|サポート|問い合わせ|CS/u',
        'リサーチ・データ入力' => '/リサーチ|調査|データ入力|データ確認|仕分け/u',
    );
}

/**
 * 登録の希望条件を、点数づけに使う形にする。本人確認ができないブラウザは null。
 */
function zaito_reco_prefs() {
    $id = function_exists( 'zaito_my_id' ) ? zaito_my_id() : 0;
    if ( ! $id ) {
        return null;
    }
    $interests = get_post_meta( $id, 'interests', true );
    $favs      = function_exists( 'zaito_fav_get' ) ? zaito_fav_get( $id ) : array();
    return array(
        'role'      => (string) get_post_meta( $id, 'role', true ),
        'days'      => (string) get_post_meta( $id, 'days', true ),
        'min_pay'   => (string) get_post_meta( $id, 'min_pay', true ),
        'interests' => is_array( $interests ) ? $interests : array(),
        'favorites' => $favs,
    );
}

/**
 * 1件の求人の点数と理由。$p が null なら共通のおすすめ。
 */
function zaito_reco_score( $j, $p, $today ) {
    $text    = $j['category'] . ' ' . $j['title'];
    $student = (bool) preg_match( '/学生|大学|全学年/u', $j['target'] );
    $adult   = ! $student || (bool) preg_match( '/社会人|主婦|既卒|副業|フリーランス/u', $j['target'] );
    $newbie  = zaito_remote_has_tag( $j, 'mikeiken' ) || (bool) preg_match( '/未経験|経験不問|経験は不問/u', $j['target'] );
    $pay     = zaito_remote_hourly_min( $j['pay'] );
    $days    = preg_match( '/週(\d)(?:日|〜)/u', $j['hours'], $m ) ? (int) $m[1] : 0;
    $short   = zaito_remote_has_tag( $j, 'tanjikan' ) || ( $days > 0 && $days <= 2 );
    $listed  = ! empty( $j['listed'] ) ? strtotime( $j['listed'] ) : 0;
    $fresh   = $listed && ( $today - $listed ) <= 7 * DAY_IN_SECONDS;

    $s       = 0;
    $reasons = array();

    if ( null === $p ) {
        if ( $newbie ) { $s += 3; $reasons[] = '未経験OK'; }
        if ( $pay >= 1500 ) { $s += 3; $reasons[] = '時給' . number_format( $pay ) . '円〜'; }
        elseif ( $pay >= 1200 ) { $s += 1; }
        if ( $student ) { $s += 2; $reasons[] = '学生OK'; }
        if ( $fresh ) { $s += 2; array_unshift( $reasons, '新着' ); }
        return array( $s, array_slice( $reasons, 0, 2 ) );
    }

    // やってみたい仕事
    $words = zaito_reco_interest_words();
    foreach ( $p['interests'] as $it ) {
        if ( isset( $words[ $it ] ) && preg_match( $words[ $it ], $text ) ) {
            $s += 6;
            $reasons[] = $it . 'に興味あり';
            break;
        }
    }
    // 立場：学生なのに学生が応募できない求人は、ほぼ出さない。
    $is_student = (bool) preg_match( '/生$/u', $p['role'] );
    if ( $is_student ) {
        if ( $student ) { $s += 3; $reasons[] = '学生OK'; } else { $s -= 12; }
    } elseif ( '' !== $p['role'] ) {
        if ( $adult ) { $s += 2; } else { $s -= 12; }
    }
    // 働ける日数
    if ( '週1〜2日' === $p['days'] ) {
        if ( $short ) { $s += 3; $reasons[] = '週1〜2日でOK'; } elseif ( $days >= 4 ) { $s -= 3; }
    } elseif ( '週3〜4日' === $p['days'] && $days >= 5 ) {
        $s -= 2;
    }
    // 希望時給
    $want = preg_match( '/([\d,]+)円/u', $p['min_pay'], $m ) ? (int) str_replace( ',', '', $m[1] ) : 0;
    if ( $want ) {
        if ( $pay >= $want ) { $s += 3; $reasons[] = '時給' . number_format( $pay ) . '円〜'; } elseif ( $pay ) { $s -= 4; } else { $s -= 2; } // 時給の書かれていない成果報酬などは少し下げる
    } elseif ( $pay >= 1500 ) {
        $s += 1;
    }
    if ( $newbie ) { $s += 1; }
    if ( $fresh ) { $s += 1; }
    return array( $s, array_slice( $reasons, 0, 2 ) );
}

/**
 * おすすめを返す。POST seen[]: このブラウザで見終わった求人。
 */
function zaito_handle_reco() {
    $seen = isset( $_POST['seen'] ) && is_array( $_POST['seen'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['seen'] ) ) : array();
    $seen = array_slice( $seen, -300 );
    $p    = zaito_reco_prefs();
    $has  = $p && ( $p['interests'] || $p['role'] || $p['days'] || $p['min_pay'] );
    $skip = array_merge( $seen, $p ? $p['favorites'] : array() );
    $now  = time();

    $rows = array();
    foreach ( zaito_remote_jobs() as $i => $j ) { // $i は zaito_remote_jobs() の並び順（同点のときに使う）
        if ( ! empty( $j['closed'] ) ) {
            continue;
        }
        list( $s, $why ) = zaito_reco_score( $j, $has ? $p : null, $now );
        $rows[] = array( 'slug' => $j['slug'], 's' => $s, 'i' => $i, 'why' => $why );
    }
    $fresh = array_values( array_filter( $rows, function ( $r ) use ( $skip ) {
        return ! in_array( $r['slug'], $skip, true );
    } ) );
    $all_seen = count( $fresh ) < ZAITO_RECO_N;
    $pool     = $all_seen ? $rows : $fresh;
    usort( $pool, function ( $a, $b ) {
        return $b['s'] - $a['s'] ?: $a['i'] - $b['i'];
    } );
    // 同じ職種や同じ会社ばかりにならないよう、職種は2件まで・会社は1件までにする（足りなければ点数順で埋める）。
    $pick  = array();
    $by_g  = array();
    $by_co = array();
    $jobs  = zaito_remote_jobs();
    foreach ( $pool as $r ) {
        $j  = $jobs[ $r['i'] ];
        $g  = zaito_remote_group( $j['category'] )[0];
        $co = $j['company'];
        if ( ( isset( $by_g[ $g ] ) && $by_g[ $g ] >= 2 ) || isset( $by_co[ $co ] ) ) {
            continue;
        }
        $pick[]       = $r;
        $by_g[ $g ]   = isset( $by_g[ $g ] ) ? $by_g[ $g ] + 1 : 1;
        $by_co[ $co ] = 1;
        if ( count( $pick ) >= ZAITO_RECO_N ) {
            break;
        }
    }
    foreach ( $pool as $r ) {
        if ( count( $pick ) >= ZAITO_RECO_N ) {
            break;
        }
        if ( ! in_array( $r, $pick, true ) ) {
            $pick[] = $r;
        }
    }

    wp_send_json_success( array(
        'mode'     => $has ? 'member' : ( $p ? 'member_noprefs' : 'guest' ),
        'all_seen' => $all_seen,
        'items'    => array_map( function ( $r ) {
            return array( 'slug' => $r['slug'], 'why' => $r['why'] );
        }, $pick ),
    ) );
}
add_action( 'wp_ajax_zaito_reco', 'zaito_handle_reco' );
add_action( 'wp_ajax_nopriv_zaito_reco', 'zaito_handle_reco' );
