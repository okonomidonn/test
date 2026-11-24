<?php
/**
 * 録画面接の質問の読み上げ音声（Google Cloud Text-to-Speech）。
 *
 * - 質問の文から音声（MP3）を作り、uploads/zaito-tts/ に置いて使い回す。同じ文は2回目から作らない。
 * - Googleに送るのは質問の文（企業が書いた質問と、練習の質問）だけ。学生の情報は送らない。
 * - APIキーは public_html の外の zaito-secrets.php（デプロイのときに GitHub の秘密情報 GOOGLE_TTS_API_KEY から作る）で定義する。
 *   試験環境は、本番と同じファイルを読む。
 * - キーがない・Googleが答えないときは空を返し、画面は端末の声で読み上げる（page-interview.php）。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 声の種類（日本語・女性の自然な声）と話す速さ。変えると、音声は作り直しになる。 */
define( 'ZAITO_TTS_VOICE', 'ja-JP-Neural2-B' );
define( 'ZAITO_TTS_RATE', 1.0 );

function zaito_tts_key() {
	if ( ! defined( 'ZAITO_GOOGLE_TTS_API_KEY' ) ) {
		// 本番は public_html のすぐ外、試験環境（public_html/staging.zaito-work.com）は、その1つ上の本番と同じ場所。
		$dir     = defined( 'ZAITO_STAGING' ) && ZAITO_STAGING ? dirname( dirname( ABSPATH ) ) : dirname( ABSPATH );
		$secrets = $dir . '/zaito-secrets.php';
		if ( is_readable( $secrets ) ) {
			require_once $secrets;
		}
	}
	return defined( 'ZAITO_GOOGLE_TTS_API_KEY' ) ? (string) ZAITO_GOOGLE_TTS_API_KEY : '';
}

/**
 * 文を読み上げた音声のURL。作れなければ空。
 */
function zaito_tts_url( $text ) {
	$text = trim( wp_strip_all_tags( (string) $text ) );
	if ( '' === $text || mb_strlen( $text ) > 500 ) {
		return '';
	}
	$up   = wp_upload_dir();
	$name = md5( ZAITO_TTS_VOICE . '|' . ZAITO_TTS_RATE . '|' . $text ) . '.mp3';
	$path = trailingslashit( $up['basedir'] ) . 'zaito-tts/' . $name;
	$url  = trailingslashit( $up['baseurl'] ) . 'zaito-tts/' . $name;
	if ( is_readable( $path ) && filesize( $path ) > 0 ) {
		return $url;
	}
	$key = zaito_tts_key();
	if ( '' === $key ) {
		return '';
	}
	// 失敗が続くときに毎回待たせないよう、失敗したら5分は作りにいかない。
	if ( get_transient( 'zaito_tts_down' ) ) {
		return '';
	}
	$res = wp_remote_post(
		'https://texttospeech.googleapis.com/v1/text:synthesize',
		array(
			'timeout' => 8,
			'headers' => array(
				'Content-Type'   => 'application/json; charset=utf-8',
				'X-Goog-Api-Key' => $key,
			),
			'body'    => wp_json_encode( array(
				'input'       => array( 'text' => $text ),
				'voice'       => array( 'languageCode' => 'ja-JP', 'name' => ZAITO_TTS_VOICE ),
				'audioConfig' => array( 'audioEncoding' => 'MP3', 'speakingRate' => ZAITO_TTS_RATE ),
			) ),
		)
	);
	$body = is_wp_error( $res ) ? null : json_decode( wp_remote_retrieve_body( $res ), true );
	$mp3  = ( 200 === (int) wp_remote_retrieve_response_code( $res ) && ! empty( $body['audioContent'] ) ) ? base64_decode( $body['audioContent'], true ) : false;
	if ( ! $mp3 ) {
		set_transient( 'zaito_tts_down', 1, 5 * MINUTE_IN_SECONDS );
		error_log( 'zaito tts: failed ' . ( is_wp_error( $res ) ? $res->get_error_message() : wp_remote_retrieve_response_code( $res ) ) );
		return '';
	}
	wp_mkdir_p( dirname( $path ) );
	if ( false === file_put_contents( $path, $mp3 ) ) {
		return '';
	}
	return $url;
}

/** 練習の質問（画面に出す文と、読み上げる文）。 */
function zaito_tts_practice() {
	return array(
		'text'  => '練習：好きな食べ物と、その理由を教えてください（この回答は記録されません）。',
		'speak' => '練習の質問です。好きな食べ物と、その理由を教えてください。',
	);
}

/**
 * 企業が求人を保存したときや、運営が承認したときに、質問の音声を先に作っておく（最初の学生を待たせないため）。
 */
function zaito_tts_prepare_job( $job_id ) {
	if ( '' === zaito_tts_key() ) {
		return;
	}
	$qs = get_post_meta( (int) $job_id, '_job_video_questions', true );
	foreach ( is_array( $qs ) ? $qs : array() as $q ) {
		zaito_tts_url( $q );
	}
	zaito_tts_url( zaito_tts_practice()['speak'] );
}
add_action( 'updated_post_meta', function ( $mid, $post_id, $key ) {
	if ( '_job_video_questions' === $key ) {
		zaito_tts_prepare_job( $post_id );
	}
}, 10, 3 );
add_action( 'added_post_meta', function ( $mid, $post_id, $key ) {
	if ( '_job_video_questions' === $key ) {
		zaito_tts_prepare_job( $post_id );
	}
}, 10, 3 );
