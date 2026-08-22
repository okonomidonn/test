<?php
$registration_errors = zaito_get_form_errors_from_token();
$zaito_register_redirect_to = ! empty( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';
get_header();
?>

<main class="auth-main">
  <div class="auth-container">
    <div class="auth-card">
      <h1>ワーカー登録</h1>

      <?php if ( ! empty( $registration_errors ) ) : ?>
        <div class="auth-error">
          <?php foreach ( $registration_errors as $error ) : ?>
            <p><?php echo esc_html( $error ); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="auth-form">
        <input type="hidden" name="action" value="zaito_register_worker" />
        <?php if ( $zaito_register_redirect_to ) : ?>
          <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $zaito_register_redirect_to ); ?>" />
        <?php endif; ?>
        <?php wp_nonce_field( 'zaito_register_worker' ); ?>

        <div class="form-group">
          <label for="name">氏名</label>
          <input type="text" id="name" name="name" required />
        </div>

        <div class="form-group">
          <label for="email">メールアドレス</label>
          <input type="email" id="email" name="email" required />
        </div>

        <div class="form-group">
          <label for="password">パスワード</label>
          <input type="password" id="password" name="password" required />
          <small>8文字以上で設定してください</small>
        </div>

        <div class="form-group">
          <label for="password_confirm">パスワード（確認）</label>
          <input type="password" id="password_confirm" name="password_confirm" required />
        </div>

        <div class="form-group form-check">
          <label>
            <input type="checkbox" name="agree_terms" value="1" required />
            <a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>" target="_blank" rel="noopener">利用規約</a>と<a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>" target="_blank" rel="noopener">プライバシーポリシー</a>に同意する
          </label>
        </div>

        <button type="submit" class="btn btn-accent btn-block">登録</button>
      </form>

      <p class="auth-link">
        既にアカウントをお持ちの方は
        <a href="<?php echo esc_url( $zaito_register_redirect_to ? add_query_arg( 'redirect_to', rawurlencode( $zaito_register_redirect_to ), home_url( '/login/' ) ) : home_url( '/login/' ) ); ?>">こちらからログイン</a>
      </p>
    </div>
  </div>
</main>

<?php get_footer(); ?>
