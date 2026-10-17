<?php
/**
 * 在宅バイトの記事（/guide/ と /guide/{slug}/）。
 *
 * 検索で探している人（「大学生 在宅バイト」など）に役立つ読み物を載せ、求人一覧へつなぐ。
 * 記事の本文は lp/guides/{slug}.php に書き、ここには一覧に出す情報だけを持つ。
 * 記事を足すときは zaito_guides() に1件足し、同じ名前のファイルを lp/guides/ に置く。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function zaito_guides() {
    return array(
        'daigakusei-zaitaku-baito' => array(
            'title'       => '大学生におすすめの在宅バイト｜職種ごとの仕事内容・時給と、未経験から始めやすい仕事',
            'h1'          => '大学生におすすめの在宅バイト',
            'description' => '大学生が家でできる在宅バイトを、職種ごとに仕事内容・時給の目安・未経験から始めやすいかで紹介します。zaitoに掲載中の、出社なしで学生OKの求人をもとにまとめています。',
            'published'   => '2026-10-17',
            'updated'     => '2026-10-17',
        ),
        'zaitaku-baito-sagi' => array(
            'title'       => '在宅バイトの詐欺の見分け方｜応募前に確かめる7つのポイントと相談先',
            'h1'          => '在宅バイトの詐欺の見分け方',
            'description' => '「スマホだけで高収入」「先に教材費が必要」など、在宅バイトでよくある詐欺・トラブルの見分け方と、困ったときの相談先をまとめました。応募前に確かめたい7つのポイントです。',
            'published'   => '2026-10-17',
            'updated'     => '2026-10-17',
        ),
    );
}

function zaito_guide_url( $slug = '' ) {
    return home_url( '/guide/' . ( $slug ? $slug . '/' : '' ) );
}

function zaito_guide_rewrite() {
    add_rewrite_rule( '^guide/?$', 'index.php?zaito_page=guide', 'top' );
    add_rewrite_rule( '^guide/([a-z0-9-]+)/?$', 'index.php?zaito_page=guide&zaito_guide=$matches[1]', 'top' );
}
add_action( 'init', 'zaito_guide_rewrite' );

function zaito_guide_query_vars( $vars ) {
    $vars[] = 'zaito_guide';
    return $vars;
}
add_filter( 'query_vars', 'zaito_guide_query_vars' );

function zaito_guide_template_redirect() {
    if ( 'guide' !== get_query_var( 'zaito_page' ) ) {
        return;
    }
    $slug   = (string) get_query_var( 'zaito_guide' );
    $guides = zaito_guides();
    if ( '' !== $slug && ! isset( $guides[ $slug ] ) ) {
        return; // 存在しない記事は通常の404へ。
    }
    status_header( 200 );
    header( 'Content-Type: text/html; charset=UTF-8' );
    $zaito_guide_slug = $slug;
    include get_template_directory() . '/lp/guide.php';
    exit;
}
add_action( 'template_redirect', 'zaito_guide_template_redirect', -1 );

/**
 * 記事の中で使う：掲載中の求人から、条件に合うものを返す（募集終了は除く）。
 */
function zaito_guide_jobs( $filter ) {
    return array_values( array_filter( zaito_remote_jobs(), function ( $j ) use ( $filter ) {
        return empty( $j['closed'] ) && $filter( $j );
    } ) );
}

/**
 * 記事の中で使う：求人の小さなカード（タイトル・会社・時給）を並べる。
 */
function zaito_guide_job_list( $jobs, $max = 3 ) {
    if ( ! $jobs ) {
        return;
    }
    echo '<ul class="g-jobs">';
    foreach ( array_slice( $jobs, 0, $max ) as $j ) {
        echo '<li><a href="' . esc_url( zaito_remote_url( $j['slug'] ) ) . '"><b>' . esc_html( $j['title'] ) . '</b><span>' . esc_html( $j['company'] ) . '</span>';
        if ( '' !== $j['pay'] ) {
            echo '<span class="gp">' . esc_html( $j['pay'] ) . '</span>';
        }
        echo '</a></li>';
    }
    echo '</ul>';
}
