<?php
/**
 * 記事「大学生におすすめの在宅バイト」の本文。件数・時給・求人例は掲載中の学生OKの求人から毎回つくる。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$zaito_conds  = zaito_remote_conditions();
$zaito_sjobs  = zaito_guide_jobs( $zaito_conds['gakusei']['match'] );
$zaito_newbie = function ( $j ) {
    return zaito_remote_has_tag( $j, 'mikeiken' ) || preg_match( '/未経験|経験不問|経験は不問/u', $j['target'] );
};

// 職種ごとの説明（zaito_remote_group の分類名ごと）。
$zaito_kinds = array(
    '事務・サポート'     => array(
        'カスタマーサポート・事務',
        'サービスを使っているお客様からの問い合わせに、メールやチャットで答える仕事や、データの入力・確認、書類の準備などの事務です。マニュアルがあることが多く、未経験から始めやすい仕事です。',
        '丁寧な文章でやりとりができる人、細かい確認が苦にならない人',
    ),
    'SNS・マーケ'      => array(
        'SNS運用・Webマーケティング',
        '企業のInstagramやTikTok、Xの投稿づくりや、反応の数字をまとめて改善する仕事です。普段からSNSを使っている学生は、感覚をそのまま活かせます。',
        'SNSのトレンドに詳しい人、数字を見て改善するのが好きな人',
    ),
    'ライティング・編集' => array(
        'ライター・編集',
        'Webメディアの記事を書いたり、企画を考えたり、ほかの人が書いた記事を確認・修正したりする仕事です。書いた記事が残るので、就活でも見せやすい経験になります。',
        '文章を書くこと・読むことが好きな人、調べものが得意な人',
    ),
    'デザイン・動画'     => array(
        'デザイン・動画編集',
        'バナーやSNS用の画像、YouTubeのサムネイル、ショート動画の編集などを担当します。IllustratorやPhotoshop、動画編集ソフトを使ったことがあると応募しやすくなります。',
        '作品づくりが好きな人、ポートフォリオ（作品集）を作りたい人',
    ),
    'エンジニア'        => array(
        'エンジニア',
        'Webサービスやアプリの開発、AI・データ分析などを担当します。時給は高めですが、授業や個人開発などでプログラミングの経験があることを条件にしている求人がほとんどです。',
        'プログラミングの経験がある人、情報系の学部の人',
    ),
    '教育'            => array(
        '教育・学習サポート',
        'オンラインで生徒の質問に答えたり、学習の進み具合を面談でサポートしたりする仕事です。自分が受験や勉強で工夫した経験を活かせます。',
        '人に教えるのが好きな人、話を聞くのが得意な人',
    ),
    'その他'           => array(
        'インサイドセールス・その他',
        'お問い合わせをくれた企業に電話やメールで連絡して、打ち合わせを設定するインサイドセールスなどです。社会人と話す機会が多く、ビジネスの基本が身につきます。',
        '人と話すのが苦にならない人、営業の仕事に興味がある人',
    ),
);

$zaito_by = array();
foreach ( $zaito_sjobs as $zaito_j ) {
    $zaito_gname = zaito_remote_group( $zaito_j['category'] )[0];
    $zaito_by[ $zaito_gname ][] = $zaito_j;
}
foreach ( $zaito_by as $zaito_gname => $zaito_list ) {
    usort( $zaito_list, function ( $a, $b ) {
        return zaito_remote_hourly_min( $b['pay'] ) - zaito_remote_hourly_min( $a['pay'] );
    } );
    $zaito_by[ $zaito_gname ] = $zaito_list;
}
// 載せる順番：未経験から始めやすいものを先に。
$zaito_order = array_values( array_filter( array_keys( $zaito_kinds ), function ( $k ) use ( $zaito_by ) {
    return ! empty( $zaito_by[ $k ] );
} ) );
$zaito_newbie_n = count( array_filter( $zaito_sjobs, $zaito_newbie ) );
?>
<p class="lead">在宅バイトなら、通学や通勤の時間がかからず、空きコマや夜の時間を使って働けます。地方に住んでいても、都市部の企業の仕事ができます。</p>
<p>この記事では、zaitoに掲載中の「出社なしで、学生が応募できる求人」<?php echo (int) count( $zaito_sjobs ); ?>件をもとに、大学生におすすめの在宅バイトを<?php echo (int) count( $zaito_order ); ?>つの職種に分けて、仕事内容・時給の目安・向いている人を紹介します。そのうち<?php echo (int) $zaito_newbie_n; ?>件は未経験OKの求人です。</p>

<div class="g-toc">
  <p><b>目次</b></p>
  <ol>
    <?php foreach ( $zaito_order as $zaito_i => $zaito_k ) : ?>
      <li><a href="#kind-<?php echo (int) $zaito_i; ?>"><?php echo esc_html( $zaito_kinds[ $zaito_k ][0] ); ?></a></li>
    <?php endforeach; ?>
    <li><a href="#choose">在宅バイトの選び方</a></li>
    <li><a href="#caution">気をつけること</a></li>
  </ol>
</div>

<?php foreach ( $zaito_order as $zaito_i => $zaito_k ) : ?>
  <?php
  $zaito_list  = $zaito_by[ $zaito_k ];
  $zaito_pays  = array_values( array_filter( array_map( function ( $j ) {
      return zaito_remote_hourly_min( $j['pay'] );
  }, $zaito_list ) ) );
  $zaito_nb    = count( array_filter( $zaito_list, $zaito_newbie ) );
  ?>
  <h2 id="kind-<?php echo (int) $zaito_i; ?>"><?php echo (int) ( $zaito_i + 1 ); ?>. <?php echo esc_html( $zaito_kinds[ $zaito_k ][0] ); ?></h2>
  <p><?php echo esc_html( $zaito_kinds[ $zaito_k ][1] ); ?></p>
  <ul class="g-facts">
    <li><b>向いている人</b><?php echo esc_html( $zaito_kinds[ $zaito_k ][2] ); ?></li>
    <li><b>時給の目安</b><?php echo $zaito_pays ? esc_html( number_format( min( $zaito_pays ) ) . '円〜' . ( max( $zaito_pays ) > min( $zaito_pays ) ? number_format( max( $zaito_pays ) ) . '円（下限）' : '' ) ) : '求人によって異なります（成果報酬など）'; ?></li>
    <li><b>未経験から</b><?php echo $zaito_nb ? esc_html( '始めやすい（未経験OKの求人' . $zaito_nb . '件）' ) : '経験やスキルを求める求人が中心'; ?></li>
  </ul>
  <p class="g-sub">zaitoに掲載中の求人（<?php echo (int) count( $zaito_list ); ?>件）</p>
  <?php zaito_guide_job_list( $zaito_list, 3 ); ?>
<?php endforeach; ?>

<h2 id="choose">在宅バイトの選び方</h2>
<p><b>1. 本当に出社がないかを確かめる。</b>「在宅OK」「リモート可」と書かれていても、研修期間や月1回の会議で出社がある求人があります。入社初日や研修の場所まで書かれているかを見ましょう。zaitoでは、出社がないことを運営が確かめた求人だけを載せています（<a href="<?php echo esc_url( home_url( '/kijun/' ) ); ?>">掲載基準</a>）。</p>
<p><b>2. 契約の形を確かめる。</b>アルバイトとして雇われる求人は、時給と働く時間が決まっています。業務委託や成果報酬の求人は、作業量によって収入が変わり、時給の保証がないことがあります。</p>
<p><b>3. 連絡がとれる時間を確かめる。</b>在宅でも、平日の日中にチャットで連絡がとれることや、週の最低時間を条件にしている求人があります。授業の時間割やテスト期間と合うかを、応募する前に確認しておくと安心です。</p>

<h2 id="caution">気をつけること</h2>
<p>働く前に教材費や登録料を払わせる求人や、「スマホだけで月30万円」のように報酬が高すぎる求人は避けましょう。詳しくは<a href="<?php echo esc_url( zaito_guide_url( 'zaitaku-baito-sagi' ) ); ?>">在宅バイトの詐欺の見分け方</a>にまとめています。</p>
<p>また、年間の収入が一定額を超えると、親の扶養から外れたり、自分で確定申告が必要になったりすることがあります。どのくらい働くかを決める前に、家族と確認しておきましょう。</p>

<div class="g-cta">
  <p><b>出社なしで、学生が応募できる求人をまとめて見る</b>職種・時給・週の日数で絞り込めます。</p>
  <a class="btn btn-p" href="<?php echo esc_url( zaito_remote_condition_url( 'gakusei' ) ); ?>">大学生OKの在宅バイトを見る</a>
</div>
