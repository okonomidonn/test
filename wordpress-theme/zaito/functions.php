<?php
function zaito_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    register_nav_menus( array(
        'primary' => '主要メニュー',
    ) );
}
add_action( 'after_setup_theme', 'zaito_setup' );

function zaito_scripts() {
    wp_enqueue_style(
        'zaito-fonts',
        'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700;800;900&display=swap',
        array(),
        null
    );
    wp_enqueue_style(
        'zaito-style',
        get_stylesheet_uri(),
        array(),
        filemtime( get_stylesheet_directory() . '/style.css' )
    );
    wp_enqueue_script(
        'zaito-main',
        get_template_directory_uri() . '/js/zaito.js',
        array(),
        filemtime( get_template_directory() . '/js/zaito.js' ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'zaito_scripts' );

/**
 * ログイン後のリダイレクト先をロールごとに振り分ける。
 * ワーカーはマイページへ、企業はダッシュボードへ、それ以外（管理者等）は既定の動作。
 */
function zaito_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
    if ( isset( $user->roles ) && is_array( $user->roles ) && ! is_wp_error( $user ) ) {
        if ( in_array( 'zaito_company', $user->roles, true ) ) {
            return home_url( '/company/' );
        }
        if ( in_array( 'zaito_seeker', $user->roles, true ) ) {
            return home_url( '/mypage/' );
        }
    }
    return $redirect_to;
}
add_filter( 'login_redirect', 'zaito_login_redirect', 10, 3 );

/**
 * ログアウト後はトップページへ。
 */
function zaito_logout_redirect() {
    wp_safe_redirect( home_url( '/' ) );
    exit;
}
add_action( 'wp_logout', 'zaito_logout_redirect' );

/**
 * カスタムロールの登録
 */
function zaito_register_roles() {
    add_role( 'zaito_seeker', 'ワーカー', array(
        'read' => true,
        'edit_posts' => true,
        'delete_posts' => true,
    ) );
    add_role( 'zaito_company', '企業', array(
        'read' => true,
        'publish_posts' => true,
        'edit_posts' => true,
    ) );
}
add_action( 'init', 'zaito_register_roles' );

/**
 * カスタム投稿タイプの登録
 */
function zaito_register_post_types() {
    register_post_type( 'zaito_application', array(
        'label' => '応募',
        'public' => false,
        'show_ui' => true,
        'supports' => array( 'title', 'editor' ),
        'capability_type' => 'post',
    ) );

    register_post_type( 'zaito_message', array(
        'label' => 'メッセージ',
        'public' => false,
        'show_ui' => true,
        'supports' => array( 'title', 'editor' ),
        'capability_type' => 'post',
    ) );
}
add_action( 'init', 'zaito_register_post_types' );

/**
 * WP Job Manager の求人一覧を取得するヘルパー。
 * プラグイン未導入時は空配列を返す。
 */
function zaito_get_featured_jobs( $limit = 3 ) {
    if ( ! post_type_exists( 'job_listing' ) ) {
        return array();
    }
    return get_posts( array(
        'post_type'      => 'job_listing',
        'posts_per_page' => $limit,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );
}

/**
 * チャットメッセージを読み込むAJAXハンドラー
 */
function zaito_load_messages() {
    check_ajax_referer( 'zaito_chat_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error();
    }

    $conversation_id = intval( $_POST['conversation_id'] );
    $application = get_post( $conversation_id );

    if ( ! $application || $application->post_type !== 'zaito_application' ) {
        wp_send_json_error();
    }

    $current_user = wp_get_current_user();
    $applicant_id = get_post_meta( $conversation_id, 'applicant_id', true );
    $company_id = get_post_meta( $conversation_id, 'company_id', true );

    if ( $current_user->ID !== $applicant_id && $current_user->ID !== $company_id ) {
        wp_send_json_error();
    }

    $args = array(
        'post_type' => 'zaito_message',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'conversation_id',
                'value' => $conversation_id,
            ),
        ),
        'orderby' => 'date',
        'order' => 'ASC',
    );

    $messages = get_posts( $args );
    $html = '';

    foreach ( $messages as $msg ) {
        $sender_id = get_post_meta( $msg->ID, 'sender_id', true );
        $is_own = ( $sender_id == $current_user->ID );
        $class = $is_own ? 'own' : 'other';
        $sender = get_user_by( 'id', $sender_id );
        $sender_name = $sender ? $sender->first_name : '不明';

        $html .= '<div class="message message-' . esc_attr( $class ) . '">';
        $html .= '<div class="message-bubble">';
        $html .= '<p class="message-text">' . wp_kses_post( nl2br( $msg->post_content ) ) . '</p>';
        $html .= '<span class="message-time">' . esc_html( get_the_date( 'H:i', $msg ) ) . '</span>';
        $html .= '</div>';
        $html .= '</div>';
    }

    wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_zaito_load_messages', 'zaito_load_messages' );

/**
 * チャットメッセージを送信するAJAXハンドラー
 */
function zaito_send_message() {
    check_ajax_referer( 'zaito_chat_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error();
    }

    $current_user = wp_get_current_user();
    $conversation_id = intval( $_POST['conversation_id'] );
    $message_text = sanitize_textarea_field( $_POST['message'] );

    if ( ! $message_text ) {
        wp_send_json_error( array( 'message' => 'メッセージが空です' ) );
    }

    $application = get_post( $conversation_id );
    if ( ! $application || $application->post_type !== 'zaito_application' ) {
        wp_send_json_error();
    }

    $applicant_id = get_post_meta( $conversation_id, 'applicant_id', true );
    $company_id = get_post_meta( $conversation_id, 'company_id', true );

    if ( $current_user->ID !== $applicant_id && $current_user->ID !== $company_id ) {
        wp_send_json_error();
    }

    $message_id = wp_insert_post( array(
        'post_type' => 'zaito_message',
        'post_content' => $message_text,
        'post_status' => 'publish',
        'post_title' => 'Message ' . current_time( 'timestamp' ),
    ) );

    if ( $message_id ) {
        update_post_meta( $message_id, 'conversation_id', $conversation_id );
        update_post_meta( $message_id, 'sender_id', $current_user->ID );
        update_post_meta( $conversation_id, 'last_message_text', $message_text );
        wp_send_json_success( array( 'message_id' => $message_id ) );
    } else {
        wp_send_json_error( array( 'message' => 'メッセージの送信に失敗しました' ) );
    }
}
add_action( 'wp_ajax_zaito_send_message', 'zaito_send_message' );
