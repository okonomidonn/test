<?php
/**
 * 動画で答える応募の試作ページ（/demo-interview/）。試験環境でだけ開ける（本番では出さない）。検索にも出さない。
 * 中身は demo/interview.html（ブラウザだけで動き、録画した動画はその端末の中にだけ保存する。サーバーには何も送らない）。
 * 営業で企業に見せるときと、運営が動きを確かめるときに使う。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function zaito_demo_interview() {
    // 本番では出さない（2026-11-13 運営者の指示。本番の企業向けページには画面のイメージ図だけを載せる）。試験環境でだけ開ける。
    if ( ! defined( 'ZAITO_STAGING' ) || ! ZAITO_STAGING ) {
        return;
    }
    $path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
    if ( '/demo-interview/' !== $path && '/demo-interview' !== $path ) {
        return;
    }
    $file = get_template_directory() . '/demo/interview.html';
    if ( ! is_readable( $file ) ) {
        return;
    }
    status_header( 200 );
    nocache_headers();
    header( 'Content-Type: text/html; charset=utf-8' );
    header( 'X-Robots-Tag: noindex, nofollow' );
    // カメラとマイクは、このページ（同じサイト）の中でだけ使う。
    header( 'Permissions-Policy: camera=(self), microphone=(self)' );
    header( 'X-Frame-Options: SAMEORIGIN' );
    header( 'X-Content-Type-Options: nosniff' );
    readfile( $file );
    exit;
}
add_action( 'init', 'zaito_demo_interview', 0 );
