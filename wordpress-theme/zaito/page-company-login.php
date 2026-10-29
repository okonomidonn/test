<?php
/**
 * 企業のログイン（/company-login/）。送信先は WordPress の wp-login.php（失敗したら zaito_login_failed でここへ戻る）。
 */
require_once get_template_directory() . '/lp/biz-parts.php';

if ( is_user_logged_in() && zaito_is_company() ) {
    wp_safe_redirect( home_url( '/company/' ) );
    exit;
}
$zaito_to = ! empty( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : home_url( '/company/' );
if ( 0 !== strpos( $zaito_to, home_url() ) ) {
    $zaito_to = home_url( '/company/' );
}
zaito_biz_head( '企業ログイン' );
?>
<body>
<?php zaito_remote_header( 'company' ); ?>
<main class="auth">
  <h1>企業ログイン</h1>
  <p class="lead">求人の管理と、応募者とのメッセージはこちらから。</p>

  <?php if ( isset( $_GET['login'] ) && 'failed' === $_GET['login'] ) : ?>
    <div class="note n-err" role="alert"><span class="ms" aria-hidden="true">warning</span><div><b>ログインできませんでした</b><p>メールアドレスかパスワードが違います。</p></div></div>
  <?php endif; ?>

  <?php if ( function_exists( 'zaito_google_login_is_configured' ) && zaito_google_login_is_configured() ) : ?>
    <a class="btn btn-g btn-lg" href="<?php echo esc_url( zaito_google_login_url( $zaito_to, 'zaito_company' ) ); ?>">Googleでログイン</a>
    <div class="or">または</div>
  <?php endif; ?>

  <form class="form" method="post" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>">
    <input type="hidden" name="redirect_to" value="<?php echo esc_url( $zaito_to ); ?>">
    <div class="fld"><label for="user_login">メールアドレス</label><input type="email" id="user_login" name="log" autocomplete="username" required></div>
    <div class="fld"><label for="user_pass">パスワード</label><input type="password" id="user_pass" name="pwd" autocomplete="current-password" required></div>
    <input type="hidden" name="rememberme" value="forever">
    <button type="submit" class="btn btn-p btn-lg">ログイン</button>
  </form>
  <p class="alt"><a href="<?php echo esc_url( home_url( '/forgot-password/' ) ); ?>">パスワードを忘れた方</a></p>
  <p class="alt">アカウントをお持ちでない企業の方は<br><a href="<?php echo esc_url( home_url( '/company-register/' ) ); ?>"><b>無料で企業アカウントを作る</b></a></p>
</main>
<?php zaito_remote_footer(); ?>
</body>
</html>
