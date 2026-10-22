<?php
/**
 * 記事「在宅でできる事務・カスタマーサポートのバイト」の本文。件数・時給・求人例は、掲載中の事務・サポートの求人から毎回つくる。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$zaito_ojobs = zaito_guide_jobs( function ( $j ) {
    return '事務・サポート' === zaito_remote_group( $j['category'] )[0];
} );
$zaito_is_cs = function ( $j ) {
    return false !== mb_strpos( $j['category'], 'カスタマー' );
};
$zaito_cs   = array_values( array_filter( $zaito_ojobs, $zaito_is_cs ) );
$zaito_jimu = array_values( array_filter( $zaito_ojobs, function ( $j ) use ( $zaito_is_cs ) {
    return ! $zaito_is_cs( $j );
} ) );
$zaito_newbie = array_values( array_filter( $zaito_ojobs, function ( $j ) {
    return zaito_remote_has_tag( $j, 'mikeiken' ) || preg_match( '/未経験|経験不問|経験は不問/u', $j['target'] );
} ) );
$zaito_conds   = zaito_remote_conditions();
$zaito_student = count( array_filter( $zaito_ojobs, $zaito_conds['gakusei']['match'] ) );
// 仕事内容と応募条件に「電話」が出てこない求人（メール・チャットだけで進む可能性が高い）。
$zaito_nophone = count( array_filter( $zaito_ojobs, function ( $j ) {
    return false === mb_strpos( $j['summary'] . $j['target'] . $j['title'], '電話' );
} ) );
$zaito_byhour = function ( $list ) {
    usort( $list, function ( $a, $b ) {
        return zaito_remote_hourly_min( $b['pay'] ) - zaito_remote_hourly_min( $a['pay'] );
    } );
    return $list;
};
$zaito_range = zaito_guide_pay_range( $zaito_ojobs );
$zaito_all   = home_url( '/?g=' . rawurlencode( '事務・サポート' ) );
?>
<p class="lead">在宅バイトの中でも、事務とカスタマーサポートは求人が多く、始めやすい仕事です。パソコンでのメール・チャット・表計算が中心で、特別な資格がなくても応募できる求人があります。</p>
<p>この記事では、zaitoに掲載中の「出社なしの事務・カスタマーサポートの求人」<?php echo (int) count( $zaito_ojobs ); ?>件をもとに、仕事内容と時給の目安、応募前に確かめたいことをまとめます。内訳は事務・アシスタントが<?php echo (int) count( $zaito_jimu ); ?>件、カスタマーサポートが<?php echo (int) count( $zaito_cs ); ?>件<?php echo $zaito_range ? '、時給（下限）は' . esc_html( $zaito_range ) : ''; ?>です。</p>

<div class="g-toc">
  <p><b>目次</b></p>
  <ol>
    <li><a href="#what">在宅の事務・カスタマーサポートの仕事内容</a></li>
    <li><a href="#jimu">事務・アシスタントの求人</a></li>
    <li><a href="#cs">カスタマーサポートの求人</a></li>
    <li><a href="#newbie">未経験から始めやすい求人</a></li>
    <li><a href="#check">応募前に確かめたい5つのこと</a></li>
  </ol>
</div>

<h2 id="what">在宅の事務・カスタマーサポートの仕事内容</h2>
<h3>事務・アシスタント</h3>
<p>データの入力や確認、資料づくり、請求書の発行、日程の調整、メールの送信など、会社の仕事を後ろから支える仕事です。決まった手順に沿って進めるものが多く、正確さと、頼まれたことを期限までに終わらせることが大切です。</p>
<h3>カスタマーサポート</h3>
<p>サービスや商品を使っているお客様からの問い合わせに答える仕事です。メールやチャットで返信するものと、電話で対応するものがあります。回答の例文（マニュアル）が用意されていることが多く、慣れるまでは先輩に確認しながら進めます。</p>
<p>zaitoに載っている<?php echo (int) count( $zaito_ojobs ); ?>件のうち、仕事内容と応募条件に「電話」が出てこない求人は<?php echo (int) $zaito_nophone; ?>件です。電話が苦手な人は、こうした求人から探すと始めやすくなります（実際に電話がないかは、募集ページで確かめてください）。</p>

<h2 id="jimu">事務・アシスタントの求人（<?php echo (int) count( $zaito_jimu ); ?>件）</h2>
<?php $zaito_r = zaito_guide_pay_range( $zaito_jimu ); ?>
<?php if ( $zaito_r ) : ?><p class="g-sub">時給（下限）の目安：<?php echo esc_html( $zaito_r ); ?></p><?php endif; ?>
<p>経理や法律、採用など、専門的な知識を使う事務は時給が高めです。前の仕事で事務をしていた人は、その経験をそのまま活かせます。</p>
<?php zaito_guide_job_list( $zaito_byhour( $zaito_jimu ), 5 ); ?>

<h2 id="cs">カスタマーサポートの求人（<?php echo (int) count( $zaito_cs ); ?>件）</h2>
<?php $zaito_r = zaito_guide_pay_range( $zaito_cs ); ?>
<?php if ( $zaito_r ) : ?><p class="g-sub">時給（下限）の目安：<?php echo esc_html( $zaito_r ); ?></p><?php endif; ?>
<p>Webサービスやアプリ、医療・保険などの分野で、お客様や取引先からの問い合わせに対応します。シフト制で、土日や夜も対応する求人もあります。</p>
<?php zaito_guide_job_list( $zaito_byhour( $zaito_cs ), 5 ); ?>

<h2 id="newbie">未経験から始めやすい求人</h2>
<?php if ( $zaito_newbie ) : ?>
  <p>募集ページに「未経験OK」「経験不問」と書かれている求人は<?php echo (int) count( $zaito_newbie ); ?>件です。<?php echo $zaito_student ? '学生が応募できる求人は' . (int) $zaito_student . '件あります。' : ''; ?></p>
  <?php zaito_guide_job_list( $zaito_byhour( $zaito_newbie ), 5 ); ?>
<?php else : ?>
  <p>いま掲載中の事務・カスタマーサポートの求人に、未経験OKのものはありません。ほかの職種で未経験から始められる求人は<a href="<?php echo esc_url( zaito_guide_url( 'mikeiken-zaitaku-baito' ) ); ?>">未経験OKの在宅バイト</a>にまとめています。</p>
<?php endif; ?>
<p>未経験の人が見られやすいのは、文章の丁寧さと、連絡の早さです。応募の文面は誤字がないかを読み直し、面接の日程調整にはその日のうちに返事をしましょう。</p>

<h2 id="check">応募前に確かめたい5つのこと</h2>
<p><b>1. 電話の対応があるか。</b>メール・チャットだけか、電話もあるかで、仕事のしやすさが大きく変わります。電話がある場合は、家で静かに話せる時間と場所を用意できるかを考えておきましょう。</p>
<p><b>2. 働く時間帯とシフトの決め方。</b>「平日9〜18時の間で週3日」のように時間帯が決まっている求人と、自分で時間を決められる求人があります。カスタマーサポートは、問い合わせの多い時間帯に入れる人を求めることが多いです。</p>
<p><b>3. 働く部屋とインターネット環境。</b>お客様の個人情報を扱う仕事では、家族を含めて他の人に画面を見られない個室や、自宅の固定回線を条件にしている求人があります。カフェなど外での作業を禁止している会社もあります。</p>
<p><b>4. 研修が本当にオンラインか。</b>「在宅OK」でも、最初の研修だけ出社が必要な求人があります。zaitoでは、研修や会議も含めて出社がないことを確かめた求人だけを載せています（<a href="<?php echo esc_url( zaito_guide_url( 'zaitaku-ok-kanzen-zaitaku' ) ); ?>">「在宅OK」と「完全在宅」の違い</a>）。</p>
<p><b>5. 契約の形。</b>アルバイトとして雇われる求人と、業務委託の求人があります。業務委託は時給が保証されないことや、収入によっては自分で確定申告が必要になることがあります。</p>
<p>「データ入力だけで高収入」「先に教材費が必要」のような求人は、詐欺の可能性があります。詳しくは<a href="<?php echo esc_url( zaito_guide_url( 'zaitaku-baito-sagi' ) ); ?>">在宅バイトの詐欺の見分け方</a>にまとめています。</p>

<div class="g-cta">
  <p><b>出社なしの事務・カスタマーサポートの求人を見る</b>時給・週の日数・学生OKなどで絞り込めます。</p>
  <a class="btn btn-p" href="<?php echo esc_url( $zaito_all ); ?>">事務・サポートの在宅求人を見る</a>
</div>
