<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
  // LINE・メール・SNS等でURLを共有した際にプレビューカード(OGP)が出るようにする。
  // job_listingの単一ページはLoop開始前(the_post()実行前)にheader.phpが呼ばれるため、
  // グローバル$postに依存せずget_queried_object_id()経由で安全に値を取得する。
  $zaito_meta_desc = 'zaitoは「完全在宅」にこだわった在宅ワーク専門の求人サイトです。データ入力・SNS運用・カスタマーサポートなど、主婦・学生向けの在宅ワークを掲載しています。';
  if ( is_singular( 'job_listing' ) ) {
      $zaito_ogp_job_id = get_queried_object_id();
      $zaito_ogp_company = get_post_meta( $zaito_ogp_job_id, '_company_name', true );
      $zaito_ogp_content = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $zaito_ogp_job_id ) ), 50 );
      if ( $zaito_ogp_content ) {
          $zaito_meta_desc = ( $zaito_ogp_company ? $zaito_ogp_company . 'の求人。' : '' ) . $zaito_ogp_content;
      }
  }
  $zaito_ogp_url = ( is_ssl() ? 'https://' : 'http://' ) . ( $_SERVER['HTTP_HOST'] ?? '' ) . ( $_SERVER['REQUEST_URI'] ?? '' );
  ?>
  <meta name="description" content="<?php echo esc_attr( $zaito_meta_desc ); ?>">
  <meta property="og:site_name" content="ZAITO">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?php echo esc_attr( wp_get_document_title() ); ?>">
  <meta property="og:description" content="<?php echo esc_attr( $zaito_meta_desc ); ?>">
  <meta property="og:url" content="<?php echo esc_url( $zaito_ogp_url ); ?>">
  <meta name="twitter:card" content="summary">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
  <div class="wrap">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo">
      za<span>it</span>o
    </a>

    <?php
    // is_front_page() だけで判定すると、仮想ルート(/jobs/ 等を zaito_page クエリ変数経由で
    // page-*.php に直接includeする仕組み)配下のページでもWordPressのメインクエリが
    // トップページ相当に解決され、is_front_page()がtrueを返すことがある。
    // その結果「zaitoとは」のリンク先が本来のURLではなく素の「#feature」になり、
    // トップページ以外(例: 求人一覧ページ)でクリックしても何も起きなくなっていた。
    // zaito_page が設定されている(=仮想ルート配下にいる)場合は、必ず本来のトップページURLを使う。
    $zaito_is_true_home = is_front_page() && ! get_query_var( 'zaito_page' );
    ?>
    <nav class="site-nav">
      <a href="<?php echo esc_url( home_url( '/jobs/' ) ); ?>">求人を探す</a>
      <a href="<?php echo esc_url( $zaito_is_true_home ? '#feature' : home_url( '/#feature' ) ); ?>">zaitoとは</a>
      <a href="<?php echo esc_url( home_url( '/for-companies/' ) ); ?>">企業の方へ</a>
    </nav>

    <button type="button" class="nav-toggle" aria-label="メニューを開く" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>

    <div class="header-actions">
      <?php if ( ! is_user_logged_in() ) : ?>
        <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="btn btn-outline">ログイン</a>
        <a href="<?php echo esc_url( home_url( '/register/' ) ); ?>" class="btn btn-solid">無料で登録（1分）</a>
      <?php else :
        $zaito_current_user = wp_get_current_user();
        $zaito_mypage_url = in_array( 'zaito_company', $zaito_current_user->roles, true )
          ? home_url( '/company/' )
          : home_url( '/mypage/' );
      ?>
        <a href="<?php echo esc_url( $zaito_mypage_url ); ?>" class="header-mypage">マイページ</a>
        <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="btn btn-outline">ログアウト</a>
      <?php endif; ?>
    </div>
  </div>
</header>
