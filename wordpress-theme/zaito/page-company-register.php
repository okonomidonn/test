<?php get_header(); ?>

<main class="auth-main">
  <div class="auth-container">
    <div class="auth-card">
      <h1>企業登録</h1>

      <?php
      $registration_errors = array();
      if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['action'] ) && $_POST['action'] === 'register_company' ) {
          $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
          $company_name = isset( $_POST['company_name'] ) ? sanitize_text_field( $_POST['company_name'] ) : '';
          $contact_person = isset( $_POST['contact_person'] ) ? sanitize_text_field( $_POST['contact_person'] ) : '';
          $phone = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
          $password = isset( $_POST['password'] ) ? $_POST['password'] : '';
          $password_confirm = isset( $_POST['password_confirm'] ) ? $_POST['password_confirm'] : '';

          if ( ! $email ) {
              $registration_errors[] = 'メールアドレスを入力してください';
          } elseif ( email_exists( $email ) ) {
              $registration_errors[] = 'このメールアドレスは既に登録されています';
          }

          if ( ! $company_name ) {
              $registration_errors[] = '企業名を入力してください';
          }

          if ( ! $contact_person ) {
              $registration_errors[] = 'ご担当者名を入力してください';
          }

          if ( ! $phone ) {
              $registration_errors[] = '電話番号を入力してください';
          }

          if ( ! $password || strlen( $password ) < 8 ) {
              $registration_errors[] = 'パスワードは8文字以上で入力してください';
          }

          if ( $password !== $password_confirm ) {
              $registration_errors[] = 'パスワードが一致しません';
          }

          if ( empty( $registration_errors ) ) {
              $user_id = wp_insert_user( array(
                  'user_email' => $email,
                  'user_login' => sanitize_user( $email ),
                  'user_pass' => $password,
                  'first_name' => $contact_person,
                  'role' => 'zaito_company',
              ) );

              if ( is_wp_error( $user_id ) ) {
                  $registration_errors[] = $user_id->get_error_message();
              } else {
                  update_user_meta( $user_id, 'company_name', $company_name );
                  update_user_meta( $user_id, 'company_phone', $phone );

                  wp_signon( array(
                      'user_login' => sanitize_user( $email ),
                      'user_password' => $password,
                      'remember' => false,
                  ) );
                  wp_redirect( home_url( '/company/' ) );
                  exit;
              }
          }
      }
      ?>

      <?php if ( ! empty( $registration_errors ) ) : ?>
        <div class="auth-error">
          <?php foreach ( $registration_errors as $error ) : ?>
            <p><?php echo esc_html( $error ); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="post" class="auth-form">
        <input type="hidden" name="action" value="register_company" />

        <div class="form-group">
          <label for="company_name">企業名</label>
          <input
            type="text"
            id="company_name"
            name="company_name"
            value="<?php echo isset( $_POST['company_name'] ) ? esc_attr( $_POST['company_name'] ) : ''; ?>"
            required
          />
        </div>

        <div class="form-group">
          <label for="contact_person">ご担当者名</label>
          <input
            type="text"
            id="contact_person"
            name="contact_person"
            value="<?php echo isset( $_POST['contact_person'] ) ? esc_attr( $_POST['contact_person'] ) : ''; ?>"
            required
          />
        </div>

        <div class="form-group">
          <label for="phone">電話番号</label>
          <input
            type="tel"
            id="phone"
            name="phone"
            value="<?php echo isset( $_POST['phone'] ) ? esc_attr( $_POST['phone'] ) : ''; ?>"
            required
          />
        </div>

        <div class="form-group">
          <label for="email">メールアドレス</label>
          <input
            type="email"
            id="email"
            name="email"
            value="<?php echo isset( $_POST['email'] ) ? esc_attr( $_POST['email'] ) : ''; ?>"
            required
          />
        </div>

        <div class="form-group">
          <label for="password">パスワード</label>
          <input
            type="password"
            id="password"
            name="password"
            required
          />
          <small>8文字以上で設定してください</small>
        </div>

        <div class="form-group">
          <label for="password_confirm">パスワード（確認）</label>
          <input
            type="password"
            id="password_confirm"
            name="password_confirm"
            required
          />
        </div>

        <button type="submit" class="btn btn-accent btn-block">登録</button>
      </form>

      <p class="auth-link">
        企業アカウントをお持ちの方は <a href="<?php echo esc_url( home_url( '/company-login/' ) ); ?>">こちらからログイン</a>
      </p>
    </div>
  </div>
</main>

<?php get_footer(); ?>
