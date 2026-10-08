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

function zaito_ga4_snippet() {
    $id = zaito_ga4_id();
    if ( ! $id ) {
        return '';
    }
    return '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr( $id ) . '"></script>'
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
                array( 'loc' => zaito_remote_url() ),
                array( 'loc' => home_url( '/terms/' ) ),
                array( 'loc' => home_url( '/privacy/' ) ),
            );
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
        'register'      => '/#early',
        'interest'      => '/#early',
        'for-companies' => '/#companies',
    );
}

function zaito_prelaunch_template_redirect() {
    if ( ! ZAITO_PRELAUNCH ) {
        return;
    }

    if ( is_front_page() && ! get_query_var( 'zaito_page' ) && ! is_search() ) {
        // 運営者は /?classic=1 で従来の求人サイトのトップを確認できる。
        if ( ! ( isset( $_GET['classic'] ) && current_user_can( 'manage_options' ) ) ) {
            zaito_render_lp();
            exit;
        }
        return;
    }

    $page = get_query_var( 'zaito_page' );

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

function zaito_render_lp() {
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
<link rel="canonical" href="<?php echo esc_url( home_url( '/' ) ); ?>">
<meta property="og:site_name" content="zaito">
<meta property="og:type" content="website">
<meta property="og:locale" content="ja_JP">
<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $og_desc ); ?>">
<meta property="og:url" content="<?php echo esc_url( home_url( '/' ) ); ?>">
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
