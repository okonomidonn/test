<?php
/**
 * 記事「主婦・子育て中の在宅ワーク」の本文。件数・時給・求人例は、掲載中の社会人・主婦OKの求人から毎回つくる。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$zaito_conds = zaito_remote_conditions();
$zaito_sjobs = zaito_guide_jobs( $zaito_conds['shakaijin']['match'] );
$zaito_short = zaito_guide_by_pay( array_values( array_filter( $zaito_sjobs, function ( $j ) {
    return zaito_remote_has_tag( $j, 'tanjikan' );
} ) ) );
$zaito_by    = zaito_guide_by_group( $zaito_sjobs );
$zaito_names = array(
    '事務・サポート'     => array( '事務・カスタマーサポート・オンラインアシスタント', 'メールやチャットでのお客様対応、データの入力や確認、経理や採用のサポートなどです。会社で事務をしていた経験があれば、そのまま活かせます。' ),
    'デザイン・動画'     => array( 'デザイン・動画編集', 'バナーやSNS用の画像、動画の編集などです。作業する時間を自分で決めやすい仕事が多い職種です。' ),
    'エンジニア'        => array( 'エンジニア', 'Webサイトやシステムの開発・運用です。経験者向けの求人が中心ですが、時給は高めです。' ),
    'ライティング・編集' => array( 'ライター・編集', '記事の執筆や校正です。文字単価や記事単価で報酬が決まる求人もあります。' ),
    'SNS・マーケ'      => array( 'SNS運用・マーケティング', '企業のSNSの投稿づくりや、集客のサポートです。' ),
    'その他'           => array( '電話・オンラインでのお客様対応など', '電話やオンラインでの説明、日程の調整などの仕事です。' ),
    '教育'            => array( '教育・学習サポート', 'オンラインでの学習サポートです。' ),
);
$zaito_mm    = zaito_guide_pay_minmax( $zaito_sjobs );
?>
<p class="lead">子育てや家事の合間に、家で働きたい。在宅ワークなら通勤がないぶん、子どもの送り迎えの前後や、家族が寝たあとの時間も使えます。</p>

<?php
$zaito_stats = array(
    array( count( $zaito_sjobs ), '件', '出社なしで社会人・主婦OKの求人' ),
    array( count( $zaito_short ), '件', '週3日以下・短時間から始められる求人' ),
);
if ( $zaito_mm ) {
    $zaito_stats[] = array( number_format( $zaito_mm[0] ), '円〜', '時給（下限）のいちばん低いもの' );
}
zaito_guide_stats( $zaito_stats, 'zaitoに掲載中の求人から集計しています（' . date_i18n( 'Y年n月j日' ) . '時点）。' );

zaito_guide_summary( array(
    'まずは<b>週3日以下・短時間</b>の求人から始めて、慣れてから時間を増やす方法もある',
    '応募の前に、<b>働く時間帯</b>と<b>急な休みや中抜けができるか</b>を確かめる',
    '扶養の内で働きたいときは、<b>税金と社会保険の基準</b>を先に確かめる',
) );
zaito_guide_toc( array(
    array( 'short', '短時間から始められる在宅ワーク' ),
    array( 'kinds', '職種ごとの在宅ワーク' ),
    array( 'points', '家事・育児と両立するために確かめたいこと' ),
    array( 'fuyou', '扶養の内で働きたいとき' ),
) );
?>

<h2 id="short">短時間から始められる在宅ワーク</h2>
<?php if ( $zaito_short ) : ?>
  <p>週3日以下や、1日数時間から始められる求人です。最初は短い時間から始めて、慣れてから時間を増やす人もいます。</p>
  <?php zaito_guide_job_list( $zaito_short, 6 ); ?>
  <a class="g-more" href="<?php echo esc_url( zaito_remote_condition_url( 'tanjikan' ) ); ?>">週3日以下・短時間OKの求人をすべて見る<span class="ms" aria-hidden="true">arrow_forward</span></a>
<?php endif; ?>

<h2 id="kinds">職種ごとの在宅ワーク</h2>
<?php
$zaito_i = 0;
foreach ( $zaito_by as $zaito_g => $zaito_list ) {
    $zaito_nm = isset( $zaito_names[ $zaito_g ] ) ? $zaito_names[ $zaito_g ] : array( $zaito_g, '' );
    zaito_guide_kind( 'kind-' . $zaito_i++, $zaito_nm[0], $zaito_nm[1], $zaito_list, array(), 4 );
}
?>

<h2 id="points">家事・育児と両立するために確かめたいこと</h2>
<?php
zaito_guide_cards( array(
    array( '働く時間帯が決まっているか', '「平日9〜18時のうち週○時間」のように時間帯が決まっている求人と、時間を自由に決められる求人があります。送り迎えや家事の時間と重ならないかを確かめましょう。' ),
    array( '急な休みや中抜けができるか', '子どもの急な体調不良などに備えて、シフトの変更や中抜けがしやすいかは大事なポイントです。募集ページに書かれていなければ、面談のときに聞いておきましょう。' ),
    array( '電話やWeb会議があるか', '電話対応や決まった時間の会議がある仕事では、家の中で静かな時間をつくる必要があります。チャットとメールだけで進む仕事もあります。' ),
    array( '本当に出社がないか', '「在宅OK」でも、研修の間だけ出社が必要な求人があります。zaitoには、研修や会議も含めて出社がないことを確かめた求人だけを載せています（<a href="' . esc_url( zaito_guide_url( 'zaitaku-ok-kanzen-zaitaku' ) ) . '">「在宅OK」と「完全在宅」の違い</a>）。' ),
) );
?>

<h2 id="fuyou">扶養の内で働きたいとき</h2>
<p>家族の扶養に入ったまま働きたい場合は、年間の収入に気をつける必要があります。税金の扶養と、社会保険（健康保険・年金）の扶養には、それぞれ収入の基準があります。</p>
<?php zaito_guide_note( 'point', '基準は、制度の改正で変わることがあります', '働き始める前に、下の公式ページで最新の基準を確かめ、家族の勤務先の扶養の条件も聞いておきましょう。' ); ?>
<?php
zaito_guide_links( array(
    array( 'name' => 'パート収入はいくらまで所得税がかからないか', 'tel' => '', 'desc' => 'パートやアルバイトの収入と、所得税がかかる基準です。', 'url' => 'https://www.nta.go.jp/taxes/shiraberu/taxanswer/shotoku/1800.htm', 'src' => '国税庁 タックスアンサー' ),
    array( 'name' => '配偶者控除', 'tel' => '', 'desc' => '配偶者の収入によって受けられる控除と、その条件です。', 'url' => 'https://www.nta.go.jp/taxes/shiraberu/taxanswer/shotoku/1191.htm', 'src' => '国税庁 タックスアンサー' ),
    array( 'name' => '「年収の壁」への対応', 'tel' => '', 'desc' => '社会保険（健康保険・年金）の扶養の基準と、国の支援策です。', 'url' => 'https://www.mhlw.go.jp/stf/taiou_001_00002.html', 'src' => '厚生労働省' ),
) );
?>
<p>アルバイト・パートとして雇われる求人は、働く時間を調整しやすいものが多くあります。業務委託の場合は、収入によっては自分で確定申告が必要になることがあります。</p>

<?php zaito_guide_cta( '社会人・主婦の方が応募できる在宅求人を見る', '職種・時給・週の日数で絞り込めます。', zaito_remote_condition_url( 'shakaijin' ), '社会人・主婦OKの求人を見る' ); ?>
