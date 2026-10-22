<?php
/**
 * 記事「高時給の在宅バイト」の本文。件数・時給・求人例は、掲載中の時給1,500円以上の求人から毎回つくる。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$zaito_conds  = zaito_remote_conditions();
$zaito_hjobs  = zaito_guide_jobs( $zaito_conds['jikyu1500']['match'] );
$zaito_by     = zaito_guide_by_group( $zaito_hjobs );
$zaito_names  = array(
    'エンジニア'        => array( 'エンジニア・データ分析', 'Webサービスやアプリの開発、AI・データ分析の仕事です。在宅の求人の中でも時給が高く、経験に応じてさらに上がることが多い職種です。' ),
    '事務・サポート'     => array( '専門的な事務・カスタマーサポート', '法律・保険・採用など専門知識を使う事務や、法人のお客様のサポートは、一般的な事務より時給が高めです。前の仕事の経験をそのまま活かせます。' ),
    'デザイン・動画'     => array( 'デザイン・動画', 'WebデザインやLP（ランディングページ）、広告用の画像・動画をつくる仕事です。ポートフォリオ（作品集）で実力を見せられる人が有利です。' ),
    'ライティング・編集' => array( 'ライター・編集', '専門分野の記事や校正・校閲など、正確さが求められる文章の仕事です。' ),
    'SNS・マーケ'      => array( 'マーケティング', '広告運用やマーケティングの企画など、成果を数字で示せる仕事です。' ),
    'その他'           => array( 'インサイドセールス・採用支援など', '法人のお客様との商談や採用の支援など、ビジネスの経験を活かす仕事です。' ),
    '教育'            => array( '教育', 'オンラインでの指導や学習サポートです。' ),
);
$zaito_newbie = zaito_guide_by_pay( array_values( array_filter( $zaito_hjobs, 'zaito_guide_is_newbie' ) ) );
$zaito_2000   = count( array_filter( $zaito_hjobs, function ( $j ) {
    return zaito_remote_hourly_min( $j['pay'] ) >= 2000;
} ) );
?>
<p class="lead">在宅バイトは時給が低いと思われがちですが、経験やスキルを活かせる仕事なら、時給1,500円以上の求人も珍しくありません。通勤がないぶん、同じ時給でも使える時間は多くなります。</p>

<?php
zaito_guide_stats( array(
    array( count( $zaito_hjobs ), '件', '出社なしで時給1,500円以上の求人' ),
    array( $zaito_2000, '件', 'そのうち時給2,000円以上' ),
    array( count( $zaito_newbie ), '件', 'そのうち未経験から応募できる求人' ),
), 'zaitoに掲載中の求人から集計しています（' . date_i18n( 'Y年n月j日' ) . '時点）。' );

zaito_guide_summary( array(
    '時給が高いのは、<b>専門知識や経験がいる仕事</b>。エンジニアや専門的な事務が中心',
    '高時給の求人は、<b>週4〜5日</b>など稼働時間が長めのものが多い',
    '業務委託の求人は、表示の金額が<b>時給の保証ではない</b>ことがある',
) );
zaito_guide_toc( array(
    array( 'kinds', '時給1,500円以上の在宅バイト（職種別）' ),
    array( 'newbie', '未経験から応募できる高時給の求人' ),
    array( 'why', '時給が高い在宅バイトの共通点' ),
    array( 'up', '時給を上げるために身につけたいこと' ),
) );
?>

<h2 id="kinds">時給1,500円以上の在宅バイト（職種別）</h2>
<?php
$zaito_i = 0;
foreach ( $zaito_by as $zaito_g => $zaito_list ) {
    $zaito_nm = isset( $zaito_names[ $zaito_g ] ) ? $zaito_names[ $zaito_g ] : array( $zaito_g, '' );
    zaito_guide_kind( 'kind-' . $zaito_i++, $zaito_nm[0], $zaito_nm[1], $zaito_list, array(), 4 );
}
?>

<h2 id="newbie">未経験から応募できる高時給の求人</h2>
<?php if ( $zaito_newbie ) : ?>
  <p>時給1,500円以上で、未経験から応募できる求人もあります。研修や専用のマニュアルがあり、決まったルールに沿って進める仕事が中心です。</p>
  <?php zaito_guide_job_list( $zaito_newbie, 6 ); ?>
<?php else : ?>
  <p>いま掲載中の求人には、時給1,500円以上で未経験OKのものはありません。未経験から始められる求人は<a href="<?php echo esc_url( zaito_guide_url( 'mikeiken-zaitaku-baito' ) ); ?>">未経験OKの在宅バイト</a>にまとめています。</p>
<?php endif; ?>

<h2 id="why">時給が高い在宅バイトの共通点</h2>
<?php
zaito_guide_cards( array(
    array( '専門知識や経験がいる', 'プログラミング、デザイン、法律・保険などの専門的な事務、採用や営業の経験など、すぐには代わりが見つからない仕事ほど時給は高くなります。' ),
    array( 'まとまった時間を求められる', '「週4〜5日」「1日6〜8時間」のように、稼働時間が長めの求人がよく見られます。短い時間で働きたい場合は、時給と働ける時間のバランスで選びましょう。' ),
    array( '成果や責任がはっきりしている', 'お客様とのやりとりや、機密情報を扱う仕事など、任される範囲が広い仕事は時給も上がります。' ),
), 1 );
?>

<h2 id="up">時給を上げるために身につけたいこと</h2>
<ol class="g-steps">
  <li><span class="g-num">1</span><b>パソコンの基本操作を、速く正確にする</b><p>表計算、文書作成、チャットツールは、どの仕事でも毎日使います。</p></li>
  <li><span class="g-num">2</span><b>一つの分野で経験を積む</b><p>カスタマーサポート、SNS運用、ライティングなど、同じ分野で経験を重ねると、より条件の良い求人に応募できます。</p></li>
  <li><span class="g-num">3</span><b>実績を形にして残す</b><p>作ったもの、改善した数字、担当した業務を記録しておくと、次の応募で説明しやすくなります。</p></li>
</ol>
<?php zaito_guide_note( 'point', '業務委託の求人は、契約の形もあわせて確かめる', '業務委託や成果報酬の求人は、表示されている金額が時給の保証ではないことがあります。アルバイトと業務委託の違いは<a href="' . esc_url( zaito_guide_url( 'mikeiken-zaitaku-baito' ) ) . '#contract">こちら</a>で比べています。' ); ?>

<?php zaito_guide_cta( '時給1,500円以上の在宅求人をまとめて見る', '職種・週の日数・学生OKなどで絞り込めます。', zaito_remote_condition_url( 'jikyu1500' ), '時給1,500円以上の求人を見る' ); ?>
