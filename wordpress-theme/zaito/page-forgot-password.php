<?php
/**
 * パスワードの再設定メールを送る（/forgot-password/）。送信は functions.php の zaito_handle_forgot_password。
 */
require_once get_template_directory() . '/lp/biz-parts.php';
zaito_biz_head( 'パスワードの再設定' );
?>
<body>
<?php zaito_biz_auth_header( 'login' ); ?>
<div class="biz-auth">
<main class="auth">
  <h1>パスワードの再設定</h1>
  <?php if ( isset( $_GET['sent'] ) && '1' === $_GET['sent'] ) : ?>
    <div class="note n-ok"><span class="ms" aria-hidden="true">mark_email_read</span><div><b>メールを送りました</b><p>登録されているアドレスなら、再設定用のリンクが届きます。届かないときは迷惑メールのフォルダも見てください。</p></div></div>
  <?php else : ?>
    <p class="lead">登録したメールアドレスに、新しいパスワードを決めるためのリンクを送ります。</p>
    <?php zaito_biz_errors(); ?>
    <form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
      <input type="hidden" name="action" value="zaito_forgot_password">
      <?php wp_nonce_field( 'zaito_forgot_password' ); ?>
      <div class="fld"><label for="email">メールアドレス</label><input type="email" id="email" name="email" autocomplete="email" required></div>
      <button type="submit" class="btn btn-p btn-lg">再設定のリンクを送る</button>
    </form>
  <?php endif; ?>
  <p class="alt"><a href="<?php echo esc_url( home_url( '/company-login/' ) ); ?>">ログイン画面に戻る</a></p>
</main>
</div>
<?php zaito_biz_auth_footer(); ?>
</body>
</html>
