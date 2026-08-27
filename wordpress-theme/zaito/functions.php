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

    /**
     * job_listing は本来 WP Job Manager プラグインが登録している投稿タイプだが、
     * このテーマは求人の検索・表示・投稿を独自実装しており(front-page.php,
     * page-jobs.php, single-job_listing.php, zaito_handle_post_job() 等)、
     * WP Job Managerの機能は一切使っていない。それにもかかわらず投稿タイプの
     * 登録だけプラグインに依存していたため、プラグインを無効化すると求人一覧・
     * 詳細ページが丸ごと動かなくなる状態だった。ここでテーマ側が独立して
     * 登録することで、プラグインへの依存をなくす。
     * パーマリンク構造(/job/post-name/)は既存のURLと互換性を保つため
     * WP Job Managerのデフォルト('job')に合わせている。
     */
    register_post_type( 'job_listing', array(
        'label'        => '求人',
        'public'       => true,
        'show_ui'      => true,
        'show_in_menu' => true,
        'supports'     => array( 'title', 'editor', 'custom-fields' ),
        'has_archive'  => false,
        'rewrite'      => array( 'slug' => 'job', 'with_front' => false ),
        'capability_type' => 'post',
    ) );

    register_post_type( 'zaito_interest', array(
        'label'        => '興味あり登録',
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-heart',
        'supports'     => array( 'title', 'custom-fields' ),
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
        'for-companies'    => 'page-for-companies.php',
        'company-login'    => 'page-company-login.php',
        'company-register' => 'page-company-register.php',
        'mypage'           => 'page-mypage.php',
        'company'          => 'page-company.php',
        'company-jobs'     => 'page-company-jobs.php',
        'company-applicants' => 'page-company-applicants.php',
        'worker-profile'   => 'page-worker-profile.php',
        'forgot-password'  => 'page-forgot-password.php',
        'reset-password'   => 'page-reset-password.php',
        'verify-email'     => 'page-verify-email.php',
        'google-callback'  => 'page-google-callback.php',
        'terms'            => 'page-terms.php',
        'privacy'          => 'page-privacy.php',
        'jobs'             => 'page-jobs.php',
        'apply'            => 'page-apply.php',
        'chat'             => 'page-chat.php',
        'interest'         => 'page-interest.php',
        'company-profile'  => 'page-company-profile.php',
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
 * 著者アーカイブ(/author/xxx/)を無効化しトップページへリダイレクトする。
 * このサイトはブログ機能を使わず、ユーザーのdisplay_name(氏名の場合がある)が
 * 意図せず公開URLとして露出してしまう経路を塞ぐための恒久対応(2026-08-25追加)。
 */
function zaito_disable_author_archives() {
    if ( is_author() ) {
        wp_safe_redirect( home_url( '/' ), 301 );
        exit;
    }
}
add_action( 'template_redirect', 'zaito_disable_author_archives', 0 );

/**
 * WP標準サイトマップからユーザー(著者)一覧を除外する。
 * 上記のリダイレクトと合わせて、検索エンジンに著者URLをそもそも案内しないようにする。
 */
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
    return 'users' === $name ? false : $provider;
}, 10, 2 );

/**
 * WP REST APIのユーザー一覧エンドポイント(/wp-json/wp/v2/users)を未認証アクセスから塞ぐ。
 * デフォルトでは投稿を持つユーザーの氏名・プロフィールURL等がJSONで公開されてしまうため、
 * テーマ側でこのエンドポイントを使用していないことを確認のうえ無効化する(2026-08-25追加)。
 */
add_filter( 'rest_endpoints', function ( $endpoints ) {
    if ( is_user_logged_in() ) {
        return $endpoints;
    }
    unset( $endpoints['/wp/v2/users'] );
    unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
    return $endpoints;
} );

/**
 * 上記のリライトルールをデータベースに反映させるため、
 * テーマの更新時に一度だけ flush_rewrite_rules() を実行する。
 */
function zaito_maybe_flush_rewrite_rules() {
    $version = '10';
    if ( get_option( 'zaito_rewrite_version' ) !== $version ) {
        flush_rewrite_rules();
        update_option( 'zaito_rewrite_version', $version );
    }
}
add_action( 'init', 'zaito_maybe_flush_rewrite_rules', 20 );

/**
 * このテーマはWordPressの「固定ページ」機能に依存せず、front-page.php
 * を直接トップページとして使う設計になっている。「設定 > 表示設定」が
 * 何らかの理由で「固定ページ」表示に変更されていたり、指定されている
 * 固定ページが削除されていたりすると、トップページ（/）自体が404に
 * なってしまう。これを防ぐため、常に「最新の投稿を表示」設定
 * （show_on_front = posts）を強制する。
 */
function zaito_ensure_front_page_setting() {
    if ( get_option( 'show_on_front' ) !== 'posts' ) {
        update_option( 'show_on_front', 'posts' );
    }
    if ( get_option( 'page_on_front' ) ) {
        update_option( 'page_on_front', 0 );
    }
}
add_action( 'init', 'zaito_ensure_front_page_setting', 20 );

/**
 * WP Job Manager の求人一覧を取得するヘルパー。
 * プラグイン未導入時は空配列を返す。
 */
function zaito_get_featured_jobs( $limit = 3 ) {
    if ( ! post_type_exists( 'job_listing' ) ) {
        return array();
    }
    // 実企業の求人のみに絞り込む処理は zaito_hide_fake_jobs_from_public() が
    // 公開画面向けの全求人クエリに対して一括で適用する。get_posts() はデフォルトで
    // suppress_filters=true のため posts_where フィルタが効かない点に注意し、
    // 明示的に false を指定する。
    return get_posts( array(
        'post_type'       => 'job_listing',
        'posts_per_page'  => $limit,
        'post_status'     => 'publish',
        'orderby'         => 'date',
        'order'           => 'DESC',
        'suppress_filters' => false,
    ) );
}

/**
 * 掲載終了(post_status=draft)にした自社の求人を、企業が「詳細」プレビューリンク
 * (get_preview_post_link()、?p=ID&preview=true形式)経由で見られるようにする。
 * WordPressの標準プレビュー機構は投稿者本人でもデフォルトのpost_status絞り込み
 * (publishのみ)を素通りできないケースがあり、実際にdraft状態の自社求人が404に
 * なることを確認したため、該当求人の所有企業のみ明示的にpost_statusを広げる。
 */
add_action( 'pre_get_posts', function ( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    $post_id = $query->get( 'p' );
    if ( ! $post_id || 'job_listing' !== $query->get( 'post_type' ) ) {
        return;
    }
    if ( ! is_user_logged_in() ) {
        return;
    }
    $owner_id = (int) get_post_meta( $post_id, '_company_user_id', true );
    if ( $owner_id && $owner_id === get_current_user_id() ) {
        $query->set( 'post_status', array( 'publish', 'draft' ) );
    }
} );

/**
 * 公開画面(管理画面以外)の求人クエリから、実企業アカウントに紐づかない求人
 * (zaito_generate_demo_jobs()が自動生成した_zaito_demo=1の架空求人、
 * 動作確認・デモ用に作成した求人)を除外する。表示対象になるのは
 * 「_company_user_idを持つ」かつ「_zaito_demoではない」かつ
 * 「タイトル・会社名に動作確認/テスト/デモを含まない」求人のみ。
 * 投稿データ自体は削除せず、管理画面(投稿一覧等)では引き続き全件確認できる。
 * 実企業が求人を投稿し始めたら自動的に表示対象になる。
 */
add_filter( 'posts_where', 'zaito_hide_fake_jobs_from_public', 10, 2 );
function zaito_hide_fake_jobs_from_public( $where, $query ) {
    if ( is_admin() ) {
        return $where;
    }
    $post_type = $query->get( 'post_type' );
    $is_job_only_query = ( 'job_listing' === $post_type ) || ( is_array( $post_type ) && in_array( 'job_listing', $post_type, true ) );

    // WordPress標準検索(?s=)はpost_typeを明示指定しないことが多く、その場合job_listingも
    // 検索対象に含まれる(exclude_from_searchを設定していないため)。post_type未指定の検索クエリも
    // 対象に含めないと、非公開の仮ページ(_zaito_preview=1)の本文が検索結果に漏れてしまう
    // (2026-08-26に実際に発見: 「プラコレ」で検索すると仮ページの内容がヒットしていた)。
    $is_mixed_search = $query->is_search() && empty( $post_type );

    if ( ! $is_job_only_query && ! $is_mixed_search ) {
        return $where;
    }

    global $wpdb;

    // 仮ページ(_zaito_preview=1)は、その求人自身の詳細ページ(直接リンク)に限り
    // _company_user_idを持たなくても表示を許可する。一覧・PICK UP・関連求人・検索結果などの
    // 公開リスト系クエリには含めない(is_singular()でない場合は通常の除外条件を適用)。
    // is_singular('job_listing') のように引数付きで呼ぶと内部で get_queried_object() が
    // 実行され、posts_where の時点(クエリ実行前)ではまだ解決できず常にfalseになってしまう。
    // post_typeは上のチェックで既にjob_listingと確定しているため、引数なしのis_singular()でよい。
    if ( $query->is_singular() ) {
        $where .= " AND ( {$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_zaito_preview' AND meta_value = '1' )
          OR {$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_company_user_id' ) )";
        return $where;
    }

    // post_type未指定の混在検索クエリ(job_listing以外の投稿・固定ページ等も含む)では、
    // job_listing以外の投稿を巻き込んで除外しないよう、「job_listingでなければ無条件で許可」を
    // OR条件の先頭に置く。$is_job_only_queryの場合はpost_typeがjob_listingのみに確定しているため
    // 実質的に効果はないが、同じ文で両方のケースを安全に扱える。
    $where .= $wpdb->prepare(
        " AND ( {$wpdb->posts}.post_type != 'job_listing' OR (
              {$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_company_user_id' )
              AND {$wpdb->posts}.ID NOT IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_zaito_demo' AND meta_value = '1' )
              AND {$wpdb->posts}.post_title NOT LIKE %s
              AND {$wpdb->posts}.ID NOT IN (
                  SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_company_name' AND ( meta_value LIKE %s OR meta_value LIKE %s )
              )
          ) )",
        '%動作確認%',
        '%テスト%',
        '%デモ%'
    );
    return $where;
}

/**
 * 求人投稿フォームで使う選択肢。企業が自由記述すると表記がバラバラになる
 * (「完全在宅・シフト制」「リモート」等)ため、選択式に統一するための一覧。
 * フォーム側(page-company-jobs.php)と表示側(single-job_listing.php等)の
 * 両方から参照する。
 */
function zaito_job_categories() {
    return array(
        'ライティング',
        'デザイン',
        'プログラミング',
        '事務・データ入力',
        'カスタマーサポート',
        'SNS運用・マーケティング',
        '翻訳・通訳',
        '動画編集',
        '経理・事務代行',
        'テレアポ・営業事務',
        'その他',
    );
}
function zaito_employment_type_options() {
    return array( '業務委託', 'アルバイト・パート', '契約社員', '正社員' );
}
function zaito_salary_type_options() {
    return array( '時給', '日給', '月給', '固定報酬制' );
}
function zaito_work_style_options() {
    return array( '完全在宅・シフト制', '完全在宅・固定時間制', '完全在宅・フレックス制', '完全在宅・曜日応相談' );
}
function zaito_min_days_options() {
    return array( '週1日〜', '週2日〜', '週3日〜', '週4日〜', '週5日(フルタイム)', '応相談' );
}
function zaito_target_tag_options() {
    return array( '未経験者歓迎', '主婦・主夫歓迎', '学生歓迎', 'シニア世代歓迎', 'Wワーク・副業OK', 'ブランクOK', '経験者優遇' );
}

/**
 * 求人カテゴリごとにバッジの配色を変える。カテゴリが5→11種類に増えた際も
 * ハッシュ3色割り当てのままだったため、無関係なカテゴリ同士が同じ色になる問題が
 * あった。ブランドパレット系統の6色をカテゴリごとに固定で割り当てる方式に変更し、
 * どのカテゴリがどの色かユーザーが覚えられるようにする。
 */
function zaito_category_badge_class( $category ) {
    $map = array(
        'ライティング'         => 'badge-coral',
        'デザイン'             => 'badge-rose',
        'プログラミング'       => 'badge-slate',
        '事務・データ入力'     => 'badge-gold',
        'カスタマーサポート'   => 'badge-teal',
        'SNS運用・マーケティング' => 'badge-rose',
        '翻訳・通訳'           => 'badge-moss',
        '動画編集'             => 'badge-slate',
        '経理・事務代行'       => 'badge-gold',
        'テレアポ・営業事務'   => 'badge-coral',
        'その他'               => 'badge-teal',
    );
    return isset( $map[ $category ] ) ? $map[ $category ] : 'badge-teal';
}

/**
 * 求人メタから実データに基づくタグを組み立てる。固定文言の「#完全在宅 #未経験OK」を
 * 全カードに貼り付けると金太郎飴になるため、案件ごとの勤務日数・対象者を優先的に使う。
 */
function zaito_job_tags( $post_id ) {
    $days   = get_post_meta( $post_id, '_job_days', true );
    $target = get_post_meta( $post_id, '_job_target', true );
    $type   = get_post_meta( $post_id, '_job_type', true );

    $tags = array( '#完全在宅' );
    if ( $days ) {
        $tags[] = '#' . $days;
    }
    if ( $target ) {
        $tags[] = '#' . $target;
    }
    if ( $type && count( $tags ) < 3 ) {
        $tags[] = '#' . $type;
    }
    return array_slice( $tags, 0, 3 );
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
 * 新規登録ユーザーにメールアドレス確認メールを送信する。
 * トークンは user meta に保存し、48時間有効。
 */
function zaito_send_verification_email( $user_id ) {
    $user = get_userdata( $user_id );
    if ( ! $user ) {
        return;
    }
    $token = wp_generate_password( 32, false );
    update_user_meta( $user_id, 'zaito_email_verify_token', $token );
    update_user_meta( $user_id, 'zaito_email_verify_expires', time() + ( 48 * HOUR_IN_SECONDS ) );

    $verify_url = add_query_arg(
        array(
            'uid'   => $user_id,
            'token' => $token,
        ),
        home_url( '/verify-email/' )
    );

    $subject = '【zaito】メールアドレスの確認をお願いします';
    $message = $user->first_name . " 様\n\n"
        . "zaitoにご登録いただきありがとうございます。\n"
        . "以下のリンクをクリックしてメールアドレスの確認を完了してください。\n\n"
        . $verify_url . "\n\n"
        . "このリンクの有効期限は48時間です。\n"
        . "心当たりがない場合はこのメールを破棄してください。";

    wp_mail( $user->user_email, $subject, $message );
}

/**
 * ユーザーのメールアドレス確認が完了しているかどうか。
 */
function zaito_is_email_verified( $user_id ) {
    return get_user_meta( $user_id, 'zaito_email_verified', true ) === '1';
}

/**
 * ワーカー向け機能（応募・マイページ・プロフィール編集）を利用できるかどうか。
 * zaito_seeker ロールに加え、サイト管理者（manage_options権限を持つユーザー）
 * も許可する。Googleログインで管理者自身のメールアドレスを使った場合など、
 * 既存の管理者アカウントでログインしてもワーカー向け機能の動作確認ができるように
 * するため。
 */
function zaito_can_use_seeker_features( $user ) {
    return in_array( 'zaito_seeker', $user->roles, true ) || user_can( $user, 'manage_options' );
}

/**
 * ログイン中ユーザーがメール未確認の場合、確認を促すバナーを表示する。
 * マイページ・企業ダッシュボードの冒頭で呼び出す。
 */
function zaito_render_verification_banner() {
    $user_id = get_current_user_id();
    if ( ! $user_id || zaito_is_email_verified( $user_id ) ) {
        return;
    }
    ?>
    <div class="verification-banner">
      <span>メールアドレスがまだ確認されていません。届いた確認メールのリンクをクリックしてください。</span>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="inline-form">
        <input type="hidden" name="action" value="zaito_resend_verification" />
        <?php wp_nonce_field( 'zaito_resend_verification' ); ?>
        <button type="submit" class="btn btn-outline btn-small">確認メールを再送信</button>
      </form>
    </div>
    <?php
}

/**
 * Googleログインが利用可能かどうか。
 * 利用には wp-config.php で下記の定数を定義する必要がある（サイト管理者が
 * Google Cloud Console で発行したOAuthクライアントIDとシークレットを設定）：
 *   define( 'ZAITO_GOOGLE_CLIENT_ID', '...' );
 *   define( 'ZAITO_GOOGLE_CLIENT_SECRET', '...' );
 * また、Google Cloud ConsoleのOAuth設定で、リダイレクトURIに
 * home_url('/google-callback/') （例: https://zaito-work.com/google-callback/）
 * を登録しておく必要がある。
 */
function zaito_google_login_is_configured() {
    return defined( 'ZAITO_GOOGLE_CLIENT_ID' ) && ZAITO_GOOGLE_CLIENT_ID
        && defined( 'ZAITO_GOOGLE_CLIENT_SECRET' ) && ZAITO_GOOGLE_CLIENT_SECRET;
}

/**
 * Google OAuth2 の認可画面へのURLを組み立てる。
 * $role はコールバック側で新規ユーザー作成時に使うロール。
 */
function zaito_google_login_url( $redirect_to, $role = 'zaito_seeker' ) {
    $state = wp_generate_password( 32, false );
    set_transient( 'zaito_google_state_' . $state, array(
        'redirect_to' => $redirect_to,
        'role'        => $role,
    ), 10 * MINUTE_IN_SECONDS );

    $params = array(
        'client_id'     => ZAITO_GOOGLE_CLIENT_ID,
        'redirect_uri'  => home_url( '/google-callback/' ),
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'state'         => $state,
        'access_type'   => 'online',
        'prompt'        => 'select_account',
    );

    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query( $params );
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
 * 確認メールの再送信。ログイン中のユーザー本人のみ実行可能。
 */
function zaito_handle_resend_verification() {
    check_admin_referer( 'zaito_resend_verification' );

    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( home_url( '/login/' ) );
        exit;
    }

    $user_id = get_current_user_id();
    if ( ! zaito_is_email_verified( $user_id ) ) {
        zaito_send_verification_email( $user_id );
    }

    $current_user = wp_get_current_user();
    $redirect     = in_array( 'zaito_company', $current_user->roles, true ) ? '/company/' : '/mypage/';
    wp_safe_redirect( add_query_arg( 'verification_sent', '1', home_url( $redirect ) ) );
    exit;
}
add_action( 'admin_post_zaito_resend_verification', 'zaito_handle_resend_verification' );

/**
 * ワーカー登録処理（admin-post.php経由。ページ自己送信でのルーティング
 * トラブルを避けるため、WordPress標準のフォーム処理エンドポイントを使う）
 */
function zaito_handle_register_worker() {
    check_admin_referer( 'zaito_register_worker' );

    $errors = array();
    $email             = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
    $name              = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
    $password          = isset( $_POST['password'] ) ? $_POST['password'] : '';
    $password_confirm  = isset( $_POST['password_confirm'] ) ? $_POST['password_confirm'] : '';
    $agree_terms       = isset( $_POST['agree_terms'] ) && $_POST['agree_terms'] === '1';

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
    if ( ! $agree_terms ) {
        $errors[] = '利用規約とプライバシーポリシーに同意してください';
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
            update_user_meta( $user_id, 'zaito_email_verified', '0' );
            zaito_send_verification_email( $user_id );
            zaito_log_user_in_silently( $user_id );

            $redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '';
            $is_local_redirect = $redirect_to && strpos( $redirect_to, home_url() ) === 0;

            wp_safe_redirect( $is_local_redirect ? $redirect_to : home_url( '/mypage/' ) );
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
    check_admin_referer( 'zaito_register_company' );

    $errors = array();
    $email            = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
    $company_name     = isset( $_POST['company_name'] ) ? sanitize_text_field( $_POST['company_name'] ) : '';
    $contact_person   = isset( $_POST['contact_person'] ) ? sanitize_text_field( $_POST['contact_person'] ) : '';
    $phone            = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
    $password         = isset( $_POST['password'] ) ? $_POST['password'] : '';
    $password_confirm = isset( $_POST['password_confirm'] ) ? $_POST['password_confirm'] : '';
    $agree_terms      = isset( $_POST['agree_terms'] ) && $_POST['agree_terms'] === '1';

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
    if ( ! $agree_terms ) {
        $errors[] = '利用規約とプライバシーポリシーに同意してください';
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
            update_user_meta( $user_id, 'zaito_email_verified', '0' );
            zaito_send_verification_email( $user_id );

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
    check_admin_referer( 'zaito_apply' );

    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( home_url( '/login/' ) );
        exit;
    }
    $current_user = wp_get_current_user();
    if ( ! zaito_can_use_seeker_features( $current_user ) ) {
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

    if ( ! zaito_is_email_verified( $current_user->ID ) ) {
        zaito_store_form_errors_and_redirect( array( '応募にはメールアドレスの確認が必要です。マイページから確認メールを再送信してください。' ), $apply_url );
    }

    if ( zaito_has_applied( $current_user->ID, $job_id ) ) {
        zaito_store_form_errors_and_redirect( array( 'この求人にはすでに応募済みです。応募状況はマイページからご確認いただけます。' ), $apply_url );
    }

    $errors = array();

    $furigana   = get_user_meta( $current_user->ID, 'furigana', true );
    $birthdate  = get_user_meta( $current_user->ID, 'birthdate', true );
    $phone      = get_user_meta( $current_user->ID, 'phone', true );
    $prefecture = get_user_meta( $current_user->ID, 'prefecture', true );
    $education  = get_user_meta( $current_user->ID, 'education', true );

    if ( ! $current_user->first_name || ! $furigana || ! $birthdate || ! $prefecture || ! $education ) {
        $errors[] = 'プロフィール（氏名・フリガナ・生年月日・お住まい・最終学歴）をすべて入力してから応募してください。';
    }

    $message = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';
    if ( ! $message ) {
        $errors[] = 'メッセージを入力してください';
    }

    $screening_question = trim( (string) get_post_meta( $job_id, '_job_screening_question', true ) );
    $screening_answer = isset( $_POST['screening_answer'] ) ? sanitize_text_field( $_POST['screening_answer'] ) : '';
    if ( $screening_question && ! $screening_answer ) {
        $errors[] = '採用企業からの質問に回答してください';
    }

    if ( ! empty( $errors ) ) {
        zaito_store_form_errors_and_redirect( $errors, $apply_url );
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
    if ( $screening_question ) {
        update_post_meta( $application_id, 'screening_question', $screening_question );
        update_post_meta( $application_id, 'screening_answer', $screening_answer );
    }

    zaito_send_application_auto_reply( $application_id, $job_id );

    wp_safe_redirect( add_query_arg( 'applied', '1', $apply_url ) );
    exit;
}

/**
 * 企業がファーストメッセージ（自動一次受付メッセージ）をカスタマイズしていない
 * 場合のデフォルト文面。
 */
function zaito_default_auto_reply_message() {
    return 'この度はご応募いただき誠にありがとうございます。担当者が応募内容を確認の上、'
        . '書類選考の結果を追ってご連絡いたします。今しばらくお待ちくださいませ。';
}

/**
 * 応募直後に、求人を投稿した企業アカウントから自動で一次受付メッセージを
 * 送信する。実企業アカウント（_company_user_idを持つ求人）にのみ送信し、
 * デモ求人（架空求人）には送信しない。応募者はこれによって応募直後から
 * チャットで企業とのやり取り状況を確認できる。
 * メッセージ文面の優先順位: ①求人ごとの設定 → ②企業アカウント共通の既定文面
 * → ③システムの既定文面。1社が複数求人を掲載する場合、求人ごとに文面を
 * 変えられるようにするため、求人単位の設定を優先する。
 */
function zaito_send_application_auto_reply( $application_id, $job_id ) {
    $company_user_id = (int) get_post_meta( $job_id, '_company_user_id', true );
    if ( ! $company_user_id ) {
        return;
    }

    $job_message     = trim( (string) get_post_meta( $job_id, '_job_auto_reply_message', true ) );
    $company_message = trim( (string) get_user_meta( $company_user_id, 'auto_reply_message', true ) );
    $auto_message    = $job_message ? $job_message : ( $company_message ? $company_message : zaito_default_auto_reply_message() );

    $message_id = wp_insert_post( array(
        'post_type'   => 'zaito_message',
        'post_content' => $auto_message,
        'post_status' => 'publish',
        'post_title'  => 'Auto reply ' . current_time( 'timestamp' ),
    ) );

    if ( $message_id ) {
        update_post_meta( $message_id, 'conversation_id', $application_id );
        update_post_meta( $message_id, 'sender_id', $company_user_id );
        update_post_meta( $application_id, 'last_message_text', $auto_message );
    }
}
add_action( 'admin_post_zaito_apply', 'zaito_handle_apply' );

/**
 * 気に入った求人の保存(お気に入り)機能。
 * user_meta 'saved_jobs' に投稿IDの配列を保持する。
 */
function zaito_get_saved_jobs( $user_id ) {
    $saved = get_user_meta( $user_id, 'saved_jobs', true );
    return is_array( $saved ) ? array_map( 'intval', $saved ) : array();
}

function zaito_is_job_saved( $user_id, $job_id ) {
    return in_array( (int) $job_id, zaito_get_saved_jobs( $user_id ), true );
}

/**
 * 求人詳細ページの「保存する/保存を解除」ボタンから呼ばれる。
 * 元のページに戻る(redirect_toが渡ってこない場合はwp_get_referer()を使う)。
 */
function zaito_handle_toggle_saved_job() {
    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( home_url( '/login/' ) );
        exit;
    }
    check_admin_referer( 'zaito_toggle_saved_job' );

    $current_user = wp_get_current_user();
    if ( ! zaito_can_use_seeker_features( $current_user ) ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    $job_id = isset( $_POST['job_id'] ) ? intval( $_POST['job_id'] ) : 0;
    $redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : wp_get_referer();
    $is_local_redirect = $redirect_to && strpos( $redirect_to, home_url() ) === 0;

    if ( $job_id ) {
        $saved = zaito_get_saved_jobs( $current_user->ID );
        if ( in_array( $job_id, $saved, true ) ) {
            $saved = array_values( array_diff( $saved, array( $job_id ) ) );
        } else {
            $saved[] = $job_id;
        }
        update_user_meta( $current_user->ID, 'saved_jobs', $saved );
    }

    wp_safe_redirect( $is_local_redirect ? $redirect_to : home_url( '/jobs/' ) );
    exit;
}
add_action( 'admin_post_zaito_toggle_saved_job', 'zaito_handle_toggle_saved_job' );

/**
 * 「興味あり登録」フォーム(/interest/)の送信処理。
 * 本会員登録(/register/)よりハードルの低い、パスワード不要のリード獲得用フォーム。
 * コミュニティ投稿等、まだアカウント登録までは踏み込みたくない相手からの反応を拾うために使う。
 */
function zaito_handle_submit_interest() {
    check_admin_referer( 'zaito_submit_interest' );

    // ハニーポット: 通常のユーザーには見えない(CSSで隠した)フィールド。
    // ここに値が入っている場合は自動送信ボットとみなし、静かに成功画面へ流す(ボットに気づかせない)。
    if ( ! empty( $_POST['website'] ) ) {
        wp_safe_redirect( add_query_arg( 'submitted', '1', home_url( '/interest/' ) ) );
        exit;
    }

    $name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
    $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
    $interests = isset( $_POST['interests'] ) && is_array( $_POST['interests'] )
        ? array_map( 'sanitize_text_field', wp_unslash( $_POST['interests'] ) )
        : array();
    $hours = sanitize_text_field( wp_unslash( $_POST['hours'] ?? '' ) );
    $status = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) );
    $memo = sanitize_textarea_field( wp_unslash( $_POST['memo'] ?? '' ) );

    $errors = array();
    if ( ! $name ) {
        $errors[] = 'お名前(ニックネーム可)を入力してください';
    }
    if ( ! $email || ! is_email( $email ) ) {
        $errors[] = '正しいメールアドレスを入力してください';
    }

    if ( ! empty( $errors ) ) {
        zaito_store_form_errors_and_redirect( $errors, home_url( '/interest/' ) );
    }

    $interest_id = wp_insert_post( array(
        'post_type'   => 'zaito_interest',
        'post_title'  => $name . ' (' . $email . ')',
        'post_status' => 'private',
    ) );

    if ( $interest_id && ! is_wp_error( $interest_id ) ) {
        update_post_meta( $interest_id, 'name', $name );
        update_post_meta( $interest_id, 'email', $email );
        update_post_meta( $interest_id, 'interests', $interests );
        update_post_meta( $interest_id, 'hours', $hours );
        update_post_meta( $interest_id, 'status', $status );
        update_post_meta( $interest_id, 'memo', $memo );
    }

    wp_safe_redirect( add_query_arg( 'submitted', '1', home_url( '/interest/' ) ) );
    exit;
}
add_action( 'admin_post_zaito_submit_interest', 'zaito_handle_submit_interest' );
add_action( 'admin_post_nopriv_zaito_submit_interest', 'zaito_handle_submit_interest' );

/**
 * パスワード再設定メールの送信要求。
 * メールアドレスの存在有無に関わらず同じ結果画面を表示し、
 * 登録済みメールアドレスの推測（ユーザー列挙）を防ぐ。
 */
function zaito_handle_forgot_password() {
    check_admin_referer( 'zaito_forgot_password' );

    $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
    $user  = $email ? get_user_by( 'email', $email ) : false;

    if ( $user ) {
        $key = get_password_reset_key( $user );
        if ( ! is_wp_error( $key ) ) {
            $reset_url = add_query_arg(
                array(
                    'login' => rawurlencode( $user->user_login ),
                    'key'   => rawurlencode( $key ),
                ),
                home_url( '/reset-password/' )
            );
            $subject = '【zaito】パスワード再設定のご案内';
            $message = $user->first_name . " 様\n\n"
                . "パスワード再設定のリクエストを受け付けました。\n"
                . "以下のリンクから新しいパスワードを設定してください。\n\n"
                . $reset_url . "\n\n"
                . "このリンクの有効期限は24時間です。\n"
                . "心当たりがない場合はこのメールを破棄してください。";
            wp_mail( $user->user_email, $subject, $message );
        }
    }

    wp_safe_redirect( add_query_arg( 'sent', '1', home_url( '/forgot-password/' ) ) );
    exit;
}
add_action( 'admin_post_nopriv_zaito_forgot_password', 'zaito_handle_forgot_password' );
add_action( 'admin_post_zaito_forgot_password', 'zaito_handle_forgot_password' );

/**
 * パスワード再設定の実行。
 */
function zaito_handle_reset_password() {
    check_admin_referer( 'zaito_reset_password' );

    $login             = isset( $_POST['login'] ) ? sanitize_text_field( wp_unslash( $_POST['login'] ) ) : '';
    $key               = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
    $password          = isset( $_POST['password'] ) ? $_POST['password'] : '';
    $password_confirm  = isset( $_POST['password_confirm'] ) ? $_POST['password_confirm'] : '';

    $reset_url = add_query_arg(
        array(
            'login' => rawurlencode( $login ),
            'key'   => rawurlencode( $key ),
        ),
        home_url( '/reset-password/' )
    );

    $user = check_password_reset_key( $key, $login );

    if ( is_wp_error( $user ) ) {
        zaito_store_form_errors_and_redirect( array( 'リンクが無効か、有効期限が切れています。もう一度お試しください。' ), home_url( '/forgot-password/' ) );
    }

    $errors = array();
    if ( ! $password || strlen( $password ) < 8 ) {
        $errors[] = 'パスワードは8文字以上で入力してください';
    }
    if ( $password !== $password_confirm ) {
        $errors[] = 'パスワードが一致しません';
    }

    if ( ! empty( $errors ) ) {
        zaito_store_form_errors_and_redirect( $errors, $reset_url );
    }

    reset_password( $user, $password );

    wp_safe_redirect( add_query_arg( 'reset', '1', home_url( '/login/' ) ) );
    exit;
}
add_action( 'admin_post_nopriv_zaito_reset_password', 'zaito_handle_reset_password' );
add_action( 'admin_post_zaito_reset_password', 'zaito_handle_reset_password' );

/**
 * ワーカーのプロフィール（フリガナ・生年月日・電話番号・学歴・職務経歴）保存処理
 */
function zaito_handle_update_worker_profile() {
    check_admin_referer( 'zaito_update_worker_profile' );

    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( home_url( '/login/' ) );
        exit;
    }
    $current_user = wp_get_current_user();
    if ( ! zaito_can_use_seeker_features( $current_user ) ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    $errors = array();
    $name       = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
    $furigana   = isset( $_POST['furigana'] ) ? sanitize_text_field( $_POST['furigana'] ) : '';
    $birthdate  = isset( $_POST['birthdate'] ) ? sanitize_text_field( $_POST['birthdate'] ) : '';
    $phone      = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
    $prefecture = isset( $_POST['prefecture'] ) ? sanitize_text_field( $_POST['prefecture'] ) : '';
    $education  = isset( $_POST['education'] ) ? sanitize_text_field( $_POST['education'] ) : '';
    $work_history = isset( $_POST['work_history'] ) ? sanitize_textarea_field( $_POST['work_history'] ) : '';

    if ( ! $name ) {
        $errors[] = '氏名を入力してください';
    }
    if ( ! $furigana ) {
        $errors[] = 'フリガナを入力してください';
    }
    if ( ! $birthdate ) {
        $errors[] = '生年月日を入力してください';
    } elseif ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $birthdate ) ) {
        $errors[] = '生年月日の形式が正しくありません';
    }
    if ( ! $prefecture ) {
        $errors[] = 'お住まいの都道府県を選択してください';
    }
    if ( ! $education ) {
        $errors[] = '最終学歴を選択してください';
    }

    $redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '';
    $is_local_redirect = $redirect_to && strpos( $redirect_to, home_url() ) === 0;
    $profile_url = $is_local_redirect ? add_query_arg( 'redirect_to', rawurlencode( $redirect_to ), home_url( '/worker-profile/' ) ) : home_url( '/worker-profile/' );

    if ( ! empty( $errors ) ) {
        zaito_store_form_errors_and_redirect( $errors, $profile_url );
    }

    wp_update_user( array( 'ID' => $current_user->ID, 'first_name' => $name ) );
    update_user_meta( $current_user->ID, 'furigana', $furigana );
    update_user_meta( $current_user->ID, 'birthdate', $birthdate );
    update_user_meta( $current_user->ID, 'phone', $phone );
    update_user_meta( $current_user->ID, 'prefecture', $prefecture );
    update_user_meta( $current_user->ID, 'education', $education );
    update_user_meta( $current_user->ID, 'work_history', $work_history );

    wp_safe_redirect( $is_local_redirect ? $redirect_to : add_query_arg( 'saved', '1', home_url( '/worker-profile/' ) ) );
    exit;
}
add_action( 'admin_post_zaito_update_worker_profile', 'zaito_handle_update_worker_profile' );

/**
 * 企業プロフィール編集(/company-profile/)の保存処理。ワーカー側のworker-profileと対になる、
 * 企業アカウント向けの企業情報編集フォーム。以前は編集手段がwp-adminのprofile.php頼みだった
 * (企業アカウントには使わせるべきでない管理画面UIへ直接遷移させる作りだった)ため新設した。
 * メールアドレス変更は確認フローが別途必要になるため、このフォームでは扱わない。
 */
function zaito_handle_update_company_profile() {
    check_admin_referer( 'zaito_update_company_profile' );

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
    $company_name   = isset( $_POST['company_name'] ) ? sanitize_text_field( $_POST['company_name'] ) : '';
    $contact_person = isset( $_POST['contact_person'] ) ? sanitize_text_field( $_POST['contact_person'] ) : '';
    $phone          = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';

    if ( ! $company_name ) {
        $errors[] = '企業名を入力してください';
    }
    if ( ! $contact_person ) {
        $errors[] = 'ご担当者名を入力してください';
    }
    if ( ! $phone ) {
        $errors[] = '電話番号を入力してください';
    }

    if ( ! empty( $errors ) ) {
        zaito_store_form_errors_and_redirect( $errors, home_url( '/company-profile/' ) );
    }

    wp_update_user( array( 'ID' => $current_user->ID, 'first_name' => $contact_person ) );
    update_user_meta( $current_user->ID, 'company_name', $company_name );
    update_user_meta( $current_user->ID, 'company_phone', $phone );

    wp_safe_redirect( add_query_arg( 'saved', '1', home_url( '/company-profile/' ) ) );
    exit;
}
add_action( 'admin_post_zaito_update_company_profile', 'zaito_handle_update_company_profile' );

/**
 * 企業による求人投稿処理（admin-post.php経由）
 */
function zaito_handle_post_job() {
    check_admin_referer( 'zaito_post_job' );

    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( home_url( '/company-login/' ) );
        exit;
    }
    $current_user = wp_get_current_user();
    if ( ! in_array( 'zaito_company', $current_user->roles, true ) ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }
    if ( ! zaito_is_email_verified( $current_user->ID ) ) {
        zaito_store_form_errors_and_redirect( array( '求人の投稿にはメールアドレスの確認が必要です。ダッシュボードから確認メールを再送信してください。' ), home_url( '/company-jobs/' ) );
    }

    $errors = array();
    $title           = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
    $content         = isset( $_POST['content'] ) ? sanitize_textarea_field( $_POST['content'] ) : '';
    $category        = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';
    $employment_type = isset( $_POST['employment_type'] ) ? sanitize_text_field( $_POST['employment_type'] ) : '';
    $salary_type     = isset( $_POST['salary_type'] ) ? sanitize_text_field( $_POST['salary_type'] ) : '';
    $salary          = isset( $_POST['salary'] ) ? sanitize_text_field( $_POST['salary'] ) : '';
    $salary_max      = isset( $_POST['salary_max'] ) ? sanitize_text_field( $_POST['salary_max'] ) : '';
    $type            = isset( $_POST['job_type'] ) ? sanitize_text_field( $_POST['job_type'] ) : '';
    $days            = isset( $_POST['job_days'] ) ? sanitize_text_field( $_POST['job_days'] ) : '';
    $target_input    = isset( $_POST['job_target'] ) && is_array( $_POST['job_target'] ) ? (array) $_POST['job_target'] : array();
    $target_options  = zaito_target_tag_options();
    $target          = implode( '、', array_intersect( array_map( 'sanitize_text_field', $target_input ), $target_options ) );
    $job_auto_reply  = isset( $_POST['job_auto_reply_message'] ) ? sanitize_textarea_field( $_POST['job_auto_reply_message'] ) : '';
    $screening_question = isset( $_POST['screening_question'] ) ? sanitize_text_field( $_POST['screening_question'] ) : '';

    if ( ! $title ) {
        $errors[] = '求人タイトルを入力してください';
    }
    if ( ! $content ) {
        $errors[] = '仕事内容を入力してください';
    }
    if ( ! $category ) {
        $errors[] = '求人カテゴリを選択してください';
    }
    if ( ! in_array( $employment_type, zaito_employment_type_options(), true ) ) {
        $errors[] = '雇用形態を選択してください';
    }
    if ( $salary_type && ! in_array( $salary_type, zaito_salary_type_options(), true ) ) {
        $salary_type = '';
    }
    if ( $type && ! in_array( $type, zaito_work_style_options(), true ) ) {
        $type = '';
    }
    if ( $days && ! in_array( $days, zaito_min_days_options(), true ) ) {
        $days = '';
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
    update_post_meta( $job_id, '_job_category', $category );
    update_post_meta( $job_id, '_job_employment_type', $employment_type );
    update_post_meta( $job_id, '_job_salary_type', $salary_type );
    update_post_meta( $job_id, '_job_salary', $salary );
    update_post_meta( $job_id, '_job_salary_max', $salary_max );
    update_post_meta( $job_id, '_job_type', $type );
    update_post_meta( $job_id, '_job_days', $days );
    update_post_meta( $job_id, '_job_target', $target );
    update_post_meta( $job_id, '_job_auto_reply_message', $job_auto_reply );
    update_post_meta( $job_id, '_job_screening_question', $screening_question );

    wp_safe_redirect( add_query_arg( 'posted', '1', home_url( '/company-jobs/' ) ) );
    exit;
}
add_action( 'admin_post_zaito_post_job', 'zaito_handle_post_job' );

/**
 * 既存求人の編集保存処理。以前は編集用のフロント画面が無く、企業ダッシュボードの
 * 「編集」ボタンが素のwp-admin投稿編集画面に直接飛ばしていた(表示がバラバラで、
 * 企業アカウントの権限では触ってはいけないメタ情報まで理論上編集できてしまう構成だった)。
 * page-company-jobs.php の投稿フォームと同じ項目を編集できる、フロント側の更新処理を用意する。
 */
function zaito_handle_update_job() {
    check_admin_referer( 'zaito_update_job' );

    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( home_url( '/company-login/' ) );
        exit;
    }
    $current_user = wp_get_current_user();
    if ( ! in_array( 'zaito_company', $current_user->roles, true ) ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    $job_id = isset( $_POST['job_id'] ) ? intval( $_POST['job_id'] ) : 0;
    $job = $job_id ? get_post( $job_id ) : null;
    if ( ! $job || 'job_listing' !== $job->post_type || (int) get_post_meta( $job_id, '_company_user_id', true ) !== $current_user->ID ) {
        wp_safe_redirect( home_url( '/company-jobs/' ) );
        exit;
    }

    $errors = array();
    $title           = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
    $content         = isset( $_POST['content'] ) ? sanitize_textarea_field( $_POST['content'] ) : '';
    $category        = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';
    $employment_type = isset( $_POST['employment_type'] ) ? sanitize_text_field( $_POST['employment_type'] ) : '';
    $salary_type     = isset( $_POST['salary_type'] ) ? sanitize_text_field( $_POST['salary_type'] ) : '';
    $salary          = isset( $_POST['salary'] ) ? sanitize_text_field( $_POST['salary'] ) : '';
    $salary_max      = isset( $_POST['salary_max'] ) ? sanitize_text_field( $_POST['salary_max'] ) : '';
    $type            = isset( $_POST['job_type'] ) ? sanitize_text_field( $_POST['job_type'] ) : '';
    $days            = isset( $_POST['job_days'] ) ? sanitize_text_field( $_POST['job_days'] ) : '';
    $target_input    = isset( $_POST['job_target'] ) && is_array( $_POST['job_target'] ) ? (array) $_POST['job_target'] : array();
    $target_options  = zaito_target_tag_options();
    $target          = implode( '、', array_intersect( array_map( 'sanitize_text_field', $target_input ), $target_options ) );
    $job_auto_reply  = isset( $_POST['job_auto_reply_message'] ) ? sanitize_textarea_field( $_POST['job_auto_reply_message'] ) : '';
    $screening_question = isset( $_POST['screening_question'] ) ? sanitize_text_field( $_POST['screening_question'] ) : '';

    if ( ! $title ) {
        $errors[] = '求人タイトルを入力してください';
    }
    if ( ! $content ) {
        $errors[] = '仕事内容を入力してください';
    }
    if ( ! $category ) {
        $errors[] = '求人カテゴリを選択してください';
    }
    if ( ! in_array( $employment_type, zaito_employment_type_options(), true ) ) {
        $errors[] = '雇用形態を選択してください';
    }
    if ( $salary_type && ! in_array( $salary_type, zaito_salary_type_options(), true ) ) {
        $salary_type = '';
    }
    if ( $type && ! in_array( $type, zaito_work_style_options(), true ) ) {
        $type = '';
    }
    if ( $days && ! in_array( $days, zaito_min_days_options(), true ) ) {
        $days = '';
    }

    if ( ! empty( $errors ) ) {
        zaito_store_form_errors_and_redirect( $errors, add_query_arg( 'edit', $job_id, home_url( '/company-jobs/' ) ) );
    }

    wp_update_post( array(
        'ID'           => $job_id,
        'post_title'   => $title,
        'post_content' => $content,
    ) );

    update_post_meta( $job_id, '_job_category', $category );
    update_post_meta( $job_id, '_job_employment_type', $employment_type );
    update_post_meta( $job_id, '_job_salary_type', $salary_type );
    update_post_meta( $job_id, '_job_salary', $salary );
    update_post_meta( $job_id, '_job_salary_max', $salary_max );
    update_post_meta( $job_id, '_job_type', $type );
    update_post_meta( $job_id, '_job_days', $days );
    update_post_meta( $job_id, '_job_target', $target );
    update_post_meta( $job_id, '_job_auto_reply_message', $job_auto_reply );
    update_post_meta( $job_id, '_job_screening_question', $screening_question );

    wp_safe_redirect( add_query_arg( 'updated', '1', home_url( '/company-jobs/' ) ) );
    exit;
}
add_action( 'admin_post_zaito_update_job', 'zaito_handle_update_job' );

/**
 * 企業のファーストメッセージ（自動一次受付メッセージ）の文面保存処理（admin-post.php経由）。
 */
function zaito_handle_update_auto_reply_message() {
    check_admin_referer( 'zaito_update_auto_reply_message' );

    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( home_url( '/company-login/' ) );
        exit;
    }
    $current_user = wp_get_current_user();
    if ( ! in_array( 'zaito_company', $current_user->roles, true ) ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    $message = isset( $_POST['auto_reply_message'] ) ? sanitize_textarea_field( $_POST['auto_reply_message'] ) : '';
    update_user_meta( $current_user->ID, 'auto_reply_message', $message );

    wp_safe_redirect( add_query_arg( 'message_saved', '1', home_url( '/company/' ) ) );
    exit;
}
add_action( 'admin_post_zaito_update_auto_reply_message', 'zaito_handle_update_auto_reply_message' );

/**
 * 企業が自社の求人の掲載を終了/再開する。投稿者(_company_user_id)が
 * 自分自身であることを確認した上で、post_status を publish⇔draft でトグルする。
 * draft にすると通常のWPクエリの仕様上、公開画面(一覧・検索・詳細)から
 * 自動的に非表示になる。データは削除しない。
 */
function zaito_handle_toggle_job_status() {
    check_admin_referer( 'zaito_toggle_job_status' );

    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( home_url( '/company-login/' ) );
        exit;
    }
    $current_user = wp_get_current_user();
    if ( ! in_array( 'zaito_company', $current_user->roles, true ) ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    $job_id = isset( $_POST['job_id'] ) ? intval( $_POST['job_id'] ) : 0;
    $job = $job_id ? get_post( $job_id ) : null;

    if ( $job && $job->post_type === 'job_listing' ) {
        $owner_id = (int) get_post_meta( $job_id, '_company_user_id', true );
        if ( $owner_id === $current_user->ID ) {
            $new_status = $job->post_status === 'publish' ? 'draft' : 'publish';
            wp_update_post( array(
                'ID'          => $job_id,
                'post_status' => $new_status,
            ) );
        }
    }

    wp_safe_redirect( home_url( '/company-jobs/' ) );
    exit;
}
add_action( 'admin_post_zaito_toggle_job_status', 'zaito_handle_toggle_job_status' );

/**
 * 応募（zaito_application）に紐づく求人の投稿企業アカウントIDを返す。
 * チャットの相手（企業側）はこの値で判定する。承認・不承認に関わらず、
 * 応募した時点から企業とのやり取りができるようにするため、
 * 別途「company_id」を採用時に記録する方式はやめ、常に求人の
 * _company_user_id から動的に解決する。デモ求人（実企業アカウントを
 * 持たない）の場合は0を返す。
 */
function zaito_has_applied( $user_id, $job_id ) {
    $existing = get_posts( array(
        'post_type'      => 'zaito_application',
        'posts_per_page' => 1,
        'meta_query'      => array(
            array( 'key' => 'applicant_id', 'value' => $user_id ),
            array( 'key' => 'job_id', 'value' => $job_id ),
        ),
    ) );
    return ! empty( $existing );
}

function zaito_get_application_company_id( $application_id ) {
    $job_id = get_post_meta( $application_id, 'job_id', true );
    if ( ! $job_id ) {
        return 0;
    }
    return (int) get_post_meta( $job_id, '_company_user_id', true );
}

/**
 * 企業による応募審査（採用・不採用）処理（admin-post.php経由）。
 */
function zaito_handle_update_application_status() {
    check_admin_referer( 'zaito_update_application_status' );

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

    zaito_notify_application_status_change( $application_id, $job_id, $new_status );

    wp_safe_redirect( home_url( '/company-applicants/' ) );
    exit;
}
add_action( 'admin_post_zaito_update_application_status', 'zaito_handle_update_application_status' );

/**
 * 応募ステータス変更（承認・非承認）を応募者にメール通知する。
 */
function zaito_notify_application_status_change( $application_id, $job_id, $new_status ) {
    $applicant_id = get_post_meta( $application_id, 'applicant_id', true );
    $applicant = get_userdata( $applicant_id );
    if ( ! $applicant ) {
        return;
    }

    $job_title    = get_the_title( $job_id );
    $company_name = get_post_meta( $job_id, '_company_name', true );

    if ( 'accepted' === $new_status ) {
        $subject = '【zaito】応募が承認されました';
        $body    = $applicant->first_name . " 様\n\n"
            . $company_name . '様より、「' . $job_title . "」への応募が承認されました。\n"
            . "チャットで企業とやり取りができます。\n\n"
            . home_url( '/chat/?conversation_id=' . $application_id ) . "\n";
    } else {
        $subject = '【zaito】応募結果のお知らせ';
        $body    = $applicant->first_name . " 様\n\n"
            . '「' . $job_title . "」への応募について、今回は採用を見送らせていただくことになりました。\n"
            . "またの機会がございましたらよろしくお願いいたします。\n\n"
            . home_url( '/jobs/' ) . "\n";
    }

    wp_mail( $applicant->user_email, $subject, $body );
}

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
    $applicant_id = (int) get_post_meta( $conversation_id, 'applicant_id', true );
    $company_id = zaito_get_application_company_id( $conversation_id );

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

    $applicant_id = (int) get_post_meta( $conversation_id, 'applicant_id', true );
    $company_id = zaito_get_application_company_id( $conversation_id );

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

        $recipient_id = ( $current_user->ID === $applicant_id ) ? $company_id : $applicant_id;
        zaito_notify_new_message( $recipient_id, $current_user, $conversation_id, $message_text );

        wp_send_json_success( array( 'message_id' => $message_id ) );
    } else {
        wp_send_json_error( array( 'message' => 'メッセージの送信に失敗しました' ) );
    }
}
add_action( 'wp_ajax_zaito_send_message', 'zaito_send_message' );

/**
 * 新着チャットメッセージを相手にメール通知する。
 */
function zaito_notify_new_message( $recipient_id, $sender, $conversation_id, $message_text ) {
    $recipient = get_userdata( $recipient_id );
    if ( ! $recipient ) {
        return;
    }

    $subject = '【zaito】新しいメッセージが届いています';
    $body    = $recipient->first_name . " 様\n\n"
        . $sender->first_name . "様からメッセージが届きました。\n\n"
        . '「' . wp_trim_words( $message_text, 30 ) . "」\n\n"
        . "チャットを確認する：\n"
        . home_url( '/chat/?conversation_id=' . $conversation_id ) . "\n";

    wp_mail( $recipient->user_email, $subject, $body );
}

/**
 * デモ用の架空求人を50件生成する共通ロジック。
 * 既に生成済みの場合は何もしない（$force で強制再生成）。
 * 戻り値は今回作成した件数。
 */
function zaito_generate_demo_jobs( $force = false ) {
    if ( $force ) {
        $existing = get_posts( array(
            'post_type'      => 'job_listing',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array( 'key' => '_zaito_demo', 'value' => '1' ),
            ),
        ) );
        foreach ( $existing as $job ) {
            wp_delete_post( $job->ID, true );
        }
        delete_option( 'zaito_demo_jobs_seeded_v2' );
    }

    if ( get_option( 'zaito_demo_jobs_seeded_v2' ) ) {
        return 0;
    }

    $categories = array(
        array(
            'label' => 'ライティング',
            'titles' => array( 'Webライター', 'SEO記事作成スタッフ', 'コラムライター', '商品レビューライティング', '取材・インタビューライター', 'コピーライティングアシスタント' ),
            'content' => 'Webメディア向けの記事作成をお願いします。テーマに沿ったリサーチと執筆、簡単な校正までを一貫してご担当いただきます。文章を書くことが好きな方、正確な情報収集ができる方を歓迎します。',
        ),
        array(
            'label' => 'デザイン',
            'titles' => array( 'バナーデザイナー', 'ロゴ・ブランディングデザイン', 'ECサイトデザイナー', 'SNS投稿画像デザイン', 'LP（ランディングページ）デザイン', 'イラスト制作スタッフ' ),
            'content' => 'Webバナーや販促画像のデザイン制作をお願いします。Photoshop・Illustratorを使った基本的な操作ができればOK。ポートフォリオがある方は優遇しますが、未経験からのスタートも歓迎です。',
        ),
        array(
            'label' => 'プログラミング',
            'titles' => array( 'WordPressサイト制作', 'フロントエンドエンジニア（在宅）', '簡単な不具合修正・保守', 'Webサイトコーディング', 'スプレッドシート自動化', 'ノーコードツール構築サポート' ),
            'content' => '既存Webサイトの軽微な修正・機能追加を中心にお願いします。HTML/CSS/JavaScriptの基礎知識がある方、学習中の方も歓迎です。分からない点はチームでフォローします。',
        ),
        array(
            'label' => '事務・データ入力',
            'titles' => array( 'データ入力スタッフ', '経理サポート（在宅）', '請求書作成アシスタント', 'リスト作成・整理業務', 'アンケート集計スタッフ', '資料作成アシスタント' ),
            'content' => 'Excel・スプレッドシートを使ったデータ入力や資料整理をお願いします。パソコンの基本操作ができれば未経験でも問題ありません。マニュアルを用意しているので安心してご応募ください。',
        ),
        array(
            'label' => 'カスタマーサポート',
            'titles' => array( 'チャットサポートスタッフ', 'メール対応オペレーター', '予約受付サポート', 'ECサイトお問い合わせ対応', 'SNSアカウント運用サポート', 'ヘルプデスクスタッフ' ),
            'content' => 'お客様からのお問い合わせ対応（チャット・メール中心）をお願いします。丁寧なコミュニケーションができる方を歓迎します。研修制度がありますので未経験でも安心です。',
        ),
    );

    $companies = array(
        '株式会社リモートワークス', '合同会社おうちワーク', '株式会社クラウドスタイル', '株式会社フリーホーム',
        '合同会社ネクストリモート', '株式会社ワークシェア', '株式会社テレワークラボ', '合同会社ホームベース',
        '株式会社リンクワーク', '株式会社おうちジョブ', '合同会社ゆるコネクト', '株式会社スマイルリモート',
        '株式会社フレックスタイムズ', '合同会社セルフワーク', '株式会社ノビノビワーク',
    );

    $salaries = array( '1000', '1100', '1200', '1300', '1400', '1500', '1600', '1800', '2000', '2200' );
    $types = array( '完全在宅・シフト制', '完全在宅・時間自由', '完全在宅・週数日出社なし', '完全在宅・フレックス', '完全在宅・固定時間' );
    $days = array( '週1日〜', '週2日〜', '週3日〜', '月10時間〜', '応相談' );
    $targets = array( '未経験OK・大学生歓迎', '主婦(夫)歓迎・扶養内OK', '副業OK・経験者優遇', '未経験OK・研修あり', 'シニア世代歓迎', 'Wワーク歓迎' );

    $created = 0;
    for ( $i = 0; $i < 50; $i++ ) {
        $cat = $categories[ array_rand( $categories ) ];
        $title = $cat['titles'][ array_rand( $cat['titles'] ) ];
        $company = $companies[ array_rand( $companies ) ];

        $job_id = wp_insert_post( array(
            'post_type'    => 'job_listing',
            'post_title'   => $title,
            'post_content' => $cat['content'],
            'post_status'  => 'publish',
        ) );

        if ( ! $job_id || is_wp_error( $job_id ) ) {
            continue;
        }

        update_post_meta( $job_id, '_company_name', $company );
        update_post_meta( $job_id, '_job_category', $cat['label'] );
        update_post_meta( $job_id, '_job_salary', $salaries[ array_rand( $salaries ) ] );
        update_post_meta( $job_id, '_job_type', $types[ array_rand( $types ) ] );
        update_post_meta( $job_id, '_job_days', $days[ array_rand( $days ) ] );
        update_post_meta( $job_id, '_job_target', $targets[ array_rand( $targets ) ] );
        update_post_meta( $job_id, '_zaito_demo', '1' );
        $created++;
    }

    update_option( 'zaito_demo_jobs_seeded_v2', $created );

    return $created;
}

/**
 * サイトへの通常アクセス時に、まだ架空求人が生成されていなければ
 * 自動的に生成する。管理者のログインやクリックを一切必要とせず、
 * デプロイ後に誰かがサイトを訪問した時点で一度だけ実行される。
 */
function zaito_maybe_auto_seed_demo_jobs() {
    if ( is_admin() ) {
        return;
    }
    if ( get_option( 'zaito_demo_jobs_seeded_v2' ) ) {
        return;
    }
    zaito_generate_demo_jobs();
}
add_action( 'init', 'zaito_maybe_auto_seed_demo_jobs', 30 );

/**
 * 管理者が手動で作り直したい場合のための入口。
 * /wp-admin/admin-post.php?action=zaito_seed_demo_jobs&reset=1 のように
 * アクセスすると、既存の架空求人を削除してから作り直す。
 */
function zaito_seed_demo_jobs() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'この操作には管理者権限が必要です。' );
    }

    $force = isset( $_GET['reset'] ) && $_GET['reset'] === '1';
    $created = zaito_generate_demo_jobs( $force );

    if ( $created === 0 && ! $force ) {
        wp_die( '架空求人は既に生成済みです。作り直す場合は URL の末尾に <code>&reset=1</code> を付けて再度アクセスしてください。<br><a href="' . esc_url( home_url( '/jobs/' ) ) . '">求人一覧を見る</a>' );
    }

    wp_die( $created . '件の架空求人を作成しました。<br><a href="' . esc_url( home_url( '/jobs/' ) ) . '">求人一覧を見る</a>' );
}
add_action( 'admin_post_zaito_seed_demo_jobs', 'zaito_seed_demo_jobs' );

/**
 * 営業ヒアリング用の「仮ページ」を作成する。企業の許可を得る前に、
 * 実際の求人サイトと同じ single-job_listing.php テンプレートで見せるための
 * 非公開プレビュー。_zaito_preview=1 を持つ求人は zaito_hide_fake_jobs_from_public()
 * により通常の一覧・PICK UP・関連求人からは自動的に除外され、直接リンクを
 * 知っている人だけが閲覧できる。データは実際の求人ページ(Indeed等)を確認の上で
 * 作成し、確認が取れなかった項目は「ご相談」として明記している。
 * /wp-admin/admin-post.php?action=zaito_seed_preview_jobs にアクセスすると
 * 作成(既存があれば重複作成しない)し、パーマリンク一覧を表示する。
 */
function zaito_seed_preview_jobs() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'この操作には投稿権限が必要です。' );
    }

    $zaito_preview_disclaimer = "\n\n---\n※この説明文は、公開されている求人情報をもとにZAITO運営事務局が作成した仮の文章です。貴社が実際に書かれた文章ではありません。内容に誤りや修正したい点がございましたら、正式掲載前にご指摘ください。";

    $previews = array(
        array(
            'company'   => '一般社団法人ミライデザイン機構',
            'title'     => 'データ入力・PC作業スタッフ(障がい者採用枠)',
            'category'  => '事務・データ入力',
            'content'   => 'パソコンを使ったデータ入力や資料整理、簡単な書類作成などの事務作業を想定しています。障がいのある方が自分のペースで働けるよう配慮された採用枠として、在宅勤務・柔軟な勤務時間での募集を想定しました。特別なスキルは不要で、Word・Excelの基本操作ができれば対応できる業務を中心に構成しています。体調に合わせて勤務日数・時間を調整しやすい点は、ZAITOのワーカー層(在宅で働きたい主婦・学生・障がいのある方など)にとっても安心材料になると考えています。' . $zaito_preview_disclaimer,
            'salary_type' => '',
            'salary'    => '',
            'salary_max' => '',
            'salary_note' => 'ご相談(公開されている求人ページから確認できませんでした)',
            'employment_type' => '業務委託',
            'job_type'  => '完全在宅・シフト制',
            'job_days'  => '応相談',
            'job_target' => '未経験者歓迎',
        ),
        array(
            'company'   => '株式会社ZOS',
            'title'     => 'データ入力・動画編集スタッフ(就労継続支援B型)',
            'category'  => '動画編集',
            'content'   => 'データ入力に加え、テンプレートを使った簡単な動画編集(テロップ入れ・カット編集程度)をお願いする案件を想定しています。就労継続支援B型という枠組みで在宅ワーカーを受け入れている点から、体調や生活リズムに合わせて無理のないペースで働けることを重視した業務設計になっていると考えられます。動画編集は未経験でも、基本的な操作をマニュアル・研修でサポートいただける前提で構成しました。' . $zaito_preview_disclaimer,
            'salary_type' => '',
            'salary'    => '',
            'salary_max' => '',
            'salary_note' => 'ご相談(公開されている求人ページから確認できませんでした)',
            'employment_type' => '業務委託',
            'job_type'  => '完全在宅・シフト制',
            'job_days'  => '応相談',
            'job_target' => '未経験者歓迎',
        ),
        array(
            'company'   => '株式会社PRIDE',
            'title'     => '動画編集・SNS運用スタッフ',
            'category'  => '動画編集',
            'content'   => '動画編集(カット編集・テロップ入れ等)と、Instagram等のSNSアカウント運用サポートをお願いする案件です。実際の掲載求人では、9:00〜18:00・10:00〜19:00・11:00〜20:00のシフトから選べる形で、週3日〜・実働6時間〜という働き方が案内されています。Word・Excel・PowerPointが使えると望ましいとされていますが、研修でサポートいただける前提のため未経験でも問題ありません。学歴・経験不問、学生・フリーターも歓迎、交通費規定支給やインセンティブ制度、昇給制度もあるとのことで、副業・Wワークとしても検討しやすい内容だと考えています。' . $zaito_preview_disclaimer,
            'salary_type' => '時給',
            'salary'    => '1800',
            'salary_max' => '2500',
            'salary_note' => '',
            'employment_type' => 'アルバイト・パート',
            'job_type'  => '完全在宅・シフト制',
            'job_days'  => '週3日〜',
            'job_target' => '未経験者歓迎、学生歓迎、ブランクOK',
        ),
        array(
            'company'   => '合同会社ワンワールド',
            'title'     => 'データ入力スタッフ',
            'category'  => '事務・データ入力',
            'content'   => 'パソコンまたはスマートフォンでの文字入力を中心としたデータ入力業務です。実際の掲載求人によると、週1日からOKでシフトは自由、平日のみ・土日のみといった相談も可能とのことです。特別な資格は不要で、指定されたルールに沿って正確に入力できることや、簡単な報告・連絡ができることが重視されているようです。未経験・ブランクのある方も歓迎、副業としての応募も想定されており、スキマ時間を使って働きたい方に向いている案件だと考えています。' . $zaito_preview_disclaimer,
            'salary_type' => '日給',
            'salary'    => '1500',
            'salary_max' => '',
            'salary_note' => '',
            'employment_type' => '業務委託',
            'job_type'  => '完全在宅・曜日応相談',
            'job_days'  => '週1日〜',
            'job_target' => '未経験者歓迎、Wワーク・副業OK',
        ),
        array(
            'company'   => '株式会社コモリク',
            'title'     => 'データ入力・データ収集スタッフ',
            'category'  => '事務・データ入力',
            'content'   => 'パソコンを使ったデータ入力・データ収集業務です。実際の掲載求人では、9:00〜14:00(休憩30分)の勤務時間で、週1日から勤務可能、変形労働時間制のため体調に合わせて休日を調整しやすい設計になっているとのことです。パソコンの所有と基本操作ができれば未経験・初心者でも対応可能で、学歴も不問です。障がいや難病、特定疾患をお持ちの方、通院中の方、人とのコミュニケーションに不安がある方も対象とされており、年齢不問・全国からの応募が可能(一部未対応エリアあり)という間口の広さが特徴です。' . $zaito_preview_disclaimer,
            'salary_type' => '月給',
            'salary'    => '20000',
            'salary_max' => '60000',
            'salary_note' => '',
            'employment_type' => '業務委託',
            'job_type'  => '完全在宅・固定時間制',
            'job_days'  => '週1日〜',
            'job_target' => '未経験者歓迎、ブランクOK',
        ),
        array(
            'company'   => '有限会社ハニーボックス',
            'title'     => '内職スタッフ(軽作業)',
            'category'  => 'その他',
            'content'   => '巾着の縫製作業、またはギフト箱のセット作業のどちらかを選べる内職業務です。実際の掲載求人によると、縫製作業はミシンを使った製作・糸処理・検品、ギフト箱のセット作業は箱の組み立てや薄紙・緩衝材のセット・検品など、未経験でも始めやすい軽作業とのことです。完全在宅で、材料や梱包資材は会社側から配送されるため、自分の好きな時間に自宅で作業を進められる点が特徴です。出来高制のため、作業量に応じて収入を得られます。主婦(夫)の方の活躍が多く、副業・扶養内での勤務にも対応しているとのことで、スキマ時間を活用したい方に向いている案件だと考えています。' . $zaito_preview_disclaimer,
            'salary_type' => '',
            'salary'    => '',
            'salary_max' => '',
            'salary_note' => '出来高制(内容・個数による、公開されている求人ページから具体額は確認できませんでした)',
            'employment_type' => '業務委託',
            'job_type'  => '完全在宅・時間自由',
            'job_days'  => '応相談',
            'job_target' => '未経験者歓迎、主婦(夫)歓迎、Wワーク・扶養内OK',
        ),
        array(
            'company'   => '株式会社プラコレ',
            'title'     => '在宅ウェディングチャットアドバイザー',
            'category'  => 'カスタマーサポート',
            'content'   => 'ウェディング関連の花嫁サポート業務です。実際の掲載求人によると、電話対応・予約調整・事務サポート・データ入力などを完全在宅で行うとのことです。1日4時間から勤務可能で、早番(10時〜14時)・遅番(14時〜18時)のシフト制、実績に応じた昇給もあるとのことです。パソコンとWi-Fi環境があればスタートでき、Web面接(スマートフォン対応)にも対応しているため、忙しい方でも選考に進みやすいと考えられます。' . $zaito_preview_disclaimer,
            'salary_type' => '時給',
            'salary'    => '1225',
            'salary_max' => '1500',
            'salary_note' => '',
            'employment_type' => '業務委託',
            'job_type'  => '完全在宅・シフト制',
            'job_days'  => '応相談',
            'job_target' => '未経験者歓迎',
        ),
        array(
            'company'   => 'Quasar株式会社',
            'title'     => 'データ入力・編集アシスタント',
            'category'  => '事務・データ入力',
            'content'   => '出版業務を支援するアシスタント職です。実際の掲載求人によると、Excel・Googleスプレッドシートを使ったリスト作成、インターネットを活用した企業・著者・書店等の情報収集、データ入力・整理・更新などが主な業務とのことです。週3日以上・1日3時間以上から勤務可能で、フルリモート対応、土日祝休みとのことです。Excel・Wordの基本操作ができれば未経験でも対応でき、SNSが好きな方も歓迎されているとのことで、出版業界に興味がある方にも向いている案件だと考えています。' . $zaito_preview_disclaimer,
            'salary_type' => '時給',
            'salary'    => '1200',
            'salary_max' => '',
            'salary_note' => '',
            'employment_type' => 'アルバイト・パート',
            'job_type'  => '完全在宅・シフト制',
            'job_days'  => '週3日〜',
            'job_target' => '未経験者歓迎',
        ),
        array(
            'company'   => '株式会社World Life Mapping',
            'title'     => '医療機関向けアプリのQAテスター',
            'category'  => 'その他',
            'content'   => '医療機関向けのWebアプリ・スマートフォンアプリの品質確認業務です。実際の掲載求人によると、テスト手順やチェックリストに沿って操作し、期待通りに動くか・不具合がないかを確認する業務で、プログラミング経験は不要とのことです。完全リモートで、平日週2〜3日程度・1日3〜5時間が目安、9時〜18時の間で柔軟に時間帯を選べます。土日祝は原則休務、複数名のチームでシフトをカバーする体制とのことです。筑波大学発のヘルスケア系スタートアップが手掛ける自社サービスの品質を支える業務のため、腰を据えて取り組みたい方に向いていると考えています。' . $zaito_preview_disclaimer,
            'salary_type' => '時給',
            'salary'    => '1350',
            'salary_max' => '1500',
            'salary_note' => '',
            'employment_type' => '業務委託',
            'job_type'  => '完全在宅・シフト制',
            'job_days'  => '週2日〜',
            'job_target' => '未経験者歓迎',
        ),
    );

    $links = array();
    foreach ( $previews as $p ) {
        $existing = get_posts( array(
            'post_type'      => 'job_listing',
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array( 'key' => '_zaito_preview', 'value' => '1' ),
                array( 'key' => '_company_name', 'value' => $p['company'] ),
            ),
        ) );
        if ( ! empty( $existing ) ) {
            // 既存の仮ページは内容を最新の説明文で更新する(URLは変えない)。
            $job_id = $existing[0]->ID;
            wp_update_post( array(
                'ID'           => $job_id,
                'post_content' => $p['content'],
            ) );
        } else {
            $job_id = wp_insert_post( array(
                'post_type'    => 'job_listing',
                'post_title'   => $p['title'],
                'post_content' => $p['content'],
                'post_status'  => 'publish',
            ) );
        }
        if ( ! $job_id || is_wp_error( $job_id ) ) {
            continue;
        }

        update_post_meta( $job_id, '_company_name', $p['company'] );
        update_post_meta( $job_id, '_job_category', $p['category'] );
        update_post_meta( $job_id, '_job_salary_type', $p['salary_type'] );
        update_post_meta( $job_id, '_job_salary', $p['salary'] );
        update_post_meta( $job_id, '_job_salary_max', $p['salary_max'] );
        update_post_meta( $job_id, '_job_salary_note', $p['salary_note'] );
        update_post_meta( $job_id, '_job_employment_type', $p['employment_type'] );
        update_post_meta( $job_id, '_job_type', $p['job_type'] );
        update_post_meta( $job_id, '_job_days', $p['job_days'] );
        update_post_meta( $job_id, '_job_target', $p['job_target'] );
        update_post_meta( $job_id, '_zaito_preview', '1' );

        $links[] = $p['company'] . ': ' . get_permalink( $job_id );
    }

    wp_die( implode( '<br>', array_map( 'esc_html', $links ) ) );
}
add_action( 'admin_post_zaito_seed_preview_jobs', 'zaito_seed_preview_jobs' );

/**
 * /wp-admin/admin-post.php?action=zaito_fix_author_display_name にアクセスすると、
 * ユーザーnicename「torii-jun1020gmail-com」の表示名(display_name)を安全な値に変更する。
 *
 * このアカウントは著者アーカイブページ(/author/torii-jun1020gmail-com/)や
 * ページタイトルに本名「鳥居潤」がそのまま公開表示されてしまっていた
 * (WP標準サイトマップ wp-sitemap-users-1.xml にも同URLが掲載され、検索エンジンに
 * インデックスされうる状態だった)。運営者の実名を公開しない方針のため修正する。
 * 一度実行すれば十分な一回限りの修正アクション。
 */
function zaito_fix_author_display_name() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'この操作には投稿権限が必要です。' );
    }

    $target_user = get_user_by( 'slug', 'torii-jun1020gmail-com' );
    if ( ! $target_user ) {
        wp_die( '対象ユーザーが見つかりませんでした(すでに修正済みか、slugが異なります)。' );
    }

    wp_update_user( array(
        'ID'           => $target_user->ID,
        'display_name' => 'ZAITO運営事務局',
    ) );

    wp_die( '修正しました。ユーザーID: ' . esc_html( $target_user->ID ) . ' の表示名を「ZAITO運営事務局」に変更しました。' );
}
add_action( 'admin_post_zaito_fix_author_display_name', 'zaito_fix_author_display_name' );
