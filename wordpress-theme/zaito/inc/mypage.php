<?php
/**
 * マイページ（/mypage/）と、求人の保存（お気に入り）。
 *
 * - 本人確認: 登録フォームの zaito_member Cookie は「メールアドレスを入れただけ」でも付くため、
 *   それだけでは本人の希望条件や保存した求人は見せない。次のどれかを通ったブラウザにだけ
 *   zaito_my Cookie（登録ID.署名）を付け、マイページの中身を返す。
 *   1. このブラウザで新しく登録した（zaito_remote_registered）
 *   2. マイページのログインリンク（メールで届く、24時間有効）を開いた
 *   3. 新着メールのリンク（本人専用の署名つき）から来た
 * - 保存した求人: 本人確認済みなら登録（post meta 'favorites'）に、まだならブラウザ（localStorage）に保存する。
 *   本人確認が済んだ最初のマイページ表示で、ブラウザの保存を登録に移す。
 * - ページはキャッシュされても困らないよう中身を持たず、JSが admin-ajax から読み込む。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ZAITO_FAV_MAX', 100 );

function zaito_mypage_url() {
    return home_url( '/mypage/' );
}

/* ---------- 本人確認済みのCookie ---------- */

function zaito_my_sign( $interest_id ) {
    return hash_hmac( 'sha256', 'zaito_my|' . (int) $interest_id, wp_salt( 'auth' ) );
}

function zaito_my_set_cookie( $interest_id ) {
    $opts = array(
        'expires'  => time() + YEAR_IN_SECONDS,
        'path'     => '/',
        'secure'   => is_ssl(),
        'httponly' => true,
        'samesite' => 'Lax',
    );
    setcookie( 'zaito_my', (int) $interest_id . '.' . zaito_my_sign( $interest_id ), $opts );
    $_COOKIE['zaito_my'] = (int) $interest_id . '.' . zaito_my_sign( $interest_id );
    // ページのJSが「保存をサーバーにも送るか」を判断する目印（中身は見ない）。
    $opts['httponly'] = false;
    setcookie( 'zaito_myf', '1', $opts );
    if ( ! zaito_remote_member_id() ) {
        zaito_remote_set_member_cookie( $interest_id );
    }
}

function zaito_my_id() {
    $raw = isset( $_COOKIE['zaito_my'] ) ? (string) wp_unslash( $_COOKIE['zaito_my'] ) : '';
    if ( ! preg_match( '/^(\d+)\.([a-f0-9]{64})$/', $raw, $m ) ) {
        return 0;
    }
    if ( ! hash_equals( zaito_my_sign( $m[1] ), $m[2] ) ) {
        return 0;
    }
    return 'zaito_interest' === get_post_type( (int) $m[1] ) ? (int) $m[1] : 0;
}

/**
 * 新しく登録したブラウザは本人とみなす（zaito_handle_remote_register から呼ばれる）。
 */
function zaito_my_on_register( $interest_id, $created ) {
    if ( $created ) {
        zaito_my_set_cookie( $interest_id );
    }
}
add_action( 'zaito_remote_registered', 'zaito_my_on_register', 10, 2 );

/**
 * 新着メールの本人専用リンクから来た人も本人とみなす（inc/newsletter.php）。
 */
function zaito_my_on_newsletter_return( $member ) {
    if ( zaito_my_id() !== (int) $member ) {
        zaito_my_set_cookie( $member );
    }
}
add_action( 'zaito_nl_returned', 'zaito_my_on_newsletter_return' );

/* ---------- ログインリンク ---------- */

function zaito_my_login_sig( $id, $exp ) {
    return hash_hmac( 'sha256', 'zaito_my_login|' . (int) $id . '|' . (int) $exp, wp_salt( 'auth' ) );
}

function zaito_my_login_url( $id ) {
    $exp = time() + DAY_IN_SECONDS;
    return add_query_arg( 'k', (int) $id . '.' . $exp . '.' . zaito_my_login_sig( $id, $exp ), zaito_mypage_url() );
}

/**
 * ログインフォーム。登録の有無にかかわらず同じ返事をする（他人の登録の有無が分からないように）。
 */
function zaito_handle_my_login() {
    $email = isset( $_POST['email'] ) ? strtolower( trim( sanitize_email( wp_unslash( $_POST['email'] ) ) ) ) : '';
    if ( ! $email || ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => '正しいメールアドレスを入力してください' ), 400 );
    }
    if ( ! empty( $_POST['website'] ) ) {
        wp_send_json_success( array() );
    }
    if ( zaito_lp_rate_limited( 'mypage_login' ) ) {
        wp_send_json_error( array( 'message' => '送信回数が多すぎます。しばらくしてからお試しください' ), 400 );
    }
    $ids = get_posts( array(
        'post_type'      => 'zaito_interest',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => array( array( 'key' => 'email', 'value' => $email ) ),
    ) );
    if ( $ids ) {
        // 同じアドレスに送るのは10分に1回まで。
        $key = 'zaito_my_mail_' . md5( $email );
        if ( ! get_transient( $key ) ) {
            set_transient( $key, 1, 10 * MINUTE_IN_SECONDS );
            $host = preg_replace( '/^www\./', '', (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
            $body = "zaitoのマイページを開くリンクです。\n\n"
                . zaito_my_login_url( $ids[0] ) . "\n\n"
                . "リンクは24時間有効です。開いたブラウザでは、次回からそのままマイページを使えます。\n"
                . "※お心当たりのない場合は、このメールを破棄してください。\n\n"
                . "──────────\n"
                . "zaito（ザイト）出社なしの仕事だけを集めた求人サイト\n"
                . home_url( '/' ) . "\n";
            wp_mail( $email, '【zaito】マイページのログインリンク', $body, array( 'From: zaito <info@' . $host . '>' ) );
        }
    }
    wp_send_json_success( array() );
}
add_action( 'wp_ajax_zaito_my_login', 'zaito_handle_my_login' );
add_action( 'wp_ajax_nopriv_zaito_my_login', 'zaito_handle_my_login' );

/* ---------- 表示（/mypage/） ---------- */

function zaito_mypage_rewrite() {
    add_rewrite_rule( '^mypage/?$', 'index.php?zaito_page=mypage', 'top' );
}
add_action( 'init', 'zaito_mypage_rewrite' );

function zaito_mypage_template_redirect() {
    if ( 'mypage' !== get_query_var( 'zaito_page' ) ) {
        return;
    }
    nocache_headers();
    header( 'X-Robots-Tag: noindex, nofollow' );
    // ログインリンク: 確かめてCookieを付け、リンクの中身がURLに残らないよう付け外したURLへ移る。
    if ( isset( $_GET['k'] ) ) {
        $ok = false;
        if ( preg_match( '/^(\d+)\.(\d+)\.([a-f0-9]{64})$/', (string) wp_unslash( $_GET['k'] ), $m ) ) {
            $ok = (int) $m[2] >= time()
                && 'zaito_interest' === get_post_type( (int) $m[1] )
                && hash_equals( zaito_my_login_sig( $m[1], $m[2] ), $m[3] );
            if ( $ok ) {
                zaito_my_set_cookie( (int) $m[1] );
            }
        }
        wp_safe_redirect( $ok ? zaito_mypage_url() : add_query_arg( 'login', 'expired', zaito_mypage_url() ) );
        exit;
    }
    status_header( 200 );
    header( 'Content-Type: text/html; charset=UTF-8' );
    include get_template_directory() . '/lp/mypage.php';
    exit;
}
add_action( 'template_redirect', 'zaito_mypage_template_redirect', -1 );

/* ---------- 保存した求人 ---------- */

function zaito_fav_clean( $slugs ) {
    $out = array();
    foreach ( (array) $slugs as $s ) {
        $s = sanitize_key( $s );
        if ( $s && zaito_remote_job( $s ) && ! in_array( $s, $out, true ) ) {
            $out[] = $s;
        }
    }
    return array_slice( $out, 0, ZAITO_FAV_MAX );
}

function zaito_fav_get( $id ) {
    $f = get_post_meta( $id, 'favorites', true );
    return is_array( $f ) ? zaito_fav_clean( $f ) : array();
}

/**
 * 求人ページで保存・解除したとき（本人確認済みのブラウザだけ）。
 */
function zaito_handle_fav_toggle() {
    $id = zaito_my_id();
    if ( ! $id ) {
        wp_send_json_error( array( 'message' => 'login' ), 401 );
    }
    $slug = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
    if ( ! $slug || ! zaito_remote_job( $slug ) ) {
        wp_send_json_error( array( 'message' => 'not found' ), 400 );
    }
    $on   = ! empty( $_POST['on'] );
    $favs = array_values( array_diff( zaito_fav_get( $id ), array( $slug ) ) );
    if ( $on ) {
        array_unshift( $favs, $slug );
    }
    update_post_meta( $id, 'favorites', array_slice( $favs, 0, ZAITO_FAV_MAX ) );
    wp_send_json_success( array( 'favorites' => $favs ) );
}
add_action( 'wp_ajax_zaito_fav', 'zaito_handle_fav_toggle' );
add_action( 'wp_ajax_nopriv_zaito_fav', 'zaito_handle_fav_toggle' );

/* ---------- マイページの中身 ---------- */

function zaito_my_job_summary( $slug ) {
    $j = zaito_remote_job( $slug );
    return $j ? array(
        'slug'    => $j['slug'],
        'title'   => $j['title'],
        'company' => $j['company'],
        'pay'     => $j['pay'] ? $j['pay'] : '報酬は募集ページで確認',
        'url'     => zaito_remote_url( $j['slug'] ),
        'closed'  => ! empty( $j['closed'] ),
    ) : null;
}

/**
 * 本人確認済みなら希望条件・保存した求人・配信の状態を返す。まだなら、ブラウザに保存した求人の情報だけ返す。
 * local: ブラウザ（localStorage）に保存されている求人。merge=1 のときは登録の保存に足す（初回の引き継ぎ）。
 */
function zaito_handle_my_data() {
    $local = isset( $_POST['local'] ) && is_array( $_POST['local'] ) ? zaito_fav_clean( wp_unslash( $_POST['local'] ) ) : array();
    $id    = zaito_my_id();
    if ( ! $id ) {
        wp_send_json_success( array(
            'state'     => 'guest',
            'registered' => (bool) zaito_remote_member_id(),
            'favorites' => array_values( array_filter( array_map( 'zaito_my_job_summary', $local ) ) ),
        ) );
    }
    $favs = zaito_fav_get( $id );
    if ( ! empty( $_POST['merge'] ) && $local ) {
        $favs = array_slice( array_values( array_unique( array_merge( $favs, $local ) ) ), 0, ZAITO_FAV_MAX );
        update_post_meta( $id, 'favorites', $favs );
    }
    $prefs = array();
    foreach ( array( 'role', 'grade', 'school', 'days', 'min_pay' ) as $k ) {
        $prefs[ $k ] = (string) get_post_meta( $id, $k, true );
    }
    $interests          = get_post_meta( $id, 'interests', true );
    $prefs['interests'] = is_array( $interests ) ? array_values( $interests ) : array();
    wp_send_json_success( array(
        'state'        => 'member',
        'email'        => (string) get_post_meta( $id, 'email', true ),
        'prefs'        => $prefs,
        'favorites'    => array_values( array_filter( array_map( 'zaito_my_job_summary', $favs ) ) ),
        'unsubscribed' => (bool) get_post_meta( $id, 'unsubscribed', true ),
    ) );
}
add_action( 'wp_ajax_zaito_my_data', 'zaito_handle_my_data' );
add_action( 'wp_ajax_nopriv_zaito_my_data', 'zaito_handle_my_data' );

/**
 * 希望条件の保存。マイページでは、選び直した内容でそのまま置き換える（外した選択肢も反映する）。
 */
function zaito_handle_my_save() {
    $id = zaito_my_id();
    if ( ! $id ) {
        wp_send_json_error( array( 'message' => 'ログインし直してください' ), 401 );
    }
    if ( zaito_lp_rate_limited( 'mypage_save' ) ) {
        wp_send_json_error( array( 'message' => '送信回数が多すぎます。しばらくしてからお試しください' ), 400 );
    }
    $opts = zaito_remote_profile_options();
    foreach ( array( 'role', 'grade', 'days', 'min_pay' ) as $k ) {
        $v = isset( $_POST[ $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) : '';
        if ( in_array( $v, $opts[ $k ], true ) ) {
            update_post_meta( $id, $k, $v );
        } elseif ( '' === $v ) {
            delete_post_meta( $id, $k );
        }
    }
    $school = isset( $_POST['school'] ) ? sanitize_text_field( wp_unslash( $_POST['school'] ) ) : '';
    if ( '' !== $school ) {
        update_post_meta( $id, 'school', mb_substr( $school, 0, 80 ) );
    } else {
        delete_post_meta( $id, 'school' );
    }
    $picked = isset( $_POST['interests'] ) && is_array( $_POST['interests'] )
        ? array_values( array_intersect( array_map( 'sanitize_text_field', wp_unslash( $_POST['interests'] ) ), $opts['interests'] ) )
        : array();
    update_post_meta( $id, 'interests', $picked );

    // 新着メールの受け取り（マイページから止めた・再開した）。
    $mail = isset( $_POST['mail'] ) ? (string) $_POST['mail'] : '';
    if ( 'off' === $mail && ! get_post_meta( $id, 'unsubscribed', true ) ) {
        zaito_nl_mark_unsubscribed( $id );
    } elseif ( 'on' === $mail ) {
        delete_post_meta( $id, 'unsubscribed' );
    }
    wp_send_json_success( array() );
}
add_action( 'wp_ajax_zaito_my_save', 'zaito_handle_my_save' );
add_action( 'wp_ajax_nopriv_zaito_my_save', 'zaito_handle_my_save' );

/**
 * このブラウザのログインを解除する。
 */
function zaito_handle_my_logout() {
    $opts = array( 'expires' => time() - HOUR_IN_SECONDS, 'path' => '/', 'secure' => is_ssl(), 'samesite' => 'Lax' );
    foreach ( array( 'zaito_my', 'zaito_myf', 'zaito_member', 'zaito_m' ) as $c ) {
        setcookie( $c, '', $opts );
    }
    wp_send_json_success( array() );
}
add_action( 'wp_ajax_zaito_my_logout', 'zaito_handle_my_logout' );
add_action( 'wp_ajax_nopriv_zaito_my_logout', 'zaito_handle_my_logout' );
