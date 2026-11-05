<?php
/**
 * 記事「オンライン家庭教師・塾講師の在宅バイト」の本文。件数・時給・求人例は、掲載中の教育・学習サポートの求人から毎回つくる。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$zaito_ejobs = zaito_guide_jobs( function ( $j ) {
    return '教育' === zaito_remote_group( $j['category'] )[0];
} );
// 生徒に授業をする仕事（講師）と、面談や質問対応で学習を支える仕事（学習サポート）に分ける。
$zaito_is_teach = function ( $j ) {
    return (bool) preg_match( '/講師|家庭教師|指導|授業/u', $j['title'] . $j['summary'] );
};
$zaito_teach   = array_values( array_filter( $zaito_ejobs, $zaito_is_teach ) );
$zaito_support = array_values( array_filter( $zaito_ejobs, function ( $j ) use ( $zaito_is_teach ) {
    return ! $zaito_is_teach( $j );
} ) );
$zaito_newbie = array_values( array_filter( $zaito_ejobs, 'zaito_guide_is_newbie' ) );
$zaito_mm     = zaito_guide_pay_minmax( $zaito_ejobs );
$zaito_all    = home_url( '/?g=' . rawurlencode( '教育' ) );
?>
<p class="lead">オンラインの家庭教師や塾講師は、家からパソコンで生徒に勉強を教えるバイトです。教室に通わなくてよいので、授業の前後に移動の時間がかかりません。地方に住んでいても、都市の塾の生徒を担当できます。</p>

<?php
$zaito_stats = array(
    array( count( $zaito_ejobs ), '件', '出社なしの教育・学習サポートの求人' ),
    array( count( $zaito_teach ), '件', 'そのうち生徒に授業をする講師の求人' ),
);
if ( $zaito_mm ) {
    $zaito_stats[] = array( number_format( $zaito_mm[1] ), '円', 'いちばん高い時給（下限）' );
}
zaito_guide_stats( $zaito_stats, 'zaitoに掲載中の求人から集計しています（' . date_i18n( 'Y年n月j日' ) . '時点）。' );

zaito_guide_summary( array(
    '授業は<b>夕方から夜</b>が中心。授業のない昼の時間は、大学の授業や課題に使える',
    'パソコンと安定したインターネット、<b>静かに話せる部屋</b>があれば始められる',
    '応募の前に、<b>授業の準備や報告書の時間にも給料が出るか</b>を確かめる',
) );
zaito_guide_toc( array(
    array( 'kinds', '家庭教師・塾講師・学習サポートの違い' ),
    array( 'teach', '講師の求人' ),
    array( 'support', '学習サポートの求人' ),
    array( 'need', '始めるのに必要なもの' ),
    array( 'check', '応募前に確かめたい4つのこと' ),
) );
?>

<h2 id="kinds">家庭教師・塾講師・学習サポートの違い</h2>
<table class="g-table">
  <thead><tr><th></th><th>家庭教師・塾講師</th><th>学習サポート</th></tr></thead>
  <tbody>
    <tr><th>おもな仕事</th><td data-l="家庭教師・塾講師">決まった生徒に、ビデオ通話で授業をする</td><td data-l="学習サポート">生徒や受講生の質問に答えたり、面談で勉強の進み具合や計画を一緒に確かめたりする</td></tr>
    <tr><th>働く時間</th><td data-l="家庭教師・塾講師">生徒が学校から帰ったあとの夕方から夜と、土日が中心</td><td data-l="学習サポート">平日の昼にシフトで入る求人もある</td></tr>
    <tr><th>向いている人</th><td data-l="家庭教師・塾講師">得意な科目がある人、受験の勉強で工夫した経験がある人</td><td data-l="学習サポート">話を聞くのが得意な人、人の相談にのるのが好きな人</td></tr>
  </tbody>
</table>

<h2 id="teach">講師の求人</h2>
<?php
if ( $zaito_teach ) {
    zaito_guide_kind( 'teach-list', 'オンライン家庭教師・塾講師', '担当する生徒が決まり、その生徒の授業の時間に合わせて働きます。研修があり、教えるのが初めてでも応募できる求人があります。', zaito_guide_by_pay( $zaito_teach ), array(), 6 );
} else {
    echo '<p>いま掲載中の講師の求人はありません。新しい求人が入ったら、この記事に表示されます。</p>';
}
?>
<?php zaito_guide_note( 'point', '時給と授業の時間', '時給は「授業1コマあたり」で決まっていることが多く、1コマの長さは塾によって違います。1回の授業が何分で、週に何コマ入れそうかを確かめると、1か月の収入の見込みを立てやすくなります。' ); ?>

<h2 id="support">学習サポートの求人</h2>
<?php
if ( $zaito_support ) {
    zaito_guide_kind( 'support-list', '学習サポート・コーチング', 'オンラインの学校や講座で、生徒や受講生の質問に答えたり、面談で勉強の計画を支えたりします。授業の準備がいらない求人もあります。', zaito_guide_by_pay( $zaito_support ), array(), 6 );
} else {
    echo '<p>いま掲載中の学習サポートの求人はありません。</p>';
}
?>

<h2 id="need">始めるのに必要なもの</h2>
<?php
zaito_guide_cards( array(
    array( 'パソコン', 'スマートフォンでも通話はできますが、画面に問題を映したり、板書のかわりに書き込んだりするので、パソコンを条件にしている求人がほとんどです。' ),
    array( '安定したインターネット', '授業の途中で映像や音声が止まると、生徒が集中できません。自宅のWi-Fiの速さを、ビデオ通話で一度試しておきましょう。' ),
    array( '静かに話せる部屋', '家族の声やテレビの音が入らない場所が必要です。生徒の顔や名前が映るので、ほかの人に画面を見られない部屋を条件にしている塾もあります。' ),
    array( 'ヘッドセットとカメラ', 'パソコンに付いているもので足りることもあります。声が聞き取りにくいと言われたら、マイク付きのイヤホンを使うと聞きやすくなります。' ),
) );
?>

<h2 id="check">応募前に確かめたい4つのこと</h2>
<?php
zaito_guide_cards( array(
    array( '準備や報告書の時間にも給料が出るか', '授業の準備や、授業のあとに書く報告書の時間に、給料が出るかどうかは塾によって違います。授業以外の時間がどう扱われるかを、面接で聞いておきましょう。' ),
    array( '担当できる科目と学年', '小学生の算数から高校生の数学・英語まで、塾によって教える範囲が違います。自分が自信を持って教えられる科目と学年の求人を選びましょう。' ),
    array( '週に何コマから入れるか', '「週1日1コマから」の塾もあれば、「週2日以上」を条件にする塾もあります。試験の前に授業を減らせるかも確かめておくと、大学の勉強と両立しやすくなります。' ),
    array( '研修と最初の授業までの流れ', '研修がオンラインで受けられるか、最初の授業の前に練習の時間があるかを確かめましょう。zaitoには、研修も含めて出社がないことを確かめた求人を載せています。' ),
) );
if ( $zaito_newbie ) {
    zaito_guide_note( 'point', '教えるのが初めての人は', '募集ページに「未経験OK」と書かれている求人は' . count( $zaito_newbie ) . '件あります。自分が受験で使った参考書や、つまずいたところをどう乗り越えたかを話せると、面接で伝わりやすくなります。' );
}
?>

<?php zaito_guide_cta( '出社なしの教育・学習サポートの求人を見る', '時給・週の日数・学生OKなどで絞り込めます。', $zaito_all, '教育の求人を見る' ); ?>
