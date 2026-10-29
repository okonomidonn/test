<?php
/**
 * 直接掲載（企業がzaitoに求人を載せ、学生がzaitoの中で応募・やりとりする）。
 *
 * - 企業: WordPressのユーザー（ロール zaito_company）。登録すると「審査中」になり、運営が承認すると求人を公開できる。
 *   user meta: company_name, company_phone, company_url, first_name（担当者名）, zaito_company_status（pending / approved / suspended）
 * - 求人: job_listing の投稿（_zaito_direct=1）。企業が出すと「審査待ち」になり、運営が承認すると公開（post_status=publish）。
 *   公開中の求人は zaito_remote_jobs() に混ぜ、まとめ求人と同じ一覧・詳細（/remote/d{ID}/）に出す。
 * - 学生: まとめ求人と同じメールアドレスの登録（zaito_interest）。パスワードはなく、本人確認はマイページのCookie（zaito_my）。
 * - 応募: zaito_application の投稿。応募ごとにメッセージ（zaito_message）でやりとりする。
 * - 成果報酬: 企業が応募の状態を「内定」にした時点で発生する。金額は運営の管理画面で決める（決まっていなくても記録は残る）。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ---------- 選択肢 ---------- */

function zaito_direct_categories() {
    return array( '事務・アシスタント', 'カスタマーサポート', 'マーケティング', 'SNS運用', 'ライティング', 'デザイン', '動画制作', 'エンジニア', '教育・学習サポート', '経営企画・事業開発', 'その他' );
}

function zaito_direct_contracts() {
    return array( 'アルバイト', '長期インターン', '業務委託', '契約社員', '正社員' );
}

function zaito_direct_pay_types() {
    return array( '時給', '日給', '月給', '1件あたり' );
}

/** 求人の特徴（一覧の絞り込みに使う remote-conditions.php のタグと同じ名前）。 */
function zaito_direct_tag_options() {
    return array(
        'gakusei'  => '学生OK',
        'mikeiken' => '未経験OK',
        'tanjikan' => '週1〜2日・短時間OK',
    );
}

function zaito_direct_app_statuses() {
    return array(
        'new'       => '新着',
        'talking'   => 'やりとり中',
        'interview' => '面接',
        'offer'     => '内定',
        'rejected'  => '不採用',
        'declined'  => '辞退',
    );
}

function zaito_direct_fee_statuses() {
    return array(
        'unbilled' => '未請求',
        'billed'   => '請求済み',
        'paid'     => '入金済み',
        'void'     => '対象外',
    );
}

/** 運営への知らせを送るアドレス。 */
function zaito_direct_ops_email() {
    $host = preg_replace( '/^www\./', '', (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
    return (string) get_option( 'zaito_direct_ops_email', 'info@' . $host );
}

/* ---------- 企業 ---------- */

function zaito_is_company( $user = null ) {
    $user = $user ? $user : wp_get_current_user();
    return $user && $user->ID && in_array( 'zaito_company', (array) $user->roles, true );
}

/** pending（審査中）/ approved（承認済み）/ suspended（停止）。記録がない古いアカウントは審査中として扱う。 */
function zaito_company_status( $user_id ) {
    $s = (string) get_user_meta( $user_id, 'zaito_company_status', true );
    return in_array( $s, array( 'pending', 'approved', 'suspended' ), true ) ? $s : 'pending';
}

function zaito_company_name( $user_id ) {
    $n = (string) get_user_meta( $user_id, 'company_name', true );
    return '' !== $n ? $n : '（企業名未設定）';
}

/**
 * 企業向けページの入口チェック。ログインしていない・企業でなければ、ログイン画面かトップへ移す。
 */
function zaito_require_company() {
    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( add_query_arg( 'redirect_to', rawurlencode( home_url( add_query_arg( array() ) ) ), home_url( '/company-login/' ) ) );
        exit;
    }
    $user = wp_get_current_user();
    if ( ! zaito_is_company( $user ) ) {
        wp_safe_redirect( current_user_can( 'manage_options' ) ? admin_url( 'admin.php?page=zaito-direct' ) : home_url( '/' ) );
        exit;
    }
    nocache_headers();
    return $user;
}

/* ---------- 求人 ---------- */

function zaito_direct_slug( $post_id ) {
    return 'd' . (int) $post_id;
}

function zaito_direct_id_from_slug( $slug ) {
    return preg_match( '/^d(\d+)$/', (string) $slug, $m ) ? (int) $m[1] : 0;
}

/** 報酬の表示（「時給1,300〜1,800円」）。まとめ求人と同じ書き方にして、時給の絞り込みにもかかるようにする。 */
function zaito_direct_pay_text( $post_id ) {
    $type = (string) get_post_meta( $post_id, '_job_salary_type', true );
    $min  = (int) get_post_meta( $post_id, '_job_salary', true );
    $max  = (int) get_post_meta( $post_id, '_job_salary_max', true );
    if ( ! $type || ! $min ) {
        return '';
    }
    $range = number_format( $min ) . ( $max > $min ? '〜' . number_format( $max ) : '' );
    if ( '1件あたり' === $type ) {
        return '1件' . $range . '円';
    }
    return $type . $range . '円' . ( $max > $min ? '' : '〜' );
}

/** 審査の状態。pending（審査待ち）/ approved（承認済み）/ returned（差し戻し）/ draft（下書き）。 */
function zaito_direct_review( $post_id ) {
    $r = (string) get_post_meta( $post_id, '_zaito_review', true );
    return in_array( $r, array( 'pending', 'approved', 'returned', 'draft' ), true ) ? $r : 'draft';
}

/** 企業の画面に出す求人の状態。 */
function zaito_direct_job_state( $post ) {
    if ( get_post_meta( $post->ID, '_zaito_closed', true ) ) {
        return array( 'closed', '掲載終了' );
    }
    if ( 'publish' === $post->post_status ) {
        return array( 'open', '掲載中' );
    }
    $map = array(
        'pending'  => array( 'pending', '審査待ち' ),
        'returned' => array( 'returned', '差し戻し' ),
        'approved' => array( 'pending', '公開準備中' ),
        'draft'    => array( 'draft', '下書き' ),
    );
    return $map[ zaito_direct_review( $post->ID ) ];
}

/**
 * 公開中の直接掲載の求人を、まとめ求人（zaito_remote_jobs()）と同じ形に直す。
 * 企業が停止中のものは出さない。1回の表示で何度も呼ばれるので、結果を覚えておく。
 */
function zaito_direct_jobs() {
    static $cache = null;
    if ( null !== $cache ) {
        return $cache;
    }
    $cache = array();
    if ( ! did_action( 'init' ) ) {
        return array();
    }
    $posts = get_posts( array(
        'post_type'        => 'job_listing',
        'post_status'      => 'publish',
        'posts_per_page'   => 300,
        'orderby'          => 'date',
        'order'            => 'DESC',
        'suppress_filters' => true,
        'meta_query'       => array( array( 'key' => '_zaito_direct', 'value' => '1' ) ),
    ) );
    foreach ( $posts as $p ) {
        $owner = (int) get_post_meta( $p->ID, '_company_user_id', true );
        if ( ! $owner || 'approved' !== zaito_company_status( $owner ) || get_post_meta( $p->ID, '_zaito_closed', true ) ) {
            continue;
        }
        $cache[] = zaito_direct_job_array( $p );
    }
    return $cache;
}

function zaito_direct_job_array( $p ) {
    $owner    = (int) get_post_meta( $p->ID, '_company_user_id', true );
    $approved = (string) get_post_meta( $p->ID, '_zaito_approved_at', true );
    $tags     = get_post_meta( $p->ID, '_job_tags', true );
    return array(
        'slug'     => zaito_direct_slug( $p->ID ),
        'company'  => zaito_company_name( $owner ),
        'title'    => $p->post_title,
        'category' => (string) get_post_meta( $p->ID, '_job_category', true ),
        'summary'  => (string) get_post_meta( $p->ID, '_job_summary', true ),
        'remote'   => '完全在宅（面接・研修・会議を含めて出社なし）',
        'pay'      => zaito_direct_pay_text( $p->ID ),
        'hours'    => (string) get_post_meta( $p->ID, '_job_hours', true ),
        'target'   => (string) get_post_meta( $p->ID, '_job_target_text', true ),
        'contract' => (string) get_post_meta( $p->ID, '_job_employment_type', true ),
        'url'      => home_url( '/remote/' . zaito_direct_slug( $p->ID ) . '/apply/' ),
        'listed'   => get_the_date( 'Y-m-d', $p ),
        'checked'  => $approved ? substr( $approved, 0, 10 ) : get_the_date( 'Y-m-d', $p ),
        'direct'   => true,
        'post_id'  => $p->ID,
        'owner'    => $owner,
        'content'  => $p->post_content,
        'question' => (string) get_post_meta( $p->ID, '_job_screening_question', true ),
        'tags'     => is_array( $tags ) ? array_values( $tags ) : array(),
    );
}

/** 直接掲載の求人を一覧の先頭に足す（inc/remote-jobs.php の zaito_remote_jobs フィルター）。 */
function zaito_direct_merge_jobs( $jobs ) {
    $direct = zaito_direct_jobs();
    return $direct ? array_merge( $direct, $jobs ) : $jobs;
}
add_filter( 'zaito_remote_jobs', 'zaito_direct_merge_jobs' );

/** 直接掲載の求人の古いURL（/job/{name}/）は、詳細ページ（/remote/d{ID}/）へ移す。 */
function zaito_direct_redirect_old_single() {
    if ( ! is_singular( 'job_listing' ) ) {
        return;
    }
    $id = get_queried_object_id();
    if ( $id && get_post_meta( $id, '_zaito_direct', true ) ) {
        wp_safe_redirect( home_url( '/remote/' . zaito_direct_slug( $id ) . '/' ), 301 );
        exit;
    }
}
add_action( 'template_redirect', 'zaito_direct_redirect_old_single', 5 );

/* ---------- 応募 ---------- */

function zaito_direct_find_application( $interest_id, $job_id ) {
    $ids = get_posts( array(
        'post_type'      => 'zaito_application',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => array(
            array( 'key' => 'interest_id', 'value' => (int) $interest_id ),
            array( 'key' => 'job_id', 'value' => (int) $job_id ),
        ),
    ) );
    return $ids ? (int) $ids[0] : 0;
}

/** 応募の一覧。$by は 'company'（企業のユーザーID）か 'interest'（学生の登録ID）か 'all'。 */
function zaito_direct_applications( $by, $id = 0, $status = '' ) {
    $mq = array( array( 'key' => 'interest_id', 'compare' => 'EXISTS' ) );
    if ( 'company' === $by ) {
        $mq[] = array( 'key' => 'company_id', 'value' => (int) $id );
    } elseif ( 'interest' === $by ) {
        $mq[] = array( 'key' => 'interest_id', 'value' => (int) $id );
    }
    if ( $status ) {
        $mq[] = array( 'key' => 'status', 'value' => $status );
    }
    return get_posts( array(
        'post_type'      => 'zaito_application',
        'post_status'    => 'any',
        'posts_per_page' => 500,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => $mq,
    ) );
}

function zaito_direct_app_status( $app_id ) {
    $s = (string) get_post_meta( $app_id, 'status', true );
    return array_key_exists( $s, zaito_direct_app_statuses() ) ? $s : 'new';
}

/** 応募者の表示名と、立場・学年・学校のひとこと。 */
function zaito_direct_applicant_line( $app_id ) {
    $parts = array_filter( array(
        get_post_meta( $app_id, 'applicant_school', true ),
        get_post_meta( $app_id, 'applicant_role', true ),
        get_post_meta( $app_id, 'applicant_grade', true ),
    ) );
    return implode( '・', $parts );
}

/**
 * 応募を受け付ける（lp/direct-apply.php のフォーム）。本人確認済み（zaito_my）の登録だけが応募できる。
 */
function zaito_handle_direct_apply() {
    $job_id = isset( $_POST['job_id'] ) ? (int) $_POST['job_id'] : 0;
    $back   = home_url( '/remote/' . zaito_direct_slug( $job_id ) . '/apply/' );
    if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'zaito_direct_apply_' . $job_id ) ) {
        zaito_store_form_errors_and_redirect( array( '時間がたったため送信できませんでした。もう一度送ってください。' ), $back );
    }
    $me = zaito_my_id();
    if ( ! $me || ! zaito_interest_verified( $me ) ) {
        wp_safe_redirect( $back );
        exit;
    }
    $job = zaito_remote_job( zaito_direct_slug( $job_id ) );
    if ( ! $job || empty( $job['direct'] ) ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }
    $existing = zaito_direct_find_application( $me, $job_id );
    if ( $existing ) {
        wp_safe_redirect( zaito_direct_chat_url( $existing ) );
        exit;
    }
    if ( zaito_lp_rate_limited( 'direct_apply', 10 ) ) {
        zaito_store_form_errors_and_redirect( array( '送信回数が多すぎます。しばらくしてからお試しください。' ), $back );
    }

    $opts    = zaito_remote_profile_options();
    $name    = isset( $_POST['name'] ) ? mb_substr( sanitize_text_field( wp_unslash( $_POST['name'] ) ), 0, 40 ) : '';
    $role    = isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : '';
    $grade   = isset( $_POST['grade'] ) ? sanitize_text_field( wp_unslash( $_POST['grade'] ) ) : '';
    $school  = isset( $_POST['school'] ) ? mb_substr( sanitize_text_field( wp_unslash( $_POST['school'] ) ), 0, 80 ) : '';
    $message = isset( $_POST['message'] ) ? mb_substr( sanitize_textarea_field( wp_unslash( $_POST['message'] ) ), 0, 2000 ) : '';
    $answer  = isset( $_POST['answer'] ) ? mb_substr( sanitize_textarea_field( wp_unslash( $_POST['answer'] ) ), 0, 1000 ) : '';

    $errors = array();
    if ( '' === $name ) {
        $errors[] = 'お名前を入力してください';
    }
    if ( ! in_array( $role, $opts['role'], true ) ) {
        $errors[] = '現在の職業を選んでください';
    }
    if ( ! in_array( $grade, $opts['grade'], true ) ) {
        $grade = '';
    }
    if ( '' === trim( $message ) ) {
        $errors[] = '自己紹介・応募の理由を入力してください';
    }
    if ( $job['question'] && '' === trim( $answer ) ) {
        $errors[] = '企業からの質問に答えてください';
    }
    if ( $errors ) {
        set_transient( 'zaito_apply_draft_' . $me, compact( 'name', 'role', 'grade', 'school', 'message', 'answer' ), 10 * MINUTE_IN_SECONDS );
        zaito_store_form_errors_and_redirect( $errors, $back );
    }

    // 次の応募で入力を省けるよう、登録にも残す。
    update_post_meta( $me, 'name', $name );
    update_post_meta( $me, 'role', $role );
    if ( $grade ) {
        update_post_meta( $me, 'grade', $grade );
    }
    if ( $school ) {
        update_post_meta( $me, 'school', $school );
    }

    $app_id = wp_insert_post( array(
        'post_type'   => 'zaito_application',
        'post_title'  => $name . ' → ' . $job['company'] . '「' . $job['title'] . '」',
        'post_status' => 'publish',
    ) );
    if ( ! $app_id || is_wp_error( $app_id ) ) {
        zaito_store_form_errors_and_redirect( array( '応募を送れませんでした。時間をおいてもう一度お試しください。' ), $back );
    }
    $now = current_time( 'mysql' );
    foreach ( array(
        'interest_id'        => $me,
        'job_id'             => $job_id,
        'company_id'         => (int) $job['owner'],
        'status'             => 'new',
        'status_at'          => $now,
        'applicant_name'     => $name,
        'applicant_role'     => $role,
        'applicant_grade'    => $grade,
        'applicant_school'   => $school,
        'message'            => $message,
        'screening_question' => $job['question'],
        'screening_answer'   => $answer,
    ) as $k => $v ) {
        update_post_meta( $app_id, $k, $v );
    }
    delete_transient( 'zaito_apply_draft_' . $me );

    // 応募の内容を、やりとりの最初のメッセージとして残す。
    $first = $message . ( $job['question'] ? "\n\n【" . $job['question'] . "】\n" . $answer : '' );
    zaito_direct_add_message( $app_id, 'student', $me, $first, false );

    // 企業が決めた「応募直後の自動メッセージ」。
    $auto = trim( (string) get_post_meta( $job_id, '_job_auto_reply_message', true ) );
    if ( '' === $auto ) {
        $auto = trim( (string) get_user_meta( $job['owner'], 'auto_reply_message', true ) );
    }
    if ( '' !== $auto ) {
        zaito_direct_add_message( $app_id, 'company', (int) $job['owner'], $auto, false );
    }

    zaito_direct_notify_company_new_application( $app_id );
    do_action( 'zaito_direct_applied', $app_id );

    wp_safe_redirect( add_query_arg( 'applied', '1', zaito_direct_chat_url( $app_id ) ) );
    exit;
}
add_action( 'admin_post_zaito_direct_apply', 'zaito_handle_direct_apply' );
add_action( 'admin_post_nopriv_zaito_direct_apply', 'zaito_handle_direct_apply' );

/* ---------- メッセージ ---------- */

function zaito_direct_chat_url( $app_id ) {
    return add_query_arg( 'c', (int) $app_id, home_url( '/chat/' ) );
}

/**
 * 今の閲覧者が、この応募のやりとりで誰にあたるか。'company' / 'student' / 'admin'（運営は読むだけ）/ ''。
 */
function zaito_direct_viewer_role( $app_id ) {
    if ( 'zaito_application' !== get_post_type( $app_id ) || ! get_post_meta( $app_id, 'interest_id', true ) ) {
        return '';
    }
    if ( is_user_logged_in() ) {
        $uid = get_current_user_id();
        if ( $uid === (int) get_post_meta( $app_id, 'company_id', true ) ) {
            return 'company';
        }
        if ( current_user_can( 'manage_options' ) ) {
            return 'admin';
        }
    }
    $me = function_exists( 'zaito_my_id' ) ? zaito_my_id() : 0;
    if ( $me && $me === (int) get_post_meta( $app_id, 'interest_id', true ) ) {
        return 'student';
    }
    return '';
}

function zaito_direct_add_message( $app_id, $role, $sender, $text, $notify = true ) {
    $msg_id = wp_insert_post( array(
        'post_type'    => 'zaito_message',
        'post_content' => $text,
        'post_status'  => 'publish',
        'post_title'   => 'Message ' . current_time( 'timestamp' ),
    ) );
    if ( ! $msg_id || is_wp_error( $msg_id ) ) {
        return 0;
    }
    update_post_meta( $msg_id, 'conversation_id', (int) $app_id );
    update_post_meta( $msg_id, 'sender_role', $role );
    update_post_meta( $msg_id, 'sender_id', (int) $sender );
    update_post_meta( $app_id, 'last_message_text', $text );
    update_post_meta( $app_id, 'last_message_at', current_time( 'mysql' ) );
    update_post_meta( $app_id, 'last_sender', $role );
    // 相手側の「未読」の印。開いたら消す。
    update_post_meta( $app_id, 'company' === $role ? 'unread_student' : 'unread_company', 1 );
    // 企業が最初に返事をした日（返信までの時間を運営が見るため）。
    if ( 'company' === $role && $notify && ! get_post_meta( $app_id, 'first_reply_at', true ) ) {
        update_post_meta( $app_id, 'first_reply_at', current_time( 'mysql' ) );
        if ( 'new' === zaito_direct_app_status( $app_id ) ) {
            update_post_meta( $app_id, 'status', 'talking' );
            update_post_meta( $app_id, 'status_at', current_time( 'mysql' ) );
        }
    }
    if ( $notify ) {
        zaito_direct_notify_message( $app_id, $role, $text );
    }
    return $msg_id;
}

function zaito_direct_messages( $app_id ) {
    return get_posts( array(
        'post_type'      => 'zaito_message',
        'posts_per_page' => 500,
        'orderby'        => 'ID',
        'order'          => 'ASC',
        'meta_query'     => array( array( 'key' => 'conversation_id', 'value' => (int) $app_id ) ),
    ) );
}

function zaito_direct_messages_payload( $app_id, $viewer ) {
    $out = array();
    foreach ( zaito_direct_messages( $app_id ) as $m ) {
        $role = (string) get_post_meta( $m->ID, 'sender_role', true );
        $out[] = array(
            'id'   => $m->ID,
            'from' => $role,
            'own'  => $role === $viewer,
            'text' => $m->post_content,
            'time' => get_the_date( 'n/j H:i', $m ),
        );
    }
    return $out;
}

/** やりとりの読み込み（/chat/ のJSが数秒ごとに呼ぶ）。 */
function zaito_handle_direct_messages() {
    $app_id = isset( $_POST['c'] ) ? (int) $_POST['c'] : 0;
    $viewer = zaito_direct_viewer_role( $app_id );
    if ( ! $viewer ) {
        wp_send_json_error( array( 'message' => 'このやりとりを見る権限がありません' ), 403 );
    }
    if ( 'company' === $viewer || 'student' === $viewer ) {
        delete_post_meta( $app_id, 'unread_' . $viewer );
    }
    wp_send_json_success( array(
        'messages' => zaito_direct_messages_payload( $app_id, $viewer ),
        'status'     => zaito_direct_app_statuses()[ zaito_direct_app_status( $app_id ) ],
        'status_key' => zaito_direct_app_status( $app_id ),
    ) );
}
add_action( 'wp_ajax_zaito_direct_messages', 'zaito_handle_direct_messages' );
add_action( 'wp_ajax_nopriv_zaito_direct_messages', 'zaito_handle_direct_messages' );

function zaito_handle_direct_send() {
    $app_id = isset( $_POST['c'] ) ? (int) $_POST['c'] : 0;
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'zaito_chat_' . $app_id ) ) {
        wp_send_json_error( array( 'message' => 'ページを読み込み直してから送ってください' ), 400 );
    }
    $viewer = zaito_direct_viewer_role( $app_id );
    if ( 'company' !== $viewer && 'student' !== $viewer ) {
        wp_send_json_error( array( 'message' => 'このやりとりに送る権限がありません' ), 403 );
    }
    if ( 'company' === $viewer && 'approved' !== zaito_company_status( get_current_user_id() ) ) {
        wp_send_json_error( array( 'message' => '企業アカウントが停止中のため送れません' ), 403 );
    }
    $text = isset( $_POST['text'] ) ? trim( sanitize_textarea_field( wp_unslash( $_POST['text'] ) ) ) : '';
    if ( '' === $text ) {
        wp_send_json_error( array( 'message' => 'メッセージを入力してください' ), 400 );
    }
    if ( zaito_lp_rate_limited( 'direct_send', 30 ) ) {
        wp_send_json_error( array( 'message' => '送信回数が多すぎます。しばらくしてからお試しください' ), 400 );
    }
    $sender = 'company' === $viewer ? get_current_user_id() : (int) get_post_meta( $app_id, 'interest_id', true );
    zaito_direct_add_message( $app_id, $viewer, $sender, mb_substr( $text, 0, 4000 ) );
    delete_post_meta( $app_id, 'unread_' . $viewer );
    wp_send_json_success( array( 'messages' => zaito_direct_messages_payload( $app_id, $viewer ) ) );
}
add_action( 'wp_ajax_zaito_direct_send', 'zaito_handle_direct_send' );
add_action( 'wp_ajax_nopriv_zaito_direct_send', 'zaito_handle_direct_send' );

/* ---------- 応募の状態（企業が動かす） ---------- */

function zaito_handle_direct_set_status() {
    $user   = zaito_require_company();
    $app_id = isset( $_POST['application_id'] ) ? (int) $_POST['application_id'] : 0;
    check_admin_referer( 'zaito_direct_status_' . $app_id );
    $status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : '';
    $back   = isset( $_POST['back'] ) ? esc_url_raw( wp_unslash( $_POST['back'] ) ) : home_url( '/company-applicants/' );
    if ( 0 !== strpos( $back, home_url() ) ) {
        $back = home_url( '/company-applicants/' );
    }
    if ( (int) get_post_meta( $app_id, 'company_id', true ) !== $user->ID || ! array_key_exists( $status, zaito_direct_app_statuses() ) ) {
        wp_safe_redirect( $back );
        exit;
    }
    $old = zaito_direct_app_status( $app_id );
    if ( $old !== $status ) {
        update_post_meta( $app_id, 'status', $status );
        update_post_meta( $app_id, 'status_at', current_time( 'mysql' ) );
        // 内定: 成果報酬の記録を作る（一度作ったら、状態を戻しても運営が見られるよう残す）。
        if ( 'offer' === $status && ! get_post_meta( $app_id, 'offer_at', true ) ) {
            update_post_meta( $app_id, 'offer_at', current_time( 'mysql' ) );
            update_post_meta( $app_id, 'fee_status', 'unbilled' );
            $default = (string) get_option( 'zaito_direct_fee_default', '' );
            if ( '' !== $default ) {
                update_post_meta( $app_id, 'fee_amount', $default );
            }
            zaito_direct_notify_ops_offer( $app_id );
        }
        zaito_direct_notify_status( $app_id, $status );
    }
    wp_safe_redirect( add_query_arg( 'saved', '1', $back ) );
    exit;
}
add_action( 'admin_post_zaito_direct_set_status', 'zaito_handle_direct_set_status' );

/* ---------- お知らせメール ---------- */

function zaito_direct_from() {
    $host = preg_replace( '/^www\./', '', (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
    return 'zaito <info@' . $host . '>';
}

/** 学生へのメール。ボタンは、そのやりとりを開くログインリンク（3日有効）。 */
function zaito_direct_mail_student( $app_id, $subject, $title, $lead, $button ) {
    $me    = (int) get_post_meta( $app_id, 'interest_id', true );
    $email = (string) get_post_meta( $me, 'email', true );
    if ( ! $email ) {
        return;
    }
    $url = add_query_arg( 'next', rawurlencode( '/chat/?c=' . (int) $app_id ), zaito_my_login_url( $me, 3 * DAY_IN_SECONDS ) );
    zaito_mail_button( $email, $subject, $title, $lead, $button, $url, 'ボタンは3日間有効です。期限が切れたら、zaitoのマイページからログインしてください。', 'このメールは、zaitoで応募した求人のやりとりについてお送りしています。', zaito_direct_from() );
}

function zaito_direct_mail_company( $app_id, $subject, $title, $lead, $button ) {
    $company = get_userdata( (int) get_post_meta( $app_id, 'company_id', true ) );
    if ( ! $company ) {
        return;
    }
    zaito_mail_button( $company->user_email, $subject, $title, $lead, $button, zaito_direct_chat_url( $app_id ), '', 'このメールは、zaitoに掲載中の求人への応募についてお送りしています。', zaito_direct_from() );
}

function zaito_direct_notify_company_new_application( $app_id ) {
    $job = get_post( (int) get_post_meta( $app_id, 'job_id', true ) );
    zaito_direct_mail_company(
        $app_id,
        '【zaito】新しい応募がありました',
        '新しい応募がありました',
        '「' . ( $job ? $job->post_title : '' ) . '」に、' . get_post_meta( $app_id, 'applicant_name', true ) . 'さんから応募がありました。応募の内容を確認して、zaitoのメッセージで返信してください。',
        '応募を確認する'
    );
    wp_mail( zaito_direct_ops_email(), '【zaito運営】応募がありました（' . zaito_company_name( (int) get_post_meta( $app_id, 'company_id', true ) ) . '）', admin_url( 'admin.php?page=zaito-direct&tab=apps' ) );
}

/** 新しいメッセージ。同じやりとりで続けて送られたときにメールが重ならないよう、相手ごとに10分に1通まで。 */
function zaito_direct_notify_message( $app_id, $from, $text ) {
    $to  = 'company' === $from ? 'student' : 'company';
    $key = 'zaito_dm_' . $to . '_' . (int) $app_id;
    if ( get_transient( $key ) ) {
        return;
    }
    set_transient( $key, 1, 10 * MINUTE_IN_SECONDS );
    $preview = mb_strimwidth( preg_replace( '/\s+/u', ' ', $text ), 0, 120, '…' );
    if ( 'student' === $to ) {
        $job = get_post( (int) get_post_meta( $app_id, 'job_id', true ) );
        zaito_direct_mail_student( $app_id, '【zaito】' . zaito_company_name( (int) get_post_meta( $app_id, 'company_id', true ) ) . 'からメッセージが届きました', 'メッセージが届きました', '「' . ( $job ? $job->post_title : '' ) . '」の企業から：' . "\n" . $preview, 'メッセージを見る' );
    } else {
        zaito_direct_mail_company( $app_id, '【zaito】' . get_post_meta( $app_id, 'applicant_name', true ) . 'さんからメッセージが届きました', 'メッセージが届きました', $preview, 'メッセージを見る' );
    }
}

function zaito_direct_notify_status( $app_id, $status ) {
    $job     = get_post( (int) get_post_meta( $app_id, 'job_id', true ) );
    $company = zaito_company_name( (int) get_post_meta( $app_id, 'company_id', true ) );
    $title   = $job ? $job->post_title : '';
    if ( 'offer' === $status ) {
        zaito_direct_mail_student( $app_id, '【zaito】' . $company . 'から内定の連絡がありました', '内定の連絡がありました', '「' . $title . '」で、' . $company . 'から内定の連絡がありました。これからの流れは、zaitoのメッセージで企業と確認してください。', 'メッセージを見る' );
    } elseif ( 'rejected' === $status ) {
        zaito_direct_mail_student( $app_id, '【zaito】応募の結果のお知らせ', '応募の結果のお知らせ', '「' . $title . '」は、今回は見送りとなりました。ほかにも出社なしの仕事を毎日載せています。', 'メッセージを見る' );
    }
}

function zaito_direct_notify_ops_offer( $app_id ) {
    $job = get_post( (int) get_post_meta( $app_id, 'job_id', true ) );
    wp_mail(
        zaito_direct_ops_email(),
        '【zaito運営】内定が出ました（成果報酬の対象）',
        zaito_company_name( (int) get_post_meta( $app_id, 'company_id', true ) ) . '「' . ( $job ? $job->post_title : '' ) . '」' . "\n"
        . '応募者: ' . get_post_meta( $app_id, 'applicant_name', true ) . "\n\n"
        . admin_url( 'admin.php?page=zaito-direct&tab=offers' )
    );
}

/* ---------- マイページ（学生の応募一覧） ---------- */

/** マイページ（inc/mypage.php の zaito_my_data）に渡す、応募した求人の一覧。 */
function zaito_direct_my_apps( $interest_id ) {
    $out    = array();
    $labels = zaito_direct_app_statuses();
    foreach ( zaito_direct_applications( 'interest', $interest_id ) as $a ) {
        $job = get_post( (int) get_post_meta( $a->ID, 'job_id', true ) );
        $out[] = array(
            'title'   => $job ? $job->post_title : '（削除された求人）',
            'company' => zaito_company_name( (int) get_post_meta( $a->ID, 'company_id', true ) ),
            'status'  => $labels[ zaito_direct_app_status( $a->ID ) ],
            'unread'  => (bool) get_post_meta( $a->ID, 'unread_student', true ),
            'date'    => get_the_date( 'n月j日', $a ),
            'url'     => zaito_direct_chat_url( $a->ID ),
        );
    }
    return $out;
}

/* ---------- 運営の管理画面 ---------- */

require_once __DIR__ . '/direct-admin.php';
require_once __DIR__ . '/direct-company.php';
