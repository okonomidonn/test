<?php
/**
 * 記事「未経験OKの在宅バイト」の本文。件数・時給・求人例は、掲載中の未経験OKの求人から毎回つくる。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$zaito_conds = zaito_remote_conditions();
$zaito_njobs = zaito_guide_jobs( $zaito_conds['mikeiken']['match'] );
$zaito_by    = zaito_guide_by_group( $zaito_njobs );
$zaito_names = array(
    '事務・サポート'     => array( 'カスタマーサポート・事務', 'お客様からの問い合わせにメールやチャットで答える仕事や、データの確認・入力などの事務です。マニュアルが用意されていることが多く、いちばん始めやすい仕事です。' ),
    'SNS・マーケ'      => array( 'SNS運用・マーケティングのアシスタント', '企業のSNSの投稿づくりや、広告・集客のサポートを担当します。ふだんからSNSを使っている人なら、その感覚を活かせます。' ),
    'ライティング・編集' => array( 'ライター・編集', '記事を書いたり、確認・修正したりする仕事です。最初は短い記事や、決まった形式の文章から始めることが多いです。' ),
    'デザイン・動画'     => array( 'デザイン・動画編集', '画像や動画をつくる仕事です。未経験OKの求人でも、ソフトを自分で触って覚える意欲が求められます。' ),
    '教育'            => array( '教育・学習サポート', 'オンラインで受講生の学習を支えます。' ),
    'その他'           => array( 'インサイドセールス・電話対応など', '電話やメールでお客様と連絡をとり、説明や日程の調整をする仕事です。話すのが苦にならない人に向いています。' ),
);
$zaito_baito = count( array_filter( $zaito_njobs, function ( $j ) {
    return false !== mb_strpos( isset( $j['contract'] ) ? $j['contract'] : '', 'アルバイト' );
} ) );
$zaito_high  = count( array_filter( $zaito_njobs, function ( $j ) {
    return zaito_remote_hourly_min( $j['pay'] ) >= 1500;
} ) );
?>
<p class="lead">在宅の仕事は経験者向けばかりと思われがちですが、未経験から始められる求人もあります。研修やマニュアルが用意されていて、先輩に確認してもらいながら覚えていける仕事です。</p>

<?php
zaito_guide_stats( array(
    array( count( $zaito_njobs ), '件', '出社なしで未経験OKの求人' ),
    array( $zaito_baito, '件', 'そのうちアルバイトとして雇われる求人' ),
    array( $zaito_high, '件', '未経験OKで時給1,500円以上' ),
), 'zaitoに掲載中の求人から集計しています（' . date_i18n( 'Y年n月j日' ) . '時点）。' );

zaito_guide_summary( array(
    'いちばん始めやすいのは、マニュアルのある<b>カスタマーサポート・事務</b>',
    'はじめての在宅なら、収入の見通しが立ちやすい<b>アルバイト</b>から始めると安心',
    '採用されやすいのは、<b>働ける時間を具体的に書き、連絡に早く返す</b>人',
) );
zaito_guide_toc( array(
    array( 'kinds', '未経験から始めやすい在宅の仕事' ),
    array( 'contract', 'アルバイトと業務委託、どちらを選ぶ？' ),
    array( 'tips', '未経験から採用されやすい応募のコツ' ),
    array( 'need', '始める前に用意しておくもの' ),
) );
?>

<h2 id="kinds">未経験から始めやすい在宅の仕事</h2>
<?php
$zaito_i = 0;
foreach ( $zaito_by as $zaito_g => $zaito_list ) {
    $zaito_nm = isset( $zaito_names[ $zaito_g ] ) ? $zaito_names[ $zaito_g ] : array( $zaito_g, '' );
    zaito_guide_kind( 'kind-' . $zaito_i++, $zaito_nm[0], $zaito_nm[1], $zaito_list, array(), 4 );
}
?>

<h2 id="contract">アルバイトと業務委託、どちらを選ぶ？</h2>
<p>未経験OKの<?php echo (int) count( $zaito_njobs ); ?>件のうち、アルバイトとして雇われる求人（アルバイトと業務委託のどちらも選べるものを含む）は<?php echo (int) $zaito_baito; ?>件です。</p>
<table class="g-table">
  <thead><tr><th></th><th>アルバイト</th><th>業務委託</th></tr></thead>
  <tbody>
    <tr><th>報酬の決まり方</th><td data-l="アルバイト">時給と働く時間が決まっている</td><td data-l="業務委託">作業量や成果によって決まる。時給の保証がないことがある</td></tr>
    <tr><th>守られるルール</th><td data-l="アルバイト">最低賃金などのルールが適用される</td><td data-l="業務委託">労働基準法や最低賃金のルールは、原則として適用されない</td></tr>
    <tr><th>税金の手続き</th><td data-l="アルバイト">会社が年末調整をすることが多い</td><td data-l="業務委託">収入によっては自分で確定申告が必要</td></tr>
  </tbody>
</table>
<p>はじめての在宅の仕事なら、収入の見通しが立ちやすいアルバイトから始めるのが安心です。</p>
<?php
zaito_guide_links( array(
    array( 'name' => '働くときの基礎知識', 'tel' => '', 'desc' => '労働条件の確かめ方、最低賃金、アルバイトにも適用されるルールをまとめた国のサイトです。', 'url' => 'https://www.check-roudou.mhlw.go.jp/study/', 'src' => '厚生労働省 確かめよう労働条件' ),
    array( 'name' => '確定申告', 'tel' => '', 'desc' => '確定申告が必要な人と、手続きの方法です。', 'url' => 'https://www.nta.go.jp/taxes/shiraberu/shinkoku/kakutei.htm', 'src' => '国税庁' ),
) );
?>

<h2 id="tips">未経験から採用されやすい応募のコツ</h2>
<?php
zaito_guide_cards( array(
    array( '働ける時間を具体的に書く', '「週3日、平日の10〜15時」のように使える曜日と時間をはっきり伝えると、企業は仕事の割り振りを考えやすくなります。在宅の仕事では、経験よりも「決めた時間に確実に動けるか」が重視されることが多いです。' ),
    array( '連絡に早く返す', '応募後のメールやチャットには、できるだけその日のうちに返しましょう。在宅では、やりとりの速さと丁寧さがそのまま評価につながります。' ),
    array( '近い経験を一言添える', '仕事の経験がなくても、「SNSを毎日更新している」「ブログを続けている」「接客のアルバイトでお客様対応をしていた」など、仕事に近い経験は立派なアピールになります。' ),
    array( '募集ページをよく読んでから応募する', '応募条件や稼働時間を読まずに応募すると、すぐに分かってしまいます。条件に合わない点があれば、正直に書いて相談するほうが印象は良くなります。' ),
) );
?>

<h2 id="need">始める前に用意しておくもの</h2>
<?php
zaito_guide_cards( array(
    array( 'パソコン', 'スマホだけでできる仕事は少なく、ほとんどの求人でパソコンが必要です。会社から貸してもらえる求人もあります。' ),
    array( '途切れにくいインターネット回線', 'オンラインの面談や会議があるため、安定した回線が必要です。' ),
    array( '集中できる場所', 'お客様の情報を扱う仕事では、家族を含めて他の人に画面を見られない環境を求められることもあります。' ),
), 1 );
zaito_guide_note( 'warn', '未経験向けをうたう、お金のかかる求人に注意', '働く前に教材費や登録料を払わせる求人は、未経験向けをうたっていても避けましょう。見分け方は<a href="' . esc_url( zaito_guide_url( 'zaitaku-baito-sagi' ) ) . '">在宅バイトの詐欺の見分け方</a>にまとめています。' );
?>

<?php zaito_guide_cta( '出社なしで、未経験OKの求人をまとめて見る', '職種・時給・週の日数で絞り込めます。', zaito_remote_condition_url( 'mikeiken' ), '未経験OKの在宅求人を見る' ); ?>
