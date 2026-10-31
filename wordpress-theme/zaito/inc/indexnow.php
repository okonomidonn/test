<?php
/**
 * IndexNow（ページの追加・更新を Bing などの検索エンジンにすぐ知らせる仕組み）の確認用ファイル。
 *
 * IndexNow では、サイトの直下に「キー.txt」を置き、中身をキーにする。ファイルを置く代わりに、
 * このテーマが https://zaito-work.com/{キー}.txt を返す。キーは公開してよい値（秘密ではない）。
 * 知らせる処理は .github/workflows/deploy-wordpress.yml の最後（サイトに反映したあと、サイトマップのURLを送る）。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ZAITO_INDEXNOW_KEY', '80e74d193dab765a497be33ec450ff9d' );

function zaito_indexnow_key_file() {
    $path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
    if ( '/' . ZAITO_INDEXNOW_KEY . '.txt' !== $path ) {
        return;
    }
    status_header( 200 );
    header( 'Content-Type: text/plain; charset=utf-8' );
    echo ZAITO_INDEXNOW_KEY; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 固定の英数字
    exit;
}
add_action( 'init', 'zaito_indexnow_key_file', 0 );
