<?php
$registration_errors = array();

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['action'] ) && $_POST['action'] === 'register_worker' ) {
    $email             = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
    $name              = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
    $password          = isset( $_POST['password'] ) ? $_POST['password'] : '';
    $password_confirm  = isset( $_POST['password_confirm'] ) ? $_POST['password_confirm'] : '';

    if ( ! $email ) {
        $registration_errors[] = 'メールアドレスを入力してください';
    } elseif ( email_exists( $email ) ) {
        $registration_errors[] = 'このメールアドレスは既に登録されています';
    }

    if ( ! $name ) {
        $registration_errors[] = '氏名を入力してください';
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
            'user_pass'  => $password,
            'first_name' => $name,
            'role'       => 'zaito_seeker',
        ) );

        if ( is_wp_error( $user_id ) ) {
            $registration_errors[] = $user_id->get_error_message();
        } else {
            $signon = wp_signon( array(
                'user_login'    => sanitize_user( $email ),
                'user_password' => $password,
                'remember'      => false,
            ) );

            if ( is_wp_error( $signon ) ) {
                $registration_errors[] = 'ログインに失敗しました。お手数ですがログインページからお試しください。';
            } else {
                wp_set_current_user( $signon->ID );
                wp_safe_redirect( home_url( '/mypage/' ) );
                exit;
            }
        }
    }
}

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

      <form method="post" class="auth-form">
        <input type="hidden" name="action" value="register_worker" />

        <div class="form-group">
          <label for="name">氏名</label>
          <input
            type="text"
            id="name"
            name="name"
            value="<?php echo isset( $_POST['name'] ) ? esc_attr( $_POST['name'] ) : ''; ?>"
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
        既にアカウントをお持ちの方は <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>">こちらからログイン</a>
      </p>
    </div>
  </div>
</main>

<?php get_footer(); ?>
