<?php
/**
 * 記事「地方の大学生の在宅バイト・長期インターン」の本文。件数・時給・求人例は、掲載中の学生OKの求人から毎回つくる。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// トップの一覧と同じ「学生が応募できる求人」（zaito_remote_student_ok）を使う。
$zaito_sjobs = zaito_guide_jobs( '__return_true' );
// 募集ページに「全国どこからでも」「遠方からも応募可」などと書かれている求人。
$zaito_zenkoku = array_values( array_filter( $zaito_sjobs, function ( $j ) {
    return (bool) preg_match( '/全国|どこからでも|遠方|地方/u', $j['remote'] . $j['summary'] );
} ) );
$zaito_intern = array_values( array_filter( $zaito_sjobs, function ( $j ) {
    return false !== mb_strpos( ( isset( $j['contract'] ) ? $j['contract'] : '' ) . $j['title'], 'インターン' );
} ) );
$zaito_by = zaito_guide_by_group( $zaito_sjobs );
// 地元では見つけにくいことが多い職種。掲載中の求人がある職種だけを、読みやすい名前で並べる。
$zaito_rare_map = array(
    'SNS・マーケ'      => 'マーケティング',
    'エンジニア'        => 'エンジニア',
    'ライティング・編集' => 'Webメディアの編集',
    'デザイン・動画'     => 'デザイン',
);
$zaito_rare = array_values( array_intersect_key( $zaito_rare_map, $zaito_by ) );
$zaito_mm   = zaito_guide_pay_minmax( $zaito_sjobs );
?>
<p class="lead">地方の大学では、学生が働ける会社や仕事の種類が、東京に比べて少なくなりがちです。在宅のバイトや長期インターンなら、地元に住んだまま、東京などの会社の仕事ができます。</p>
<p>この記事では、zaitoに掲載中の「出社なしで、学生が応募できる求人」をもとに、地方の大学生が在宅で働くときの選び方と注意点をまとめます。</p>

<?php
$zaito_stats = array(
    array( count( $zaito_sjobs ), '件', '出社なしで学生OKの求人' ),
    array( count( $zaito_intern ), '件', 'そのうち長期インターンの求人' ),
);
if ( $zaito_mm ) {
    $zaito_stats[] = array( number_format( $zaito_mm[1] ), '円', 'いちばん高い時給（下限）' );
}
zaito_guide_stats( $zaito_stats, 'zaitoに掲載中の求人から集計しています（' . date_i18n( 'Y年n月j日' ) . '時点）。' );

zaito_guide_summary( array(
    '在宅なら、<b>地元では少ない職種</b>（マーケティング・エンジニア・企画など）にも応募できる',
    '長期インターンで積んだ経験は、<b>就活で話せる経験</b>になる',
    '応募の前に、<b>研修や面接も含めて出社がないか</b>を必ず確かめる',
) );
zaito_guide_toc( array(
    array( 'merit', '地方の大学生が在宅で働くよさ' ),
    array( 'zenkoku', '全国から応募できると書かれた求人' ),
    array( 'intern', '在宅でできる長期インターン' ),
    array( 'caution', '気をつけること' ),
) );
?>

<h2 id="merit">地方の大学生が在宅で働くよさ</h2>
<?php
$zaito_rare_names = $zaito_rare ? implode( '、', $zaito_rare ) : '';
zaito_guide_cards( array(
    array( '地元にない仕事を選べる', '地元のバイトは、飲食や販売、塾などが中心になりがちです。在宅なら、' . ( $zaito_rare_names ? $zaito_rare_names . 'など、' : '' ) . '都市の会社が募集している仕事に応募できます。' ),
    array( '移動の時間とお金がかからない', '東京のインターンに参加するために、交通費や宿泊費をかけて通う必要がありません。授業の空き時間や夜にも働けます。' ),
    array( '就活で話せる経験になる', '会社の一員として続けた仕事は、エントリーシートや面接で具体的に話せます。都市の会社で働いた経験は、地元の会社を受けるときにも伝えられます。' ),
    array( '大学の勉強と両立しやすい', '週1〜2日や、1日数時間から働ける求人があります。試験の前に働く時間を減らせるかを、面接で相談しておくと安心です。' ),
) );
?>

<h2 id="zenkoku">全国から応募できると書かれた求人</h2>
<?php if ( $zaito_zenkoku ) : ?>
  <p>募集ページに「全国どこからでも」「遠方からも応募可」などと書かれている求人は<?php echo (int) count( $zaito_zenkoku ); ?>件です。ほかの求人も、zaitoでは出社がないことを確かめてから載せています。</p>
  <?php zaito_guide_job_list( zaito_guide_by_pay( $zaito_zenkoku ), 6 ); ?>
<?php else : ?>
  <p>いま掲載中の求人に、全国から応募できると明記したものはありません。zaitoに載っている求人は、どれも出社がないことを確かめています。</p>
<?php endif; ?>

<h2 id="intern">在宅でできる長期インターン</h2>
<?php if ( $zaito_intern ) : ?>
  <p>長期インターンは、数か月以上続けて、社員と同じチームで働く形です。時給が出る有給のものがほとんどです。</p>
  <?php zaito_guide_job_list( zaito_guide_by_pay( $zaito_intern ), 6 ); ?>
  <p>職種ごとの探し方や選考の流れは、<a href="<?php echo esc_url( zaito_guide_url( 'zaitaku-chouki-intern' ) ); ?>">在宅でできる長期インターン</a>にまとめています。</p>
<?php else : ?>
  <p>いま掲載中の長期インターンの求人はありません。</p>
<?php endif; ?>

<h2 id="caution">気をつけること</h2>
<?php
zaito_guide_cards( array(
    array( '「在宅OK」でも出社がある求人', '「リモート可」「在宅OK」と書かれていても、最初の研修や月に一度の会議だけ出社が必要な求人があります。地方から通うのは難しいので、出社が一度もないかを募集ページで確かめましょう（<a href="' . esc_url( zaito_guide_url( 'zaitaku-ok-kanzen-zaitaku' ) ) . '">「在宅OK」と「完全在宅」の違い</a>）。' ),
    array( '面接の日程と方法', 'オンラインで面接をする会社が多いですが、最終面接だけ会社に来てもらう会社もあります。応募する前に、面接もオンラインで終わるかを確かめておくと安心です。' ),
    array( '働く時間帯', '会社の営業時間に合わせて、平日の昼に働く時間を決めている求人があります。大学の授業の時間割と合うかを見比べておきましょう。' ),
    array( '業務委託の契約', '業務委託の求人は、アルバイトと違って時給が保証されないことがあります。収入によっては、自分で確定申告が必要になることもあります。' ),
) );
?>

<?php zaito_guide_cta( '出社なしで学生OKの求人を見る', '時給・週の日数・職種などで絞り込めます。', home_url( '/' ), '求人一覧を見る' ); ?>
