<?php
/**
 * 記事「大学生におすすめの在宅バイト」の本文。件数・時給・求人例は掲載中の学生OKの求人から毎回つくる。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$zaito_conds = zaito_remote_conditions();
$zaito_sjobs = zaito_guide_jobs( $zaito_conds['gakusei']['match'] );
$zaito_by    = zaito_guide_by_group( $zaito_sjobs );

// 職種ごとの説明（zaito_remote_group の分類名ごと）。並びは未経験から始めやすい順。
$zaito_kinds = array(
    '事務・サポート'     => array( 'カスタマーサポート・事務', 'お客様からの問い合わせにメールやチャットで答える仕事や、データの入力・確認、書類の準備などの事務です。マニュアルがあることが多く、未経験から始めやすい仕事です。', '丁寧な文章でやりとりできる人、細かい確認が苦にならない人' ),
    'SNS・マーケ'      => array( 'SNS運用・Webマーケティング', '企業のInstagramやTikTok、Xの投稿をつくり、反応の数字をまとめて改善する仕事です。ふだんからSNSを使っている学生は、その感覚をそのまま活かせます。', 'SNSのトレンドに詳しい人、数字を見て改善するのが好きな人' ),
    'ライティング・編集' => array( 'ライター・編集', 'Webメディアの記事を書いたり、企画を考えたり、ほかの人が書いた記事を確認・修正したりする仕事です。書いた記事が残るので、就活でも見せやすい経験になります。', '文章を書くのも読むのも好きな人、調べものが得意な人' ),
    'デザイン・動画'     => array( 'デザイン・動画編集', 'バナーやSNS用の画像、YouTubeのサムネイル、ショート動画の編集などを担当します。IllustratorやPhotoshop、動画編集ソフトを使ったことがあると応募しやすくなります。', '作品づくりが好きな人、ポートフォリオ（作品集）を作りたい人' ),
    'エンジニア'        => array( 'エンジニア', 'Webサービスやアプリの開発、AI・データ分析などを担当します。時給は高めですが、授業や個人開発などでプログラミングをした経験を条件にしている求人がほとんどです。', 'プログラミングの経験がある人、情報系の学部の人' ),
    '教育'            => array( '教育・学習サポート', 'オンラインで生徒の質問に答えたり、面談で学習の進み具合を支えたりする仕事です。受験や勉強で自分が工夫した経験を活かせます。', '人に教えるのが好きな人、話を聞くのが得意な人' ),
    'その他'           => array( 'インサイドセールス・その他', '問い合わせをくれた企業に電話やメールで連絡して、打ち合わせを設定するインサイドセールスなどです。社会人と話す機会が多く、ビジネスの基本が身につきます。', '人と話すのが苦にならない人、営業の仕事に興味がある人' ),
);
$zaito_order  = array_values( array_filter( array_keys( $zaito_kinds ), function ( $k ) use ( $zaito_by ) {
    return ! empty( $zaito_by[ $k ] );
} ) );
$zaito_newbie = array_filter( $zaito_sjobs, 'zaito_guide_is_newbie' );
$zaito_mm     = zaito_guide_pay_minmax( $zaito_sjobs );
?>
<p class="lead">在宅バイトなら、通学や通勤の時間がかからず、空きコマや夜の時間を使って働けます。地方に住んでいても、都市部の企業の仕事ができます。</p>
<p>この記事では、zaitoに掲載中の「出社なしで、学生が応募できる求人」をもとに、大学生におすすめの在宅バイトを職種ごとに紹介します。</p>

<?php
$zaito_stats = array(
    array( count( $zaito_sjobs ), '件', '出社なしで学生OKの求人' ),
    array( count( $zaito_newbie ), '件', 'そのうち未経験から応募できる求人' ),
);
if ( $zaito_mm ) {
    $zaito_stats[] = array( number_format( $zaito_mm[1] ), '円', 'いちばん高い時給（下限）' );
}
zaito_guide_stats( $zaito_stats, 'zaitoに掲載中の求人から集計しています（' . date_i18n( 'Y年n月j日' ) . '時点）。' );

zaito_guide_summary( array(
    '未経験から始めるなら、<b>カスタマーサポート・事務</b>や<b>SNS運用</b>が始めやすい',
    'プログラミングやデザインの経験があれば、<b>時給の高い仕事</b>も狙える',
    '応募の前に、<b>本当に出社がないか</b>と<b>授業の時間割と合うか</b>を確かめる',
) );

zaito_guide_toc( array(
    array( 'kinds', '職種ごとの在宅バイト（' . count( $zaito_order ) . '職種）' ),
    array( 'choose', '在宅バイトの選び方' ),
    array( 'caution', '気をつけること' ),
) );
?>

<h2 id="kinds">職種ごとの在宅バイト</h2>
<p>未経験から始めやすいものから順に並べています。求人のカードを押すと、仕事内容や応募条件を確かめられます。</p>
<?php
foreach ( $zaito_order as $zaito_i => $zaito_k ) {
    $zaito_list = $zaito_by[ $zaito_k ];
    $zaito_nb   = count( array_filter( $zaito_list, 'zaito_guide_is_newbie' ) );
    zaito_guide_kind(
        'kind-' . $zaito_i,
        $zaito_kinds[ $zaito_k ][0],
        $zaito_kinds[ $zaito_k ][1],
        $zaito_list,
        array(
            array( '向いている人', $zaito_kinds[ $zaito_k ][2] ),
            array( '未経験から', $zaito_nb ? '始めやすい（未経験OKの求人' . $zaito_nb . '件）' : '経験やスキルを求める求人が中心' ),
        ),
        4
    );
}
?>

<h2 id="choose">在宅バイトの選び方</h2>
<?php
zaito_guide_cards( array(
    array( '本当に出社がないか', '「在宅OK」「リモート可」と書かれていても、研修期間や月1回の会議で出社がある求人があります。入社初日や研修の場所まで書かれているかを見ましょう。zaitoには、出社がないことを運営が確かめた求人だけを載せています（<a href="' . esc_url( zaito_guide_url( 'zaitaku-ok-kanzen-zaitaku' ) ) . '">「在宅OK」と「完全在宅」の違い</a>）。' ),
    array( '契約の形', 'アルバイトとして雇われる求人は、時給と働く時間が決まっています。業務委託や成果報酬の求人は作業量によって収入が変わり、時給の保証がないことがあります。' ),
    array( '連絡がとれる時間', '在宅でも、平日の日中にチャットで連絡がとれることや、週の最低時間を条件にしている求人があります。授業の時間割やテスト期間と合うかを、応募する前に確かめておくと安心です。' ),
), 1 );
?>

<h2 id="caution">気をつけること</h2>
<?php zaito_guide_note( 'warn', '先にお金を払わせる求人は避ける', '働く前に教材費や登録料を払わせる求人や、「スマホだけで月30万円」のように報酬が高すぎる求人は避けましょう。見分け方は<a href="' . esc_url( zaito_guide_url( 'zaitaku-baito-sagi' ) ) . '">在宅バイトの詐欺の見分け方</a>にまとめています。' ); ?>
<?php zaito_guide_note( 'point', '年間の収入が増えたら、扶養と税金を確かめる', '年間の収入が一定額を超えると、親の扶養から外れたり、自分で確定申告が必要になったりすることがあります。どのくらい働くかを決める前に、家族と確かめておきましょう。基準は下の国税庁のページで確かめられます。' ); ?>
<?php
zaito_guide_links( array(
    array( 'name' => '勤労学生控除', 'tel' => '', 'desc' => '働いている学生が受けられる所得税の控除と、その条件です。', 'url' => 'https://www.nta.go.jp/taxes/shiraberu/taxanswer/shotoku/1175.htm', 'src' => '国税庁 タックスアンサー' ),
    array( 'name' => '扶養控除', 'tel' => '', 'desc' => '親が子を扶養に入れるときの控除と、子の収入の基準です。', 'url' => 'https://www.nta.go.jp/taxes/shiraberu/taxanswer/shotoku/1180.htm', 'src' => '国税庁 タックスアンサー' ),
) );
?>

<?php zaito_guide_cta( '出社なしで、学生が応募できる求人をまとめて見る', '職種・時給・週の日数で絞り込めます。', zaito_remote_condition_url( 'gakusei' ), '大学生OKの在宅バイトを見る' ); ?>
