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
    'SNS・マーケ'      => array( 'SNS運用・マーケティングのアシスタント', '企業のSNSの投稿づくりや、広告・集客のサポートを担当します。普段からSNSを使っている人なら、感覚を活かせます。' ),
    'ライティング・編集' => array( 'ライター・編集', '記事を書いたり、確認・修正したりする仕事です。最初は短い記事や決まった形式の文章から始めることが多いです。' ),
    'デザイン・動画'     => array( 'デザイン・動画編集', '画像や動画の制作です。未経験OKでも、ソフトを自分で触って覚える意欲が求められます。' ),
    '教育'            => array( '教育・学習サポート', 'オンラインで受講生の学習を支えます。' ),
    'その他'           => array( 'インサイドセールス・電話対応など', '電話やメールでお客様と連絡をとり、説明や日程調整をする仕事です。話すのが苦にならない人に向いています。' ),
);
$zaito_baito = count( array_filter( $zaito_njobs, function ( $j ) {
    return false !== mb_strpos( isset( $j['contract'] ) ? $j['contract'] : '', 'アルバイト' );
} ) );
$zaito_high  = count( array_filter( $zaito_njobs, function ( $j ) {
    return zaito_remote_hourly_min( $j['pay'] ) >= 1500;
} ) );
$zaito_range = zaito_guide_pay_range( $zaito_njobs );
?>
<p class="lead">在宅の仕事は「経験者向けばかり」と思われがちですが、未経験から始められる求人もあります。研修やマニュアルが用意されていて、先輩に確認してもらいながら覚えていける仕事です。</p>
<p>この記事では、zaitoに掲載中の「出社なしで、未経験OKの求人」<?php echo (int) count( $zaito_njobs ); ?>件をもとに、始めやすい仕事と、採用されやすい応募のコツを紹介します。<?php echo $zaito_range ? '時給（下限）は' . esc_html( $zaito_range ) . 'で、' : ''; ?>時給1,500円以上の求人は<?php echo (int) $zaito_high; ?>件あります。</p>

<div class="g-toc">
  <p><b>目次</b></p>
  <ol>
    <li><a href="#kinds">未経験から始めやすい在宅の仕事</a></li>
    <li><a href="#contract">アルバイトと業務委託、どちらを選ぶ？</a></li>
    <li><a href="#tips">未経験から採用されやすい応募のコツ</a></li>
    <li><a href="#need">始める前に用意しておくもの</a></li>
  </ol>
</div>

<h2 id="kinds">未経験から始めやすい在宅の仕事</h2>
<?php foreach ( $zaito_by as $zaito_g => $zaito_list ) : ?>
  <?php $zaito_nm = isset( $zaito_names[ $zaito_g ] ) ? $zaito_names[ $zaito_g ] : array( $zaito_g, '' ); ?>
  <h3><?php echo esc_html( $zaito_nm[0] ); ?>（<?php echo (int) count( $zaito_list ); ?>件）</h3>
  <?php if ( $zaito_nm[1] ) : ?><p><?php echo esc_html( $zaito_nm[1] ); ?></p><?php endif; ?>
  <?php $zaito_r = zaito_guide_pay_range( $zaito_list ); ?>
  <?php if ( $zaito_r ) : ?><p class="g-sub">時給（下限）の目安：<?php echo esc_html( $zaito_r ); ?></p><?php endif; ?>
  <?php zaito_guide_job_list( $zaito_list, 3 ); ?>
<?php endforeach; ?>

<h2 id="contract">アルバイトと業務委託、どちらを選ぶ？</h2>
<p>未経験OKの<?php echo (int) count( $zaito_njobs ); ?>件のうち、アルバイトとして雇われる求人（アルバイトと業務委託のどちらも選べるものを含む）は<?php echo (int) $zaito_baito; ?>件です。</p>
<p><b>アルバイト</b>は時給と働く時間が決まっていて、最低賃金などのルールが適用されます。はじめての在宅の仕事なら、収入の見通しが立ちやすいアルバイトから始めるのが安心です。<b>業務委託</b>は作業量や成果によって報酬が決まる形で、時給の保証がないことがあります。収入によっては自分で確定申告が必要です。</p>

<h2 id="tips">未経験から採用されやすい応募のコツ</h2>
<p><b>1. 働ける時間を具体的に書く。</b>「週3日、平日の10〜15時」のように、使える曜日と時間をはっきり伝えると、企業は仕事の割り振りを考えやすくなります。在宅の仕事では、経験よりも「決めた時間に確実に動けるか」が重視されることが多いです。</p>
<p><b>2. 連絡に早く返す。</b>応募後のメールやチャットには、できるだけその日のうちに返しましょう。在宅では、やりとりの速さと丁寧さがそのまま評価につながります。</p>
<p><b>3. 関係のある経験を一言添える。</b>仕事の経験がなくても、「SNSを毎日更新している」「文章を書くのが好きでブログを続けている」「接客のアルバイトでお客様対応をしていた」など、仕事に近い経験は立派なアピールになります。</p>
<p><b>4. 募集ページをよく読んでから応募する。</b>応募条件や稼働時間を読まずに応募すると、すぐに分かってしまいます。条件に合わない点があれば、正直に書いて相談するほうが印象は良くなります。</p>

<h2 id="need">始める前に用意しておくもの</h2>
<ul>
  <li><b>パソコン</b>：スマホだけでできる仕事は少なく、ほとんどの求人でパソコンが必要です（会社から貸してもらえる求人もあります）。</li>
  <li><b>安定したインターネット回線</b>：オンラインの面談や会議があるため、途切れにくい回線が必要です。</li>
  <li><b>集中できる場所</b>：お客様の情報を扱う仕事では、家族を含めて他の人に画面を見られない環境を求められることもあります。</li>
</ul>
<p>働く前に教材費や登録料を払わせる求人は、未経験向けをうたっていても避けましょう。詳しくは<a href="<?php echo esc_url( zaito_guide_url( 'zaitaku-baito-sagi' ) ); ?>">在宅バイトの詐欺の見分け方</a>にまとめています。</p>

<div class="g-cta">
  <p><b>出社なしで、未経験OKの求人をまとめて見る</b>職種・時給・週の日数で絞り込めます。</p>
  <a class="btn btn-p" href="<?php echo esc_url( zaito_remote_condition_url( 'mikeiken' ) ); ?>">未経験OKの在宅求人を見る</a>
</div>
