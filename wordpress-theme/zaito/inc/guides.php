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
        'zaitaku-chouki-intern' => array(
            'title'       => '在宅でできる長期インターン｜大学生向けの職種・時給・探し方と選考の流れ',
            'h1'          => '在宅でできる長期インターン',
            'description' => '出社なしで参加できる長期インターンを、職種ごとの仕事内容・時給・求められる稼働時間で紹介します。探し方や選考の流れ、就活での活かし方もまとめました。',
            'published'   => '2026-10-17',
            'updated'     => '2026-10-17',
        ),
        'mikeiken-zaitaku-baito' => array(
            'title'       => '未経験OKの在宅バイト｜経験がなくても始めやすい仕事と、採用されやすい応募のコツ',
            'h1'          => '未経験OKの在宅バイト',
            'description' => '経験がなくても始めやすい在宅バイトを、職種ごとの仕事内容・時給・契約の形で紹介します。未経験から採用されやすい応募のコツもまとめました。',
            'published'   => '2026-10-17',
            'updated'     => '2026-10-17',
        ),
        'zaitaku-baito-koujikyu' => array(
            'title'       => '高時給の在宅バイト｜時給1,500円以上の仕事と、時給を上げるために身につけたいこと',
            'h1'          => '高時給の在宅バイト',
            'description' => '時給1,500円以上の在宅バイトを、職種ごとの仕事内容・時給・求められる経験で紹介します。未経験から応募できる高時給の求人や、時給を上げるために身につけたいスキルもまとめました。',
            'published'   => '2026-10-18',
            'updated'     => '2026-10-18',
        ),
        'shufu-zaitaku-work' => array(
            'title'       => '主婦・子育て中の在宅ワーク｜家事や育児と両立しやすい仕事と、探すときのポイント',
            'h1'          => '主婦・子育て中の在宅ワーク',
            'description' => '主婦・子育て中の人が家で働ける在宅ワークを、職種ごとの仕事内容・時給・働く時間で紹介します。扶養や子どもの急な体調不良など、探すときに確かめたいポイントもまとめました。',
            'published'   => '2026-10-18',
            'updated'     => '2026-10-18',
        ),
        'zaitaku-ok-kanzen-zaitaku' => array(
            'title'       => '「在宅OK」と「完全在宅」の違い｜求人の書き方で分かる、出社がある・ないの見分け方',
            'h1'          => '「在宅OK」と「完全在宅」の違い',
            'description' => '「在宅OK」「リモート可」「フルリモート」「完全在宅」は、求人によって意味が違います。研修や会議で出社がある求人を避けるために、募集ページで確かめたい書き方をまとめました。',
            'published'   => '2026-10-18',
            'updated'     => '2026-10-18',
        ),
        'zaitaku-jimu-cs-baito' => array(
            'title'       => '在宅でできる事務・カスタマーサポートのバイト｜仕事内容・時給と、電話なしで探すコツ',
            'h1'          => '在宅でできる事務・カスタマーサポートのバイト',
            'description' => '出社なしでできる事務・カスタマーサポートのバイトを、仕事内容・時給の目安・未経験から始めやすいかで紹介します。電話の有無や働く部屋の条件など、応募前に確かめたいこともまとめました。',
            'published'   => '2026-10-22',
            'updated'     => '2026-10-22',
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
    // 存在しない記事は、404を返して記事一覧を見せる（テーマの古いトップが200で出ないように）。
    $found = '' === $slug || isset( $guides[ $slug ] );
    if ( ! $found ) {
        $slug = '';
    }
    status_header( $found ? 200 : 404 );
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

/**
 * 記事の中で使う：求人を分類（zaito_remote_group）ごとにまとめ、各分類の中は時給（下限）の高い順に並べる。
 * 戻り値は 分類名 => 求人の配列（求人の多い分類から）。
 */
function zaito_guide_by_group( $jobs ) {
    $by = array();
    foreach ( $jobs as $j ) {
        $by[ zaito_remote_group( $j['category'] )[0] ][] = $j;
    }
    foreach ( $by as $g => $list ) {
        usort( $list, function ( $a, $b ) {
            return zaito_remote_hourly_min( $b['pay'] ) - zaito_remote_hourly_min( $a['pay'] );
        } );
        $by[ $g ] = $list;
    }
    uasort( $by, function ( $a, $b ) {
        return count( $b ) - count( $a );
    } );
    return $by;
}

/**
 * 記事の中で使う：求人の時給（下限）の範囲を「1,200円〜2,000円」の形で返す。時給の分かる求人がなければ空文字。
 */
function zaito_guide_pay_range( $jobs ) {
    $pays = array_values( array_filter( array_map( function ( $j ) {
        return zaito_remote_hourly_min( $j['pay'] );
    }, $jobs ) ) );
    if ( ! $pays ) {
        return '';
    }
    return min( $pays ) === max( $pays ) ? number_format( min( $pays ) ) . '円〜' : number_format( min( $pays ) ) . '円〜' . number_format( max( $pays ) ) . '円';
}
