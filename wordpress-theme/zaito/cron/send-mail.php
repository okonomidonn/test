<?php
/**
 * サーバーのCronから毎日動かす：一人ひとりに合わせた求人メールの自動配信（inc/nl-auto.php）。
 * ブラウザからは動かさない（コマンドラインのときだけ動く）。
 * 送らずに中身だけ確かめる: php cron/send-mail.php --dry
 * Cronのコマンド: /usr/bin/php8.3 zaito-work.com/public_html/wp-content/themes/zaito/cron/send-mail.php
 */
if ( 'cli' !== PHP_SAPI ) {
    http_response_code( 404 );
    exit;
}
// コマンドラインでは https で動いていると分からないため、メールのリンクが http になる。本番と同じく https として動かす。
$_SERVER['HTTPS']     = 'on';
$_SERVER['HTTP_HOST'] = 'zaito-work.com';
define( 'WP_USE_THEMES', false );
require dirname( __DIR__, 4 ) . '/wp-load.php';
set_time_limit( 0 );
// 見本: php cron/send-mail.php --sample … 送り先になる人を数人選び、その人あての中身を info@ に見本として送る（本人には送らない）。
if ( in_array( '--sample', $argv, true ) ) {
    $n = 0;
    foreach ( zaito_nla_due( 30 ) as $id ) {
        $slugs = zaito_nla_pick( $id );
        if ( count( $slugs ) < ZAITO_NLA_MIN ) {
            continue;
        }
        $p    = zaito_reco_prefs_for( $id );
        $note = '【見本】登録ID ' . $id . '（やってみたい仕事：' . ( $p['interests'] ? implode( '・', $p['interests'] ) : '未回答' ) . '／保存：' . count( $p['favorites'] ) . "件）あての内容です。本人には送っていません。\n\n";
        $body = $note . zaito_nla_body( 'zaitoです。登録のときに教えていただいた希望と、保存した求人をもとに、あなたに合いそうな出社なしの在宅バイトを選びました。', $slugs, 'sample', 0 );
        zaito_nl_send_one( 'info@zaito-work.com', '【見本】あなたに合いそうな在宅バイトが' . count( $slugs ) . '件あります', $body, home_url( '/' ) );
        if ( ++$n >= 3 ) {
            break;
        }
    }
    echo 'samples ' . $n . "\n";
    exit;
}
$dry = in_array( '--dry', $argv, true );
$r   = zaito_nla_run( $dry );
echo ( $dry ? '[dry] ' : '' ) . 'sent ' . $r['sent'] . ', skipped ' . $r['skipped'] . ', failed ' . $r['failed'] . ', planned ' . count( $r['plan'] ) . "\n";
foreach ( $r['plan'] as $id => $slugs ) {
    echo $id . ': ' . implode( ', ', $slugs ) . "\n";
}
