<?php
/**
 * 試験環境（https://staging.zaito-work.com/）の設定。
 *
 * 入り方：本番に管理者でログインした状態で https://zaito-work.com/?zaito_staging を開く。
 * 本番が5分だけ有効な通行証をつけて試験環境へ送り、試験環境はそのブラウザに30日有効の「合鍵」クッキーを置く。
 * 合鍵があれば、ログアウトして学生や企業として試せる。合鍵がない人には、ログイン画面も含めて何も見せない
 * （本番と同じ管理者パスワードを、試験環境で総当たりされないようにするため）。
 *
 * 試験環境だけで動くこと（wp-config.php に define( 'ZAITO_STAGING', true ); があるときだけ）：
 * 1. 検索エンジンに載せない。
 * 2. 合鍵のない人を入れない。
 * 3. メールは宛先を問わず info@zaito-work.com にだけ送る（本物の学生や企業に届かないように）。
 * 4. どの画面にも「試験環境」の印を出す。
 */

define( 'ZAITO_STAGING_URL', 'https://staging.zaito-work.com' );

/**
 * 本番と試験環境の両方が知っている秘密の値。両方の wp-config.php は同じデータベースのユーザーを使うので、
 * そのパスワードから作る（外からは作れない）。
 */
function zaito_staging_link_secret() {
	return hash_hmac( 'sha256', 'zaito-staging-link', DB_USER . '|' . DB_PASSWORD );
}

if ( ! defined( 'ZAITO_STAGING' ) || ! ZAITO_STAGING ) {
	/** 本番：?zaito_staging を開いた管理者を、通行証つきで試験環境へ送る。 */
	add_action(
		'init',
		function () {
			if ( ! isset( $_GET['zaito_staging'] ) ) {
				return;
			}
			nocache_headers();
			if ( ! is_user_logged_in() ) {
				auth_redirect();
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				status_header( 403 );
				exit;
			}
			$t = (string) time();
			wp_redirect( ZAITO_STAGING_URL . '/?zaito_stg_pass=' . $t . '.' . hash_hmac( 'sha256', $t, zaito_staging_link_secret() ) );
			exit;
		},
		1
	);
	return;
}

/** 合鍵クッキーの値。試験環境だけの秘密の値（wp_salt）から作る。 */
function zaito_staging_key() {
	return hash_hmac( 'sha256', 'zaito-staging-gate', wp_salt( 'auth' ) );
}

/** 2. 入口の制限。 */
function zaito_staging_gate() {
	// wp-cron.php も止める（試験環境では予約の処理を動かさない）。サーバーの中の wp コマンドだけ通す。
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return;
	}
	nocache_headers();
	$key = zaito_staging_key();

	// 本番から来た通行証（5分以内）なら、合鍵を置いて、通行証を消したURLへ。
	if ( isset( $_GET['zaito_stg_pass'] ) ) {
		$parts = explode( '.', (string) $_GET['zaito_stg_pass'], 2 );
		if ( 2 === count( $parts ) && ctype_digit( $parts[0] ) && abs( time() - (int) $parts[0] ) <= 300
			&& hash_equals( hash_hmac( 'sha256', $parts[0], zaito_staging_link_secret() ), $parts[1] ) ) {
			setcookie(
				'zaito_stg',
				$key,
				array(
					'expires'  => time() + 30 * DAY_IN_SECONDS,
					'path'     => '/',
					'secure'   => true,
					'httponly' => true,
					'samesite' => 'Lax',
				)
			);
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}
	}

	if ( isset( $_COOKIE['zaito_stg'] ) && hash_equals( $key, (string) $_COOKIE['zaito_stg'] ) ) {
		return;
	}
	status_header( 403 );
	header( 'Content-Type: text/plain; charset=UTF-8' );
	exit( "403\n" );
}
add_action( 'init', 'zaito_staging_gate', -1000 );

/** 1. 検索エンジンに載せない。 */
add_filter( 'pre_option_blog_public', '__return_zero' );
add_action(
	'send_headers',
	function () {
		if ( ! headers_sent() ) {
			header( 'X-Robots-Tag: noindex, nofollow', true );
		}
	}
);

/** 3. メールは info@ にだけ送る。件名に本来の宛先を書く。 */
function zaito_staging_mail( $args ) {
	$to              = is_array( $args['to'] ) ? implode( ', ', $args['to'] ) : (string) $args['to'];
	$args['to']      = 'info@zaito-work.com';
	$args['subject'] = '【試験環境・本来の宛先 ' . $to . '】' . $args['subject'];
	$headers         = $args['headers'];
	if ( ! is_array( $headers ) ) {
		$headers = preg_split( "/\r\n|\n/", (string) $headers );
	}
	$args['headers'] = array_values(
		array_filter(
			$headers,
			function ( $h ) {
				return '' !== trim( (string) $h ) && ! preg_match( '/^\s*(cc|bcc)\s*:/i', (string) $h );
			}
		)
	);
	return $args;
}
add_filter( 'wp_mail', 'zaito_staging_mail', PHP_INT_MAX );

/** 4. 「試験環境」の印。 */
function zaito_staging_badge() {
	echo '<div style="position:fixed;left:8px;bottom:8px;z-index:99999;background:#c62828;color:#fff;font:bold 12px/1 sans-serif;padding:6px 10px;border-radius:4px;pointer-events:none">試験環境</div>';
}
add_action( 'wp_footer', 'zaito_staging_badge' );
add_action( 'admin_footer', 'zaito_staging_badge' );
add_action( 'login_footer', 'zaito_staging_badge' );
