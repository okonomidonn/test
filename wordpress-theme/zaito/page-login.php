<?php
/**
 * 以前の求職者向けログイン（/login/）。学生はパスワードなしのマイページ（/mypage/）を使うようになったため、
 * パスワードでログインするのは企業と運営だけになった。ログインは /company-login/ にまとめ、
 * ここに来た人（wp-login.php からの転送・ログイン失敗の戻り・古いリンク）は付いていた値ごとそちらへ移す。
 */
$zaito_args = array();
foreach ( array( 'redirect_to', 'login', 'reset' ) as $zaito_k ) {
    if ( isset( $_GET[ $zaito_k ] ) ) {
        $zaito_args[ $zaito_k ] = rawurlencode( sanitize_text_field( wp_unslash( $_GET[ $zaito_k ] ) ) );
    }
}
wp_safe_redirect( add_query_arg( $zaito_args, home_url( '/company-login/' ) ), 301 );
exit;
