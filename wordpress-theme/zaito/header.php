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

    <button type="button" class="nav-toggle" aria-label="メニューを開く" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>

    <div class="header-actions">
      <?php if ( ! is_user_logged_in() ) : ?>
        <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="btn btn-outline">ログイン</a>
        <a href="<?php echo esc_url( home_url( '/register/' ) ); ?>" class="btn btn-solid">無料で登録</a>
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
