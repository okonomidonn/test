<?php
/**
 * 条件別の求人一覧（/zaitaku/{slug}/）。例: /zaitaku/mikeiken/（未経験OKの在宅求人）。
 *
 * 「在宅 事務 求人」「在宅 未経験」のような検索から来た人に、条件に合う求人だけを見せるページ。
 * 求人の振り分けは、職種（zaito_remote_group()）・報酬・対象の文字から自動で行うものと、
 * 求人ページで確かめた内容をもとに運営が付ける印（zaito_remote_job_tags()）を使う。
 * 該当する求人が ZAITO_COND_MIN 件未満の条件は、中身の薄いページにならないよう表示しない。
 */

// 職種の分類（zaito_remote_group）はページのテンプレートと共通の関数を使う。
require_once dirname( __DIR__ ) . '/lp/remote-parts.php';

define( 'ZAITO_COND_MIN', 3 );

/**
 * 求人ページで確かめた条件の印（求人のslug => 印の一覧）。
 * - mikeiken: 未経験OK・初心者歓迎と書かれている
 * - tanjikan: 週3日以下や週数時間など、短い時間から働ける
 */
function zaito_remote_job_tags() {
    return array(
        'clearnote-marketing'      => array( 'mikeiken', 'tanjikan' ),
        'albona-beauty-sns'        => array( 'mikeiken' ),
        'highball-sns-marketer'    => array( 'mikeiken' ),
        'funtre-web-assistant'     => array( 'mikeiken' ),
        'ndpromotion-ai-video'     => array( 'mikeiken' ),
        'stocksun-ai-marketing'    => array( 'tanjikan' ),
        'knit-dedicated-staff'     => array( 'tanjikan' ),
        'knit-online-secretary'    => array( 'tanjikan' ),
        'xcuu-thumbnail-designer'  => array( 'tanjikan' ),
        'nhigh-net-course-support' => array( 'tanjikan' ),
        'donuts-ray-writer'        => array( 'tanjikan' ),
        'anymind-video-localize'   => array( 'tanjikan' ),
        'jurin-ai-strategy'        => array( 'tanjikan' ),
        'emuni-ai-engineer'        => array( 'tanjikan' ),
        'jmty-cs-supporter'        => array( 'mikeiken' ),
        'vividgarden-marketing-support' => array( 'mikeiken' ),
        'vividgarden-producer-support'  => array( 'mikeiken' ),
        'cosoji-accounting'        => array( 'tanjikan' ),
        'linkties-ai-assistant'    => array( 'tanjikan' ),
        'hackazouk-web-designer'   => array( 'tanjikan' ),
    );
}

function zaito_remote_has_tag( $job, $tag ) {
    $tags = zaito_remote_job_tags();
    return isset( $tags[ $job['slug'] ] ) && in_array( $tag, $tags[ $job['slug'] ], true );
}

/**
 * 報酬の文字から時給の下限を読む（「時給1,300〜3,000円」なら1300）。時給でなければ0。
 */
function zaito_remote_hourly_min( $pay ) {
    return preg_match( '/^時給([\d,]+)/u', $pay, $m ) ? (int) str_replace( ',', '', $m[1] ) : 0;
}

/**
 * 条件ページの定義。label は一覧のボタン、body はページ上部の説明（運営が書いた独自の文章）。
 */
function zaito_remote_conditions() {
    return array(
        'mikeiken'  => array(
            'label' => '未経験OK',
            'icon'  => 'emoji_people',
            'h1'    => '未経験OKの在宅求人',
            'title' => '未経験OKの在宅・リモート求人一覧（出社なし）',
            'body'  => array(
                '募集ページに「未経験OK」「初心者歓迎」と書かれている、出社なしの求人です。SNSの投稿づくりやWebマーケティングのアシスタントなど、パソコンとスマホが使えれば始めやすい仕事が中心です。',
                '未経験OKの求人でも、最初の数か月は研修や先輩の確認を受けながら進めることが多く、そのぶん連絡に早く返せることや、決めた時間を守れることが見られます。応募するときは「どのくらいの時間を使えるか」を具体的に伝えると話が進みやすくなります。',
            ),
            'match' => function ( $j ) {
                return zaito_remote_has_tag( $j, 'mikeiken' );
            },
        ),
        'tanjikan'  => array(
            'label' => '短時間OK',
            'icon'  => 'timelapse',
            'h1'    => '週3日以下・短時間から働ける在宅求人',
            'title' => '週3日以下・1日数時間から働ける在宅求人一覧（出社なし）',
            'body'  => array(
                '週2〜3日、または週数時間から始められる、出社なしの求人です。学業や家事、本業と両立しながら働きたい方に向いています。',
                '短時間の仕事でも、平日の日中に連絡がとれることを条件にしている求人があります。働ける曜日と時間帯を先に決めておき、募集ページの条件と合うかを確かめてから応募すると、あとで食い違いが起きにくくなります。',
            ),
            'match' => function ( $j ) {
                return zaito_remote_has_tag( $j, 'tanjikan' );
            },
        ),
        'jikyu1500' => array(
            'label' => '時給1,500円〜',
            'icon'  => 'payments',
            'h1'    => '時給1,500円以上の在宅求人',
            'title' => '時給1,500円以上の在宅・リモート求人一覧（出社なし）',
            'body'  => array(
                '募集ページに時給1,500円以上と書かれている、出社なしの求人です。エンジニアやオンライン秘書、企業の専任サポートなど、経験やスキルを求める仕事が多くなります。',
                '在宅の仕事は住む場所に関係なく同じ報酬で働けるのが特長です。時給が高い求人ほど、週の稼働時間や継続期間の条件がはっきり決まっていることが多いので、報酬と合わせて確認してください。',
            ),
            'match' => function ( $j ) {
                return zaito_remote_hourly_min( $j['pay'] ) >= 1500;
            },
        ),
        'gakusei'   => array(
            'label' => '学生OK',
            'icon'  => 'school',
            'h1'    => '学生OKの在宅バイト・インターン',
            'title' => '学生OKの在宅バイト・長期インターン一覧（出社なし）',
            'body'  => array(
                '大学生・大学院生などの学生が応募できる、出社なしの求人です。長期インターンとして、SNS運用やマーケティング、編集、エンジニアの実務に関われるものが中心です。',
                '在宅なら通学や通勤の時間がかからず、地方に住んでいても都市部の企業で働けます。授業の時間割と重ならないよう、平日日中の連絡が必要かどうかを募集ページで確認しておきましょう。',
            ),
            'match' => function ( $j ) {
                return (bool) preg_match( '/学生|大学|全学年/u', $j['target'] );
            },
        ),
        'shakaijin' => array(
            'label' => '社会人・主婦OK',
            'icon'  => 'work_history',
            'h1'    => '社会人・主婦の方が応募できる在宅求人',
            'title' => '社会人・主婦（主夫）向けの在宅・リモート求人一覧（出社なし）',
            'body'  => array(
                '社会人の方や、子育て中・ブランクのある方も応募できる、出社なしの求人です。事務やオンライン秘書、カスタマーサポートなど、これまでの仕事の経験を活かせるものが多くあります。',
                '業務委託の募集が多く、働く時間を自分で調整しやすい反面、報酬は稼働した時間や件数で決まります。契約の形（業務委託かアルバイトか）と、報酬の計算のしかたを応募前に確かめておくと安心です。',
            ),
            'match' => function ( $j ) {
                // 対象に学生が書かれていない求人（経験で条件を決めているものなど）と、社会人・主婦などを明記した求人。
                return ! preg_match( '/学生|大学|全学年/u', $j['target'] )
                    || preg_match( '/社会人|主婦|既卒|副業|フリーランス/u', $j['target'] );
            },
        ),
        'jimu'      => array(
            'label' => '事務・サポート',
            'icon'  => 'support_agent',
            'h1'    => '在宅の事務・アシスタント・サポート求人',
            'title' => '在宅の事務・オンラインアシスタント・カスタマーサポート求人一覧',
            'body'  => array(
                '日程調整や資料作成、経理の補助、お客様からの問い合わせ対応など、事務とサポートの仕事を在宅で行う求人です。オンラインアシスタントとして複数の会社を手伝う形も増えています。',
                'チャットやメールでのやりとりが仕事の中心になるため、返信の早さと文章の分かりやすさが大切です。事務経験の年数を条件にしている求人もあるので、応募資格を先に確認してください。',
            ),
            'match' => function ( $j ) {
                $g = zaito_remote_group( $j['category'] );
                return '事務・サポート' === $g[0];
            },
        ),
        'sns'       => array(
            'label' => 'SNS・マーケ',
            'icon'  => 'campaign',
            'h1'    => '在宅のSNS運用・Webマーケティング求人',
            'title' => '在宅のSNS運用・Webマーケティング求人一覧（出社なし）',
            'body'  => array(
                'InstagramやX、TikTokの投稿づくりや運用、広告やSEOの分析など、SNSとWebマーケティングの仕事を在宅で行う求人です。パソコンとスマホがあれば始められるものが多く、在宅の仕事の中でも募集が多い分野です。',
                '投稿の数字（見られた回数や保存数）を見て改善する仕事が多いので、自分でSNSを運用した経験があれば応募のときに伝えると強みになります。',
            ),
            'match' => function ( $j ) {
                $g = zaito_remote_group( $j['category'] );
                return 'SNS・マーケ' === $g[0];
            },
        ),
        'design'    => array(
            'label' => 'デザイン・動画',
            'icon'  => 'palette',
            'h1'    => '在宅のデザイン・動画編集求人',
            'title' => '在宅のデザイン・動画編集求人一覧（出社なし）',
            'body'  => array(
                'SNSの投稿画像やYouTubeのサムネイル、ショート動画の編集など、デザインと動画の仕事を在宅で行う求人です。',
                '多くの求人で、これまでに作った作品（ポートフォリオ）の提出を求められます。応募の前に、見せられる作品を3つほどまとめておくと選考が進めやすくなります。',
            ),
            'match' => function ( $j ) {
                $g = zaito_remote_group( $j['category'] );
                return 'デザイン・動画' === $g[0];
            },
        ),
        'writing'   => array(
            'label' => 'ライター・編集',
            'icon'  => 'edit_note',
            'h1'    => '在宅のライター・編集求人',
            'title' => '在宅のライター・編集求人一覧（出社なし）',
            'body'  => array(
                'Webメディアの記事やSNSの投稿文、ニュースの編集など、文章の仕事を在宅で行う求人です。',
                '報酬が「1記事○円」のように件数で決まる求人もあります。1本にかかる時間の目安を考えて、時給に直すといくらになるかを確かめてから応募すると安心です。',
            ),
            'match' => function ( $j ) {
                $g = zaito_remote_group( $j['category'] );
                return 'ライティング・編集' === $g[0];
            },
        ),
        'engineer'  => array(
            'label' => 'エンジニア',
            'icon'  => 'code',
            'h1'    => '在宅のエンジニア求人',
            'title' => '在宅のエンジニア・開発求人一覧（出社なし）',
            'body'  => array(
                'AIの開発やアプリ開発など、エンジニアの仕事を在宅で行う求人です。在宅の仕事の中でも時給が高めの分野です。',
                '使う言語や、チームでの開発経験を条件にしている求人が多くあります。自分で作ったアプリやGitHubのリンクを用意しておくと、応募のときに経験を伝えやすくなります。',
            ),
            'match' => function ( $j ) {
                $g = zaito_remote_group( $j['category'] );
                return 'エンジニア' === $g[0];
            },
        ),
    );
}

/**
 * 条件に合う募集中の求人。
 */
function zaito_remote_condition_jobs( $slug ) {
    $conds = zaito_remote_conditions();
    if ( ! isset( $conds[ $slug ] ) ) {
        return array();
    }
    return array_values( array_filter( zaito_remote_jobs(), function ( $j ) use ( $conds, $slug ) {
        return empty( $j['closed'] ) && call_user_func( $conds[ $slug ]['match'], $j );
    } ) );
}

/**
 * 表示する条件ページ（求人が ZAITO_COND_MIN 件以上あるもの）。slug => 件数。
 */
function zaito_remote_active_conditions() {
    $out = array();
    foreach ( array_keys( zaito_remote_conditions() ) as $slug ) {
        $n = count( zaito_remote_condition_jobs( $slug ) );
        if ( $n >= ZAITO_COND_MIN ) {
            $out[ $slug ] = $n;
        }
    }
    return $out;
}

function zaito_remote_condition_url( $slug ) {
    return home_url( '/zaitaku/' . $slug . '/' );
}

function zaito_remote_condition_rewrite() {
    add_rewrite_rule( '^zaitaku/([a-z0-9-]+)/?$', 'index.php?zaito_page=remote&zaito_cond=$matches[1]', 'top' );
}
add_action( 'init', 'zaito_remote_condition_rewrite' );

function zaito_remote_condition_query_vars( $vars ) {
    $vars[] = 'zaito_cond';
    return $vars;
}
add_filter( 'query_vars', 'zaito_remote_condition_query_vars' );
