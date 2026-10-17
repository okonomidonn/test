<?php
/**
 * 記事「主婦・子育て中の在宅ワーク」の本文。件数・時給・求人例は、掲載中の社会人・主婦OKの求人から毎回つくる。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$zaito_conds = zaito_remote_conditions();
$zaito_sjobs = zaito_guide_jobs( $zaito_conds['shakaijin']['match'] );
$zaito_short = array_values( array_filter( $zaito_sjobs, function ( $j ) {
    return zaito_remote_has_tag( $j, 'tanjikan' );
} ) );
$zaito_by    = zaito_guide_by_group( $zaito_sjobs );
$zaito_names = array(
    '事務・サポート'     => array( '事務・カスタマーサポート・オンラインアシスタント', 'メールやチャットでのお客様対応、データの入力や確認、経理や採用のサポートなどです。会社での事務経験があれば、そのまま活かせます。' ),
    'デザイン・動画'     => array( 'デザイン・動画編集', 'バナーやSNS用の画像、動画の編集などです。作業する時間を自分で決めやすい仕事が多い職種です。' ),
    'エンジニア'        => array( 'エンジニア', 'Webサイトやシステムの開発・運用です。経験者向けの求人が中心ですが、時給は高めです。' ),
    'ライティング・編集' => array( 'ライター・編集', '記事の執筆や校正です。文字単価・記事単価で報酬が決まる求人もあります。' ),
    'SNS・マーケ'      => array( 'SNS運用・マーケティング', '企業のSNSの投稿づくりや、集客のサポートです。' ),
    'その他'           => array( '電話・オンラインでのお客様対応など', '電話やオンラインでの説明・日程調整などの仕事です。' ),
    '教育'            => array( '教育・学習サポート', 'オンラインでの学習サポートです。' ),
);
$zaito_range = zaito_guide_pay_range( $zaito_sjobs );
?>
<p class="lead">子育てや家事の合間に、家で働きたい。在宅ワークなら通勤がないぶん、子どもの送り迎えの前後や、家族が寝たあとの時間も使えます。</p>
<p>この記事では、zaitoに掲載中の「出社なしで、社会人・主婦の方が応募できる求人」<?php echo (int) count( $zaito_sjobs ); ?>件をもとに、家事や育児と両立しやすい在宅ワークを紹介します。<?php echo $zaito_range ? '時給（下限）は' . esc_html( $zaito_range ) . 'で、' : ''; ?>週3日以下・短時間から始められる求人は<?php echo (int) count( $zaito_short ); ?>件あります。</p>

<div class="g-toc">
  <p><b>目次</b></p>
  <ol>
    <li><a href="#short">短時間から始められる在宅ワーク</a></li>
    <li><a href="#kinds">職種ごとの在宅ワーク</a></li>
    <li><a href="#points">家事・育児と両立するために確かめたいこと</a></li>
    <li><a href="#fuyou">扶養内で働きたいとき</a></li>
  </ol>
</div>

<h2 id="short">短時間から始められる在宅ワーク</h2>
<?php if ( $zaito_short ) : ?>
  <p>週3日以下や、1日数時間から始められる求人です。最初は短い時間から始めて、慣れてから時間を増やす人もいます。</p>
  <?php
  usort( $zaito_short, function ( $a, $b ) {
      return zaito_remote_hourly_min( $b['pay'] ) - zaito_remote_hourly_min( $a['pay'] );
  } );
  zaito_guide_job_list( $zaito_short, 5 );
  ?>
  <p><a href="<?php echo esc_url( zaito_remote_condition_url( 'tanjikan' ) ); ?>">週3日以下・短時間OKの在宅求人をすべて見る</a></p>
<?php endif; ?>

<h2 id="kinds">職種ごとの在宅ワーク</h2>
<?php foreach ( $zaito_by as $zaito_g => $zaito_list ) : ?>
  <?php $zaito_nm = isset( $zaito_names[ $zaito_g ] ) ? $zaito_names[ $zaito_g ] : array( $zaito_g, '' ); ?>
  <h3><?php echo esc_html( $zaito_nm[0] ); ?>（<?php echo (int) count( $zaito_list ); ?>件）</h3>
  <?php if ( $zaito_nm[1] ) : ?><p><?php echo esc_html( $zaito_nm[1] ); ?></p><?php endif; ?>
  <?php $zaito_r = zaito_guide_pay_range( $zaito_list ); ?>
  <?php if ( $zaito_r ) : ?><p class="g-sub">時給（下限）の目安：<?php echo esc_html( $zaito_r ); ?></p><?php endif; ?>
  <?php zaito_guide_job_list( $zaito_list, 3 ); ?>
<?php endforeach; ?>

<h2 id="points">家事・育児と両立するために確かめたいこと</h2>
<p><b>1. 働く時間帯が決まっているか。</b>「平日9〜18時のうち週○時間」のように時間帯が決まっている求人と、時間を自由に決められる求人があります。送り迎えや家事の時間と重ならないかを確かめましょう。</p>
<p><b>2. 急な休みや中抜けができるか。</b>子どもの急な体調不良などに備えて、シフトの変更や中抜けがしやすいかは大事なポイントです。募集ページに書かれていなければ、面談のときに聞いておきましょう。</p>
<p><b>3. 電話やWeb会議があるか。</b>電話対応や決まった時間の会議がある仕事は、家の中で静かな時間を確保する必要があります。チャットとメールだけで進む仕事もあります。</p>
<p><b>4. 本当に出社がないか。</b>「在宅OK」でも、研修の間だけ出社が必要な求人があります。zaitoでは、研修や会議も含めて出社がないことを確かめた求人だけを載せています（<a href="<?php echo esc_url( zaito_guide_url( 'zaitaku-ok-kanzen-zaitaku' ) ); ?>">「在宅OK」と「完全在宅」の違い</a>）。</p>

<h2 id="fuyou">扶養内で働きたいとき</h2>
<p>家族の扶養に入ったまま働きたい場合は、年間の収入に気をつける必要があります。税金の扶養と、社会保険（健康保険・年金）の扶養には、それぞれ収入の基準があり、制度の改正で基準が変わることもあります。働き始める前に、最新の基準と、家族の勤務先の扶養の条件を確かめておきましょう。</p>
<p>アルバイト・パートとして雇われる求人は、働く時間を調整しやすいものが多くあります。業務委託の場合は、収入によっては自分で確定申告が必要になることがあります。</p>

<div class="g-cta">
  <p><b>社会人・主婦の方が応募できる在宅求人を見る</b>職種・時給・週の日数で絞り込めます。</p>
  <a class="btn btn-p" href="<?php echo esc_url( zaito_remote_condition_url( 'shakaijin' ) ); ?>">社会人・主婦OKの在宅求人を見る</a>
</div>
