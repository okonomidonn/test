<?php
/**
 * 記事「在宅でできるコールセンター・電話のバイト」の本文。件数・時給・求人例は、掲載中の電話が中心の求人から毎回つくる。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$zaito_cjobs = zaito_guide_jobs( function ( $j ) {
    return (bool) preg_match( '/電話|コール|テレアポ|受電|架電|インサイドセールス/u', $j['title'] . $j['summary'] . $j['category'] );
} );
// お客様からの電話を受ける仕事（受電）と、こちらから電話をかける仕事（架電）に分ける。
$zaito_is_in = function ( $j ) {
    return (bool) preg_match( '/受電|電話を受け/u', $j['title'] . $j['summary'] );
};
$zaito_in  = array_values( array_filter( $zaito_cjobs, $zaito_is_in ) );
$zaito_out = array_values( array_filter( $zaito_cjobs, function ( $j ) use ( $zaito_is_in ) {
    return ! $zaito_is_in( $j );
} ) );
$zaito_newbie = array_values( array_filter( $zaito_cjobs, 'zaito_guide_is_newbie' ) );
// 仕事に使うパソコンや携帯電話を会社が貸す・支給すると書かれている求人。
$zaito_lend = count( array_filter( $zaito_cjobs, function ( $j ) {
    return (bool) preg_match( '/貸し|貸与|支給/u', $j['summary'] );
} ) );
$zaito_mm = zaito_guide_pay_minmax( $zaito_cjobs );
?>
<p class="lead">在宅のコールセンターのバイトは、家から電話でお客様や企業とやりとりする仕事です。話す内容はマニュアルで決まっていることが多く、未経験から応募できる求人もあります。</p>

<?php
$zaito_stats = array(
    array( count( $zaito_cjobs ), '件', '出社なしで電話が中心の求人' ),
    array( $zaito_lend, '件', 'パソコンや携帯電話を会社が貸してくれる求人' ),
);
if ( $zaito_mm ) {
    $zaito_stats[] = array( number_format( $zaito_mm[1] ), '円', 'いちばん高い時給（下限）' );
}
zaito_guide_stats( $zaito_stats, 'zaitoに掲載中の求人から集計しています（' . date_i18n( 'Y年n月j日' ) . '時点）。' );

zaito_guide_summary( array(
    '電話を<b>受ける仕事</b>と、こちらから<b>かける仕事</b>で、大変さが大きく違う',
    '話す内容は<b>マニュアル</b>で決まっていることが多く、未経験から始められる求人がある',
    '応募の前に、<b>シフトの時間帯・ノルマや成果報酬の有無・契約の形</b>を確かめる',
) );
zaito_guide_toc( array(
    array( 'kinds', '電話を受ける仕事と、かける仕事' ),
    array( 'in', '電話を受ける仕事の求人' ),
    array( 'out', 'こちらから電話をかける仕事の求人' ),
    array( 'room', '家で電話の仕事をするときの環境' ),
    array( 'check', '応募前に確かめたい4つのこと' ),
) );
?>

<h2 id="kinds">電話を受ける仕事と、かける仕事</h2>
<table class="g-table">
  <thead><tr><th></th><th>電話を受ける仕事（受電）</th><th>電話をかける仕事（テレアポなど）</th></tr></thead>
  <tbody>
    <tr><th>おもな仕事</th><td data-l="電話を受ける仕事">お客様からの問い合わせや申込みの電話を受け、内容を聞き取って記録する</td><td data-l="電話をかける仕事">企業や、資料を請求した人に電話して、サービスの案内や打ち合わせの約束をする</td></tr>
    <tr><th>相手の気持ち</th><td data-l="電話を受ける仕事">相手が用件を持ってかけてくるので、話を聞いてもらいやすい</td><td data-l="電話をかける仕事">相手が忙しいときにかけることもあり、断られることも多い</td></tr>
    <tr><th>給料</th><td data-l="電話を受ける仕事">時給で決まっていることが多い</td><td data-l="電話をかける仕事">時給に加えて、約束や申込みの件数に応じた報酬が付く求人がある</td></tr>
  </tbody>
</table>
<?php zaito_guide_note( 'point', '初めてなら、電話を受ける仕事から', '断られることが苦手な人は、電話を受ける仕事から始めると続けやすくなります。話すことに慣れてきたら、成果に応じて報酬が増える、かける仕事に挑戦する人もいます。' ); ?>

<h2 id="in">電話を受ける仕事の求人</h2>
<?php
if ( $zaito_in ) {
    zaito_guide_kind( 'in-list', '電話を受ける仕事（受電・問い合わせ対応）', 'お客様からの電話を受けて、マニュアルどおりに案内したり、担当の部署や企業に引き継いだりします。', zaito_guide_by_pay( $zaito_in ), array(), 6 );
} else {
    echo '<p>いま掲載中の、電話を受ける仕事の求人はありません。</p>';
}
?>

<h2 id="out">こちらから電話をかける仕事の求人</h2>
<?php
if ( $zaito_out ) {
    zaito_guide_kind( 'out-list', '電話をかける仕事（テレアポ・インサイドセールス）', '企業や、資料を請求した人に電話をかけて、サービスを案内したり、打ち合わせの日程を決めたりします。話し方の研修がある求人が多いです。', zaito_guide_by_pay( $zaito_out ), array(), 6 );
} else {
    echo '<p>いま掲載中の、電話をかける仕事の求人はありません。</p>';
}
?>

<h2 id="room">家で電話の仕事をするときの環境</h2>
<?php
zaito_guide_cards( array(
    array( '静かな個室', 'お客様の名前や電話番号などの個人情報を扱うので、家族にも話を聞かれない部屋を条件にしている求人がほとんどです。カフェなど外での仕事は認められないことが多いです。' ),
    array( 'パソコンと電話', '会社がパソコンや社用の携帯電話を貸してくれる求人もあれば、自分のパソコンを使う求人もあります。自分のものを使う場合は、必要なパソコンの性能や、ソフトを入れられるかを確かめましょう。' ),
    array( 'インターネットの回線', '通話や記録の入力をインターネットで行うので、途中で切れにくい回線が必要です。スマートフォンのテザリングを認めていない会社もあります。' ),
    array( 'マイク付きのイヤホン', '両手が空くので、話しながら記録を入力できます。会社から支給されることもあります。' ),
) );
?>

<h2 id="check">応募前に確かめたい4つのこと</h2>
<?php
zaito_guide_cards( array(
    array( 'シフトの時間帯と曜日', '平日の昼だけの求人もあれば、夜や土日の勤務が必要な求人もあります。大学の授業がない時間に入れるかを、時間割と見比べておきましょう。' ),
    array( 'ノルマや成果報酬があるか', 'かける仕事では、約束や申込みの件数に目標があることがあります。時給とは別に成果に応じた報酬があるか、目標に届かなかったときにどうなるかを聞いておきましょう。' ),
    array( 'アルバイトか業務委託か', 'アルバイトは雇用契約で、時給が決まっています。業務委託は、働き方の自由が大きい一方で、収入によっては自分で確定申告が必要になることがあります。' ),
    array( '研修とマニュアル', '最初にどのくらい研修があるか、困ったときにすぐ聞ける先輩がいるかを確かめましょう。研修がオンラインで受けられるかも大切です。' ),
) );
if ( $zaito_newbie ) {
    zaito_guide_note( 'point', '電話の仕事が初めての人は', '募集ページに「未経験OK」と書かれている求人は' . count( $zaito_newbie ) . '件あります。接客や販売のアルバイトの経験は、電話の仕事でも話せる経験になります。' );
}
zaito_guide_note( 'warn', '電話が苦手な人は', '電話を使わずに、メールやチャットだけで進む仕事もあります。<a href="' . esc_url( zaito_guide_url( 'zaitaku-jimu-cs-baito' ) ) . '">在宅でできる事務・カスタマーサポートのバイト</a>にまとめています。' );
?>

<?php zaito_guide_cta( '出社なしの求人をすべて見る', '時給・週の日数・学生OKなどで絞り込めます。', home_url( '/' ), '求人一覧を見る' ); ?>
