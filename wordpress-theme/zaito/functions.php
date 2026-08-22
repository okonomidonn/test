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
 * ログイン失敗時、標準のwp-login.php画面ではなく
 * デザイン済みの自作ログインページへエラー付きで戻す。
 */
function zaito_login_failed( $username ) {
    $referrer_url = isset( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : '';
    $login_page   = home_url( '/login/' );

    if ( strpos( $referrer_url, '/company/' ) !== false ) {
        $login_page = home_url( '/company-login/' );
    }

    $login_page = add_query_arg( 'login', 'failed', $login_page );
    if ( $referrer_url ) {
        $login_page = add_query_arg( 'redirect_to', rawurlencode( $referrer_url ), $login_page );
    }

    wp_safe_redirect( $login_page );
    exit;
}
add_action( 'wp_login_failed', 'zaito_login_failed' );

/**
 * 未ログイン状態で wp-login.php に直接来た場合も
 * 自作ログインページへ誘導する（管理者のログインだけは wp-admin 側で処理させる）。
 */
function zaito_redirect_wp_login_to_custom_page() {
    $script = isset( $_SERVER['SCRIPT_NAME'] ) ? $_SERVER['SCRIPT_NAME'] : '';

    if ( strpos( $script, 'wp-login.php' ) === false ) {
        return;
    }
    if ( isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'logout', 'register', 'lostpassword', 'rp', 'resetpass', 'postpass' ), true ) ) {
        return;
    }
    if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
        return;
    }
    if ( is_user_logged_in() ) {
        return;
    }

    $login_page = home_url( '/login/' );
    if ( ! empty( $_GET['redirect_to'] ) ) {
        $login_page = add_query_arg( 'redirect_to', rawurlencode( $_GET['redirect_to'] ), $login_page );
    }

    wp_safe_redirect( $login_page );
    exit;
}
add_action( 'login_init', 'zaito_redirect_wp_login_to_custom_page' );

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
 * zaito の各機能ページ（ログイン・登録・マイページ等）を
 * 固定ページ（投稿）の作成に依存しない「バーチャルルート」として
 * 登録する。スラッグの競合やページ未作成による404を避けるため、
 * URLを直接テーマのテンプレートファイルにマッピングする。
 */
function zaito_virtual_route_map() {
    return array(
        'login'            => 'page-login.php',
        'register'         => 'page-register.php',
        'company-login'    => 'page-company-login.php',
        'company-register' => 'page-company-register.php',
        'mypage'           => 'page-mypage.php',
        'company'          => 'page-company.php',
        'company-jobs'     => 'page-company-jobs.php',
        'company-applicants' => 'page-company-applicants.php',
        'jobs'             => 'page-jobs.php',
        'apply'            => 'page-apply.php',
        'chat'             => 'page-chat.php',
    );
}

function zaito_register_virtual_routes() {
    foreach ( array_keys( zaito_virtual_route_map() ) as $route ) {
        add_rewrite_rule( '^' . $route . '/?$', 'index.php?zaito_page=' . $route, 'top' );
    }
}
add_action( 'init', 'zaito_register_virtual_routes' );

function zaito_add_query_vars( $vars ) {
    $vars[] = 'zaito_page';
    return $vars;
}
add_filter( 'query_vars', 'zaito_add_query_vars' );

function zaito_render_virtual_routes() {
    $page = get_query_var( 'zaito_page' );
    if ( ! $page ) {
        return;
    }
    $template_map = zaito_virtual_route_map();
    if ( ! isset( $template_map[ $page ] ) ) {
        return;
    }
    status_header( 200 );
    include get_stylesheet_directory() . '/' . $template_map[ $page ];
    exit;
}
add_action( 'template_redirect', 'zaito_render_virtual_routes', 1 );

/**
 * 上記のリライトルールをデータベースに反映させるため、
 * テーマの更新時に一度だけ flush_rewrite_rules() を実行する。
 */
function zaito_maybe_flush_rewrite_rules() {
    $version = '4';
    if ( get_option( 'zaito_rewrite_version' ) !== $version ) {
        flush_rewrite_rules();
        update_option( 'zaito_rewrite_version', $version );
    }
}
add_action( 'init', 'zaito_maybe_flush_rewrite_rules', 20 );

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
 * wp_login アクションを発火させずにユーザーをログイン状態にする。
 * wp_signon() 経由だと do_action('wp_login') が発火し、
 * Ultimate Member 等が自前のプロフィールURLへ強制リダイレクトして
 * しまうため、新規登録直後のサイレントログインではこちらを使う。
 */
function zaito_log_user_in_silently( $user_id ) {
    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id, false );
}

/**
 * フォームのエラー内容を一時保存し、トークン付きでリダイレクト元に戻す。
 */
function zaito_store_form_errors_and_redirect( $errors, $redirect_url ) {
    $token = wp_generate_password( 20, false );
    set_transient( 'zaito_form_errors_' . $token, $errors, 60 );
    wp_safe_redirect( add_query_arg( 'zaito_error', $token, $redirect_url ) );
    exit;
}

/**
 * トークンからフォームエラーを取得して破棄する。
 */
function zaito_get_form_errors_from_token() {
    if ( empty( $_GET['zaito_error'] ) ) {
        return array();
    }
    $token = sanitize_text_field( wp_unslash( $_GET['zaito_error'] ) );
    $errors = get_transient( 'zaito_form_errors_' . $token );
    delete_transient( 'zaito_form_errors_' . $token );
    return $errors ? $errors : array();
}

/**
 * ワーカー登録処理（admin-post.php経由。ページ自己送信でのルーティング
 * トラブルを避けるため、WordPress標準のフォーム処理エンドポイントを使う）
 */
function zaito_handle_register_worker() {
    $errors = array();
    $email             = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
    $name              = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
    $password          = isset( $_POST['password'] ) ? $_POST['password'] : '';
    $password_confirm  = isset( $_POST['password_confirm'] ) ? $_POST['password_confirm'] : '';

    if ( ! $email ) {
        $errors[] = 'メールアドレスを入力してください';
    } elseif ( email_exists( $email ) ) {
        $errors[] = 'このメールアドレスは既に登録されています';
    }
    if ( ! $name ) {
        $errors[] = '氏名を入力してください';
    }
    if ( ! $password || strlen( $password ) < 8 ) {
        $errors[] = 'パスワードは8文字以上で入力してください';
    }
    if ( $password !== $password_confirm ) {
        $errors[] = 'パスワードが一致しません';
    }

    if ( empty( $errors ) ) {
        $user_id = wp_insert_user( array(
            'user_email' => $email,
            'user_login' => sanitize_user( $email ),
            'user_pass'  => $password,
            'first_name' => $name,
            'role'       => 'zaito_seeker',
        ) );

        if ( is_wp_error( $user_id ) ) {
            $errors[] = $user_id->get_error_message();
        } else {
            zaito_log_user_in_silently( $user_id );
            wp_safe_redirect( home_url( '/mypage/' ) );
            exit;
        }
    }

    zaito_store_form_errors_and_redirect( $errors, home_url( '/register/' ) );
}
add_action( 'admin_post_nopriv_zaito_register_worker', 'zaito_handle_register_worker' );
add_action( 'admin_post_zaito_register_worker', 'zaito_handle_register_worker' );

/**
 * 企業登録処理（admin-post.php経由）
 */
function zaito_handle_register_company() {
    $errors = array();
    $email            = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
    $company_name     = isset( $_POST['company_name'] ) ? sanitize_text_field( $_POST['company_name'] ) : '';
    $contact_person   = isset( $_POST['contact_person'] ) ? sanitize_text_field( $_POST['contact_person'] ) : '';
    $phone            = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
    $password         = isset( $_POST['password'] ) ? $_POST['password'] : '';
    $password_confirm = isset( $_POST['password_confirm'] ) ? $_POST['password_confirm'] : '';

    if ( ! $email ) {
        $errors[] = 'メールアドレスを入力してください';
    } elseif ( email_exists( $email ) ) {
        $errors[] = 'このメールアドレスは既に登録されています';
    }
    if ( ! $company_name ) {
        $errors[] = '企業名を入力してください';
    }
    if ( ! $contact_person ) {
        $errors[] = 'ご担当者名を入力してください';
    }
    if ( ! $phone ) {
        $errors[] = '電話番号を入力してください';
    }
    if ( ! $password || strlen( $password ) < 8 ) {
        $errors[] = 'パスワードは8文字以上で入力してください';
    }
    if ( $password !== $password_confirm ) {
        $errors[] = 'パスワードが一致しません';
    }

    if ( empty( $errors ) ) {
        $user_id = wp_insert_user( array(
            'user_email' => $email,
            'user_login' => sanitize_user( $email ),
            'user_pass'  => $password,
            'first_name' => $contact_person,
            'role'       => 'zaito_company',
        ) );

        if ( is_wp_error( $user_id ) ) {
            $errors[] = $user_id->get_error_message();
        } else {
            update_user_meta( $user_id, 'company_name', $company_name );
            update_user_meta( $user_id, 'company_phone', $phone );

            zaito_log_user_in_silently( $user_id );
            wp_safe_redirect( home_url( '/company/' ) );
            exit;
        }
    }

    zaito_store_form_errors_and_redirect( $errors, home_url( '/company-register/' ) );
}
add_action( 'admin_post_nopriv_zaito_register_company', 'zaito_handle_register_company' );
add_action( 'admin_post_zaito_register_company', 'zaito_handle_register_company' );

/**
 * 求人応募処理（admin-post.php経由）
 */
function zaito_handle_apply() {
    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( home_url( '/login/' ) );
        exit;
    }
    $current_user = wp_get_current_user();
    if ( ! in_array( 'zaito_seeker', $current_user->roles, true ) ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    $job_id = isset( $_POST['job_id'] ) ? intval( $_POST['job_id'] ) : 0;
    $apply_url = add_query_arg( 'job_id', $job_id, home_url( '/apply/' ) );

    $job = get_post( $job_id );
    if ( ! $job || $job->post_type !== 'job_listing' ) {
        wp_safe_redirect( home_url( '/jobs/' ) );
        exit;
    }

    $message = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';
    if ( ! $message ) {
        zaito_store_form_errors_and_redirect( array( 'メッセージを入力してください' ), $apply_url );
    }

    $application_id = wp_insert_post( array(
        'post_type'   => 'zaito_application',
        'post_title'  => 'Application from ' . $current_user->user_email,
        'post_status' => 'publish',
    ) );

    if ( ! $application_id ) {
        zaito_store_form_errors_and_redirect( array( '応募の送信に失敗しました' ), $apply_url );
    }

    update_post_meta( $application_id, 'applicant_id', $current_user->ID );
    update_post_meta( $application_id, 'job_id', $job_id );
    update_post_meta( $application_id, 'message', $message );
    update_post_meta( $application_id, 'status', 'pending' );

    wp_safe_redirect( add_query_arg( 'applied', '1', $apply_url ) );
    exit;
}
add_action( 'admin_post_zaito_apply', 'zaito_handle_apply' );

/**
 * 企業による求人投稿処理（admin-post.php経由）
 */
function zaito_handle_post_job() {
    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( home_url( '/company-login/' ) );
        exit;
    }
    $current_user = wp_get_current_user();
    if ( ! in_array( 'zaito_company', $current_user->roles, true ) ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    $errors = array();
    $title   = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
    $content = isset( $_POST['content'] ) ? sanitize_textarea_field( $_POST['content'] ) : '';
    $salary  = isset( $_POST['salary'] ) ? sanitize_text_field( $_POST['salary'] ) : '';
    $type    = isset( $_POST['job_type'] ) ? sanitize_text_field( $_POST['job_type'] ) : '';
    $days    = isset( $_POST['job_days'] ) ? sanitize_text_field( $_POST['job_days'] ) : '';
    $target  = isset( $_POST['job_target'] ) ? sanitize_text_field( $_POST['job_target'] ) : '';

    if ( ! $title ) {
        $errors[] = '求人タイトルを入力してください';
    }
    if ( ! $content ) {
        $errors[] = '仕事内容を入力してください';
    }

    if ( ! empty( $errors ) ) {
        zaito_store_form_errors_and_redirect( $errors, home_url( '/company-jobs/' ) );
    }

    $job_id = wp_insert_post( array(
        'post_type'    => 'job_listing',
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => 'publish',
    ) );

    if ( ! $job_id || is_wp_error( $job_id ) ) {
        zaito_store_form_errors_and_redirect( array( '求人の投稿に失敗しました' ), home_url( '/company-jobs/' ) );
    }

    update_post_meta( $job_id, '_company_user_id', $current_user->ID );
    update_post_meta( $job_id, '_company_name', get_user_meta( $current_user->ID, 'company_name', true ) );
    update_post_meta( $job_id, '_job_salary', $salary );
    update_post_meta( $job_id, '_job_type', $type );
    update_post_meta( $job_id, '_job_days', $days );
    update_post_meta( $job_id, '_job_target', $target );

    wp_safe_redirect( add_query_arg( 'posted', '1', home_url( '/company-jobs/' ) ) );
    exit;
}
add_action( 'admin_post_zaito_post_job', 'zaito_handle_post_job' );

/**
 * 企業による応募審査（採用・不採用）処理（admin-post.php経由）。
 * 採用にした場合はapplicationにcompany_idを記録し、チャットを開通させる。
 */
function zaito_handle_update_application_status() {
    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( home_url( '/company-login/' ) );
        exit;
    }
    $current_user = wp_get_current_user();
    if ( ! in_array( 'zaito_company', $current_user->roles, true ) ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    $application_id = isset( $_POST['application_id'] ) ? intval( $_POST['application_id'] ) : 0;
    $new_status     = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';

    $application = get_post( $application_id );
    if ( ! $application || $application->post_type !== 'zaito_application' || ! in_array( $new_status, array( 'accepted', 'rejected' ), true ) ) {
        wp_safe_redirect( home_url( '/company-applicants/' ) );
        exit;
    }

    $job_id = get_post_meta( $application_id, 'job_id', true );
    $job_owner_id = get_post_meta( $job_id, '_company_user_id', true );

    if ( intval( $job_owner_id ) !== $current_user->ID ) {
        wp_safe_redirect( home_url( '/company-applicants/' ) );
        exit;
    }

    update_post_meta( $application_id, 'status', $new_status );
    if ( 'accepted' === $new_status ) {
        update_post_meta( $application_id, 'company_id', $current_user->ID );
    }

    wp_safe_redirect( home_url( '/company-applicants/' ) );
    exit;
}
add_action( 'admin_post_zaito_update_application_status', 'zaito_handle_update_application_status' );

/**
 * チャットメッセージを読み込むAJAXハンドラー
 */
function zaito_load_messages() {
    check_ajax_referer( 'zaito_chat_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error();
        return;
    }

    $conversation_id = intval( $_POST['conversation_id'] );
    $application = get_post( $conversation_id );

    if ( ! $application || $application->post_type !== 'zaito_application' ) {
        wp_send_json_error();
        return;
    }

    $current_user = wp_get_current_user();
    $applicant_id = get_post_meta( $conversation_id, 'applicant_id', true );
    $company_id = get_post_meta( $conversation_id, 'company_id', true );

    if ( $current_user->ID !== $applicant_id && $current_user->ID !== $company_id ) {
        wp_send_json_error();
        return;
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
        return;
    }

    $current_user = wp_get_current_user();
    $conversation_id = intval( $_POST['conversation_id'] );
    $message_text = sanitize_textarea_field( $_POST['message'] );

    if ( ! $message_text ) {
        wp_send_json_error( array( 'message' => 'メッセージが空です' ) );
        return;
    }

    $application = get_post( $conversation_id );
    if ( ! $application || $application->post_type !== 'zaito_application' ) {
        wp_send_json_error();
        return;
    }

    $applicant_id = get_post_meta( $conversation_id, 'applicant_id', true );
    $company_id = get_post_meta( $conversation_id, 'company_id', true );

    if ( $current_user->ID !== $applicant_id && $current_user->ID !== $company_id ) {
        wp_send_json_error();
        return;
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
