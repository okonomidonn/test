<?php
/**
 * 正式ローンチ前モード。
 *
 * - トップページ(/)で、Claude Designで作成したLP(lp/zaito-lp.dc.html)を表示する。
 * - ログインしていない訪問者が求人サイト側の一般向けページ(求人一覧・ワーカー登録など)を
 *   開いた場合はLPへ転送する。ページやコード自体は残しており、ローンチ時は
 *   ZAITO_PRELAUNCH を false にするだけで元の求人サイトに戻る。
 * - 転送しないもの: 営業用の仮ページ(_zaito_preview=1の求人)、利用規約・プライバシー
 *   ポリシー、ログイン・パスワード再設定などの認証系、企業向け管理画面、wp-admin。
 *   ログイン中のユーザー(運営・企業)は一切転送しない。
 */

if ( ! defined( 'ZAITO_PRELAUNCH' ) ) {
    define( 'ZAITO_PRELAUNCH', true );
}

/**
 * LPの「よくある質問」。HTMLに直接出力し（検索エンジンやAI検索が読めるように）、
 * 同じ内容を構造化データ（FAQPage）にも使う。
 */
/**
 * zaitoのSNSアカウント（フッターなどに表示）。
 */
function zaito_sns_accounts() {
    return array(
        array( 'X（旧Twitter）', 'https://x.com/zaito_work', '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M17.75 3h3.07l-6.7 7.66L22 21h-6.17l-4.83-6.32L5.47 21H2.4l7.17-8.2L2 3h6.33l4.37 5.78L17.75 3zm-1.08 16.17h1.7L7.4 4.74H5.58l11.09 14.43z"/></svg>' ),
        array( 'Instagram', 'https://www.instagram.com/zaito_work/', '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 2.2c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.7 3.7 0 0 1-1.38-.9 3.7 3.7 0 0 1-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.21 15.58 2.2 15.2 2.2 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.21 8.8 2.2 12 2.2zm0 4.86a4.94 4.94 0 1 0 0 9.88 4.94 4.94 0 0 0 0-9.88zm0 8.15a3.21 3.21 0 1 1 0-6.42 3.21 3.21 0 0 1 0 6.42zm5.13-9.5a1.15 1.15 0 1 0 0 2.3 1.15 1.15 0 0 0 0-2.3z"/></svg>' ),
    );
}

function zaito_sns_links_html() {
    $html = '<div class="sns" aria-label="zaitoのSNS">';
    foreach ( zaito_sns_accounts() as $a ) {
        $html .= '<a href="' . esc_url( $a[1] ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( 'zaitoの' . $a[0] ) . '">' . $a[2] . '</a>';
    }
    return $html . '</div>';
}

function zaito_lp_faqs() {
    return array(
        array( '掲載料金はいくらですか？', '現在、先行掲載企業については無料です。' ),
        array( 'どのような求人を掲載できますか？', '大学生・若手が応募可能で、原則としてリモートで完結する求人を対象としています。' ),
        array( '求職者は現在どのくらいいますか？', '現在は正式ローンチ前のため、先行登録ユーザーを募集しています。' ),
        array( '応募数は保証されますか？', '現段階では応募数の保証は行っていません。' ),
        array( '求人原稿がなくても大丈夫ですか？', '募集内容をお伺いし、zaito運営が求人原稿の作成をサポートします。' ),
        array( 'アルバイト以外も掲載できますか？', '大学生・若手が応募可能で、サービスの掲載基準に合う募集であれば相談可能です。' ),
    );
}

/**
 * FAQの開閉は <details> で行う（JavaScriptなしで動き、文章もHTMLに残る）。
 */
function zaito_lp_faq_html() {
    $html = '';
    foreach ( zaito_lp_faqs() as $faq ) {
        $html .= '<details class="zfaq"><summary><h3>' . esc_html( $faq[0] ) . '</h3><span class="zfaq-ic" aria-hidden="true">add</span></summary>'
            . '<div class="zfaq-a">' . esc_html( $faq[1] ) . '</div></details>';
    }
    return $html;
}

/**
 * 構造化データ（運営組織・サイト・よくある質問）。
 */
function zaito_lp_json_ld() {
    $faq = array();
    foreach ( zaito_lp_faqs() as $item ) {
        $faq[] = array(
            '@type'          => 'Question',
            'name'           => $item[0],
            'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $item[1] ),
        );
    }
    $data = array(
        '@context' => 'https://schema.org',
        '@graph'   => array(
            array(
                '@type'        => 'Organization',
                '@id'          => home_url( '/#organization' ),
                'name'         => 'zaito',
                'url'          => home_url( '/' ),
                'logo'         => get_template_directory_uri() . '/lp/apple-touch-icon.png',
                'email'        => 'info@zaito-work.com',
                'description'  => '大学生・若手向けの完全在宅求人サービス',
                'contactPoint' => array(
                    '@type'       => 'ContactPoint',
                    'contactType' => 'customer support',
                    'email'       => 'info@zaito-work.com',
                ),
            ),
            array(
                '@type'      => 'WebSite',
                '@id'        => home_url( '/#website' ),
                'name'       => 'zaito',
                'url'        => home_url( '/' ),
                'inLanguage' => 'ja',
                'publisher'  => array( '@id' => home_url( '/#organization' ) ),
            ),
            array(
                '@type'      => 'FAQPage',
                '@id'        => home_url( '/#faq' ),
                'mainEntity' => $faq,
            ),
        ),
    );
    return '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';
}

/**
 * Googleアナリティクス（GA4）。管理画面「設定 > 一般」の「GA4 測定ID」に入力されているときだけ出力する。
 */
if ( ! defined( 'ZAITO_GA4_DEFAULT_ID' ) ) {
    // zaito-work.com 用のGA4測定ID。管理画面で別のIDを保存した場合はそちらを使い、空欄で保存すると計測を止める。
    define( 'ZAITO_GA4_DEFAULT_ID', 'G-0X7SF35P0L' );
}

function zaito_ga4_id() {
    $id = strtoupper( trim( (string) get_option( 'zaito_ga4_id', ZAITO_GA4_DEFAULT_ID ) ) );
    return preg_match( '/^G-[A-Z0-9]{4,}$/', $id ) ? $id : '';
}

/**
 * Search Consoleの所有権確認用メタタグ（設定 > 一般 で入力した値）。
 * <head> に入れるものなので、GA4のタグと一緒に出力する。
 */
function zaito_gsc_meta() {
    $code = trim( (string) get_option( 'zaito_gsc_verify', '' ) );
    return preg_match( '/^[A-Za-z0-9_-]{10,100}$/', $code ) ? '<meta name="google-site-verification" content="' . esc_attr( $code ) . '">' : '';
}

function zaito_ga4_snippet() {
    $id = zaito_ga4_id();
    if ( ! $id ) {
        return zaito_gsc_meta();
    }
    return zaito_gsc_meta() . '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr( $id ) . '"></script>'
        . '<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config",' . wp_json_encode( $id ) . ');</script>';
}

function zaito_ga4_register_setting() {
    register_setting( 'general', 'zaito_ga4_id', array(
        'type'              => 'string',
        'sanitize_callback' => function ( $v ) {
            $v = strtoupper( trim( (string) $v ) );
            return preg_match( '/^G-[A-Z0-9]{4,}$/', $v ) ? $v : '';
        },
        'default'           => '',
    ) );
    add_settings_field( 'zaito_ga4_id', 'GA4 測定ID（zaito）', function () {
        echo '<input type="text" name="zaito_ga4_id" id="zaito_ga4_id" class="regular-text" placeholder="G-XXXXXXXXXX" value="' . esc_attr( get_option( 'zaito_ga4_id', ZAITO_GA4_DEFAULT_ID ) ) . '">';
        echo '<p class="description">Googleアナリティクスの測定ID（G-から始まる文字列）を入れると、LPと利用規約・プライバシーポリシーのページで計測を始めます。空欄にすると計測しません。</p>';
    }, 'general', 'default', array( 'label_for' => 'zaito_ga4_id' ) );

    register_setting( 'general', 'zaito_gsc_verify', array(
        'type'              => 'string',
        'sanitize_callback' => function ( $v ) {
            $v = trim( (string) $v );
            // メタタグをまるごと貼られた場合は content の値だけを取り出す。
            if ( preg_match( '/content=["\']([^"\']+)["\']/', $v, $m ) ) {
                $v = $m[1];
            }
            return preg_match( '/^[A-Za-z0-9_-]{10,100}$/', $v ) ? $v : '';
        },
        'default'           => '',
    ) );
    add_settings_field( 'zaito_gsc_verify', 'Search Console 確認コード（zaito）', function () {
        echo '<input type="text" name="zaito_gsc_verify" id="zaito_gsc_verify" class="regular-text" placeholder="<meta name=&quot;google-site-verification&quot; ...> をそのまま貼り付け" value="' . esc_attr( get_option( 'zaito_gsc_verify', '' ) ) . '">';
        echo '<p class="description">Search Consoleの「HTMLタグ」で表示されるメタタグを貼り付けて保存すると、全ページに入ります。</p>';
    }, 'general', 'default', array( 'label_for' => 'zaito_gsc_verify' ) );
}
add_action( 'admin_init', 'zaito_ga4_register_setting' );

/**
 * 公開前は、検索エンジンに渡すサイトマップを公開中のページ（トップ・利用規約・プライバシーポリシー）だけにする。
 * WordPressが自動で載せる投稿・固定ページ・カテゴリー・ユーザーのサイトマップは止める。
 */
function zaito_prelaunch_sitemap_providers( $provider, $name ) {
    if ( ZAITO_PRELAUNCH && in_array( $name, array( 'posts', 'taxonomies', 'users' ), true ) ) {
        return false;
    }
    return $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'zaito_prelaunch_sitemap_providers', 10, 2 );

if ( class_exists( 'WP_Sitemaps_Provider' ) && ! class_exists( 'Zaito_Prelaunch_Sitemap' ) ) {
    class Zaito_Prelaunch_Sitemap extends WP_Sitemaps_Provider {
        public function __construct() {
            $this->name        = 'zaito';
            $this->object_type = 'zaito';
        }

        public function get_url_list( $page_num, $object_subtype = '' ) {
            $urls = array(
                array( 'loc' => home_url( '/' ) ),
                array( 'loc' => home_url( '/for-companies/' ) ),
                array( 'loc' => home_url( '/terms/' ) ),
                array( 'loc' => home_url( '/privacy/' ) ),
            );
            // 条件別の一覧
            foreach ( array_keys( zaito_remote_active_conditions() ) as $cond ) {
                $urls[] = array( 'loc' => zaito_remote_condition_url( $cond ) );
            }
            // まとめ求人の詳細（募集中のもの）
            foreach ( zaito_remote_jobs() as $job ) {
                if ( empty( $job['closed'] ) ) {
                    $urls[] = array( 'loc' => zaito_remote_url( $job['slug'] ), 'lastmod' => $job['checked'] );
                }
            }
            return $urls;
        }

        public function get_max_num_pages( $object_subtype = '' ) {
            return 1;
        }
    }
}

function zaito_prelaunch_register_sitemap() {
    if ( ZAITO_PRELAUNCH && class_exists( 'Zaito_Prelaunch_Sitemap' ) && function_exists( 'wp_register_sitemap_provider' ) ) {
        wp_register_sitemap_provider( 'zaito', new Zaito_Prelaunch_Sitemap() );
    }
}
add_action( 'init', 'zaito_prelaunch_register_sitemap' );

/**
 * 公開前に残っている旧テーマのページ（サンプルページなど）は、検索結果に出さない。
 */
function zaito_prelaunch_noindex( $robots ) {
    if ( ZAITO_PRELAUNCH && ! is_admin() && ! is_front_page() ) {
        $robots['noindex']  = true;
        $robots['nofollow'] = true;
    }
    return $robots;
}
add_filter( 'wp_robots', 'zaito_prelaunch_noindex' );

/**
 * ログアウト状態でLPへ転送する求人サイト側のページ（バーチャルルート名 => 転送先）。
 */
function zaito_prelaunch_redirect_map() {
    return array(
        'jobs'          => '/',
        'register'      => '/',
        'interest'      => '/',
    );
}

function zaito_prelaunch_template_redirect() {
    if ( ! ZAITO_PRELAUNCH ) {
        return;
    }

    if ( is_front_page() && ! get_query_var( 'zaito_page' ) && ! is_search() ) {
        // 運営者は /?classic=1 で従来の求人サイトのトップを確認できる。
        if ( ! ( isset( $_GET['classic'] ) && current_user_can( 'manage_options' ) ) ) {
            // トップページは「まとめ求人」の一覧。
            status_header( 200 );
            header( 'Content-Type: text/html; charset=UTF-8' );
            include get_template_directory() . '/lp/remote-list.php';
            exit;
        }
        return;
    }

    $page = get_query_var( 'zaito_page' );

    // 企業向けの案内と掲載の問い合わせフォーム。
    if ( 'for-companies' === $page ) {
        status_header( 200 );
        header( 'Content-Type: text/html; charset=UTF-8' );
        include get_template_directory() . '/lp/companies.php';
        exit;
    }

    // 利用規約・プライバシーポリシーはLPからリンクしているため、LPと同じデザインで表示する。
    $legal = array(
        'terms'   => '利用規約',
        'privacy' => 'プライバシーポリシー',
    );
    if ( $page && isset( $legal[ $page ] ) ) {
        $zaito_legal_slug  = $page;
        $zaito_legal_title = $legal[ $page ];
        status_header( 200 );
        header( 'Content-Type: text/html; charset=UTF-8' );
        include get_template_directory() . '/lp/legal.php';
        exit;
    }

    // 営業用の仮ページは、企業に送る掲載イメージとしてLPと同じ新デザインで表示する。
    if ( is_singular( 'job_listing' ) && '1' === get_post_meta( get_queried_object_id(), '_zaito_preview', true ) ) {
        $zaito_job = get_queried_object();
        status_header( 200 );
        header( 'Content-Type: text/html; charset=UTF-8' );
        header( 'X-Robots-Tag: noindex, nofollow' );
        include get_template_directory() . '/lp/job-preview.php';
        exit;
    }

    if ( is_user_logged_in() ) {
        return;
    }

    $map = zaito_prelaunch_redirect_map();
    if ( $page && isset( $map[ $page ] ) ) {
        wp_safe_redirect( home_url( $map[ $page ] ), 302 );
        exit;
    }

    if ( is_singular( 'job_listing' ) && '1' !== get_post_meta( get_queried_object_id(), '_zaito_preview', true ) ) {
        wp_safe_redirect( home_url( '/' ), 302 );
        exit;
    }
}
// バーチャルルートの描画(priority 1)より先に判定する。
add_action( 'template_redirect', 'zaito_prelaunch_template_redirect', 0 );

/**
 * LPのURLに付けるバージョン文字列（ファイル更新時刻）。デプロイ後に古いJSが
 * キャッシュから使われ続けないようにする。
 */
function zaito_lp_asset( $file ) {
    $path = get_template_directory() . '/lp/' . $file;
    $ver  = file_exists( $path ) ? filemtime( $path ) : '1';
    return get_template_directory_uri() . '/lp/' . $file . '?ver=' . $ver;
}

function zaito_render_lp( $canonical = '' ) {
    $canonical = $canonical ? $canonical : home_url( '/' );
    $html = file_get_contents( get_template_directory() . '/lp/zaito-lp.dc.html' );
    if ( false === $html ) {
        return;
    }

    $title       = 'zaito | 大学生・若手向け完全在宅求人サービス';
    $description = '大学生・若手向けの完全在宅求人サービス「zaito」。SNS運用、動画編集、Webマーケティング、AI、オンライン事務など、自宅から働ける仕事を掲載。現在、先行登録・先行掲載企業を募集中。';
    $og_desc     = '大学生・若手向けの完全在宅求人サービス。現在、先行登録・先行掲載企業を募集中。';
    $config      = array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
    );

    ob_start();
    ?>
<title><?php echo esc_html( $title ); ?></title>
<meta name="description" content="<?php echo esc_attr( $description ); ?>">
<link rel="canonical" href="<?php echo esc_url( $canonical ); ?>">
<meta property="og:site_name" content="zaito">
<meta property="og:type" content="website">
<meta property="og:locale" content="ja_JP">
<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $og_desc ); ?>">
<meta property="og:url" content="<?php echo esc_url( $canonical ); ?>">
<meta property="og:image" content="<?php echo esc_url( get_template_directory_uri() . '/lp/ogp.png' ); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/lp/favicon.svg' ); ?>" type="image/svg+xml">
<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/lp/favicon-32.png' ); ?>" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="<?php echo esc_url( get_template_directory_uri() . '/lp/apple-touch-icon.png' ); ?>">
<meta name="theme-color" content="#ffffff">
<?php echo zaito_lp_json_ld(); // 内容はこの関数内でエンコード済み ?>
<?php echo zaito_ga4_snippet(); // 測定IDは形式チェック済み ?>
<style>
.zfaq{border-bottom:1px solid #E6E9F0}
.zfaq summary{list-style:none;min-height:72px;display:flex;align-items:center;gap:16px;padding:20px 0;cursor:pointer;color:#0B1530}
.zfaq summary::-webkit-details-marker{display:none}
.zfaq summary:hover{color:#3D5AFE}
.zfaq summary:focus-visible{outline:3px solid #3D5AFE;outline-offset:4px;border-radius:8px}
.zfaq h3{flex:1;margin:0;font-size:16px;font-weight:700;line-height:1.6;color:inherit}
.zfaq-ic{flex:none;width:32px;height:32px;border-radius:50%;background:#F4F5F8;display:flex;align-items:center;justify-content:center;font-family:'Material Symbols Rounded';font-size:20px;line-height:1;font-feature-settings:'liga';white-space:nowrap;color:#0B1530;transition:transform .25s}
.zfaq[open] .zfaq-ic{transform:rotate(45deg)}
.zfaq-a{padding:0 48px 24px 0;font-size:15px;line-height:1.95;color:#3A4563}
</style>
<script>
// LPは幅1440pxで見たときのバランスで作られているため、それより広い画面では
// ページ全体を拡大して左右の余白が広がりすぎないようにする。
(function () {
    var root = document.documentElement;
    function fit() {
        var w = window.innerWidth;
        root.style.zoom = w > 1440 ? String(Math.min(w / 1440, 1.5)) : '';
    }
    fit();
    window.addEventListener('resize', fit);
})();
</script>
<script>window.ZAITO_LP =<?php echo wp_json_encode( $config ); ?>; window.__IMAGE_SLOT_STATE_URL = <?php echo wp_json_encode( zaito_lp_asset( 'image-slots.state.json' ) ); ?>;</script>
<script src="<?php echo esc_url( zaito_lp_asset( 'vendor/react.production.min.js' ) ); ?>"></script>
<script src="<?php echo esc_url( zaito_lp_asset( 'vendor/react-dom.production.min.js' ) ); ?>"></script>
<script src="<?php echo esc_url( zaito_lp_asset( 'support.js' ) ); ?>"></script>
    <?php
    $head = ob_get_clean();

    $html = str_replace( '<script src="./support.js"></script>', $head, $html );
    $html = str_replace( '<script src="./image-slot.js"></script>', '<script src="' . esc_url( zaito_lp_asset( 'image-slot.js' ) ) . '"></script>', $html );
    $html = str_replace( '<html>', '<html lang="ja">', $html );
    $html = str_replace( '<!--ZAITO_FAQ-->', zaito_lp_faq_html(), $html );
    $html = str_replace(
        '<body>',
        '<body><noscript><p style="padding:24px;font-family:sans-serif">zaitoは大学生・若手向けの完全在宅求人サービスです。ページを表示するにはJavaScriptを有効にしてください。お問い合わせ: info@zaito-work.com</p></noscript>',
        $html
    );

    status_header( 200 );
    header( 'Content-Type: text/html; charset=UTF-8' );
    echo $html; // LPは運営が用意した固定のHTML
}
