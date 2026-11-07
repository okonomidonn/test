<?php
/**
 * 登録した学生から退会・個人情報の削除を頼まれたときに、その人の登録をまとめて消す（コマンドラインだけで動く）。
 *
 * 確かめるだけ: php cron/delete-member.php --email=someone@example.com
 * 本当に消す:   php cron/delete-member.php --email=someone@example.com --do
 * 消したあと本人に「削除しました」のメールも送る: --notify=宛名（例: --notify=Chiharu）を足す
 *
 * 消すもの:
 * - 登録（zaito_interest の投稿と、そのメタ：メールアドレス・名前・学校・希望・保存した求人・メールの記録など）
 * - その人の応募（zaito_application）
 * - 集計の中に残っている、その人の登録ID（応募ページへ進んだ記録・応募の状況・メール配信の反応）
 * 結果は info@ に送る（メールアドレスは書かず、登録IDと件数だけ）。
 */
if ( 'cli' !== PHP_SAPI ) {
    http_response_code( 404 );
    exit;
}
$_SERVER['HTTPS']     = 'on';
$_SERVER['HTTP_HOST'] = 'zaito-work.com';
define( 'WP_USE_THEMES', false );
require dirname( __DIR__, 4 ) . '/wp-load.php';

$email  = '';
$do     = false;
$notify = '';
foreach ( array_slice( $argv, 1 ) as $a ) {
    if ( 0 === strpos( $a, '--email=' ) ) {
        $email = strtolower( trim( substr( $a, 8 ) ) );
    } elseif ( '--do' === $a ) {
        $do = true;
    } elseif ( 0 === strpos( $a, '--notify=' ) ) {
        $notify = trim( substr( $a, 9 ) );
    }
}
if ( ! is_email( $email ) ) {
    fwrite( STDERR, "--email=メールアドレス を指定してください\n" );
    exit( 1 );
}

$ids = get_posts( array(
    'post_type'      => 'zaito_interest',
    'post_status'    => 'any',
    'posts_per_page' => -1,
    'fields'         => 'ids',
    'meta_query'     => array( array( 'key' => 'email', 'value' => $email ) ),
) );
$apps = array();
foreach ( $ids as $id ) {
    $apps = array_merge( $apps, get_posts( array(
        'post_type'      => 'zaito_application',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => array( array( 'key' => 'interest_id', 'value' => (int) $id ) ),
    ) ) );
}

$summary = '登録ID: ' . ( $ids ? implode( ',', $ids ) : 'なし' ) . ' / 応募: ' . count( $apps ) . '件';
if ( ! $do ) {
    echo '（確かめるだけ）' . $summary . "\n";
    exit( 0 );
}

// 集計の中の登録IDを消す。
$strip = function ( $option, $path ) use ( $ids ) {
    $stats = get_option( $option, array() );
    if ( ! is_array( $stats ) ) {
        return;
    }
    foreach ( $stats as $k => $row ) {
        foreach ( $ids as $id ) {
            if ( 'top' === $path ) {
                unset( $stats[ $k ][ $id ] );
            } elseif ( isset( $stats[ $k ][ $path ] ) && is_array( $stats[ $k ][ $path ] ) ) {
                unset( $stats[ $k ][ $path ][ $id ] );
            }
        }
    }
    update_option( $option, $stats, false );
};
$strip( 'zaito_remote_stats', 'members' );
$strip( 'zaito_apply_stats', 'top' );
foreach ( array( 'returns', 'clicks', 'unsub' ) as $kind ) {
    $strip( 'zaito_nl_stats', $kind );
}

foreach ( $apps as $app ) {
    wp_delete_post( $app, true );
}
foreach ( $ids as $id ) {
    wp_delete_post( $id, true );
}

$left = get_posts( array(
    'post_type'      => 'zaito_interest',
    'post_status'    => 'any',
    'posts_per_page' => -1,
    'fields'         => 'ids',
    'meta_query'     => array( array( 'key' => 'email', 'value' => $email ) ),
) );
$result = ( $left ? '消し残りあり（' . count( $left ) . '件）' : '削除しました' ) . '：' . $summary;
echo $result . "\n";
// 本人への知らせ。消し残りがないときだけ送る。
$sent_note = '';
if ( '' !== $notify && ! $left && $ids ) {
    $body  = $notify . " 様\n\n";
    $body .= "いつもzaitoをご利用いただき、誠にありがとうございます。\n";
    $body .= "zaito運営事務局です。\n\n";
    $body .= "ご依頼いただきました退会のお手続きが完了いたしましたので、お知らせいたします。\n";
    $body .= "ご登録いただいていたメールアドレスをはじめとする個人情報は、すべて削除いたしました。今後、zaitoからメールをお送りすることはございません。\n\n";
    $body .= "また、退会のお手続き方法がわかりにくく、ご不便をおかけしましたことをお詫び申し上げます。\n\n";
    $body .= "これまでzaitoをご利用いただき、誠にありがとうございました。\n\n";
    $body .= "――――――――――――\nzaito運営事務局\ninfo@zaito-work.com\nhttps://zaito-work.com/\n";
    $ok        = wp_mail( $email, '【zaito】退会手続き完了のお知らせ', $body, array( 'From: zaito <info@zaito-work.com>', 'Content-Type: text/plain; charset=UTF-8' ) );
    $sent_note = $ok ? '本人に削除完了のメールを送りました。' : '本人へのメールの送信に失敗しました。';
    echo $sent_note . "\n";
}
wp_mail( 'info@zaito-work.com', '【zaito運営】退会の依頼による登録の削除', $result . "\n" . $sent_note . "\n（メールアドレスはこのメールに書いていません）" );
