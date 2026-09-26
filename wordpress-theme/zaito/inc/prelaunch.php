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
    $html = str_replace(
        '<body>',
        '<body><noscript><p style="padding:24px;font-family:sans-serif">zaitoは大学生・若手向けの完全在宅求人サービスです。ページを表示するにはJavaScriptを有効にしてください。お問い合わせ: info@zaito-work.com</p></noscript>',
        $html
    );

    status_header( 200 );
    header( 'Content-Type: text/html; charset=UTF-8' );
    echo $html; // LPは運営が用意した固定のHTML
}
