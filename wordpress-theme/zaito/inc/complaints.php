<?php
/**
 * ご意見・苦情・相談の窓口（/contact/）。
 *
 * 特定募集情報等提供事業者は、求職者・求人企業からの苦情・相談を受け付け、適切に対応しなければならない
 * （職業安定法・指針。2026-11-25 の労働局の電話でも、サービスの中に窓口を設けるよう言われた）。
 * - だれでも送れるフォーム。送ると運営（info@）にメールが届き、送った人には受付のメールが届く。
 * - 受けた苦情・相談は、管理画面の「苦情・相談」に残し、対応の状況と記録を書き込む（対応の記録を残すため）。
 * - 返事は info@ から運営がメールで送る。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 立場と、相談の種類。 */
function zaito_complaint_roles() {
	return array( 'student' => '学生・求職者', 'company' => '求人企業', 'other' => 'その他' );
}
function zaito_complaint_kinds() {
	return array(
		'job'     => '求人の内容について（実際とちがう・出社を求められた など）',
		'contact' => '応募・企業とのやりとりについて',
		'privacy' => '個人情報について（削除の依頼など）',
		'site'    => 'サイトの使い方・不具合について',
		'other'   => 'その他',
	);
}
function zaito_complaint_statuses() {
	return array( 'new' => '未対応', 'doing' => '対応中', 'done' => '対応済み' );
}

/* ---------- 保存先（管理画面だけで見る） ---------- */

function zaito_complaint_register() {
	register_post_type( 'zaito_complaint', array(
		'labels'          => array( 'name' => '苦情・相談', 'singular_name' => '苦情・相談', 'edit_item' => '苦情・相談の対応' ),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'menu_position'   => 5,
		'menu_icon'       => 'dashicons-format-chat',
		'supports'        => array( 'title' ),
		'capability_type' => 'post',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'    => true,
	) );
	add_rewrite_rule( '^contact/?$', 'index.php?zaito_page=contact', 'top' );
}
add_action( 'init', 'zaito_complaint_register' );

/** 一覧に、立場・種類・状況・受付日を出す。 */
add_filter( 'manage_zaito_complaint_posts_columns', function ( $cols ) {
	return array(
		'cb'        => $cols['cb'],
		'title'     => '件名',
		'zc_role'   => '立場',
		'zc_kind'   => '種類',
		'zc_status' => '状況',
		'date'      => '受付日',
	);
} );
add_action( 'manage_zaito_complaint_posts_custom_column', function ( $col, $id ) {
	if ( 'zc_role' === $col ) {
		echo esc_html( zaito_complaint_roles()[ get_post_meta( $id, 'role', true ) ] ?? '' );
	} elseif ( 'zc_kind' === $col ) {
		echo esc_html( preg_replace( '/（.*$/u', '', zaito_complaint_kinds()[ get_post_meta( $id, 'kind', true ) ] ?? '' ) );
	} elseif ( 'zc_status' === $col ) {
		$st = get_post_meta( $id, 'status', true );
		echo esc_html( zaito_complaint_statuses()[ $st ? $st : 'new' ] );
	}
}, 10, 2 );

/** 対応の画面：送られた内容を見せ、状況と対応の記録を書き込む。 */
add_action( 'add_meta_boxes_zaito_complaint', function () {
	add_meta_box( 'zaito_complaint_box', '送られた内容と対応の記録', 'zaito_complaint_box', 'zaito_complaint', 'normal', 'high' );
} );
function zaito_complaint_box( $post ) {
	$m = function ( $k ) use ( $post ) {
		return (string) get_post_meta( $post->ID, $k, true );
	};
	wp_nonce_field( 'zaito_complaint_save_' . $post->ID, 'zaito_complaint_nonce' );
	echo '<table class="form-table"><tbody>';
	foreach ( array(
		'受付日'           => get_the_date( 'Y年n月j日 H:i', $post ),
		'立場'             => zaito_complaint_roles()[ $m( 'role' ) ] ?? '',
		'種類'             => zaito_complaint_kinds()[ $m( 'kind' ) ] ?? '',
		'お名前'           => $m( 'name' ),
		'メールアドレス'   => $m( 'email' ),
		'関係する求人・企業' => $m( 'job' ),
	) as $label => $value ) {
		echo '<tr><th>' . esc_html( $label ) . '</th><td>' . esc_html( $value ) . '</td></tr>';
	}
	echo '<tr><th>内容</th><td><div style="white-space:pre-wrap;background:#f6f7f7;padding:10px;border-radius:4px">' . esc_html( $m( 'body' ) ) . '</div></td></tr>';
	echo '<tr><th><label for="zc_status">状況</label></th><td><select name="zc_status" id="zc_status">';
	foreach ( zaito_complaint_statuses() as $k => $v ) {
		echo '<option value="' . esc_attr( $k ) . '"' . selected( $m( 'status' ) ? $m( 'status' ) : 'new', $k, false ) . '>' . esc_html( $v ) . '</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th><label for="zc_log">対応の記録</label></th><td><textarea name="zc_log" id="zc_log" rows="8" class="large-text" placeholder="いつ・だれに・どう対応したか（例：11/26 企業に事実を確認、11/27 求人の表記を修正して相談者に返信）">' . esc_textarea( $m( 'log' ) ) . '</textarea>';
	echo '<p class="description">返事は info@zaito-work.com から、相談者のメールアドレスへ送ってください。</p></td></tr>';
	echo '</tbody></table>';
}
add_action( 'save_post_zaito_complaint', function ( $id ) {
	if ( ! isset( $_POST['zaito_complaint_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zaito_complaint_nonce'] ) ), 'zaito_complaint_save_' . $id ) || ! current_user_can( 'edit_post', $id ) ) {
		return;
	}
	$st = isset( $_POST['zc_status'] ) ? sanitize_key( wp_unslash( $_POST['zc_status'] ) ) : 'new';
	if ( isset( zaito_complaint_statuses()[ $st ] ) ) {
		update_post_meta( $id, 'status', $st );
	}
	if ( isset( $_POST['zc_log'] ) ) {
		update_post_meta( $id, 'log', sanitize_textarea_field( wp_unslash( $_POST['zc_log'] ) ) );
	}
} );

/** 管理画面のメニューに、未対応の件数を出す。 */
add_action( 'admin_menu', function () {
	global $menu;
	$n = count( get_posts( array( 'post_type' => 'zaito_complaint', 'post_status' => 'private', 'posts_per_page' => 50, 'fields' => 'ids', 'meta_key' => 'status', 'meta_value' => 'new' ) ) );
	if ( ! $n || ! is_array( $menu ) ) {
		return;
	}
	foreach ( $menu as $i => $item ) {
		if ( isset( $item[2] ) && 'edit.php?post_type=zaito_complaint' === $item[2] ) {
			$menu[ $i ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . (int) $n . '</span></span>';
		}
	}
}, 20 );

/* ---------- 受け付け ---------- */

function zaito_handle_complaint() {
	$back = home_url( '/contact/' );
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'zaito_complaint' ) ) {
		zaito_store_form_errors_and_redirect( array( '時間がたったため送信できませんでした。もう一度送ってください。' ), $back );
	}
	if ( ! empty( $_POST['website'] ) ) { // ロボットよけ（人には見えない欄）
		wp_safe_redirect( add_query_arg( 'sent', '1', $back ) );
		exit;
	}
	$f = function ( $k, $len ) {
		return isset( $_POST[ $k ] ) ? mb_substr( trim( sanitize_textarea_field( wp_unslash( $_POST[ $k ] ) ) ), 0, $len ) : '';
	};
	$role  = $f( 'role', 20 );
	$kind  = $f( 'kind', 20 );
	$name  = $f( 'name', 60 );
	$email = strtolower( sanitize_email( $f( 'email', 200 ) ) );
	$job   = $f( 'job', 300 );
	$body  = $f( 'body', 4000 );

	$errors = array();
	if ( ! isset( zaito_complaint_roles()[ $role ] ) ) {
		$errors[] = 'あなたの立場を選んでください';
	}
	if ( ! isset( zaito_complaint_kinds()[ $kind ] ) ) {
		$errors[] = 'ご相談の種類を選んでください';
	}
	if ( ! $email || ! is_email( $email ) ) {
		$errors[] = '返事を送るメールアドレスを正しく入力してください';
	}
	if ( '' === $body ) {
		$errors[] = 'ご相談の内容を入力してください';
	}
	if ( ! $errors && zaito_lp_rate_limited( 'complaint', 5 ) ) {
		$errors[] = '送信回数が多すぎます。しばらくしてからお試しください。';
	}
	if ( $errors ) {
		set_transient( 'zaito_complaint_draft_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) ), compact( 'role', 'kind', 'name', 'email', 'job', 'body' ), 10 * MINUTE_IN_SECONDS );
		zaito_store_form_errors_and_redirect( $errors, $back );
	}

	$kinds = zaito_complaint_kinds();
	$id    = wp_insert_post( array(
		'post_type'   => 'zaito_complaint',
		'post_status' => 'private',
		'post_title'  => preg_replace( '/（.*$/u', '', $kinds[ $kind ] ) . '：' . mb_substr( preg_replace( '/\s+/u', ' ', $body ), 0, 30 ),
	) );
	if ( ! $id || is_wp_error( $id ) ) {
		zaito_store_form_errors_and_redirect( array( '送信できませんでした。時間をおいてもう一度お試しいただくか、info@zaito-work.com までメールでご連絡ください。' ), $back );
	}
	foreach ( compact( 'role', 'kind', 'name', 'email', 'job', 'body' ) + array( 'status' => 'new' ) as $k => $v ) {
		update_post_meta( $id, $k, $v );
	}

	// 運営に知らせる。
	wp_mail(
		get_option( 'admin_email' ),
		'【zaito】苦情・相談が届きました（' . preg_replace( '/（.*$/u', '', $kinds[ $kind ] ) . '）',
		"窓口のフォームから苦情・相談が届きました。内容を確認して、返事と対応の記録をお願いします。\n\n"
		. '立場: ' . zaito_complaint_roles()[ $role ] . "\n"
		. '種類: ' . $kinds[ $kind ] . "\n"
		. 'お名前: ' . $name . "\n"
		. 'メールアドレス: ' . $email . "\n"
		. '関係する求人・企業: ' . $job . "\n\n"
		. $body . "\n\n"
		. '対応の記録: ' . admin_url( 'post.php?post=' . (int) $id . '&action=edit' ) . "\n",
		array( 'Reply-To: ' . $email )
	);
	// 送った人に、受け付けたことを知らせる。
	wp_mail(
		$email,
		'【zaito】ご相談を受け付けました',
		( $name ? $name . ' 様' . "\n\n" : '' )
		. "zaito運営です。ご意見・苦情・相談の窓口に、次の内容でご連絡をいただきました。\n"
		. "内容を確認し、原則として3営業日以内に、このメールアドレスあてにご返事します。\n\n"
		. '種類: ' . $kinds[ $kind ] . "\n"
		. "内容:\n" . $body . "\n\n"
		. "お心当たりがない場合は、このメールを破棄してください。\n\n"
		. "zaito 運営\ninfo@zaito-work.com\n" . home_url( '/' ) . "\n",
		array( 'From: zaito <info@' . wp_parse_url( home_url(), PHP_URL_HOST ) . '>' )
	);
	delete_transient( 'zaito_complaint_draft_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) ) );
	wp_safe_redirect( add_query_arg( 'sent', '1', $back ) );
	exit;
}
add_action( 'admin_post_zaito_complaint', 'zaito_handle_complaint' );
add_action( 'admin_post_nopriv_zaito_complaint', 'zaito_handle_complaint' );

/** /contact/ のページ。 */
add_action( 'template_redirect', function () {
	if ( 'contact' !== get_query_var( 'zaito_page' ) ) {
		return;
	}
	status_header( 200 );
	header( 'Content-Type: text/html; charset=UTF-8' );
	include get_template_directory() . '/lp/contact.php';
	exit;
}, -1 );
