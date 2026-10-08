<?php
/**
 * まとめ求人（/remote/）の一覧・詳細ページで共通の <head>・ヘッダー・フッター。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'zaito_remote_head' ) ) {
    function zaito_remote_head( $title, $description, $canonical, $json_ld = null ) {
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?php echo esc_html( $title ); ?></title>
<meta name="description" content="<?php echo esc_attr( $description ); ?>">
<link rel="canonical" href="<?php echo esc_url( $canonical ); ?>">
<meta property="og:site_name" content="zaito">
<meta property="og:type" content="website">
<meta property="og:locale" content="ja_JP">
<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
<meta property="og:url" content="<?php echo esc_url( $canonical ); ?>">
<meta property="og:image" content="<?php echo esc_url( get_template_directory_uri() . '/lp/ogp.png' ); ?>">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/lp/favicon.svg' ); ?>" type="image/svg+xml">
<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/lp/favicon-32.png' ); ?>" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="<?php echo esc_url( get_template_directory_uri() . '/lp/apple-touch-icon.png' ); ?>">
<meta name="theme-color" content="#ffffff">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700;900&amp;family=Outfit:wght@500;600;700&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0&amp;display=block" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_url( zaito_lp_asset( 'tokens.css' ) ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( zaito_lp_asset( 'remote.css' ) ); ?>">
<?php if ( $json_ld ) : ?>
<script type="application/ld+json"><?php echo wp_json_encode( $json_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?></script>
<?php endif; ?>
<?php echo zaito_ga4_snippet(); // 測定IDは形式チェック済み ?>
</head>
        <?php
    }
}

if ( ! function_exists( 'zaito_remote_header' ) ) {
    function zaito_remote_header() {
        ?>
<header class="hd"><div class="wrap">
  <a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="zaito トップへ">za<span>i</span>to</a>
  <nav aria-label="メインナビゲーション">
    <a class="tx" href="<?php echo esc_url( zaito_remote_url() ); ?>">求人を探す</a>
    <a class="tx" href="<?php echo esc_url( home_url( '/for-companies/' ) ); ?>">企業の方</a>
    <a class="btn btn-p" href="<?php echo esc_url( home_url( '/for-companies/#contact' ) ); ?>">無料で求人掲載</a>
  </nav>
</div></header>
        <?php
    }
}

if ( ! function_exists( 'zaito_remote_footer' ) ) {
    function zaito_remote_footer() {
        ?>
<footer class="ft"><div class="wrap">
  <div class="ft-top"><p>出社なしの仕事だけを<br>集めた求人サイト</p>
    <nav aria-label="フッターナビゲーション"><a href="<?php echo esc_url( zaito_remote_url() ); ?>">求人を探す</a><a href="<?php echo esc_url( home_url( '/for-companies/' ) ); ?>">企業の方へ</a><a href="<?php echo esc_url( home_url( '/for-companies/#contact' ) ); ?>">掲載・削除のご依頼</a><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">利用規約</a><a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシーポリシー</a></nav></div>
  <div class="ft-b"><span>運営：zaito 運営事務局</span><small>Copyright © zaito</small></div>
  <div class="logo ft-big" aria-hidden="true">za<span>i</span>to</div>
</div></footer>
        <?php
    }
}
