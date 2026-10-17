<?php
/**
 * 記事「高時給の在宅バイト」の本文。件数・時給・求人例は、掲載中の時給1,500円以上の求人から毎回つくる。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$zaito_conds = zaito_remote_conditions();
$zaito_hjobs = zaito_guide_jobs( $zaito_conds['jikyu1500']['match'] );
$zaito_by    = zaito_guide_by_group( $zaito_hjobs );
$zaito_names = array(
    'エンジニア'        => array( 'エンジニア・データ分析', 'Webサービスやアプリの開発、AI・データ分析の仕事です。在宅の求人の中でも時給が高く、経験に応じてさらに上がることが多い職種です。' ),
    '事務・サポート'     => array( '事務・カスタマーサポート・専門的な事務', '法律・保険・採用など専門知識を使う事務や、法人のお客様のサポートは、一般的な事務より時給が高めです。前職の経験をそのまま活かせます。' ),
    'デザイン・動画'     => array( 'デザイン・動画', 'WebデザインやLP（ランディングページ）、広告用の画像・動画の制作です。ポートフォリオ（作品集）で実力を見せられる人が有利です。' ),
    'ライティング・編集' => array( 'ライター・編集', '専門分野の記事や、校正・校閲など、正確さが求められる文章の仕事です。' ),
    'SNS・マーケ'      => array( 'マーケティング', '広告運用やマーケティングの企画など、成果を数字で示せる仕事です。' ),
    'その他'           => array( 'インサイドセールス・採用支援など', '法人のお客様との商談や、採用の支援など、ビジネスの経験を活かす仕事です。' ),
    '教育'            => array( '教育', 'オンラインでの指導や学習サポートです。' ),
);
$zaito_newbie = array_values( array_filter( $zaito_hjobs, function ( $j ) {
    return zaito_remote_has_tag( $j, 'mikeiken' ) || preg_match( '/未経験|経験不問|経験は不問/u', $j['target'] );
} ) );
$zaito_2000   = count( array_filter( $zaito_hjobs, function ( $j ) {
    return zaito_remote_hourly_min( $j['pay'] ) >= 2000;
} ) );
?>
<p class="lead">在宅バイトは時給が低いと思われがちですが、経験やスキルを活かせる仕事なら、時給1,500円以上の求人も珍しくありません。通勤がないぶん、同じ時給でも使える時間は多くなります。</p>
<p>この記事では、zaitoに掲載中の「出社なしで、時給1,500円以上の求人」<?php echo (int) count( $zaito_hjobs ); ?>件をもとに、高時給の在宅バイトを職種ごとに紹介します。そのうち時給2,000円以上は<?php echo (int) $zaito_2000; ?>件、未経験OKのものは<?php echo (int) count( $zaito_newbie ); ?>件です。</p>

<div class="g-toc">
  <p><b>目次</b></p>
  <ol>
    <li><a href="#kinds">時給1,500円以上の在宅バイト（職種別）</a></li>
    <li><a href="#newbie">未経験から応募できる高時給の求人</a></li>
    <li><a href="#why">時給が高い在宅バイトの共通点</a></li>
    <li><a href="#up">時給を上げるために身につけたいこと</a></li>
  </ol>
</div>

<h2 id="kinds">時給1,500円以上の在宅バイト（職種別）</h2>
<?php foreach ( $zaito_by as $zaito_g => $zaito_list ) : ?>
  <?php $zaito_nm = isset( $zaito_names[ $zaito_g ] ) ? $zaito_names[ $zaito_g ] : array( $zaito_g, '' ); ?>
  <h3><?php echo esc_html( $zaito_nm[0] ); ?>（<?php echo (int) count( $zaito_list ); ?>件）</h3>
  <?php if ( $zaito_nm[1] ) : ?><p><?php echo esc_html( $zaito_nm[1] ); ?></p><?php endif; ?>
  <?php $zaito_r = zaito_guide_pay_range( $zaito_list ); ?>
  <?php if ( $zaito_r ) : ?><p class="g-sub">時給（下限）の目安：<?php echo esc_html( $zaito_r ); ?></p><?php endif; ?>
  <?php zaito_guide_job_list( $zaito_list, 3 ); ?>
<?php endforeach; ?>

<h2 id="newbie">未経験から応募できる高時給の求人</h2>
<?php if ( $zaito_newbie ) : ?>
  <p>時給1,500円以上で、未経験から応募できる求人もあります。研修や専用のマニュアルがあり、決まったルールに沿って進める仕事が中心です。</p>
  <?php
  usort( $zaito_newbie, function ( $a, $b ) {
      return zaito_remote_hourly_min( $b['pay'] ) - zaito_remote_hourly_min( $a['pay'] );
  } );
  zaito_guide_job_list( $zaito_newbie, 5 );
  ?>
<?php else : ?>
  <p>いま掲載中の求人では、時給1,500円以上で未経験OKのものはありません。未経験から始められる求人は<a href="<?php echo esc_url( zaito_guide_url( 'mikeiken-zaitaku-baito' ) ); ?>">未経験OKの在宅バイト</a>にまとめています。</p>
<?php endif; ?>

<h2 id="why">時給が高い在宅バイトの共通点</h2>
<p><b>専門知識や経験がいる。</b>プログラミング、デザイン、法律・保険などの専門的な事務、採用や営業の経験など、すぐには代わりが見つからない仕事ほど時給は高くなります。</p>
<p><b>まとまった時間を求められることが多い。</b>「週4〜5日」「1日6〜8時間」のように、稼働時間が長めの求人がよく見られます。短時間で働きたい場合は、時給と働ける時間のバランスで選びましょう。</p>
<p><b>成果や責任がはっきりしている。</b>お客様とのやりとりや、機密情報を扱う仕事など、任される範囲が広い仕事は時給も上がります。</p>

<h2 id="up">時給を上げるために身につけたいこと</h2>
<ul>
  <li><b>パソコンの基本操作を速く・正確に</b>：表計算、文書作成、チャットツール。どの仕事でも土台になります。</li>
  <li><b>一つの分野の経験を積む</b>：カスタマーサポート、SNS運用、ライティングなど、同じ分野で経験を重ねると、より条件の良い求人に応募できます。</li>
  <li><b>実績を形にして残す</b>：作ったもの、改善した数字、担当した業務を記録しておくと、次の応募で説明しやすくなります。</li>
</ul>
<p>なお、業務委託や成果報酬の求人は、表示されている金額が時給の保証ではないことがあります。契約の形もあわせて確認してください。</p>

<div class="g-cta">
  <p><b>時給1,500円以上の在宅求人をまとめて見る</b>職種・週の日数・学生OKなどで絞り込めます。</p>
  <a class="btn btn-p" href="<?php echo esc_url( zaito_remote_condition_url( 'jikyu1500' ) ); ?>">時給1,500円以上の在宅求人を見る</a>
</div>
