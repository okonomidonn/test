<?php
/**
 * Google OAuth2 のコールバック処理。
 * /login/ または /company-login/ の「Googleでログイン」ボタンから
 * 遷移してきたユーザーの認可コードをアクセストークンに交換し、
 * ユーザー情報を取得してログイン（または新規登録）する。
 */

$zaito_google_error = '';

if ( ! zaito_google_login_is_configured() ) {
    $zaito_google_error = 'Googleログインは現在利用できません。';
} elseif ( ! empty( $_GET['error'] ) ) {
    $zaito_google_error = 'Googleログインがキャンセルされました。';
} else {
    $code  = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
    $state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
    $state_data = $state ? get_transient( 'zaito_google_state_' . $state ) : false;

    if ( ! $code || ! $state_data ) {
        $zaito_google_error = 'リクエストが無効です。もう一度お試しください。';
    } else {
        delete_transient( 'zaito_google_state_' . $state );

        $token_response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
            'body' => array(
                'code'          => $code,
                'client_id'     => ZAITO_GOOGLE_CLIENT_ID,
                'client_secret' => ZAITO_GOOGLE_CLIENT_SECRET,
                'redirect_uri'  => home_url( '/google-callback/' ),
                'grant_type'    => 'authorization_code',
            ),
        ) );

        if ( is_wp_error( $token_response ) ) {
            $zaito_google_error = 'Googleとの通信に失敗しました。しばらくしてから再度お試しください。';
        } else {
            $token_body = json_decode( wp_remote_retrieve_body( $token_response ), true );
            $access_token = $token_body['access_token'] ?? '';

            if ( ! $access_token ) {
                $zaito_google_error = 'Googleログインに失敗しました。';
            } else {
                $profile_response = wp_remote_get( 'https://www.googleapis.com/oauth2/v3/userinfo', array(
                    'headers' => array( 'Authorization' => 'Bearer ' . $access_token ),
                ) );

                if ( is_wp_error( $profile_response ) ) {
                    $zaito_google_error = 'Googleとの通信に失敗しました。しばらくしてから再度お試しください。';
                } else {
                    $profile = json_decode( wp_remote_retrieve_body( $profile_response ), true );
                    $google_email    = isset( $profile['email'] ) ? sanitize_email( $profile['email'] ) : '';
                    $google_verified = ! empty( $profile['email_verified'] );
                    $google_name     = isset( $profile['name'] ) ? sanitize_text_field( $profile['name'] ) : '';
                    $google_id       = isset( $profile['sub'] ) ? sanitize_text_field( $profile['sub'] ) : '';

                    if ( ! $google_email || ! $google_verified ) {
                        $zaito_google_error = 'Googleアカウントのメールアドレスを確認できませんでした。';
                    } else {
                        $user = get_user_by( 'email', $google_email );

                        if ( ! $user ) {
                            $role = in_array( $state_data['role'], array( 'zaito_seeker', 'zaito_company' ), true )
                                ? $state_data['role']
                                : 'zaito_seeker';

                            $user_id = wp_insert_user( array(
                                'user_email' => $google_email,
                                'user_login' => sanitize_user( $google_email ),
                                'user_pass'  => wp_generate_password( 24 ),
                                'first_name' => $google_name ?: $google_email,
                                'role'       => $role,
                            ) );

                            if ( is_wp_error( $user_id ) ) {
                                $zaito_google_error = 'アカウントの作成に失敗しました。';
                            } else {
                                update_user_meta( $user_id, 'zaito_email_verified', '1' );
                                update_user_meta( $user_id, 'google_id', $google_id );
                                if ( 'zaito_company' === $role ) {
                                    update_user_meta( $user_id, 'company_name', $google_name ?: $google_email );
                                }
                                $user = get_userdata( $user_id );
                            }
                        } elseif ( ! get_user_meta( $user->ID, 'google_id', true ) ) {
                            update_user_meta( $user->ID, 'google_id', $google_id );
                        }

                        if ( $user && ! $zaito_google_error ) {
                            zaito_log_user_in_silently( $user->ID );
                            wp_safe_redirect( esc_url_raw( $state_data['redirect_to'] ) );
                            exit;
                        }
                    }
                }
            }
        }
    }
}

get_header();
?>

<main class="auth-main">
  <div class="auth-container">
    <div class="auth-card">
      <h1>Googleログイン</h1>
      <div class="auth-error">
        <p><?php echo esc_html( $zaito_google_error ?: 'ログインに失敗しました。' ); ?></p>
      </div>
      <p class="auth-link">
        <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>">ログイン画面に戻る</a>
      </p>
    </div>
  </div>
</main>

<?php get_footer(); ?>
