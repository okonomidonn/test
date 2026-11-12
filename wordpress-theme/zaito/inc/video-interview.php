<?php
/**
 * 動画で答える応募（直接掲載の求人だけ）。決めたことは ops/録画面接_動画の扱い.md。
 *
 * 流れ
 * 1. 企業が求人に「動画で答える質問」を1〜5問登録する（job_listing の _job_video_questions）。
 * 2. 学生が応募フォームを送ると、応募は「動画の回答待ち」（video_status=pending）になり、/interview/?c={応募ID} で録画する。
 *    回答が終わるまで、企業には応募を見せず（company_hidden=1）、知らせない。
 * 3. 学生は1問ずつ質問を開き（開くまで質問は見えない）、録画して送る。撮り直しは画面の中で1回まで。
 *    全部そろって「回答を送る」を押すと video_status=done になり、企業に知らせる（zaito_direct_complete_application）。
 * 4. 企業（応募先）と運営だけが、/chat/ の画面で再生できる（admin-ajax の zaito_video_play。ダウンロードのボタンは出さない）。
 * 5. 応募の日から90日で消す（cron/check-closed.php から毎日 zaito_video_purge）。学生が応募を取り消したら、その場で消す。
 *
 * 動画のファイルは公開フォルダ（public_html）の外に置く。URLを知っていても、直接は開けない。
 *   本番:     ~/zaito-work.com/zaito-videos/{応募ID}/q{番号}.mp4（または .webm）
 *   試験環境: ~/zaito-work.com/zaito-videos-staging/{応募ID}/...
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- 決まりごと ---------- */

function zaito_video_max_questions() {
	return 5;
}
/** 考える時間と答える時間（秒）。 */
function zaito_video_think_seconds() {
	return 20;
}
function zaito_video_answer_seconds() {
	return 60;
}
/** 保存する日数（応募の日から数える）。 */
function zaito_video_keep_days() {
	return 90;
}
/** 1問のファイルの上限（60秒で5〜8MBほど。余裕をみて50MB）。 */
function zaito_video_max_bytes() {
	return 50 * 1024 * 1024;
}
/** 1問に受け付けるアップロードの回数（撮り直しは画面の中でするので、通信のやり直しの分）。 */
function zaito_video_max_uploads() {
	return 3;
}

/* ---------- 保存場所 ---------- */

function zaito_video_base_dir() {
	if ( defined( 'ZAITO_VIDEO_DIR' ) ) {
		return rtrim( (string) ZAITO_VIDEO_DIR, '/' );
	}
	$root = untrailingslashit( ABSPATH );
	// public_html の外に置く（試験環境は public_html/staging.zaito-work.com の中にあるので、public_html の1つ上）。
	$pos  = strpos( $root, '/public_html' );
	$base = false !== $pos ? substr( $root, 0, $pos ) : dirname( $root );
	return $base . '/' . ( defined( 'ZAITO_STAGING' ) && ZAITO_STAGING ? 'zaito-videos-staging' : 'zaito-videos' );
}

/** 応募ごとのフォルダ。$create のときは作る。公開フォルダの中になってしまうときは使わない（空文字を返す）。 */
function zaito_video_app_dir( $app_id, $create = false ) {
	$base = zaito_video_base_dir();
	if ( false !== strpos( $base . '/', '/public_html/' ) ) {
		return '';
	}
	$dir = $base . '/' . (int) $app_id;
	if ( $create && ! is_dir( $dir ) ) {
		if ( ! is_dir( $base ) ) {
			wp_mkdir_p( $base );
			@chmod( $base, 0700 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			// 念のため、万一ウェブから見える場所に置かれても開けないようにする。
			@file_put_contents( $base . '/.htaccess', "Require all denied\n" ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		}
		wp_mkdir_p( $dir );
		@chmod( $dir, 0700 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}
	return $dir;
}

/* ---------- 求人と応募の情報 ---------- */

/** 求人の「動画で答える質問」（空の質問は除く。最大5問）。 */
function zaito_video_job_questions( $job_id ) {
	$q = get_post_meta( (int) $job_id, '_job_video_questions', true );
	if ( ! is_array( $q ) ) {
		return array();
	}
	$q = array_values( array_filter( array_map( 'trim', array_map( 'strval', $q ) ), 'strlen' ) );
	return array_slice( $q, 0, zaito_video_max_questions() );
}

/** 応募の動画の状態。''（動画なし）/ pending（回答待ち）/ done（回答済み）/ deleted（消した）。 */
function zaito_video_status( $app_id ) {
	$s = (string) get_post_meta( $app_id, 'video_status', true );
	return in_array( $s, array( 'pending', 'done', 'deleted' ), true ) ? $s : '';
}

/** 応募したときの質問（あとで求人の質問が変わっても、応募のときのものを使う）。 */
function zaito_video_questions( $app_id ) {
	$q = get_post_meta( $app_id, 'video_questions', true );
	return is_array( $q ) ? array_values( $q ) : array();
}

/** アップロード済みのファイル。番号 => array( file, mime, size, at, tries )。 */
function zaito_video_files( $app_id ) {
	$f = get_post_meta( $app_id, 'video_files', true );
	return is_array( $f ) ? $f : array();
}

/** 答え終わった問題の数（前から順に、そろっている数）。 */
function zaito_video_answered_count( $app_id ) {
	$files = zaito_video_files( $app_id );
	$n     = 0;
	while ( isset( $files[ $n ] ) && ! empty( $files[ $n ]['file'] ) ) {
		$n++;
	}
	return $n;
}

/** 動画が消える日（Y年n月j日）。 */
function zaito_video_expire_label( $app_id ) {
	$t = get_post_time( 'U', true, $app_id ) + zaito_video_keep_days() * DAY_IN_SECONDS;
	return wp_date( 'Y年n月j日', $t );
}

function zaito_video_interview_url( $app_id ) {
	return add_query_arg( 'c', (int) $app_id, home_url( '/interview/' ) );
}

/** 応募したときに呼ぶ（inc/direct.php の zaito_handle_direct_apply）。動画で答える求人なら true。 */
function zaito_video_start( $app_id, $job_id ) {
	$q = zaito_video_job_questions( $job_id );
	if ( ! $q ) {
		return false;
	}
	update_post_meta( $app_id, 'video_status', 'pending' );
	update_post_meta( $app_id, 'video_questions', $q );
	update_post_meta( $app_id, 'company_hidden', '1' );
	return true;
}

/* ---------- 学生：質問を開く・送る ---------- */

/** 学生の画面からの呼び出しの共通チェック。問題がなければ応募IDを返す。 */
function zaito_video_student_guard() {
	$app_id = isset( $_POST['c'] ) ? (int) $_POST['c'] : 0;
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'zaito_video_' . $app_id ) ) {
		wp_send_json_error( array( 'message' => 'ページを読み込み直してから、もう一度お試しください' ), 400 );
	}
	if ( 'student' !== zaito_direct_viewer_role( $app_id ) ) {
		wp_send_json_error( array( 'message' => 'この応募に答える権限がありません' ), 403 );
	}
	if ( 'pending' !== zaito_video_status( $app_id ) ) {
		wp_send_json_error( array( 'message' => 'この応募の回答はもう送られています', 'done' => true ), 400 );
	}
	return $app_id;
}

/** 質問を1つ開く。前の問題に答え終わるまで、次の質問は見せない。 */
function zaito_handle_video_question() {
	$app_id = zaito_video_student_guard();
	$i      = isset( $_POST['i'] ) ? (int) $_POST['i'] : -1;
	$qs     = zaito_video_questions( $app_id );
	if ( $i < 0 || $i >= count( $qs ) || $i > zaito_video_answered_count( $app_id ) ) {
		wp_send_json_error( array( 'message' => '前の質問に答えてから進んでください' ), 400 );
	}
	$opened = get_post_meta( $app_id, 'video_opened', true );
	$opened = is_array( $opened ) ? $opened : array();
	if ( empty( $opened[ $i ] ) ) {
		$opened[ $i ] = time();
		update_post_meta( $app_id, 'video_opened', $opened );
	}
	wp_send_json_success( array( 'question' => $qs[ $i ] ) );
}
add_action( 'wp_ajax_zaito_video_question', 'zaito_handle_video_question' );
add_action( 'wp_ajax_nopriv_zaito_video_question', 'zaito_handle_video_question' );

/** 1問分の動画を受け取る。同じ問題をもう一度送ったら、前のファイルと入れ替える。 */
function zaito_handle_video_upload() {
	$app_id = zaito_video_student_guard();
	$i      = isset( $_POST['i'] ) ? (int) $_POST['i'] : -1;
	$qs     = zaito_video_questions( $app_id );
	$opened = get_post_meta( $app_id, 'video_opened', true );
	if ( $i < 0 || $i >= count( $qs ) || ! is_array( $opened ) || empty( $opened[ $i ] ) ) {
		wp_send_json_error( array( 'message' => '質問を開いてから録画してください' ), 400 );
	}
	$files = zaito_video_files( $app_id );
	$tries = isset( $files[ $i ]['tries'] ) ? (int) $files[ $i ]['tries'] : 0;
	if ( $tries >= zaito_video_max_uploads() ) {
		wp_send_json_error( array( 'message' => 'この質問の回答は、これ以上送れません' ), 400 );
	}
	if ( zaito_lp_rate_limited( 'video_upload', 30 ) ) {
		wp_send_json_error( array( 'message' => '送信回数が多すぎます。しばらくしてからお試しください' ), 400 );
	}
	if ( empty( $_FILES['video'] ) || ! is_array( $_FILES['video'] ) || UPLOAD_ERR_OK !== (int) $_FILES['video']['error'] || ! is_uploaded_file( $_FILES['video']['tmp_name'] ) ) {
		wp_send_json_error( array( 'message' => '動画を受け取れませんでした。通信を確認して、もう一度お試しください' ), 400 );
	}
	$size = (int) $_FILES['video']['size'];
	if ( $size <= 0 || $size > zaito_video_max_bytes() ) {
		wp_send_json_error( array( 'message' => '動画が大きすぎます（1問50MBまで）' ), 400 );
	}
	// 中身で形式を確かめる（ファイル名や送られてきた形式名は信じない）。
	$mime = '';
	if ( function_exists( 'finfo_open' ) ) {
		$fi   = finfo_open( FILEINFO_MIME_TYPE );
		$mime = (string) finfo_file( $fi, $_FILES['video']['tmp_name'] );
		finfo_close( $fi );
	}
	$types = array(
		'video/mp4'       => 'mp4',
		'video/quicktime' => 'mp4',
		'video/webm'      => 'webm',
		'audio/webm'      => 'webm', // 一部のブラウザの webm は、こう判定されることがある
	);
	if ( ! isset( $types[ $mime ] ) ) {
		wp_send_json_error( array( 'message' => 'この形式の動画は受け付けていません' ), 400 );
	}
	$ext = $types[ $mime ];
	$dir = zaito_video_app_dir( $app_id, true );
	if ( ! $dir || ! is_dir( $dir ) ) {
		wp_send_json_error( array( 'message' => '保存できませんでした。時間をおいてもう一度お試しください' ), 500 );
	}
	$name = 'q' . $i . '.' . $ext;
	$tmp  = $dir . '/.' . $name . '.' . wp_generate_password( 8, false ) . '.part';
	if ( ! move_uploaded_file( $_FILES['video']['tmp_name'], $tmp ) ) {
		wp_send_json_error( array( 'message' => '保存できませんでした。時間をおいてもう一度お試しください' ), 500 );
	}
	@chmod( $tmp, 0600 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	// 前に送った同じ問題のファイル（形式が違うこともある）を消してから入れ替える。
	foreach ( array( 'mp4', 'webm' ) as $old ) {
		if ( $old !== $ext && file_exists( $dir . '/q' . $i . '.' . $old ) ) {
			@unlink( $dir . '/q' . $i . '.' . $old ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		}
	}
	rename( $tmp, $dir . '/' . $name );
	$files[ $i ] = array(
		'file'  => $name,
		'mime'  => 'webm' === $ext ? 'video/webm' : 'video/mp4',
		'size'  => $size,
		'at'    => time(),
		'tries' => $tries + 1,
	);
	ksort( $files );
	update_post_meta( $app_id, 'video_files', $files );
	wp_send_json_success( array( 'answered' => zaito_video_answered_count( $app_id ) ) );
}
add_action( 'wp_ajax_zaito_video_upload', 'zaito_handle_video_upload' );
add_action( 'wp_ajax_nopriv_zaito_video_upload', 'zaito_handle_video_upload' );

/** 全部そろったら「回答を送る」。ここで初めて企業に応募を見せ、知らせる。 */
function zaito_handle_video_finish() {
	$app_id = zaito_video_student_guard();
	$qs     = zaito_video_questions( $app_id );
	if ( zaito_video_answered_count( $app_id ) < count( $qs ) ) {
		wp_send_json_error( array( 'message' => 'まだ答えていない質問があります' ), 400 );
	}
	update_post_meta( $app_id, 'video_status', 'done' );
	update_post_meta( $app_id, 'video_done_at', current_time( 'mysql' ) );
	delete_post_meta( $app_id, 'company_hidden' );
	zaito_direct_add_message( $app_id, 'student', (int) get_post_meta( $app_id, 'interest_id', true ), '動画で質問に答えました（' . count( $qs ) . '問）。', false );
	zaito_direct_complete_application( $app_id );
	wp_send_json_success( array( 'url' => zaito_direct_chat_url( $app_id ) ) );
}
add_action( 'wp_ajax_zaito_video_finish', 'zaito_handle_video_finish' );
add_action( 'wp_ajax_nopriv_zaito_video_finish', 'zaito_handle_video_finish' );

/* ---------- 企業と運営：再生 ---------- */

/**
 * 動画を渡す（/wp-admin/admin-ajax.php?action=zaito_video_play&c={応募ID}&i={番号}）。
 * 応募先の企業と運営だけ。iPhoneのSafariで再生できるよう、一部だけを渡す（Range）にも対応する。
 */
function zaito_handle_video_play() {
	$app_id = isset( $_GET['c'] ) ? (int) $_GET['c'] : 0;
	$i      = isset( $_GET['i'] ) ? (int) $_GET['i'] : -1;
	$role   = $app_id ? zaito_direct_viewer_role( $app_id ) : '';
	$files  = $app_id ? zaito_video_files( $app_id ) : array();
	if ( ! in_array( $role, array( 'company', 'admin' ), true ) || 'done' !== zaito_video_status( $app_id ) || empty( $files[ $i ]['file'] ) ) {
		status_header( 404 );
		exit;
	}
	$dir  = zaito_video_app_dir( $app_id );
	$path = $dir ? $dir . '/' . basename( $files[ $i ]['file'] ) : '';
	if ( ! $path || ! is_file( $path ) ) {
		status_header( 404 );
		exit;
	}
	zaito_video_stream( $path, $files[ $i ]['mime'] );
}
add_action( 'wp_ajax_zaito_video_play', 'zaito_handle_video_play' );

/** ファイルを渡す（Range対応）。 */
function zaito_video_stream( $path, $mime ) {
	while ( ob_get_level() ) {
		ob_end_clean();
	}
	$size  = filesize( $path );
	$start = 0;
	$end   = $size - 1;
	$range = isset( $_SERVER['HTTP_RANGE'] ) ? (string) $_SERVER['HTTP_RANGE'] : '';
	if ( preg_match( '/^bytes=(\d*)-(\d*)$/', trim( $range ), $m ) && ( '' !== $m[1] || '' !== $m[2] ) ) {
		if ( '' === $m[1] ) {
			$start = max( 0, $size - (int) $m[2] ); // 末尾から N バイト
		} else {
			$start = (int) $m[1];
			$end   = '' === $m[2] ? $end : min( $end, (int) $m[2] );
		}
		if ( $start > $end || $start >= $size ) {
			header( 'Content-Range: bytes */' . $size );
			status_header( 416 );
			exit;
		}
		status_header( 206 );
		header( 'Content-Range: bytes ' . $start . '-' . $end . '/' . $size );
	} else {
		status_header( 200 );
	}
	header( 'Content-Type: ' . ( 'video/webm' === $mime ? 'video/webm' : 'video/mp4' ) );
	header( 'Content-Length: ' . ( $end - $start + 1 ) );
	header( 'Accept-Ranges: bytes' );
	header( 'Content-Disposition: inline' );
	header( 'Cache-Control: private, no-store' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Robots-Tag: noindex, nofollow' );
	$fp = fopen( $path, 'rb' );
	if ( ! $fp ) {
		exit;
	}
	fseek( $fp, $start );
	$left = $end - $start + 1;
	while ( $left > 0 && ! feof( $fp ) && ! connection_aborted() ) {
		$chunk = fread( $fp, (int) min( 262144, $left ) );
		if ( false === $chunk || '' === $chunk ) {
			break;
		}
		echo $chunk; // phpcs:ignore WordPress.Security.EscapeOutput -- 動画のバイナリ
		flush();
		$left -= strlen( $chunk );
	}
	fclose( $fp );
	exit;
}

/** 再生のURL。 */
function zaito_video_play_url( $app_id, $i ) {
	return add_query_arg( array( 'action' => 'zaito_video_play', 'c' => (int) $app_id, 'i' => (int) $i ), admin_url( 'admin-ajax.php' ) );
}

/* ---------- 消す ---------- */

/** 応募の動画をすべて消す。$reason は expired（90日）/ withdrawn（取り消し）/ deleted（応募の削除）。 */
function zaito_video_delete( $app_id, $reason ) {
	$dir = zaito_video_app_dir( $app_id );
	if ( $dir && is_dir( $dir ) ) {
		foreach ( (array) scandir( $dir ) as $f ) {
			if ( '.' !== $f && '..' !== $f && is_file( $dir . '/' . $f ) ) {
				@unlink( $dir . '/' . $f ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			}
		}
		@rmdir( $dir ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}
	if ( '' !== zaito_video_status( $app_id ) ) {
		update_post_meta( $app_id, 'video_status', 'deleted' );
		update_post_meta( $app_id, 'video_deleted_at', current_time( 'mysql' ) );
		update_post_meta( $app_id, 'video_deleted_reason', $reason );
		delete_post_meta( $app_id, 'video_files' );
	}
}

/** 応募の日から90日たった動画を消す（cron/check-closed.php から毎日）。消した数を返す。 */
function zaito_video_purge() {
	$ids = get_posts( array(
		'post_type'      => 'zaito_application',
		'post_status'    => 'any',
		'posts_per_page' => 500,
		'fields'         => 'ids',
		'date_query'     => array( array( 'before' => gmdate( 'Y-m-d H:i:s', time() - zaito_video_keep_days() * DAY_IN_SECONDS ), 'column' => 'post_date_gmt' ) ),
		'meta_query'     => array( array( 'key' => 'video_status', 'value' => array( 'pending', 'done' ), 'compare' => 'IN' ) ),
	) );
	foreach ( $ids as $id ) {
		zaito_video_delete( $id, 'expired' );
	}
	return count( $ids );
}

/** 応募そのものを消すとき（退会の処理など）は、動画のファイルも消す。 */
function zaito_video_on_delete_post( $post_id ) {
	if ( 'zaito_application' === get_post_type( $post_id ) ) {
		zaito_video_delete( $post_id, 'deleted' );
	}
}
add_action( 'before_delete_post', 'zaito_video_on_delete_post' );

/* ---------- 学生：応募の取り消し ---------- */

/** 学生が応募を取り消す（/chat/ の画面のボタン）。動画はその場で消す。 */
function zaito_handle_direct_withdraw() {
	$app_id = isset( $_POST['c'] ) ? (int) $_POST['c'] : 0;
	check_admin_referer( 'zaito_direct_withdraw_' . $app_id );
	if ( 'student' !== zaito_direct_viewer_role( $app_id ) ) {
		wp_safe_redirect( home_url( '/mypage/' ) );
		exit;
	}
	$status = zaito_direct_app_status( $app_id );
	if ( in_array( $status, array( 'new', 'talking', 'interview' ), true ) ) {
		$hidden = (bool) get_post_meta( $app_id, 'company_hidden', true );
		update_post_meta( $app_id, 'status', 'declined' );
		update_post_meta( $app_id, 'status_at', current_time( 'mysql' ) );
		update_post_meta( $app_id, 'withdrawn_at', current_time( 'mysql' ) );
		zaito_video_delete( $app_id, 'withdrawn' );
		// 企業がまだ見ていない応募（動画の回答の途中）なら、企業には知らせない。
		if ( ! $hidden ) {
			zaito_direct_add_message( $app_id, 'student', (int) get_post_meta( $app_id, 'interest_id', true ), '応募を取り消しました。' );
		}
	}
	wp_safe_redirect( add_query_arg( 'withdrawn', '1', zaito_direct_chat_url( $app_id ) ) );
	exit;
}
add_action( 'admin_post_zaito_direct_withdraw', 'zaito_handle_direct_withdraw' );
add_action( 'admin_post_nopriv_zaito_direct_withdraw', 'zaito_handle_direct_withdraw' );
