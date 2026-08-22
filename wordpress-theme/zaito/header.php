<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
  <div class="wrap">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo">
      za<span>it</span>o
    </a>

    <nav class="site-nav">
      <a href="<?php echo esc_url( home_url( '/jobs/' ) ); ?>">求人を探す</a>
      <a href="#feature">zaitoとは</a>
    </nav>

    <div class="header-actions">
      <?php if ( ! is_user_logged_in() ) : ?>
        <a href="<?php echo esc_url( wp_login_url() ); ?>" class="btn btn-outline">ログイン</a>
        <a href="<?php echo esc_url( wp_registration_url() ); ?>" class="btn btn-solid">無料で登録</a>
      <?php else : ?>
        <a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>" class="header-mypage">マイページ</a>
        <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="btn btn-outline">ログアウト</a>
      <?php endif; ?>
    </div>
  </div>
</header>
