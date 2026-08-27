<?php
if ( ! is_user_logged_in() ) {
    wp_redirect( home_url( '/company-login/' ) );
    exit;
}
$current_user = wp_get_current_user();
if ( ! in_array( 'zaito_company', $current_user->roles, true ) ) {
    wp_redirect( home_url( '/' ) );
    exit;
}

$profile_errors = zaito_get_form_errors_from_token();
$profile_saved = isset( $_GET['saved'] ) && $_GET['saved'] === '1';

$company_name = get_user_meta( $current_user->ID, 'company_name', true );
$company_phone = get_user_meta( $current_user->ID, 'company_phone', true );

get_header();
?>

<main class="company-main">
  <div class="wrap">
    <div class="company-header">
      <div class="header-avatar" aria-hidden="true"><?php echo esc_html( mb_substr( $company_name ?: $current_user->display_name, 0, 1 ) ); ?></div>
      <div>
        <div class="header-eyebrow">COMPANY PROFILE</div>
        <h1>企業情報の編集</h1>
        <p>求職者に表示される企業情報です。</p>
      </div>
    </div>

    <div class="company-grid">
      <aside class="company-sidebar">
        <div class="menu-card">
          <h3>メニュー</h3>
          <a href="<?php echo esc_url( home_url( '/company/' ) ); ?>" class="menu-link">ダッシュボード</a>
          <a href="<?php echo esc_url( home_url( '/company-jobs/' ) ); ?>" class="menu-link">求人一覧</a>
          <a href="<?php echo esc_url( home_url( '/company-applicants/' ) ); ?>" class="menu-link">応募者一覧</a>
          <a href="<?php echo esc_url( home_url( '/chat/' ) ); ?>" class="menu-link">メッセージ</a>
          <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="menu-link logout">ログアウト</a>
        </div>
      </aside>

      <div class="company-content">
        <div class="content-section">
          <h2>企業情報</h2>

          <?php if ( $profile_saved ) : ?>
            <div class="auth-error" style="background:#f1fffb;color:#087f70;"><p>企業情報を保存しました</p></div>
          <?php endif; ?>

          <?php if ( ! empty( $profile_errors ) ) : ?>
            <div class="auth-error">
              <?php foreach ( $profile_errors as $error ) : ?>
                <p><?php echo esc_html( $error ); ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="apply-form">
            <input type="hidden" name="action" value="zaito_update_company_profile" />
            <?php wp_nonce_field( 'zaito_update_company_profile' ); ?>

            <div class="form-group">
              <label for="company_name">企業名</label>
              <input type="text" id="company_name" name="company_name" value="<?php echo esc_attr( $company_name ); ?>" required />
            </div>

            <div class="form-group">
              <label for="contact_person">ご担当者名</label>
              <input type="text" id="contact_person" name="contact_person" value="<?php echo esc_attr( $current_user->first_name ); ?>" required />
            </div>

            <div class="form-group">
              <label for="phone">電話番号</label>
              <input type="tel" id="phone" name="phone" value="<?php echo esc_attr( $company_phone ); ?>" required />
            </div>

            <div class="form-group">
              <label>メールアドレス</label>
              <input type="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" disabled />
              <small>メールアドレスの変更はサポートまでご連絡ください(info@zaito-work.com)。</small>
            </div>

            <button type="submit" class="btn btn-accent">保存する</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<?php get_footer(); ?>
