<?php
/**
 * 学生（メールアドレスの登録 zaito_interest）の、Google・LINEでのログインと登録。
 *
 * ボタンは /wp-admin/admin-post.php?action=zaito_social_start&p=google&to=/... を開き、押した時点で
 * stateを作ってGoogle（LINE）へ移る（ページがキャッシュされても、古いstateで失敗しないように）。
 * 戻り先の処理は page-google-callback.php（Google）と zaito_social_line_callback（LINE）。
 * ログインできたら、マイページの本人確認（zaito_my）を付ける。Google・LINEから受け取ったメールアドレスは
 * 確認済みとして扱う（メールのリンクを押さなくても、そのまま応募できる）。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** LINEログインが使えるか（wp-config.php に ZAITO_LINE_CHANNEL_ID / ZAITO_LINE_CHANNEL_SECRET を書くと使える）。 */
function zaito_line_login_is_configured() {
    return defined( 'ZAITO_LINE_CHANNEL_ID' ) && ZAITO_LINE_CHANNEL_ID
        && defined( 'ZAITO_LINE_CHANNEL_SECRET' ) && ZAITO_LINE_CHANNEL_SECRET;
}

/**
 * Google・LINEから戻ってきたURLの error（ログインをキャンセルしたなど）があるか。
 * WordPressは、URLの書き換えに一致したページで $_GET['error'] を消すため、元のURLから読む。
 */
function zaito_social_cancelled() {
    $q = array();
    parse_str( isset( $_SERVER['QUERY_STRING'] ) ? (string) $_SERVER['QUERY_STRING'] : '', $q );
    return ! empty( $q['error'] );
}

/** 戻り先は、このサイトの中のパスだけ。 */
function zaito_social_safe_to( $to ) {
    $to = (string) $to;
    return ( '' !== $to && '/' === $to[0] && '/' !== substr( $to, 1, 1 ) ) ? $to : '/mypage/';
}

/** ボタンのリンク先。$p は 'google' か 'line'。 */
function zaito_social_url( $p, $to ) {
    return add_query_arg( array( 'action' => 'zaito_social_start', 'p' => $p, 'to' => rawurlencode( zaito_social_safe_to( $to ) ) ), admin_url( 'admin-post.php' ) );
}

/**
 * Google・LINEのボタン（使える方だけ出す）。$verb は「登録」「ログイン」など。どちらも使えなければ何も出さない。
 */
function zaito_social_buttons( $to, $verb = 'ログイン' ) {
    $google = function_exists( 'zaito_google_login_is_configured' ) && zaito_google_login_is_configured();
    $line   = zaito_line_login_is_configured();
    if ( ! $google && ! $line ) {
        return false;
    }
    echo '<div class="social">';
    if ( $line ) {
        echo '<a class="sbtn sbtn-line" href="' . esc_url( zaito_social_url( 'line', $to ) ) . '" rel="nofollow"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#fff" d="M12 3C6.5 3 2 6.6 2 11c0 3.9 3.5 7.2 8.3 7.9.3.1.8.2.9.5.1.3.1.7 0 1l-.1.9c0 .3-.2 1 .9.5s5.9-3.5 8-6C21.4 14.3 22 12.7 22 11c0-4.4-4.5-8-10-8z"/></svg>LINEで' . esc_html( $verb ) . '</a>';
    }
    if ( $google ) {
        echo '<a class="sbtn sbtn-google" href="' . esc_url( zaito_social_url( 'google', $to ) ) . '" rel="nofollow"><svg viewBox="0 0 48 48" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.5 0 6.7 1.2 9.2 3.6l6.9-6.9C35.9 2.4 30.4 0 24 0 14.6 0 6.6 5.4 2.6 13.2l8 6.2C12.5 13.6 17.8 9.5 24 9.5z"/><path fill="#4285F4" d="M46.1 24.5c0-1.6-.1-3.1-.4-4.5H24v9h12.4c-.5 2.9-2.2 5.3-4.6 6.9l7.5 5.8c4.4-4 6.8-10 6.8-17.2z"/><path fill="#FBBC05" d="M10.6 28.6c-.5-1.4-.8-3-.8-4.6s.3-3.2.8-4.6l-8-6.2C.9 16.5 0 20.1 0 24s.9 7.5 2.6 10.8l8-6.2z"/><path fill="#34A853" d="M24 48c6.5 0 11.9-2.1 15.9-5.8l-7.5-5.8c-2.1 1.4-4.8 2.3-8.4 2.3-6.2 0-11.5-4.1-13.4-9.9l-8 6.2C6.6 42.6 14.6 48 24 48z"/></svg>Googleで' . esc_html( $verb ) . '</a>';
    }
    // メールアドレスの取得と使い道を、ボタンのすぐ下で伝える（LINEのメールアドレス取得権限の条件）。
    echo '<p class="social-note">' . ( $line && $google ? 'LINE・Google' : ( $line ? 'LINE' : 'Google' ) ) . 'で登録・ログインすると、アカウントに登録されているメールアドレスと名前をzaitoが受け取ります。メールアドレスは、ログイン、応募した企業からの返信のお知らせ、新着求人のお知らせに使います（<a href="' . esc_url( home_url( '/privacy/' ) ) . '" target="_blank">プライバシーポリシー</a>）。</p>';
    echo '</div>';
    return true;
}

/** ボタンを押したとき: stateを作って、Google・LINEのログイン画面へ。 */
function zaito_handle_social_start() {
    nocache_headers();
    $p  = isset( $_GET['p'] ) ? sanitize_key( $_GET['p'] ) : '';
    $to = zaito_social_safe_to( isset( $_GET['to'] ) ? rawurldecode( (string) wp_unslash( $_GET['to'] ) ) : '' );
    if ( 'google' === $p && zaito_google_login_is_configured() ) {
        wp_redirect( zaito_google_login_url( home_url( $to ), 'student' ) );
        exit;
    }
    if ( 'line' === $p && zaito_line_login_is_configured() ) {
        $state = wp_generate_password( 32, false );
        set_transient( 'zaito_line_state_' . $state, array( 'to' => $to ), 10 * MINUTE_IN_SECONDS );
        wp_redirect( add_query_arg( array(
            'response_type' => 'code',
            'client_id'     => ZAITO_LINE_CHANNEL_ID,
            'redirect_uri'  => rawurlencode( home_url( '/line-callback/' ) ),
            'state'         => $state,
            'scope'         => rawurlencode( 'profile openid email' ),
            'bot_prompt'    => 'normal',
        ), 'https://access.line.me/oauth2/v2.1/authorize' ) );
        exit;
    }
    wp_safe_redirect( home_url( $to ) );
    exit;
}
add_action( 'admin_post_zaito_social_start', 'zaito_handle_social_start' );
add_action( 'admin_post_nopriv_zaito_social_start', 'zaito_handle_social_start' );

/**
 * Google・LINEで確かめたメールアドレスで、学生の登録を探すか作り、このブラウザをログイン状態にする。
 * 戻り値は登録ID（失敗なら0）。
 */
function zaito_social_sign_in( $email, $name, $source ) {
    $email = strtolower( trim( $email ) );
    if ( ! is_email( $email ) ) {
        return 0;
    }
    $result = zaito_lp_find_or_create_interest( $email, $source );
    if ( ! $result ) {
        return 0;
    }
    list( $id, $created ) = $result;
    if ( ! get_post_meta( $id, 'verified', true ) ) {
        update_post_meta( $id, 'verified', current_time( 'Y-m-d H:i' ) );
    }
    if ( $name && '' === (string) get_post_meta( $id, 'name', true ) ) {
        update_post_meta( $id, 'name', mb_substr( $name, 0, 40 ) );
    }
    update_post_meta( $id, 'login_' . $source, current_time( 'Y-m-d H:i' ) );
    zaito_remote_set_member_cookie( $id );
    zaito_my_set_cookie( $id );
    do_action( 'zaito_remote_registered', $id, $created );
    return $id;
}

/* ---------- LINE ---------- */

function zaito_line_rewrite() {
    add_rewrite_rule( '^line-callback/?$', 'index.php?zaito_page=line-callback', 'top' );
}
add_action( 'init', 'zaito_line_rewrite' );

function zaito_line_callback() {
    if ( 'line-callback' !== get_query_var( 'zaito_page' ) ) {
        return;
    }
    nocache_headers();
    $error = 'LINEでログインできませんでした。もう一度お試しください。';
    $state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
    $data  = $state ? get_transient( 'zaito_line_state_' . $state ) : false;
    $code  = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
    if ( $data && $code && zaito_line_login_is_configured() ) {
        delete_transient( 'zaito_line_state_' . $state );
        $res  = wp_remote_post( 'https://api.line.me/oauth2/v2.1/token', array( 'body' => array(
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => home_url( '/line-callback/' ),
            'client_id'     => ZAITO_LINE_CHANNEL_ID,
            'client_secret' => ZAITO_LINE_CHANNEL_SECRET,
        ) ) );
        $body = is_wp_error( $res ) ? array() : json_decode( wp_remote_retrieve_body( $res ), true );
        if ( ! empty( $body['id_token'] ) ) {
            // IDトークンはLINEのAPIで検証してから中身を使う。
            $vr = wp_remote_post( 'https://api.line.me/oauth2/v2.1/verify', array( 'body' => array( 'id_token' => $body['id_token'], 'client_id' => ZAITO_LINE_CHANNEL_ID ) ) );
            $v  = is_wp_error( $vr ) ? array() : json_decode( wp_remote_retrieve_body( $vr ), true );
            $email = isset( $v['email'] ) ? sanitize_email( $v['email'] ) : '';
            $name  = isset( $v['name'] ) ? sanitize_text_field( $v['name'] ) : '';
            if ( ! $email ) {
                $error = 'LINEに登録されたメールアドレスを受け取れませんでした。LINEの画面でメールアドレスの利用を許可するか、メールアドレスで登録してください。';
            } else {
                $id = zaito_social_sign_in( $email, $name, 'line' );
                if ( $id ) {
                    if ( ! empty( $v['sub'] ) ) {
                        update_post_meta( $id, 'line_user_id', sanitize_text_field( $v['sub'] ) );
                    }
                    wp_safe_redirect( home_url( $data['to'] ) );
                    exit;
                }
            }
        }
    } elseif ( zaito_social_cancelled() ) {
        $error = 'LINEでのログインをキャンセルしました。';
    }
    zaito_social_error_page( 'LINEでログイン', $error );
}
add_action( 'template_redirect', 'zaito_line_callback', -1 );

/** ログインに失敗したときの画面（学生向けの見た目）。 */
function zaito_social_error_page( $title, $message ) {
    require_once get_template_directory() . '/lp/biz-parts.php';
    status_header( 200 );
    zaito_biz_head( $title );
    echo '<body>';
    zaito_remote_header();
    echo '<main class="auth"><h1>' . esc_html( $title ) . '</h1><div class="note n-err" role="alert"><span class="ms" aria-hidden="true">warning</span><div><b>ログインできませんでした</b><p>' . esc_html( $message ) . '</p></div></div>';
    echo '<a class="btn btn-p btn-lg" href="' . esc_url( home_url( '/mypage/' ) ) . '">マイページへ</a></main>';
    zaito_remote_footer();
    echo '</body></html>';
    exit;
}
