<?php
/**
 * 記事「在宅ライターのバイト」の本文。件数・報酬の形・求人例は、掲載中のライティング・編集の求人から毎回つくる。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$zaito_wjobs = zaito_guide_jobs( function ( $j ) {
    return 'ライティング・編集' === zaito_remote_group( $j['category'] )[0];
} );
// 報酬の形で分ける：時給 / 1記事・1文字あたり / それ以外（成果に応じて・記載なし）。
$zaito_pay_kind = function ( $j ) {
    if ( preg_match( '/時給/u', $j['pay'] ) ) {
        return 'hour';
    }
    if ( preg_match( '/1記事|1文字|1本/u', $j['pay'] ) ) {
        return 'unit';
    }
    return 'other';
};
$zaito_hour   = array_values( array_filter( $zaito_wjobs, function ( $j ) use ( $zaito_pay_kind ) {
    return 'hour' === $zaito_pay_kind( $j );
} ) );
$zaito_unit   = array_values( array_filter( $zaito_wjobs, function ( $j ) use ( $zaito_pay_kind ) {
    return 'unit' === $zaito_pay_kind( $j );
} ) );
$zaito_newbie = array_values( array_filter( $zaito_wjobs, 'zaito_guide_is_newbie' ) );
$zaito_all    = home_url( '/?g=' . rawurlencode( 'ライティング・編集' ) );
?>
<p class="lead">在宅ライターは、Webメディアの記事や広告の文章を、家で書く仕事です。書いた記事が残るので、就活のときに「こういう記事を書いた」と見せやすい経験になります。</p>

<?php
zaito_guide_stats( array(
    array( count( $zaito_wjobs ), '件', '出社なしのライター・編集の求人' ),
    array( count( $zaito_hour ), '件', 'そのうち時給で働く求人' ),
    array( count( $zaito_unit ), '件', '1記事・1文字あたりで報酬が決まる求人' ),
), 'zaitoに掲載中の求人から集計しています（' . date_i18n( 'Y年n月j日' ) . '時点）。' );

zaito_guide_summary( array(
    '報酬は<b>時給</b>のものと、<b>1記事・1文字あたり</b>のものがあり、働き方が大きく違う',
    '初めてなら、時給で働ける<b>インターン</b>や、型が決まった文章から始めると続けやすい',
    '応募の前に、<b>修正の回数・AIを使ってよいか・名前が載るか</b>を確かめる',
) );
zaito_guide_toc( array(
    array( 'pay', '時給の仕事と、1記事あたりの仕事の違い' ),
    array( 'jobs', 'ライター・編集の求人' ),
    array( 'start', '未経験から始めるには' ),
    array( 'check', '応募前に確かめたい4つのこと' ),
) );
?>

<h2 id="pay">時給の仕事と、1記事あたりの仕事の違い</h2>
<table class="g-table">
  <thead><tr><th></th><th>時給で働く</th><th>1記事・1文字あたり</th></tr></thead>
  <tbody>
    <tr><th>契約の形</th><td data-l="時給で働く">アルバイトやインターンが多い</td><td data-l="1記事・1文字あたり">業務委託が多い</td></tr>
    <tr><th>収入</th><td data-l="時給で働く">働いた時間で決まるので、書くのに時間がかかっても収入が減らない</td><td data-l="1記事・1文字あたり">書いた量で決まる。慣れて早く書けるようになるほど、1時間あたりの収入が増える</td></tr>
    <tr><th>仕事の中身</th><td data-l="時給で働く">記事を書くほかに、企画や調べもの、SNSの投稿など、メディアの運営にも関わる</td><td data-l="1記事・1文字あたり">決まったテーマの記事を書いて、決められた日までに出す</td></tr>
    <tr><th>働く時間</th><td data-l="時給で働く">週の日数や時間が決まっていることが多い</td><td data-l="1記事・1文字あたり">締め切りを守れば、書く時間は自分で決められる</td></tr>
  </tbody>
</table>
<?php zaito_guide_note( 'point', '1文字あたりの報酬の目安', '1文字1円の記事を3,000文字書くと、3,000円です。調べものや修正を含めて3時間かかれば、1時間あたり1,000円になります。応募する前に、1本にかかりそうな時間で割って考えましょう。' ); ?>

<h2 id="jobs">ライター・編集の求人</h2>
<?php
if ( $zaito_hour ) {
    zaito_guide_kind( 'hour-list', '時給で働くライター・編集', 'メディアの立ち上げや運営に関わりながら、記事を書きます。インターンとして、社員と一緒に企画から考える求人もあります。', zaito_guide_by_pay( $zaito_hour ), array(), 6 );
}
$zaito_rest = array_values( array_udiff( $zaito_wjobs, $zaito_hour, function ( $a, $b ) {
    return strcmp( $a['slug'], $b['slug'] );
} ) );
if ( $zaito_rest ) {
    zaito_guide_kind( 'unit-list', '1記事・1文字あたりや、成果で報酬が決まるライター', '健康・美容・ファッションなどの分野の記事や、広告・メルマガの文章を書きます。書いた量や成果に応じて報酬が決まります。', $zaito_rest, array(), 6 );
}
if ( ! $zaito_wjobs ) {
    echo '<p>いま掲載中のライター・編集の求人はありません。新しい求人が入ったら、この記事に表示されます。</p>';
}
?>

<h2 id="start">未経験から始めるには</h2>
<?php
zaito_guide_cards( array(
    array( '書いた文章を1つ用意する', '応募のときに、書いた文章を見せてほしいと言われることがあります。ブログやnoteに、自分の得意な分野の記事を1本書いておくと、見せる文章として使えます。' ),
    array( '型が決まった文章から始める', '広告の文章やメルマガは、決まった型に沿って書く求人があります。型に慣れると、記事の組み立て方も身につきます。' ),
    array( '得意な分野を決める', '美容・ファッション・健康・お金・勉強など、自分が詳しい分野の記事は、調べる時間が短くて済みます。大学で学んでいる分野も、そのまま得意な分野になります。' ),
    array( '直されたところを次に活かす', '最初は、編集の人に何か所も直されるのがふつうです。直されたところを次の記事で気をつけると、早く書けるようになります。' ),
) );
if ( $zaito_newbie ) {
    zaito_guide_note( 'point', '未経験OKの求人', '募集ページに「未経験OK」「未経験歓迎」と書かれている求人は' . count( $zaito_newbie ) . '件あります。' );
}
?>

<h2 id="check">応募前に確かめたい4つのこと</h2>
<?php
zaito_guide_cards( array(
    array( '修正の回数と締め切り', '1本の記事を何回まで直すのか、締め切りは何日あるのかを確かめましょう。修正が多いと、1時間あたりの収入が下がります。' ),
    array( 'AIを使ってよいか', '文章を書くAIを使ってよいかは、会社によって決まりが違います。使ってよい範囲と、使わずに書くべきところを、最初に確かめておきましょう。' ),
    array( '書いた記事に名前が載るか', '自分の名前が記事に載ると、就活で見せやすくなります。名前が載らない記事でも、どの記事を書いたかを人に見せてよいかを聞いておくと安心です。' ),
    array( '契約の形と税金', '業務委託の報酬は給料ではないので、収入によっては確定申告が必要です。親の扶養との関係は<a href="' . esc_url( zaito_guide_url( 'daigakusei-fuyou-zaitaku' ) ) . '">大学生の在宅バイトと扶養の壁</a>にまとめています。' ),
) );
zaito_guide_note( 'warn', '先にお金を払う仕事に注意', '「ライターの講座を受ければ仕事を紹介する」と言って、先に講座の料金を払わせる募集があります。仕事を始めるためにお金を払う必要がある募集には応募しないようにしましょう（<a href="' . esc_url( zaito_guide_url( 'zaitaku-baito-sagi' ) ) . '">在宅バイトの詐欺の見分け方</a>）。' );
?>

<?php zaito_guide_cta( '出社なしのライター・編集の求人を見る', '時給・週の日数・学生OKなどで絞り込めます。', $zaito_all, 'ライター・編集の求人を見る' ); ?>
