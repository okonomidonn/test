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
            'kicker'      => '大学生',
            'icon'        => 'school',
            'h1'          => '大学生におすすめの在宅バイト',
            'description' => '大学生が家でできる在宅バイトを、職種ごとに仕事内容・時給の目安・未経験から始めやすいかで紹介します。zaitoに掲載中の、出社なしで学生OKの求人をもとにまとめています。',
            'published'   => '2026-10-17',
            'updated'     => '2026-10-22',
        ),
        'zaitaku-chouki-intern' => array(
            'title'       => '在宅でできる長期インターン｜大学生向けの職種・時給・探し方と選考の流れ',
            'kicker'      => '長期インターン',
            'icon'        => 'work_history',
            'h1'          => '在宅でできる長期インターン',
            'description' => '出社なしで参加できる長期インターンを、職種ごとの仕事内容・時給・求められる稼働時間で紹介します。探し方や選考の流れ、就活での活かし方もまとめました。',
            'published'   => '2026-10-17',
            'updated'     => '2026-10-22',
        ),
        'mikeiken-zaitaku-baito' => array(
            'title'       => '未経験OKの在宅バイト｜経験がなくても始めやすい仕事と、採用されやすい応募のコツ',
            'kicker'      => '未経験から',
            'icon'        => 'emoji_people',
            'h1'          => '未経験OKの在宅バイト',
            'description' => '経験がなくても始めやすい在宅バイトを、職種ごとの仕事内容・時給・契約の形で紹介します。未経験から採用されやすい応募のコツもまとめました。',
            'published'   => '2026-10-17',
            'updated'     => '2026-10-22',
        ),
        'zaitaku-baito-koujikyu' => array(
            'title'       => '高時給の在宅バイト｜時給1,500円以上の仕事と、時給を上げるために身につけたいこと',
            'kicker'      => '高時給',
            'icon'        => 'payments',
            'h1'          => '高時給の在宅バイト',
            'description' => '時給1,500円以上の在宅バイトを、職種ごとの仕事内容・時給・求められる経験で紹介します。未経験から応募できる高時給の求人や、時給を上げるために身につけたいスキルもまとめました。',
            'published'   => '2026-10-18',
            'updated'     => '2026-10-22',
        ),
        'shufu-zaitaku-work' => array(
            'title'       => '主婦・子育て中の在宅ワーク｜家事や育児と両立しやすい仕事と、探すときのポイント',
            'kicker'      => '主婦・子育て中',
            'icon'        => 'home',
            'h1'          => '主婦・子育て中の在宅ワーク',
            'description' => '主婦・子育て中の人が家で働ける在宅ワークを、職種ごとの仕事内容・時給・働く時間で紹介します。扶養や子どもの急な体調不良など、探すときに確かめたいポイントもまとめました。',
            'published'   => '2026-10-18',
            'updated'     => '2026-10-22',
        ),
        'zaitaku-ok-kanzen-zaitaku' => array(
            'title'       => '「在宅OK」と「完全在宅」の違い｜求人の書き方で分かる、出社がある・ないの見分け方',
            'kicker'      => '求人の見方',
            'icon'        => 'fact_check',
            'h1'          => '「在宅OK」と「完全在宅」の違い',
            'description' => '「在宅OK」「リモート可」「フルリモート」「完全在宅」は、求人によって意味が違います。研修や会議で出社がある求人を避けるために、募集ページで確かめたい書き方をまとめました。',
            'published'   => '2026-10-18',
            'updated'     => '2026-10-22',
        ),
        'zaitaku-jimu-cs-baito' => array(
            'title'       => '在宅でできる事務・カスタマーサポートのバイト｜仕事内容・時給と、電話なしで探すコツ',
            'kicker'      => '事務・サポート',
            'icon'        => 'support_agent',
            'h1'          => '在宅でできる事務・カスタマーサポートのバイト',
            'description' => '出社なしでできる事務・カスタマーサポートのバイトを、仕事内容・時給の目安・未経験から始めやすいかで紹介します。電話の有無や働く部屋の条件など、応募前に確かめたいこともまとめました。',
            'published'   => '2026-10-22',
            'updated'     => '2026-10-22',
        ),
        'zaitaku-baito-sagi' => array(
            'title'       => '在宅バイトの詐欺の見分け方｜応募前に確かめる7つのポイントと相談先',
            'kicker'      => '安全に働く',
            'icon'        => 'verified',
            'h1'          => '在宅バイトの詐欺の見分け方',
            'description' => '「スマホだけで高収入」「先に教材費が必要」など、在宅バイトでよくある詐欺・トラブルの見分け方と、困ったときの相談先をまとめました。応募前に確かめたい7つのポイントです。',
            'published'   => '2026-10-17',
            'updated'     => '2026-10-22',
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
 * 記事の中で使う：求人のカード（職種・タイトル・会社・時給・学生OKなどの印）を2列で並べる。
 */
function zaito_guide_job_list( $jobs, $max = 3 ) {
    if ( ! $jobs ) {
        return;
    }
    $conds = zaito_remote_conditions();
    echo '<ul class="g-jobs">';
    foreach ( array_slice( $jobs, 0, $max ) as $j ) {
        echo '<li><a href="' . esc_url( zaito_remote_url( $j['slug'] ) ) . '">';
        echo '<span class="gj-cat">' . esc_html( $j['category'] ) . '</span>';
        echo '<b>' . esc_html( $j['title'] ) . '</b>';
        echo '<span class="gj-co">' . esc_html( $j['company'] ) . '</span>';
        if ( '' !== $j['pay'] ) {
            echo '<span class="gp">' . esc_html( $j['pay'] ) . '</span>';
        }
        $tags = array();
        foreach ( array( 'gakusei', 'mikeiken', 'tanjikan' ) as $k ) {
            if ( isset( $conds[ $k ] ) && $conds[ $k ]['match']( $j ) ) {
                $tags[] = $conds[ $k ]['label'];
            }
        }
        if ( $tags ) {
            echo '<span class="gj-tags">';
            foreach ( $tags as $t ) {
                echo '<i>' . esc_html( $t ) . '</i>';
            }
            echo '</span>';
        }
        echo '</a></li>';
    }
    echo '</ul>';
}

/**
 * 記事の冒頭に置く「この記事の要点」。$items は文（HTMLを含んでよい）の配列。
 */
function zaito_guide_summary( $items, $title = 'この記事の要点' ) {
    echo '<div class="g-sum"><p class="g-sum-t">' . esc_html( $title ) . '</p><ul>';
    foreach ( $items as $it ) {
        echo '<li><span class="ms" aria-hidden="true">check_circle</span><span>' . wp_kses_post( $it ) . '</span></li>';
    }
    echo '</ul></div>';
}

/**
 * 大きな数字のカード。$stats は array( 数字, 単位, 説明 ) の配列。数字は掲載中の求人から計算して渡す。
 */
function zaito_guide_stats( $stats, $note = '' ) {
    echo '<div class="g-stats">';
    foreach ( $stats as $st ) {
        echo '<div><p class="g-stat-n">' . esc_html( $st[0] ) . '<small>' . esc_html( $st[1] ) . '</small></p><p class="g-stat-l">' . esc_html( $st[2] ) . '</p></div>';
    }
    echo '</div>';
    if ( $note ) {
        echo '<p class="g-stats-note">' . esc_html( $note ) . '</p>';
    }
}

/**
 * 番号つきのカード（確かめること・コツなど）。$items は array( 見出し, 本文HTML ) の配列。
 */
function zaito_guide_cards( $items, $cols = 2 ) {
    echo '<ol class="g-cards g-cols-' . (int) $cols . '">';
    foreach ( $items as $i => $it ) {
        echo '<li><span class="g-num">' . (int) ( $i + 1 ) . '</span><b>' . esc_html( $it[0] ) . '</b><p>' . wp_kses_post( $it[1] ) . '</p></li>';
    }
    echo '</ol>';
}

/**
 * 囲みの注意書き。$type は point（ヒント）か warn（注意）。
 */
function zaito_guide_note( $type, $title, $html ) {
    $icon = 'warn' === $type ? 'warning' : 'lightbulb';
    echo '<div class="g-note g-note-' . esc_attr( $type ) . '"><span class="ms" aria-hidden="true">' . esc_html( $icon ) . '</span><div><b>' . esc_html( $title ) . '</b><p>' . wp_kses_post( $html ) . '</p></div></div>';
}

/**
 * 外部の相談先・公的な情報へのリンクカード。$links は array( 'name', 'tel', 'desc', 'url', 'src' ) の配列。
 */
function zaito_guide_links( $links ) {
    echo '<ul class="g-links">';
    foreach ( $links as $l ) {
        echo '<li><a href="' . esc_url( $l['url'] ) . '" target="_blank" rel="noopener">';
        echo '<span class="gl-top"><b>' . esc_html( $l['name'] ) . '</b>';
        if ( ! empty( $l['tel'] ) ) {
            echo '<span class="gl-tel"><span class="ms" aria-hidden="true">call</span>' . esc_html( $l['tel'] ) . '</span>';
        }
        echo '</span><span class="gl-desc">' . esc_html( $l['desc'] ) . '</span>';
        echo '<span class="gl-src">' . esc_html( $l['src'] ) . '<span class="ms" aria-hidden="true">open_in_new</span></span>';
        echo '</a></li>';
    }
    echo '</ul>';
}

/**
 * 職種ごとのまとまり（職種名・件数・説明・時給の目安・求人カード）。
 */
function zaito_guide_kind( $id, $name, $desc, $jobs, $facts = array(), $max = 4 ) {
    $range = zaito_guide_pay_range( $jobs );
    echo '<section class="g-kind" id="' . esc_attr( $id ) . '">';
    echo '<div class="g-kind-hd"><h3>' . esc_html( $name ) . '</h3><span class="g-kind-n">' . (int) count( $jobs ) . '件</span></div>';
    if ( $desc ) {
        echo '<p>' . esc_html( $desc ) . '</p>';
    }
    if ( $range ) {
        $facts = array_merge( array( array( '時給の目安', $range . '（下限）' ) ), $facts );
    }
    if ( $facts ) {
        echo '<dl class="g-facts">';
        foreach ( $facts as $f ) {
            echo '<div><dt>' . esc_html( $f[0] ) . '</dt><dd>' . esc_html( $f[1] ) . '</dd></div>';
        }
        echo '</dl>';
    }
    zaito_guide_job_list( $jobs, $max );
    echo '</section>';
}

/**
 * 記事の終わりの、求人一覧への案内（紺の帯）。
 */
function zaito_guide_cta( $title, $text, $url, $label ) {
    echo '<div class="g-cta"><p class="g-cta-t">' . esc_html( $title ) . '</p><p>' . esc_html( $text ) . '</p>';
    echo '<a class="btn btn-lg g-cta-b" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '<span class="ms" aria-hidden="true">arrow_forward</span></a></div>';
}

/**
 * 目次。$items は array( id, 見出し ) の配列。
 */
function zaito_guide_toc( $items ) {
    echo '<nav class="g-toc" aria-label="目次"><p>目次</p><ol>';
    foreach ( $items as $it ) {
        echo '<li><a href="#' . esc_attr( $it[0] ) . '">' . esc_html( $it[1] ) . '</a></li>';
    }
    echo '</ol></nav>';
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

/**
 * 記事の中で使う：求人の時給（下限）のうち、いちばん低いものと高いもの。時給の分かる求人がなければ null。
 */
function zaito_guide_pay_minmax( $jobs ) {
    $pays = array_values( array_filter( array_map( function ( $j ) {
        return zaito_remote_hourly_min( $j['pay'] );
    }, $jobs ) ) );
    return $pays ? array( min( $pays ), max( $pays ) ) : null;
}

/**
 * 記事の中で使う：未経験から始めやすい求人か（未経験OKの印、または応募条件に「未経験」「経験不問」）。
 */
function zaito_guide_is_newbie( $j ) {
    return zaito_remote_has_tag( $j, 'mikeiken' ) || preg_match( '/未経験|経験不問|経験は不問/u', $j['target'] );
}

/**
 * 記事の中で使う：時給（下限）の高い順に並べる。
 */
function zaito_guide_by_pay( $jobs ) {
    usort( $jobs, function ( $a, $b ) {
        return zaito_remote_hourly_min( $b['pay'] ) - zaito_remote_hourly_min( $a['pay'] );
    } );
    return $jobs;
}
