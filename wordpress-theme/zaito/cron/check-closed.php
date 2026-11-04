<?php
/**
 * サーバーのCronから毎日動かす：まとめ求人の募集終了チェック（inc/job-check.php）。
 * ブラウザからは動かさない（コマンドラインのときだけ動く）。
 * Cronのコマンド: /usr/bin/php8.3 zaito-work.com/public_html/wp-content/themes/zaito/cron/check-closed.php
 */
if ( 'cli' !== PHP_SAPI ) {
    http_response_code( 404 );
    exit;
}
define( 'WP_USE_THEMES', false );
require dirname( __DIR__, 4 ) . '/wp-load.php';
set_time_limit( 0 );
$r = zaito_job_check_run();
echo 'checked ' . $r['checked'] . ', closed ' . count( $r['new_closed'] ) . ', notes ' . count( $r['notes'] ) . "\n";
