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
 * 4. どの画面にも「試験環境」の印を出す。押すと操作パネル（5）。
 * 5. 操作パネル：テスト用の企業・学生・動画で答える求人を作り、企業や学生に切り替えて流れを試せるようにする。
 *    管理者への切り替えはしない（管理者になるには、いつもどおりパスワードでログインする）。
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
	// 差出人の staging.zaito-work.com を本番と同じ zaito-work.com にする。試験環境のアドレス（存在しない）から送ると、
	// 受け取る側で迷惑メール扱いや受け取り拒否になり、届き方を本番と同じように確かめられないため。
	$args['headers'] = array_map(
		function ( $h ) {
			return preg_match( '/^\s*(from|reply-to)\s*:/i', (string) $h ) ? str_ireplace( '@staging.zaito-work.com', '@zaito-work.com', (string) $h ) : $h;
		},
		$args['headers']
	);
	return $args;
}
add_filter( 'wp_mail', 'zaito_staging_mail', PHP_INT_MAX );
// 送ったメールの記録（件名・本来の宛先・時刻、最新200件）。届かなかったときに、送っていないのか届いていないのかを見分けるため。
add_action(
	'wp_mail_succeeded',
	function ( $mail ) {
		zaito_staging_mail_log( 'sent', is_array( $mail['to'] ) ? implode( ',', $mail['to'] ) : (string) $mail['to'], (string) $mail['subject'] );
	}
);
add_action(
	'wp_mail_failed',
	function ( $err ) {
		$d = $err->get_error_data();
		zaito_staging_mail_log( 'FAILED: ' . $err->get_error_message(), isset( $d['to'] ) ? implode( ',', (array) $d['to'] ) : '', isset( $d['subject'] ) ? (string) $d['subject'] : '' );
	}
);
function zaito_staging_mail_log( $result, $to, $subject ) {
	$log   = (array) get_option( 'zaito_stg_mail_log', array() );
	$log[] = array( current_time( 'mysql' ), $result, $to, $subject );
	update_option( 'zaito_stg_mail_log', array_slice( $log, -200 ), false );
}

// 差出人の指定がないメール（WordPressの既定は wordpress@staging.zaito-work.com）も、本番と同じドメインから送る。
add_filter(
	'wp_mail_from',
	function ( $from ) {
		return str_ireplace( '@staging.zaito-work.com', '@zaito-work.com', (string) $from );
	},
	PHP_INT_MAX
);

/** 4. 「試験環境」の印。押すと試験用の操作パネル（/?zaito_stg_panel）を開く。 */
function zaito_staging_badge_html() {
	return '<a href="' . esc_url( home_url( '/?zaito_stg_panel=1' ) ) . '" style="position:fixed;left:8px;bottom:8px;z-index:99999;background:#c62828;color:#fff;font:bold 12px/1 sans-serif;padding:8px 12px;border-radius:4px;text-decoration:none">試験環境（操作パネル）</a>';
}
function zaito_staging_badge() {
	echo zaito_staging_badge_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- 中でエスケープ済み
}
// zaitoのページの多くは独自のフッターで wp_footer を通らないので、ページの最後（</body> の前）に差し込む。
add_action(
	'template_redirect',
	function () {
		ob_start(
			function ( $html ) {
				$pos = strripos( $html, '</body>' );
				if ( false === $pos ) {
					return $html;
				}
				// ここ（出力の後始末の中）では ob_start が使えないので、文字列で足す。
				return substr( $html, 0, $pos ) . zaito_staging_badge_html() . substr( $html, $pos );
			}
		);
	},
	-200
);
add_action( 'admin_footer', 'zaito_staging_badge' );
add_action( 'login_footer', 'zaito_staging_badge' );

/* ---------- 5. 操作パネル ---------- */

/** パネルの操作の合言葉（合鍵から作る。よそのページから勝手に押されないように）。 */
function zaito_staging_token( $action ) {
	return substr( hash_hmac( 'sha256', 'zaito-stg-do|' . $action, zaito_staging_key() ), 0, 20 );
}

/** テスト用の企業・求人・学生を見つける（なければ 0）。 */
function zaito_staging_demo_ids() {
	$co  = get_user_by( 'login', 'stg-demo-company' );
	$job = get_posts( array( 'post_type' => 'job_listing', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_zaito_stg_demo', 'meta_value' => '1' ) );
	$stu = get_posts( array( 'post_type' => 'zaito_interest', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_zaito_stg_demo', 'meta_value' => '1' ) );
	return array(
		'company' => $co ? (int) $co->ID : 0,
		'job'     => $job ? (int) $job[0] : 0,
		'student' => $stu ? (int) $stu[0] : 0,
	);
}

/** テスト用の企業・求人・学生を作る。もう一度押すと、テスト用の学生の応募を消して、最初から試せる状態に戻す。 */
function zaito_staging_seed() {
	$ids = zaito_staging_demo_ids();

	if ( ! $ids['company'] ) {
		$ids['company'] = (int) wp_insert_user( array(
			'user_login'   => 'stg-demo-company',
			'user_email'   => 'stg-company@example.invalid',
			'user_pass'    => wp_generate_password( 32 ),
			'display_name' => 'テスト株式会社（試験用）',
			'role'         => 'zaito_company',
		) );
	}
	update_user_meta( $ids['company'], 'company_name', 'テスト株式会社（試験用）' );
	update_user_meta( $ids['company'], 'first_name', '試験 担当' );
	update_user_meta( $ids['company'], 'zaito_company_status', 'approved' );

	$postarr = array(
		'post_type'    => 'job_listing',
		'post_title'   => '【試験用】オンライン塾講師（動画で答える応募）',
		'post_content' => "試験環境の求人です。応募すると、質問に動画で答える画面に進みます。\n\n中学生・高校生に、オンラインで数学と英語を教えます。",
		'post_status'  => 'publish',
		'post_author'  => $ids['company'],
	);
	if ( $ids['job'] ) {
		$postarr['ID'] = $ids['job'];
		wp_update_post( $postarr );
	} else {
		$ids['job'] = (int) wp_insert_post( $postarr );
	}
	foreach ( array(
		'_zaito_stg_demo'         => '1',
		'_zaito_direct'           => '1',
		'_company_user_id'        => $ids['company'],
		'_company_name'           => 'テスト株式会社（試験用）',
		'_zaito_review'           => 'approved',
		'_zaito_approved_at'      => current_time( 'mysql' ),
		'_job_category'           => '教育・学習サポート',
		'_job_summary'            => '試験用の求人です。応募すると、動画で質問に答えます。',
		'_job_employment_type'    => 'アルバイト',
		'_job_salary_type'        => '時給',
		'_job_salary'             => 1500,
		'_job_salary_max'         => 2000,
		'_job_hours'              => '週2日・1日2時間から',
		'_job_target_text'        => '大学生（学年不問）',
		'_job_tags'               => array( 'gakusei', 'mikeiken' ),
		'_job_video_questions'    => array( '自己紹介と、この仕事に応募した理由を教えてください。', '中学生に「わからない」と言われたら、どう説明しますか。', '週に何日、どの時間帯なら働けますか。' ),
		'_job_auto_reply_message' => 'ご応募ありがとうございます。動画の回答を拝見して、3日以内にこのメッセージでご連絡します。（試験用の自動メッセージ）',
		'_job_confirm_remote'     => '1',
		'_job_confirm_fee'        => '1',
	) as $k => $v ) {
		update_post_meta( $ids['job'], $k, $v );
	}
	delete_post_meta( $ids['job'], '_zaito_closed' );

	if ( ! $ids['student'] ) {
		$ids['student'] = (int) wp_insert_post( array( 'post_type' => 'zaito_interest', 'post_title' => 'stg-student@example.invalid', 'post_status' => 'publish' ) );
	}
	foreach ( array(
		'_zaito_stg_demo' => '1',
		'email'           => 'stg-student@example.invalid',
		'name'            => 'テスト 学生',
		'role'            => '大学生',
		'grade'           => '2年',
		'school'          => 'テスト大学',
		'verified'        => '1',
	) as $k => $v ) {
		update_post_meta( $ids['student'], $k, $v );
	}
	// テスト用の学生の応募（と動画・メッセージ）を消して、最初から試せるようにする。
	foreach ( zaito_direct_applications( 'interest', $ids['student'] ) as $a ) {
		foreach ( zaito_direct_messages( $a->ID ) as $m ) {
			wp_delete_post( $m->ID, true );
		}
		wp_delete_post( $a->ID, true );
	}
	return $ids;
}

/**
 * GWCへの掲載案と同じ内容の試験用の求人（録画面接なし・本番と同じ応募の流れ）と、その企業を作る。トップの一番上に固定する。
 * 学生として新しく登録して応募するところから、自分で試すためのもの。何度呼んでも同じ1件を作り直す（応募は消さない）。
 */
function zaito_staging_gwc_seed() {
	$co = get_user_by( 'login', 'stg-gwc-company' );
	$uid = $co ? (int) $co->ID : (int) wp_insert_user( array(
		'user_login'   => 'stg-gwc-company',
		'user_email'   => 'stg-gwc@example.invalid',
		'user_pass'    => wp_generate_password( 32 ),
		'display_name' => '株式会社GWC（試験用）',
		'role'         => 'zaito_company',
	) );
	update_user_meta( $uid, 'company_name', '株式会社GWC（アースフレンズ東京Z）' );
	update_user_meta( $uid, 'first_name', '試験 担当' );
	update_user_meta( $uid, 'zaito_company_status', 'approved' );
	update_user_meta( $uid, 'zaito_email_verified', '1' );

	$q   = get_posts( array( 'post_type' => 'job_listing', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_zaito_stg_gwc', 'meta_value' => '1' ) );
	$arr = array(
		'post_type'    => 'job_listing',
		'post_status'  => 'publish',
		'post_author'  => $uid,
		'post_title'   => 'プロバスケットボールチームのスポンサー獲得のテレアポ（完全在宅）',
		'post_content' => "プロバスケットボールチーム「アースフレンズ東京Z」と、ハンドボールチーム「アースフレンズBM」を運営する株式会社GWCで、新しいスポンサー企業を獲得するためのテレアポを担当するお仕事です。業務はすべてオンラインで行うため、完全在宅で、全国どこからでも働けます。\n\n■仕事内容\n(1) 候補となる企業の選定・リサーチ\nインターネットなどで対象となる企業を探し、リストアップします。\n(2) 電話発信\n企業に電話をかけ、「アースフレンズ東京Z」「アースフレンズBM」のご案内をします。担当するのは商談のアポイントを取るところまでで、その後は営業スタッフが引き継ぎます。トークスクリプトがあります。\n\n■未経験OK\nPCの基本操作ができれば大丈夫です。仕事内容はきちんと教えてもらえます。テレアポや営業の経験がある方も歓迎です。\n\n■働き方\n・オンライン面接のあと、完全在宅で勤務します（出社は一切なし）。業務用のPCを貸与します。\n・週3日から、10〜17時の中で1日4時間から。土日祝は休みで、シフトは週ごとに希望を出せます。\n\n■うれしいポイント\n・年に数回、ホームゲームに無料で招待されます。\n\n■こんな方に\n・スポーツやBリーグが好きな方\n・人と話すのが好きな方（必要なのはコミュニケーション力です）",
	);
	if ( $q ) {
		$arr['ID'] = $q[0];
		wp_update_post( $arr );
		$jid = (int) $q[0];
	} else {
		$jid = (int) wp_insert_post( $arr );
	}
	foreach ( array(
		'_zaito_stg_gwc'          => '1',
		'_zaito_direct'           => '1',
		'_company_user_id'        => $uid,
		'_company_name'           => '株式会社GWC（アースフレンズ東京Z）',
		'_zaito_review'           => 'approved',
		'_zaito_approved_at'      => current_time( 'mysql' ),
		'_job_category'           => 'テレアポ・営業',
		'_job_summary'            => 'Bリーグ「アースフレンズ東京Z」などのスポンサーを増やすテレアポ。完全在宅・PC貸与・トークスクリプトあり。',
		'_job_employment_type'    => 'アルバイト',
		'_job_salary_type'        => '時給',
		'_job_salary'             => 1280,
		'_job_hours'              => '週3日から・10〜17時の中で1日4時間から（土日祝休み）、3か月以上',
		'_job_target_text'        => 'PCの基本操作ができる方（未経験OK。テレアポ・営業の経験があれば歓迎）',
		'_job_tags'               => array( 'gakusei', 'mikeiken' ),
		'_job_confirm_remote'     => '1',
		'_job_confirm_fee'        => '1',
		'_job_auto_reply_message' => 'ご応募ありがとうございます。内容を確認して、3日以内にこのメッセージでご連絡します。（試験用の自動メッセージ）',
		'_zaito_top_until'        => gmdate( 'Y-m-d', current_time( 'timestamp' ) + 30 * DAY_IN_SECONDS ),
	) as $k => $v ) {
		update_post_meta( $jid, $k, $v );
	}
	delete_post_meta( $jid, '_zaito_closed' );
	delete_post_meta( $jid, '_job_video_questions' );
	return array( 'company' => $uid, 'job' => $jid );
}

/**
 * 録画せずに、メッセージの画面をすぐ見られるサンプルの応募を作る（テスト用の学生 → テスト用の求人）。
 * 動画は、テーマの demo/sample-answer.mp4（テスト用の映像）を質問の数だけ置く。メールは送らない。
 */
function zaito_staging_sample_application() {
	$ids = zaito_staging_seed(); // 前の応募は消して、作り直す。
	$app = (int) wp_insert_post( array(
		'post_type'   => 'zaito_application',
		'post_title'  => 'テスト 学生 → テスト株式会社（試験用）「【試験用】オンライン塾講師」（サンプル）',
		'post_status' => 'publish',
	) );
	if ( ! $app ) {
		return 0;
	}
	$qs  = zaito_video_job_questions( $ids['job'] );
	$now = current_time( 'mysql' );
	foreach ( array(
		'interest_id'        => $ids['student'],
		'job_id'             => $ids['job'],
		'company_id'         => $ids['company'],
		'status'             => 'new',
		'status_at'          => $now,
		'applicant_name'     => 'テスト 学生',
		'applicant_role'     => '大学生',
		'applicant_grade'    => '2年',
		'applicant_school'   => 'テスト大学',
		'message'            => 'テスト大学2年のテスト学生です。授業のあと、平日の夜に週2日ほど働けます。（サンプルの応募）',
		'screening_question' => '',
		'screening_answer'   => '',
		'video_status'       => 'done',
		'video_questions'    => $qs,
		'video_done_at'      => $now,
	) as $k => $v ) {
		update_post_meta( $app, $k, $v );
	}
	// 回答の動画（テスト用の映像）を、質問の数だけ置く。
	$src   = get_template_directory() . '/demo/sample-answer.mp4';
	$dir   = zaito_video_app_dir( $app, true );
	$files = array();
	foreach ( array_keys( $qs ) as $i ) {
		if ( $dir && is_readable( $src ) && copy( $src, $dir . '/q' . $i . '.mp4' ) ) {
			@chmod( $dir . '/q' . $i . '.mp4', 0600 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			$files[ $i ] = array( 'file' => 'q' . $i . '.mp4', 'mime' => 'video/mp4', 'size' => filesize( $src ), 'at' => time(), 'tries' => 1 );
		}
	}
	update_post_meta( $app, 'video_files', $files );

	// やりとり（メールは送らない）。
	zaito_direct_add_message( $app, 'student', $ids['student'], 'テスト大学2年のテスト学生です。授業のあと、平日の夜に週2日ほど働けます。（サンプルの応募）', false );
	zaito_direct_add_message( $app, 'student', $ids['student'], '動画で質問に答えました（' . count( $qs ) . '問）。', false );
	zaito_direct_add_message( $app, 'company', $ids['company'], (string) get_post_meta( $ids['job'], '_job_auto_reply_message', true ), false );
	zaito_direct_add_message( $app, 'company', $ids['company'], "テスト 学生さん、動画の回答を拝見しました。ありがとうございます。\n15分ほどオンラインでお話ししたいのですが、次の日時でご都合はいかがでしょうか。\n・11月18日（水）19:00〜\n・11月19日（木）20:00〜\nZoomのURLは、日程が決まりましたらこちらでお送りします。", false );
	zaito_direct_add_message( $app, 'student', $ids['student'], 'ご連絡ありがとうございます。11月18日（水）19:00からでお願いします。', false );
	update_post_meta( $app, 'status', 'talking' );
	update_post_meta( $app, 'first_reply_at', $now );
	return $app;
}

/** 操作パネルの処理と画面。 */
function zaito_staging_panel() {
	if ( ! isset( $_GET['zaito_stg_panel'] ) && ! isset( $_GET['zaito_stg_do'] ) ) {
		return;
	}
	nocache_headers();
	$do = isset( $_GET['zaito_stg_do'] ) ? sanitize_key( wp_unslash( $_GET['zaito_stg_do'] ) ) : '';
	if ( $do ) {
		$t = isset( $_GET['t'] ) ? (string) wp_unslash( $_GET['t'] ) : '';
		if ( ! hash_equals( zaito_staging_token( $do ), $t ) ) {
			wp_safe_redirect( home_url( '/?zaito_stg_panel=1' ) );
			exit;
		}
		$ids = zaito_staging_demo_ids();
		if ( 'sample' === $do ) {
			$app = zaito_staging_sample_application();
			wp_safe_redirect( home_url( '/?zaito_stg_panel=1&sample=' . (int) $app ) );
			exit;
		}
		// 切り替えのあとに開くやりとり（サンプルの応募など）。
		$open = isset( $_GET['c'] ) ? (int) $_GET['c'] : 0;
		if ( 'seed' === $do ) {
			zaito_staging_seed();
			wp_safe_redirect( home_url( '/?zaito_stg_panel=1&seeded=1' ) );
			exit;
		}
		if ( 'gwc' === $do ) {
			zaito_staging_gwc_seed();
			wp_safe_redirect( home_url( '/?zaito_stg_panel=1&gwc=1' ) );
			exit;
		}
		if ( 'gwc_company' === $do ) {
			$g = zaito_staging_gwc_seed();
			wp_clear_auth_cookie();
			wp_set_current_user( $g['company'] );
			wp_set_auth_cookie( $g['company'], false, true );
			wp_safe_redirect( $open ? zaito_direct_chat_url( $open ) : home_url( '/company-applicants/' ) );
			exit;
		}
		if ( 'company' === $do && $ids['company'] ) {
			wp_clear_auth_cookie();
			wp_set_current_user( $ids['company'] );
			wp_set_auth_cookie( $ids['company'], false, true );
			wp_safe_redirect( $open ? zaito_direct_chat_url( $open ) : home_url( '/company-applicants/' ) );
			exit;
		}
		if ( 'student' === $do && $ids['student'] ) {
			// 企業や管理者のログインを外してから、学生の本人確認のリンク（10分有効）で、テスト用の求人の応募フォームへ。
			wp_clear_auth_cookie();
			$next = $open ? '/chat/?c=' . $open : ( $ids['job'] ? '/remote/' . zaito_direct_slug( $ids['job'] ) . '/apply/' : '' );
			$url  = zaito_my_login_url( $ids['student'], 10 * MINUTE_IN_SECONDS );
			wp_redirect( $next ? add_query_arg( 'next', rawurlencode( $next ), $url ) : $url );
			exit;
		}
		wp_safe_redirect( home_url( '/?zaito_stg_panel=1' ) );
		exit;
	}

	$ids  = zaito_staging_demo_ids();
	$user = wp_get_current_user();
	$me   = function_exists( 'zaito_my_id' ) ? zaito_my_id() : 0;
	$who  = array();
	if ( $user && $user->ID ) {
		$who[] = current_user_can( 'manage_options' ) ? '管理者（' . $user->user_login . '）' : ( zaito_is_company( $user ) ? '企業（' . zaito_company_name( $user->ID ) . '）' : $user->user_login );
	}
	if ( $me ) {
		$who[] = '学生（' . get_post_meta( $me, 'name', true ) . '）';
	}
	$btn = function ( $do, $label, $primary = false, $c = 0 ) {
		$args = array( 'zaito_stg_do' => $do, 't' => zaito_staging_token( $do ) );
		if ( $c ) {
			$args['c'] = (int) $c;
		}
		return '<a class="b' . ( $primary ? ' p' : '' ) . '" href="' . esc_url( add_query_arg( $args, home_url( '/' ) ) ) . '">' . esc_html( $label ) . '</a>';
	};
	$app = $ids['student'] ? zaito_direct_applications( 'interest', $ids['student'] ) : array();
	header( 'Content-Type: text/html; charset=UTF-8' );
	?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>試験環境の操作パネル | zaito</title>
<style>
body{margin:0;background:#F4F6FB;color:#0B1530;font-family:"Hiragino Sans","Noto Sans JP",system-ui,sans-serif;line-height:1.7}
main{max-width:640px;margin:0 auto;padding:20px 16px 60px}
h1{font-size:20px;margin:0 0 4px}h2{font-size:16px;margin:0 0 8px}
.c{background:#fff;border:1px solid #E3E7EF;border-radius:14px;padding:18px;margin-top:14px}
.b{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 16px;border-radius:10px;background:#fff;color:#0B1530;box-shadow:inset 0 0 0 1.5px #E3E7EF;font-weight:700;text-decoration:none;margin:4px 6px 4px 0}
.b.p{background:#3D5AFE;color:#fff;box-shadow:none}
.n{font-size:13px;color:#6B7590}.ok{background:#EEF9F3;color:#1A7F55;border-radius:10px;padding:10px 14px;margin-top:14px;font-weight:700}
ol{padding-left:20px;margin:0}li{margin-bottom:8px}
</style></head><body><main>
<h1>試験環境の操作パネル</h1>
<p class="n">ここは試験環境（staging.zaito-work.com）だけの画面です。本番には出ません。メールは宛先にかかわらず info@zaito-work.com に届き、件名に本来の宛先が入ります。</p>
<?php if ( ! empty( $_GET['sample'] ) ) : ?><div class="ok">メッセージつきのサンプルの応募を作りました（動画3本・やりとり5通）。下のボタンから、企業と学生それぞれの画面で開けます。<br>
<?php echo $btn( 'company', '企業として、このやりとりを開く', true, (int) $_GET['sample'] ); // エスケープ済み ?>
<?php echo $btn( 'student', '学生として、このやりとりを開く', false, (int) $_GET['sample'] ); // エスケープ済み ?></div><?php endif; ?>
<?php if ( isset( $_GET['seeded'] ) ) : ?><div class="ok">テスト用の企業・学生・求人を用意しました。テスト用の学生の応募は消したので、最初から試せます。</div><?php endif; ?>

<div class="c"><h2>いまの立場</h2><p style="margin:0"><?php echo esc_html( $who ? implode( '・', $who ) : 'ログインしていません' ); ?></p></div>

<?php $zaito_gwc = get_posts( array( 'post_type' => 'job_listing', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_zaito_stg_gwc', 'meta_value' => '1' ) ); ?>
<?php if ( isset( $_GET['gwc'] ) ) : ?><div class="ok">GWCの掲載案の試験用の求人を用意しました（トップの一番上に固定）。</div><?php endif; ?>
<div class="c"><h2>GWCの掲載案で、学生の応募を試す（録画面接なし・本番と同じ流れ）</h2>
<?php if ( $zaito_gwc ) : ?>
<p style="margin:0 0 8px">求人：<a href="<?php echo esc_url( home_url( '/remote/' . zaito_direct_slug( $zaito_gwc[0] ) . '/' ) ); ?>">プロバスケットボールチームのスポンサー獲得のテレアポ（完全在宅）</a></p>
<ol>
<li>トップページを開くと、一番上にこの求人が出ます。求人を開いて「この求人に応募する」を押します。</li>
<li>名前・学校名・メールアドレスを入れて登録します。メールアドレスは何でもかまいません（試験環境のメールは、すべて info@ に届きます）。</li>
<li>5つの質問に答えます。info@ に「登録ありがとうございます（メールアドレスの確認）」が届くので、ボタンを押して確認を済ませます（応募ページで確認のリンクを送るボタンが出たときは、それを押すと「応募フォームを開くリンク」が届きます）。</li>
<li>確認が済んだら、求人の「この求人に応募する」から応募フォームを開いて応募します。応募するとメッセージの画面に進み、info@ に企業あての「新しい応募がありました」が届きます。</li>
<li>下の「GWC（試験用）として見る」で、企業の画面から応募を開いて返信できます。</li>
</ol>
<p class="n">同じブラウザで学生としてもう一度最初から試すときは、別のメールアドレスで登録してください。</p>
<?php echo $btn( 'gwc_company', 'GWC（試験用）として見る（応募者の一覧へ）', true ); // エスケープ済み ?>
<?php echo $btn( 'gwc', '求人を作り直す' ); // エスケープ済み ?>
<?php else : ?>
<?php echo $btn( 'gwc', 'GWCの掲載案の試験用の求人を作る', true ); // エスケープ済み ?>
<?php endif; ?>
</div>

<div class="c"><h2>1. テスト用のデータ</h2>
<?php if ( $ids['company'] && $ids['job'] && $ids['student'] ) : ?>
<p style="margin:0 0 8px">企業：テスト株式会社（試験用）／学生：テスト 学生（テスト大学）／求人：<a href="<?php echo esc_url( home_url( '/remote/' . zaito_direct_slug( $ids['job'] ) . '/' ) ); ?>">【試験用】オンライン塾講師</a>（動画で答える質問3問）</p>
<p class="n" style="margin:0 0 8px">テスト用の学生の応募：<?php echo count( $app ); ?>件<?php echo $app ? '（' . esc_html( zaito_direct_app_statuses()[ zaito_direct_app_status( $app[0]->ID ) ] ) . '・動画 ' . esc_html( array( '' => 'なし', 'pending' => '回答の途中', 'done' => '回答済み', 'deleted' => '消した' )[ zaito_video_status( $app[0]->ID ) ] ) . '）' : ''; ?></p>
<?php echo $btn( 'seed', '作り直す（テスト用の学生の応募を消す）' ); // エスケープ済み ?>
<?php echo $btn( 'sample', 'メッセージつきのサンプルの応募を作る（録画なし）', true ); // エスケープ済み ?>
<p class="n" style="margin:8px 0 0">サンプルの応募は、録画せずにメッセージの画面をすぐ見るためのものです。動画はテスト用の映像で、メールは送りません。作るたびに、テスト用の学生の前の応募は消えます。</p>
<?php else : ?>
<?php echo $btn( 'seed', 'テスト用の企業・学生・求人を作る', true ); // エスケープ済み ?>
<?php echo $btn( 'sample', 'メッセージつきのサンプルの応募を作る（録画なし）' ); // エスケープ済み ?>
<?php endif; ?>
</div>

<div class="c"><h2>2. 立場を切り替える</h2>
<?php echo $btn( 'student', '学生として見る（応募フォームへ）', true ); // エスケープ済み ?>
<?php echo $btn( 'company', '企業として見る（応募者の一覧へ）' ); // エスケープ済み ?>
<a class="b" href="<?php echo esc_url( wp_login_url( admin_url( 'admin.php?page=zaito-direct&tab=apps' ) ) ); ?>">管理者としてログイン</a>
<p class="n" style="margin:8px 0 0">「学生として見る」「企業として見る」を押すと、いまのログイン（管理者を含む）は外れます。管理者に戻るときは、いつものパスワードでログインしてください。</p>
</div>

<div class="c"><h2>3. 確かめる流れ</h2><ol>
<li>「学生として見る」→ 応募フォームで「応募に進む」→ 練習の質問 → 3問に動画で答えて送る</li>
<li>info@ に「【試験環境・本来の宛先 stg-company@…】新しい応募がありました」が届く</li>
<li>「企業として見る」→ 応募者 → テスト 学生 → 動画を再生する → メッセージを送る（面接の日程など）</li>
<li>info@ に「【試験環境・本来の宛先 stg-student@…】…からメッセージが届きました」が届く。メールのボタンか「学生として見る」から、学生として返信する</li>
<li>企業に戻って、応募の状態を「面接」「内定」「不採用」に変える（内定と不採用は、学生にメールが届く）</li>
<li>学生のマイページで「応募を取り消す」も試せる（動画はその場で消える）</li>
</ol></div>
</main></body></html>
	<?php
	exit;
}
add_action( 'template_redirect', 'zaito_staging_panel', -100 ); // トップページの表示（prelaunch.php の 0）より先に。
