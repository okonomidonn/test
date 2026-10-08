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
        'supports'     => array( 'title', 'editor', 'custom-fields', 'thumbnail' ),
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
    $version = '11';
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
    wp_die( implode( '<br>', array_map( 'esc_html', zaito_upsert_preview_jobs() ) ) );
}
add_action( 'admin_post_zaito_seed_preview_jobs', 'zaito_seed_preview_jobs' );

/**
 * 仮ページを作成し、「企業名: URL」の一覧を返す。作成済みの仮ページは、
 * 管理画面で編集された内容を上書きしないようそのままにする。
 */
function zaito_upsert_preview_jobs() {
    $zaito_preview_disclaimer = "\n\n---\n※この説明文は、公開されている求人情報をもとにZAITO運営事務局が作成した仮の文章です。貴社が実際に書かれた文章ではありません。内容に誤りや修正したい点がございましたら、正式掲載前にご指摘ください。";

    $previews = array(
        array(
            'company'   => '株式会社PRIDE',
            'slug'      => 'pride-video-sns',
            'source_url' => 'https://en-gage.net/pridecompany0409_saiyo/',
            'title'     => '動画編集・SNS運用スタッフ',
            'category'  => '動画編集',
            'rev'       => '2026-09-27',
            'content'   => '動画編集と、InstagramなどのSNSアカウント運用のサポートをお任せします。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・動画編集(カット編集・テロップ入れなど)' . "\n" . '・InstagramなどSNSアカウントの運用サポート' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・完全在宅・シフト制' . "\n" . '・9:00〜18:00/10:00〜19:00/11:00〜20:00から選べます' . "\n" . '・週3日〜、実働6時間〜' . "\n" . '' . "\n" . '■ 待遇' . "\n" . '・交通費規定支給' . "\n" . '・インセンティブ制度、昇給制度あり' . "\n" . '' . "\n" . '■ 応募資格' . "\n" . '・学歴・経験不問。学生・フリーターも歓迎' . "\n" . '・Word・Excel・PowerPointが使えると望ましい(研修でサポートします)' . $zaito_preview_disclaimer,
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
            'slug'      => 'oneworld-data-entry',
            'title'     => 'データ入力スタッフ',
            'category'  => '事務・データ入力',
            'rev'       => '2026-09-27',
            'content'   => 'パソコンまたはスマートフォンでの文字入力を中心とした、データ入力の仕事です。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・指定されたルールに沿ったデータ入力' . "\n" . '・簡単な報告・連絡' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・完全在宅' . "\n" . '・週1日からOK、シフト自由(平日のみ・土日のみも相談できます)' . "\n" . '' . "\n" . '■ 応募資格' . "\n" . '・特別な資格は不要です' . "\n" . '・未経験・ブランクのある方、副業の方も歓迎' . $zaito_preview_disclaimer,
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
            'slug'      => 'komoriku-data-entry',
            'title'     => 'データ入力・データ収集スタッフ',
            'category'  => '事務・データ入力',
            'rev'       => '2026-09-27',
            'content'   => 'パソコンを使ったデータ入力・データ収集の仕事です。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・データ入力' . "\n" . '・データ収集' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・完全在宅' . "\n" . '・勤務時間 9:00〜14:00(休憩30分)' . "\n" . '・週1日から勤務できます。変形労働時間制のため、体調に合わせて休日を調整しやすい環境です' . "\n" . '' . "\n" . '■ 応募資格' . "\n" . '・パソコンを持っていて、基本操作ができる方' . "\n" . '・未経験・初心者歓迎、学歴・年齢不問' . "\n" . '・障がいや難病・特定疾患をお持ちの方、通院中の方、人とのコミュニケーションに不安がある方も対象です' . "\n" . '・全国から応募できます(一部未対応エリアあり)' . $zaito_preview_disclaimer,
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
            'slug'      => 'honeybox-home-work',
            'title'     => '内職スタッフ(軽作業)',
            'category'  => 'その他',
            'rev'       => '2026-09-27',
            'content'   => '自宅でできる軽作業の内職です。巾着の縫製作業か、ギフト箱のセット作業のどちらかを選べます。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・巾着の縫製作業:ミシンでの製作、糸処理、検品' . "\n" . '・ギフト箱のセット作業:箱の組み立て、薄紙・緩衝材のセット、検品' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・完全在宅。材料や梱包資材はご自宅へお届けします' . "\n" . '・好きな時間に作業できます' . "\n" . '' . "\n" . '■ 報酬' . "\n" . '・出来高制(作業内容・個数によります)' . "\n" . '' . "\n" . '■ こんな方を歓迎します' . "\n" . '・未経験の方' . "\n" . '・主婦(夫)の方、副業・扶養内で働きたい方' . $zaito_preview_disclaimer,
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
            'slug'      => 'placole-wedding-advisor',
            'source_url' => 'https://en-gage.net/pla-cole-dressy/',
            'title'     => '在宅ウェディングチャットアドバイザー',
            'category'  => 'カスタマーサポート',
            'rev'       => '2026-09-27',
            'content'   => 'ウェディング関連の花嫁サポート業務を、完全在宅でお任せします。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・電話対応' . "\n" . '・予約調整' . "\n" . '・事務サポート、データ入力' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・完全在宅' . "\n" . '・1日4時間から勤務できます' . "\n" . '・シフト制:早番 10:00〜14:00/遅番 14:00〜18:00' . "\n" . '' . "\n" . '■ 待遇' . "\n" . '・実績に応じた昇給あり' . "\n" . '' . "\n" . '■ 応募について' . "\n" . '・パソコンとWi-Fi環境があれば始められます' . "\n" . '・Web面接(スマートフォン対応)で選考します' . $zaito_preview_disclaimer,
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
            'slug'      => 'quasar-data-assistant',
            'title'     => 'データ入力・編集アシスタント',
            'category'  => '事務・データ入力',
            'rev'       => '2026-09-27',
            'content'   => '出版業務を支援するアシスタントの仕事です。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・Excel・Googleスプレッドシートを使ったリスト作成' . "\n" . '・インターネットでの企業・著者・書店などの情報収集' . "\n" . '・データの入力・整理・更新' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・フルリモート' . "\n" . '・週3日以上、1日3時間以上' . "\n" . '・土日祝休み' . "\n" . '' . "\n" . '■ 応募資格' . "\n" . '・Excel・Wordの基本操作ができる方(未経験可)' . "\n" . '' . "\n" . '■ こんな方を歓迎します' . "\n" . '・SNSが好きな方' . "\n" . '・出版業界に興味がある方' . $zaito_preview_disclaimer,
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
            'slug'      => 'wlm-qa-tester',
            'source_url' => 'https://startupclass.co.jp/online/companies/1339/',
            'title'     => '医療機関向けアプリのQAテスター',
            'category'  => 'その他',
            'rev'       => '2026-09-27',
            'content'   => '医療機関向けのWebアプリ・スマートフォンアプリの品質確認(QAテスト)の仕事です。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・テスト手順やチェックリストに沿ったアプリの操作' . "\n" . '・期待通りに動くか、不具合がないかの確認' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・完全リモート' . "\n" . '・平日週2〜3日程度、1日3〜5時間が目安' . "\n" . '・9:00〜18:00の間で時間帯を選べます' . "\n" . '・土日祝は原則休み。複数名のチームでシフトをカバーします' . "\n" . '' . "\n" . '■ 応募資格' . "\n" . '・プログラミング経験は不要です' . "\n" . '' . "\n" . '■ 会社について' . "\n" . '筑波大学発のヘルスケア系スタートアップで、自社サービスの品質を支える業務です。' . $zaito_preview_disclaimer,
            'salary_type' => '時給',
            'salary'    => '1350',
            'salary_max' => '1500',
            'salary_note' => '',
            'employment_type' => '業務委託',
            'job_type'  => '完全在宅・シフト制',
            'job_days'  => '週2日〜',
            'job_target' => '未経験者歓迎',
        ),
        array(
            'company'   => '岸保産業株式会社',
            'sales'     => array( 'status' => 'waiting', 'date' => '2026-09-27', 'note' => '2026-09-27 問い合わせフォームから送信' ),
            'source_url' => 'https://www.wantedly.com/projects/2234809',
            'slug'      => 'kishiho-recruit-marketing',
            'title'     => '採用マーケティング・採用広報アシスタント(学生インターン)',
            'category'  => 'SNS運用・マーケティング',
            'rev'       => '2026-09-27',
            'content'   => '業務用厨房用品の専門商社である当社で、採用マーケティング・採用広報をお任せする学生インターンです。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・Wantedlyを活用した採用広報の企画・実行' . "\n" . '・採用市場のマーケティング・分析' . "\n" . '・面接のスケジュール調整' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・完全フルリモート。PC一台あれば、世界中どこからでも働けます' . "\n" . '・面談はオンラインで行います' . "\n" . '' . "\n" . '■ こんな方を歓迎します' . "\n" . '・人と話すことが好きな方' . "\n" . '・人事・採用の仕事に興味がある方' . "\n" . '・チャレンジ精神をもち、成長を楽しめる方' . "\n" . '・責任と誇りをもって仕事に取り組みたい方' . "\n" . '' . "\n" . '■ 会社について' . "\n" . '1945年創業。鍋・フライパン・包丁など10万点以上の業務用厨房用品を、全国の飲食店にお届けしています。本社は愛知県稲沢市、シンガポールにも現地法人があります。少数精鋭の組織のため、自分のアイデアを形にしやすい環境です。' . $zaito_preview_disclaimer,
            'salary_type' => '',
            'salary'    => '',
            'salary_max' => '',
            'salary_note' => 'ご相談',
            'employment_type' => '長期インターン',
            'job_type'  => '完全在宅・時間応相談',
            'job_days'  => '応相談',
            'job_target' => '学生歓迎、人事・採用に興味がある方、人と話すことが好きな方',
        ),
        array(
            'company'   => '学校法人角川ドワンゴ学園',
            'sales'     => array( 'status' => 'waiting', 'date' => '2026-09-27', 'note' => '2026-09-27 saiyo-edu@nnn.ac.jp（人事部 採用研修課）へメールで送信' ),
            'source_url' => 'https://01intern.com/job/5757.html',
            'slug'      => 'nhigh-net-course-ta',
            'title'     => 'N高グループ ネットコースTA(オンラインでの生徒サポート)',
            'category'  => '教育・学習サポート',
            'rev'       => '2026-09-27',
            'content'   => 'N高グループのネットコースで、生徒の学習や学校生活をオンラインでサポートするTA(ティーチングアシスタント)の仕事です。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・オンライン面談(15〜30分程度)での学習の進み具合の確認、進路のアドバイス' . "\n" . '・Slackなどでの生徒からの質問対応、ガイダンス欠席者への連絡' . "\n" . '・進学・就職書類の作成サポート' . "\n" . '・志望理由書の添削や面接練習などの受験サポート' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・完全リモート(自宅にWi-Fi環境があれば始められます)' . "\n" . '・平日13:00〜18:00の間で、週2日以上・1日5時間以上' . "\n" . '・半年以上続けられる方を募集しています' . "\n" . '' . "\n" . '■ 報酬' . "\n" . '時給1,300円〜(研修中は1,200円)' . "\n" . '' . "\n" . '■ 研修' . "\n" . '入社後、初回から8回まで研修があります。' . "\n" . '' . "\n" . '■ 対象' . "\n" . '大学生・大学院生(全学年)。自分の受験や進路選択の経験を活かせる仕事です。' . $zaito_preview_disclaimer,
            'salary_type' => '時給',
            'salary'    => '1300',
            'salary_max' => '',
            'salary_note' => '',
            'employment_type' => '長期インターン',
            'job_type'  => '完全在宅・平日13〜18時',
            'job_days'  => '週2日〜',
            'job_target' => '大学生・大学院生、全学年歓迎、研修あり',
        ),
        array(
            'company'   => '株式会社JX通信社',
            'sales'     => array( 'status' => 'waiting', 'date' => '2026-09-27', 'note' => '2026-09-27 問い合わせフォーム（種別: その他）から送信' ),
            'source_url' => 'https://01intern.com/job/6976.html',
            'slug'      => 'jxpress-news-editor',
            'title'     => 'ニュース速報の編集・配信スタッフ(長期インターン)',
            'category'  => 'ライティング・編集',
            'rev'       => '2026-09-27',
            'content'   => '速報特化型ニュースアプリ「NewsDigest」やリスク情報配信サービス「FASTALERT」を運営する当社で、ニュース速報の編集・配信に携わる長期インターンです。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・SNSなどに上がる一次情報を収集・分析し、現地の状況をいち早く把握する' . "\n" . '・ニュースアプリ向けの事実確認・編集・配信' . "\n" . '・志向やスキルに応じて、チーム運営や業務プロセスの改善もお任せします' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・リモート中心(1都3県在住の方は、初期研修期間の出社を推奨)' . "\n" . '・24時間365日のシフト制。1日4時間単位で、週3日以上・週12時間以上' . "\n" . '・コミュニケーションはSlackが中心、毎月の定例会あり' . "\n" . '' . "\n" . '■ 研修' . "\n" . '1〜2か月のマンツーマン研修があります。' . "\n" . '' . "\n" . '■ 応募条件' . "\n" . '・3〜4年間続けて勤務できる方' . "\n" . '・自宅に個室、27インチ以上の4Kディスプレイ(拡張用)、25Mbps以上のインターネット環境がある方' . "\n" . '・週12時間以上勤務できる方' . "\n" . '' . "\n" . '■ こんな方を歓迎します' . "\n" . '・大学1年生' . "\n" . '・報道・ニュース、特に災害報道や速報に関心がある方' . $zaito_preview_disclaimer,
            'salary_type' => '時給',
            'salary'    => '1500',
            'salary_max' => '',
            'salary_note' => '',
            'employment_type' => '長期インターン',
            'job_type'  => 'リモート中心・シフト制',
            'job_days'  => '週3日〜(週12時間以上)',
            'job_target' => '大学1・2年生歓迎、ニュース・報道に関心がある方、研修あり',
        ),
        array(
            'company'   => '株式会社トキハナ',
            'sales'     => array( 'status' => 'waiting', 'date' => '2026-09-27', 'note' => '2026-09-27 info@tokihana.co.jp へメールで送信' ),
            'slug'      => 'tokihana-sns',
            'source_url' => 'https://www.wantedly.com/projects/1309735',
            'title'     => 'ウエディングメディア「トキハナ」のSNS運用(学生インターン)',
            'category'  => 'SNS運用・マーケティング',
            'rev'       => '2026-09-27',
            'content'   => 'LINEで結婚式場探しができるサービス「トキハナ」の、SNS運用を担当する学生インターンです。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・2つのInstagramアカウント(フォロワー8.1万人・4万人)の企画・運用' . "\n" . '・投稿の数値分析と運用改善' . "\n" . '・プロモーションやイベント企画と連動した発信' . "\n" . '・TikTok・X(旧Twitter)・YouTubeショートの運用強化' . "\n" . '・動画制作' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・フルリモート(在宅勤務)' . "\n" . '・柔軟なシフトに対応。日中に授業がある学生も働けます' . "\n" . '・面談はオンラインで行います' . "\n" . '' . "\n" . '■ こんな方を歓迎します' . "\n" . '・ウエディングに興味がある方' . "\n" . '・SNS・Webマーケティングに関心がある方' . "\n" . '・ビジネスの経験を積みたい学生' . "\n" . '・裁量を持って挑戦したい方' . "\n" . '' . "\n" . '■ 会社について' . "\n" . '2016年設立。提携式場は700件、累計の流通総額は150億円。部署や役職を置かず、プロジェクト単位で働くチームです。' . $zaito_preview_disclaimer,
            'salary_type' => '',
            'salary'    => '',
            'salary_max' => '',
            'salary_note' => 'ご相談',
            'employment_type' => '長期インターン',
            'job_type'  => '完全在宅・シフト柔軟',
            'job_days'  => '応相談',
            'job_target' => '学生歓迎、SNSが好きな方、ウエディングに興味がある方',
        ),
        array(
            'company'   => '株式会社CLEARNOTE',
            'sales'     => array( 'status' => 'waiting', 'date' => '2026-09-27', 'note' => '2026-09-27 送信' ),
            'slug'      => 'clearnote-marketing',
            'source_url' => 'https://www.wantedly.com/projects/104947',
            'title'     => '学習アプリ「Clearnote」のマーケティング(学生インターン)',
            'category'  => 'SNS運用・マーケティング',
            'rev'       => '2026-09-27',
            'content'   => '月間約400万人が使う学習ノート共有アプリ「Clearnote」で、マーケティングを担当する学生インターンです。未経験の1年生も活躍しています。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・マーケティングプランの企画・実行・分析' . "\n" . '・Instagram・X(旧Twitter)などSNSの運用' . "\n" . '・アプリ内イベント、オフラインイベントの企画・運営' . "\n" . '・コンテンツマーケティング、バイラルマーケティング、インフルエンサーマーケティングの企画' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・リモートワーク(在宅勤務)' . "\n" . '・週3日以上。曜日・時間は相談して決めます(土日もOK)' . "\n" . '・教育の流れやマネジメントの体制が整っているので、未経験から始められます' . "\n" . '' . "\n" . '■ 応募資格' . "\n" . '・大学生(長く続けられる1・2年生を歓迎)' . "\n" . '' . "\n" . '■ こんな方を歓迎します' . "\n" . '・教育サービス・EdTechに興味がある方' . "\n" . '・マーケティングに興味がある方' . "\n" . '・塾講師の経験がある方' . "\n" . '' . "\n" . '■ 会社について' . "\n" . '日本・タイ・台湾などアジアを中心に展開する学習プラットフォームです。EdTechスタートアップの世界大会で優勝した実績があります。' . $zaito_preview_disclaimer,
            'salary_type' => '',
            'salary'    => '',
            'salary_max' => '',
            'salary_note' => '有給(金額はご相談)',
            'employment_type' => '長期インターン',
            'job_type'  => '完全在宅・曜日時間は相談',
            'job_days'  => '週3日〜',
            'job_target' => '大学1・2年生歓迎、未経験OK、土日OK',
        ),
        array(
            'company'   => '株式会社DONUTS',
            'sales'     => array( 'status' => 'waiting', 'date' => '2026-09-27', 'note' => '2026-09-27 問い合わせフォーム（人事・採用・人材紹介について）から送信' ),
            'slug'      => 'donuts-ray-writer',
            'source_url' => 'https://www.in-fra.jp/long-internships/preview/11734',
            'title'     => '女性向けメディア「Ray」の記事制作スタッフ(学生限定)',
            'category'  => 'ライティング・編集',
            'rev'       => '2026-09-27',
            'content'   => '女性向けWebメディア「Ray」で、記事の制作を担当する学生スタッフです。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・雑学・漫画・エンタメなどのジャンルで、編集部が指定するテーマ・構成・制作ルールに沿った記事の作成' . "\n" . '・情報の収集・整理、文章の作成から入稿まで' . "\n" . '・複数の記事をスピーディーに制作します' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・フルリモート' . "\n" . '・10:00〜19:00の間で、週2日以上・1日3時間以上(土日も可)' . "\n" . '・最低6か月から' . "\n" . '' . "\n" . '■ 報酬' . "\n" . '・成果報酬制:1記事300〜1,200円(税抜)' . "\n" . '・交通費の支給はありません' . "\n" . '' . "\n" . '■ 応募資格' . "\n" . '・学生' . "\n" . '・基本的なPC操作・リサーチができる方' . "\n" . '・選考で記事作成テストを行う場合があります' . "\n" . '' . "\n" . '■ こんな方を歓迎します' . "\n" . '・納期を守り、コツコツ続けられる方' . "\n" . '・日常的にSNSを使っている方、ブログを書いたことがある方' . "\n" . '・ファッション・コスメ・トレンドが好きな方' . "\n" . '' . "\n" . '■ 会社について' . "\n" . 'クラウドサービス「ジョブカン」、動画・ライブ配信「ミクチャ」、メディア「Ray」「Zipper」などを手がけています。' . $zaito_preview_disclaimer,
            'salary_type' => '1記事',
            'salary'    => '300',
            'salary_max' => '1200',
            'salary_note' => '',
            'employment_type' => '学生スタッフ',
            'job_type'  => '完全在宅・10〜19時で自由',
            'job_days'  => '週2日〜',
            'job_target' => '学生限定、ファッション・トレンドが好きな方',
        ),
        array(
            'company'   => '株式会社アガルート',
            'sales'     => array( 'status' => 'waiting', 'date' => '2026-09-27', 'note' => '2026-09-27 問い合わせフォームから送信' ),
            'slug'      => 'agaroot-learning-coach',
            'source_url' => 'https://www.wantedly.com/projects/2262541',
            'title'     => '中高生のオンライン学習コーチ(学生インターン・アルバイト)',
            'category'  => '教育・学習サポート',
            'rev'       => '2026-09-27',
            'content'   => '難関資格のオンライン予備校「アガルートアカデミー」などを運営する当社で、中学生・高校生の学習をオンラインで支えるコーチングスタッフです。学生の方を歓迎しています。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・中学生〜高校生の学習の進み具合の確認' . "\n" . '・特定の科目の個別指導' . "\n" . '・進路の相談' . "\n" . '・時期や業務量に応じて、「アガルートアカデミー」の運営業務もお任せします' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・フルリモート。すべての業務がオンラインで完結します' . "\n" . '・長時間の拘束はありません' . "\n" . '' . "\n" . '■ 選考' . "\n" . '・面談はオンラインで行います' . "\n" . '' . "\n" . '■ 会社について' . "\n" . '2013年設立。「教育×IT×法律」を軸に、アガルートアカデミー・アガルートメディカル・アガルートコーチングを運営しています。' . $zaito_preview_disclaimer,
            'salary_type' => '',
            'salary'    => '',
            'salary_max' => '',
            'salary_note' => 'ご相談',
            'employment_type' => 'インターン・アルバイト',
            'job_type'  => '完全在宅',
            'job_days'  => '応相談',
            'job_target' => '学生歓迎、オンラインで完結、長時間の拘束なし',
        ),
        array(
            'company'   => 'クラウドローン株式会社',
            'sales'     => array( 'status' => 'waiting', 'date' => '2026-09-27', 'note' => '2026-09-27 問い合わせフォーム（採用・人事等）から送信' ),
            'slug'      => 'crowdloan-sns',
            'source_url' => 'https://www.wantedly.com/projects/2155805',
            'title'     => 'fintechスタートアップのSNS運用(学生インターン)',
            'category'  => 'SNS運用・マーケティング',
            'rev'       => '2026-09-27',
            'content'   => '個人向けの銀行ローン比較サービス「クラウドローン」を運営するfintechスタートアップで、SNS運用とデジタルマーケティングを担当する学生インターンです。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・YouTube・Instagram・X(旧Twitter)・TikTokなどでの認知拡大' . "\n" . '・金融をわかりやすく伝える動画・画像コンテンツの企画・制作' . "\n" . '・インフルエンサーとのコラボ企画' . "\n" . '・Web広告(Google・Metaなど)の運用、SEO・コンテンツマーケティング' . "\n" . '・施策の効果測定、分析レポートの作成' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・フルリモート(全国どこからでもOK)' . "\n" . '・学業と両立しながら、続けて取り組めます' . "\n" . '' . "\n" . '■ 応募資格' . "\n" . '・新しいことに挑戦する意欲がある方' . "\n" . '・SNSやデジタルマーケティングに興味がある方' . "\n" . '' . "\n" . '■ こんな方を歓迎します' . "\n" . '・SNS運用の経験がある方(個人アカウントでもOK)' . "\n" . '・動画編集やデザインができる方' . "\n" . '・マーケティングを学んだことがある方、金融に興味がある方' . "\n" . '' . "\n" . '■ 会社について' . "\n" . '2018年設立。提携金融機関37行、ユーザー数15万人、累計申込総額1,500億円のサービスを運営しています。' . $zaito_preview_disclaimer,
            'salary_type' => '',
            'salary'    => '',
            'salary_max' => '',
            'salary_note' => 'ご相談',
            'employment_type' => '長期インターン',
            'job_type'  => '完全在宅・全国OK',
            'job_days'  => '応相談',
            'job_target' => '学生歓迎、SNSが好きな方、未経験OK',
        ),
        array(
            'company'   => '株式会社Driving force',
            'sales'     => array( 'status' => 'waiting', 'date' => '2026-09-27', 'note' => '2026-09-27 問い合わせフォーム（その他）から送信' ),
            'slug'      => 'drivingforce-youtube',
            'source_url' => 'https://c0mpus.com/interns/774',
            'title'     => 'YouTube動画の編集・企画(学生インターン)',
            'category'  => '動画編集',
            'rev'       => '2026-09-27',
            'content'   => '岡山の人事・組織コンサルティング会社で、YouTubeの動画編集と企画、Instagramの投稿づくりを担当する学生インターンです。企画の段階から代表と一緒に取り組みます。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・YouTube動画の編集' . "\n" . '・動画の企画の考案、声での動画出演' . "\n" . '・Instagramの投稿内容の作成' . "\n" . '・動画づくりの打ち合わせ(代表と一緒に)' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・フルリモート' . "\n" . '・平日9:00〜17:00の間で、代表と相談してスケジュールを決めます' . "\n" . '・自分でできる作業は、土日や時間外でもOK' . "\n" . '・月10時間以上、3か月以上' . "\n" . '' . "\n" . '■ 報酬' . "\n" . '・時給1,200円以上' . "\n" . '' . "\n" . '■ こんな方を歓迎します' . "\n" . '・SNSに興味がある方、新しい企画を考えられる方' . "\n" . '・YouTubeの編集スキルやSNS運用の経験がある方' . "\n" . '' . "\n" . '■ 会社について' . "\n" . '「人と組織を変革し、事業成果を創出する」をビジョンに、人事・経営コンサルティング、人材育成コーチング、企業研修を行っています。' . $zaito_preview_disclaimer,
            'salary_type' => '時給',
            'salary'    => '1200',
            'salary_max' => '',
            'salary_note' => '',
            'employment_type' => '長期インターン',
            'job_type'  => '完全在宅・時間は相談',
            'job_days'  => '月10時間〜',
            'job_target' => '全学年・全学部OK、SNSが好きな方',
        ),
        array(
            'company'   => 'yoake株式会社',
            'sales'     => array( 'status' => 'waiting', 'date' => '2026-09-27', 'note' => '2026-09-27 問い合わせフォームから送信' ),
            'slug'      => 'yoake-sns-marketing',
            'source_url' => 'https://www.wantedly.com/projects/2525043',
            'title'     => 'AI就活サービスのSNSマーケティング(学生インターン)',
            'category'  => 'SNS運用・マーケティング',
            'rev'       => '2026-09-27',
            'content'   => 'AIで就活を支援するLINEサービス「就活パイセン」(登録者2.3万人)を運営するスタートアップで、SNSを中心としたマーケティングを担当する学生インターンです。代表の直下で働きます。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・ユーザー・学生へのアンケートなどの調査' . "\n" . '・サービスの企画・改善案の提案' . "\n" . '・マーケティング施策の企画と実行' . "\n" . '・SNSを活用したプロモーション' . "\n" . '・コンテンツ・クリエイティブの制作' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・完全リモート、フルフレックス' . "\n" . '・3か月以上(相談可)' . "\n" . '・ミーティングと報告は必須です。PCが必要です' . "\n" . '' . "\n" . '■ こんな方を歓迎します' . "\n" . '・28・29卒の学生' . "\n" . '・粘り強く、主体的に動ける方' . "\n" . '・積極的にコミュニケーションが取れる方、クリエイティブな発想ができる方' . "\n" . '' . "\n" . '■ 会社について' . "\n" . '2024年創業。AIを活用した個人向けの自己実現支援と、LINEを使った企業向けのマーケティング支援を行っています。週1回の1on1や、書籍購入・セミナー参加費の補助があります。' . $zaito_preview_disclaimer,
            'salary_type' => '',
            'salary'    => '',
            'salary_max' => '',
            'salary_note' => 'ご相談',
            'employment_type' => '長期インターン',
            'job_type'  => '完全在宅・フルフレックス',
            'job_days'  => '応相談',
            'job_target' => '28・29卒歓迎、SNSが好きな方',
        ),
        array(
            'company'   => 'StockSun株式会社',
            'sales'     => array( 'status' => 'waiting', 'date' => '2026-09-27', 'note' => '2026-09-27 問い合わせフォーム（取材・提携・その他）から送信' ),
            'slug'      => 'stocksun-marketing-assistant',
            'source_url' => 'https://www.wantedly.com/projects/2056107',
            'title'     => 'Webマーケ支援会社のマーケアシスタント(学生インターン)',
            'category'  => 'SNS運用・マーケティング',
            'rev'       => '2026-09-27',
            'content'   => 'Webマーケティング支援を行う会社で、採用ディレクターの補佐とマーケティングのアシスタントを担当する学生インターンです。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・生成AIを活用した資料・ホワイトペーパーの作成' . "\n" . '・クライアントの採用活動のサポート' . "\n" . '・ミーティングのアシスタント業務' . "\n" . '・希望に応じて、広告運用やSNSマーケティングにも関われます' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・完全フルリモート。地方在住でもOK' . "\n" . '・週5時間から相談できます' . "\n" . '・授業や就活と両立できます' . "\n" . '' . "\n" . '■ 対象' . "\n" . '・大学生・大学院生(既卒でインターンを希望する方も可)' . "\n" . '' . "\n" . '■ 会社について' . "\n" . '2017年設立。Webマーケティング支援、BPO、教育プログラム、キャリア支援を行っています。' . $zaito_preview_disclaimer,
            'salary_type' => '',
            'salary'    => '',
            'salary_max' => '',
            'salary_note' => 'ご相談',
            'employment_type' => '長期インターン',
            'job_type'  => '完全在宅・地方OK',
            'job_days'  => '週5時間〜',
            'job_target' => '大学生・大学院生、地方在住OK、授業と両立',
        ),
        array(
            'company'   => 'R3corporation株式会社',
            'sales'     => array( 'status' => 'waiting', 'date' => '2026-09-27', 'note' => '2026-09-27 問い合わせフォーム（r3c.jp）から送信' ),
            'slug'      => 'r3corporation-remote',
            'source_url' => 'https://www.wantedly.com/projects/2386877',
            'title'     => 'マーケ・動画編集などを経験できるフルリモートインターン(学生)',
            'category'  => 'SNS運用・マーケティング',
            'rev'       => '2026-09-27',
            'content'   => '経営コンサルタントの代表のもとで、マーケティング・社会貢献事業・動画編集の各チームに分かれて活動する学生インターンです。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・マーケティングチーム:Wantedlyの運用代行、メディアの運営' . "\n" . '・社会貢献事業チーム:プログラミング研修の支援、クラウドファンディング' . "\n" . '・動画編集チーム:動画・画像の制作' . "\n" . '・はじめは記事の執筆が中心で、先輩インターン生がメンターにつきます' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・完全リモート。留学中や地方在住でも参加できます' . "\n" . '・決まった活動は月曜夜のミーティングだけ。それ以外は自由に時間を決められます' . "\n" . '' . "\n" . '■ 報酬' . "\n" . '・成果報酬型の有給インターン' . "\n" . '' . "\n" . '■ 対象' . "\n" . '・28卒の大学生' . $zaito_preview_disclaimer,
            'salary_type' => '',
            'salary'    => '',
            'salary_max' => '',
            'salary_note' => '成果報酬',
            'employment_type' => '長期インターン',
            'job_type'  => '完全在宅・時間自由',
            'job_days'  => '応相談',
            'job_target' => '28卒、留学中・地方在住OK',
        ),
        array(
            'company'   => '株式会社R',
            'sales'     => array( 'status' => 'waiting', 'date' => '2026-09-27', 'note' => '2026-09-27 info@aitem-english.jp へメールで送信（公式サイトのプライバシーポリシー第9条記載の窓口）' ),
            'slug'      => 'aitem-sns-marketing',
            'source_url' => 'https://www.wantedly.com/projects/624928',
            'title'     => '英会話スクール「Aitem」のWeb・SNSマーケティング',
            'category'  => 'SNS運用・マーケティング',
            'rev'       => '2026-09-27b',
            'content'   => 'SNSで人気の英会話スクール「Aitem」で、Web・SNSマーケティングを担当する仕事です。学生の方も応募できます。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・BtoCのPR・マーケティング' . "\n" . '・Web・SNSでの発信' . "\n" . '・英語講師とのコミュニケーション' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・リモートワーク可' . "\n" . '・週20時間以上、7か月以上' . "\n" . '' . "\n" . '■ 会社について' . "\n" . '2017年設立。受講生約1,000人の英会話スクール「Aitem」を運営しています。' . $zaito_preview_disclaimer,
            'salary_type' => '',
            'salary'    => '',
            'salary_max' => '',
            'salary_note' => 'ご相談',
            'employment_type' => '長期インターン',
            'job_type'  => 'リモート可',
            'job_days'  => '週20時間〜',
            'job_target' => '学生OK、英語に興味がある方',
        ),
        array(
            'company'   => '株式会社SCIEN',
            'sales'     => array( 'status' => 'waiting', 'date' => '2026-09-27', 'note' => '2026-09-27 問い合わせフォーム（インターン・採用に関するご質問）から送信' ),
            'slug'      => 'scien-ai-project',
            'source_url' => 'https://01intern.com/job/6943.html',
            'title'     => 'AIプロジェクトの推進サポート(学生インターン)',
            'category'  => 'AI関連・プロジェクト推進',
            'rev'       => '2026-09-27',
            'content'   => '東大松尾研発のAIスタートアップで、企業のAI・DXプロジェクトを顧客とエンジニアの間に立って前に進める、ビジネス側の学生インターンです。新規営業ではなく、受注済みのプロジェクトの推進を担当します。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・顧客との定例ミーティングへの参加、議事録の作成' . "\n" . '・論点・課題・要望の整理と、エンジニアへの共有' . "\n" . '・顧客向け資料の作成' . "\n" . '・プロジェクトの進捗・タスク管理' . "\n" . '・慣れてきたら、顧客へのヒアリングやミーティングの進行、プレゼンにも挑戦できます' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・フルリモート' . "\n" . '・週4日以上、週20時間以上' . "\n" . '・作業する時間帯は自分で決められます' . "\n" . '' . "\n" . '■ 報酬' . "\n" . '・時給1,500円〜10,000円(昇給の機会は年4回)' . "\n" . '' . "\n" . '■ 応募資格' . "\n" . '・AIの専門知識やビジネスの経験は不要です' . "\n" . '・コミュニケーション力・共感力・素直さを重視します' . "\n" . '・全学年の大学生・大学院生' . "\n" . '' . "\n" . '■ 会社について' . "\n" . '2024年設立。オーダーメイドのAI・システム開発、AIを活用した新規事業開発の支援、先端技術の研究開発、AI・DXコンサルティングを行っています。' . $zaito_preview_disclaimer,
            'salary_type' => '時給',
            'salary'    => '1500',
            'salary_max' => '10000',
            'salary_note' => '',
            'employment_type' => '長期インターン(業務委託)',
            'job_type'  => 'フルリモート・時間は自分で決められる',
            'job_days'  => '週4日・週20時間〜',
            'job_target' => '全学年、AIの知識は不要',
        ),
        array(
            'company'   => '樹林AI株式会社',
            'slug'      => 'jurin-ai-strategy',
            'source_url' => 'https://01intern.com/job/6811.html',
            'title'     => '経営陣直下の経営アシスタント(学生インターン)',
            'category'  => '事務・リサーチ',
            'rev'       => '2026-09-27',
            'content'   => 'AIエージェントを提供するスタートアップで、経営陣の直下で事業拡大のための戦略づくりと実行を支える、経営アシスタントの学生インターンです。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・営業会議などへの同席、論点の整理と資料の作成' . "\n" . '・市場調査・データ分析をもとにした戦略の提案' . "\n" . '・経営陣のスケジュール管理、商談の準備' . "\n" . '・顧客の声をもとにしたプロダクトの改善提案' . "\n" . '・画像・動画を使った情報発信' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・フルリモート' . "\n" . '・週3日以上、平日9:00〜21:00の間で1日3時間以上(週9〜15時間程度)' . "\n" . '・時間帯は希望に合わせて相談できます' . "\n" . '' . "\n" . '■ 報酬' . "\n" . '・時給1,300円〜3,000円' . "\n" . '' . "\n" . '■ 対象' . "\n" . '・全学年の大学生・大学院生(既卒の方も可)' . "\n" . '' . "\n" . '■ 会社について' . "\n" . '2024年設立。メール・電話・LINE・SNSなどの業務を自動化するAIエージェントを企業向けに提供しています。' . $zaito_preview_disclaimer,
            'salary_type' => '時給',
            'salary'    => '1300',
            'salary_max' => '3000',
            'salary_note' => '',
            'employment_type' => '長期インターン',
            'job_type'  => 'フルリモート・時間は相談',
            'job_days'  => '週3日・週9時間〜',
            'job_target' => '全学年、既卒も可',
        ),
        array(
            'company'   => '株式会社nori・nori',
            'slug'      => 'norinori-sns',
            'source_url' => 'https://www.wantedly.com/projects/2542638',
            'title'     => 'モビリティベンチャーのSNS運用(学生インターン)',
            'category'  => 'SNS運用・マーケティング',
            'rev'       => '2026-09-28',
            'content'   => '貸切バスのタイムシェアサービスを運営するモビリティベンチャーで、会社の発信を担うSNS運用の学生インターンです。代表や広報メンバーと一緒に、何を・誰に・どう伝えるかを考えて発信を形にします。' . "\n" . '' . "\n" . '■ 主な業務' . "\n" . '・各SNSの投稿の企画・作成・運用' . "\n" . '・サービスの活用シーンや導入事例の発信' . "\n" . '・投稿の反応(インプレッション・エンゲージメント)の分析と改善' . "\n" . '・発信のネタ探し、中長期の発信方針・コンテンツカレンダーづくり' . "\n" . '・最初は既存の投稿をもとに一緒に作るところから始め、慣れてきたら企画から任されます' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・フルリモート(本社は神奈川)' . "\n" . '・やり取りはSlack、定期的なミーティングはZoomで行います' . "\n" . '' . "\n" . '■ こんな方を歓迎します' . "\n" . '・SNSやコンテンツづくりが好きな方' . "\n" . '・なぜ伸びたのかを考えるのが好きな方' . "\n" . '・SNSを仕事として使うのは初めての方も歓迎' . "\n" . '' . "\n" . '■ 会社について' . "\n" . '2024年設立。保育園・病院・部活動などに向けて、複数の利用者で貸切バスをタイムシェアし、短時間でも安く使える移動サービスを提供しています。' . $zaito_preview_disclaimer,
            'salary_type' => '',
            'salary'    => '',
            'salary_max' => '',
            'salary_note' => 'ご相談',
            'employment_type' => '長期インターン',
            'job_type'  => 'フルリモート',
            'job_days'  => '応相談',
            'job_target' => '学生、SNSが好きな方、未経験OK',
        ),
        array(
            'company'   => '株式会社エニセンス',
            'slug'      => 'anysense-app-ai',
            'source_url' => 'https://www.wantedly.com/projects/1977408',
            'title'     => '自社アプリの開発・デザイン・生成AI導入(学生インターン)',
            'category'  => 'AI関連・アプリ開発',
            'rev'       => '2026-09-28',
            'content'   => '福岡のIT企業で、自社アプリの開発・UI/UXデザイン・生成AIの導入のいずれかを担当する学生インターンです。今いるインターン生も全員、未経験からスタートしています。' . "\n" . '' . "\n" . '■ 主な業務(ポジションごと)' . "\n" . '・アプリ開発:Flutterを使ったiOS/Androidアプリの開発、API連携、機能追加' . "\n" . '・UI/UXデザイン:Figmaを使った画面デザイン、ユーザーリサーチ、プロトタイプ制作' . "\n" . '・生成AI導入:生成AIの活用方法の調査・検証、プロンプト設計、社内向けAIツールの開発、AI動向のレポート作成' . "\n" . '' . "\n" . '■ 働き方' . "\n" . '・完全在宅で働けます。地方在住のメンバーも多数' . "\n" . '・週20時間以上、7か月以上続けられる方' . "\n" . '' . "\n" . '■ 対象' . "\n" . '・大学生(社会人の方も応募できます)' . "\n" . '・経験は問いません' . "\n" . '' . "\n" . '■ 会社について' . "\n" . '2007年に福岡で設立。Web制作の実績は8,000件以上で、自社アプリ「myApp」シリーズは累計350万ダウンロードです。東京と福岡にオフィスがあり、全国からリモートで働くメンバーがいます。' . $zaito_preview_disclaimer,
            'salary_type' => '',
            'salary'    => '',
            'salary_max' => '',
            'salary_note' => 'ご相談',
            'employment_type' => '長期インターン',
            'job_type'  => '完全在宅・地方OK',
            'job_days'  => '週20時間〜',
            'job_target' => '大学生、未経験OK、7か月以上',
        ),
    );

    // 営業対象から外した仮ページ（障がい者採用枠・就労継続支援の案件、営業お断りの企業、競合にあたる採用支援会社、
    // 完全在宅ではない一部リモートの求人）はゴミ箱へ移す。
    // ゴミ箱から30日以内なら管理画面の「求人 > ゴミ箱」から復元できる。
    foreach ( array( '一般社団法人ミライデザイン機構', '株式会社ZOS', '株式会社青春貢献', '株式会社TOKUMORI', '株式会社Lightblue', '株式会社プロパゲート', '株式会社SAKIYOMI', 'TOPVIEW JAPAN株式会社' ) as $removed_company ) {
        $removed = get_posts( array(
            'post_type'      => 'job_listing',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array( 'key' => '_zaito_preview', 'value' => '1' ),
                array( 'key' => '_company_name', 'value' => $removed_company ),
            ),
        ) );
        foreach ( $removed as $removed_id ) {
            wp_trash_post( $removed_id );
        }
    }

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
            // 作成済みの仮ページは、管理画面での修正を上書きしないようそのままにする。
            // ただし日本語タイトルから自動で作られた長いURL（%e6... を含むもの）は、営業メールに
            // 貼りやすい短い英字URLに変える（古いURLはWordPressが新しいURLへ転送する）。
            $job_id = $existing[0]->ID;
            // 'rev' を上げた仮ページだけは、本文を最新の内容に差し替える。
            if ( ! empty( $p['rev'] ) && get_post_meta( $job_id, '_zaito_preview_rev', true ) !== $p['rev'] ) {
                wp_update_post( array(
                    'ID'           => $job_id,
                    'post_content' => $p['content'],
                ) );
                update_post_meta( $job_id, '_zaito_preview_rev', $p['rev'] );
            }
            if ( ! empty( $p['slug'] ) && false !== strpos( $existing[0]->post_name, '%' ) ) {
                wp_update_post( array(
                    'ID'        => $job_id,
                    'post_name' => $p['slug'],
                ) );
            }
            // 営業状況は、営業管理画面でまだ何も入力されていないか「未送信」のままの場合だけ入れる
            // （営業管理画面で「保存する」を押すと、全行が「未送信」で保存されるため）。
            if ( ! empty( $p['sales'] ) && in_array( (string) get_post_meta( $job_id, '_zaito_sales_status', true ), array( '', 'todo' ), true ) ) {
                update_post_meta( $job_id, '_zaito_sales_status', $p['sales']['status'] );
                update_post_meta( $job_id, '_zaito_sales_date', $p['sales']['date'] );
                update_post_meta( $job_id, '_zaito_sales_note', $p['sales']['note'] );
            }
            // 元の求人ページのURLは、まだ入っていない場合だけ入れる（営業管理画面での入力を優先）。
            if ( ! empty( $p['source_url'] ) && '' === (string) get_post_meta( $job_id, '_zaito_source_url', true ) ) {
                update_post_meta( $job_id, '_zaito_source_url', $p['source_url'] );
            }
            $links[] = $p['company'] . ': ' . get_permalink( $job_id );
            continue;
        } else {
            $job_id = wp_insert_post( array(
                'post_type'    => 'job_listing',
                'post_title'   => $p['title'],
                'post_name'    => isset( $p['slug'] ) ? $p['slug'] : '',
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
        if ( ! empty( $p['rev'] ) ) {
            update_post_meta( $job_id, '_zaito_preview_rev', $p['rev'] );
        }
        if ( ! empty( $p['source_url'] ) ) {
            update_post_meta( $job_id, '_zaito_source_url', $p['source_url'] );
        }

        $links[] = $p['company'] . ': ' . get_permalink( $job_id );
    }

    return $links;
}

/**
 * 仮ページのデータ(zaito_upsert_preview_jobs)を追加したら、このバージョンを上げる。
 * 次に投稿権限のあるユーザーが管理画面を開いたとき、一度だけ自動で作成する。
 */
define( 'ZAITO_PREVIEW_JOBS_VERSION', '2026-09-28a' );

function zaito_maybe_upsert_preview_jobs() {
    if ( wp_doing_ajax() || ! current_user_can( 'edit_posts' ) ) {
        return;
    }
    if ( get_option( 'zaito_preview_jobs_version' ) === ZAITO_PREVIEW_JOBS_VERSION ) {
        return;
    }
    // 同時アクセスで二重に作られないよう、先にバージョンを記録する。
    update_option( 'zaito_preview_jobs_version', ZAITO_PREVIEW_JOBS_VERSION );
    zaito_upsert_preview_jobs();
}
add_action( 'admin_init', 'zaito_maybe_upsert_preview_jobs' );

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

// 正式ローンチ前のLP用フォーム受付（学生Waiting List・企業問い合わせ）
require_once get_template_directory() . '/inc/lp-leads.php';
// 正式ローンチ前モード（トップページのLP表示・求人サイト側ページの転送）
require_once get_template_directory() . '/inc/prelaunch.php';
// 管理画面「求人 > 営業用仮ページ」（仮ページのURL一覧）
require_once get_template_directory() . '/inc/sales-preview.php';
// まとめ求人（/remote/）: 公開されている完全在宅の求人を運営が確認してまとめたもの
require_once get_template_directory() . '/inc/remote-jobs.php';
