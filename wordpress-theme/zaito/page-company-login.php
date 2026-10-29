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
<?php zaito_biz_auth_header( 'login' ); ?>
<div class="biz-auth">
<main class="auth">
    <h1>ログイン</h1>
    <p class="lead">zaitoの採用管理画面にログインします。</p>

    <?php if ( isset( $_GET['reset'] ) ) : ?>
      <div class="note n-ok" role="status"><span class="ms" aria-hidden="true">check_circle</span><div><b>パスワードを変更しました</b><p>新しいパスワードでログインしてください。</p></div></div>
    <?php endif; ?>
    <?php if ( isset( $_GET['login'] ) && 'failed' === $_GET['login'] ) : ?>
      <div class="note n-err" role="alert"><span class="ms" aria-hidden="true">warning</span><div><b>ログインできませんでした</b><p>メールアドレスまたはパスワードが正しくありません。</p></div></div>
    <?php endif; ?>

    <?php if ( function_exists( 'zaito_google_login_is_configured' ) && zaito_google_login_is_configured() ) : ?>
      <a class="btn btn-g btn-lg" href="<?php echo esc_url( zaito_google_login_url( $zaito_to, 'zaito_company' ) ); ?>">Googleでログイン</a>
      <div class="or">または</div>
    <?php endif; ?>

    <form class="form" method="post" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>">
      <input type="hidden" name="redirect_to" value="<?php echo esc_url( $zaito_to ); ?>">
      <?php // 運営の管理者はユーザー名でもログインするため、type="email" にはしない。 ?>
      <div class="fld"><label for="user_login">メールアドレス</label><input type="text" id="user_login" name="log" autocomplete="username" autocapitalize="off" spellcheck="false" inputmode="email" required></div>
      <div class="fld"><label for="user_pass">パスワード</label><input type="password" id="user_pass" name="pwd" autocomplete="current-password" required></div>
      <input type="hidden" name="rememberme" value="forever">
      <button type="submit" class="btn btn-p btn-lg">ログイン</button>
    </form>
    <a class="forgot" href="<?php echo esc_url( home_url( '/forgot-password/' ) ); ?>">パスワードをお忘れの方</a>

    <div class="first">
      <p><b style="color:var(--z-navy)">はじめてご利用の企業様</b><br>掲載は無料。費用は採用時の成果報酬のみです。</p>
      <a class="btn btn-g btn-lg" href="<?php echo esc_url( home_url( '/company-register/' ) ); ?>">企業アカウントを作成する</a>
    </div>
</main>
</div>
<?php zaito_biz_auth_footer(); ?>
</body>
</html>
