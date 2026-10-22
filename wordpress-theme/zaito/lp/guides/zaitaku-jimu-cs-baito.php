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
$zaito_newbie = array_values( array_filter( $zaito_ojobs, 'zaito_guide_is_newbie' ) );
$zaito_conds   = zaito_remote_conditions();
$zaito_student = count( array_filter( $zaito_ojobs, $zaito_conds['gakusei']['match'] ) );
// 仕事内容と応募条件に「電話」が出てこない求人（メール・チャットだけで進む可能性が高い）。
$zaito_nophone = count( array_filter( $zaito_ojobs, function ( $j ) {
    return false === mb_strpos( $j['summary'] . $j['target'] . $j['title'], '電話' );
} ) );
$zaito_mm    = zaito_guide_pay_minmax( $zaito_ojobs );
$zaito_all   = home_url( '/?g=' . rawurlencode( '事務・サポート' ) );
?>
<p class="lead">在宅バイトの中でも、事務とカスタマーサポートは求人が多く、始めやすい仕事です。パソコンでのメール・チャット・表計算が中心で、特別な資格がなくても応募できる求人があります。</p>

<?php
$zaito_stats = array(
    array( count( $zaito_ojobs ), '件', '出社なしの事務・カスタマーサポートの求人' ),
    array( $zaito_nophone, '件', '仕事内容に「電話」が出てこない求人' ),
);
if ( $zaito_mm ) {
    $zaito_stats[] = array( number_format( $zaito_mm[1] ), '円', 'いちばん高い時給（下限）' );
}
zaito_guide_stats( $zaito_stats, 'zaitoに掲載中の求人から集計しています（' . date_i18n( 'Y年n月j日' ) . '時点）。' );

zaito_guide_summary( array(
    '事務は<b>正確さと期限を守ること</b>、カスタマーサポートは<b>丁寧な返信</b>が大切',
    '電話が苦手なら、<b>メール・チャットだけで進む求人</b>から探せる',
    '応募の前に、<b>電話の有無・働く時間帯・働く部屋の条件</b>を確かめる',
) );
zaito_guide_toc( array(
    array( 'what', '事務とカスタマーサポートの違い' ),
    array( 'jimu', '事務・アシスタントの求人' ),
    array( 'cs', 'カスタマーサポートの求人' ),
    array( 'newbie', '未経験から始めやすい求人' ),
    array( 'check', '応募前に確かめたい5つのこと' ),
) );
?>

<h2 id="what">事務とカスタマーサポートの違い</h2>
<table class="g-table">
  <thead><tr><th></th><th>事務・アシスタント</th><th>カスタマーサポート</th></tr></thead>
  <tbody>
    <tr><th>おもな仕事</th><td data-l="事務・アシスタント">データの入力や確認、資料づくり、請求書の発行、日程の調整、メールの送信など、会社の仕事を後ろから支える</td><td data-l="カスタマーサポート">サービスや商品を使っているお客様からの問い合わせに答える</td></tr>
    <tr><th>やりとりの方法</th><td data-l="事務・アシスタント">社内のチャットやメールが中心</td><td data-l="カスタマーサポート">メール・チャットで返信するものと、電話で対応するものがある</td></tr>
    <tr><th>大切なこと</th><td data-l="事務・アシスタント">正確さと、頼まれたことを期限までに終わらせること</td><td data-l="カスタマーサポート">回答の例文（マニュアル）に沿って、丁寧に返すこと。慣れるまでは先輩に確認しながら進める</td></tr>
  </tbody>
</table>
<?php zaito_guide_note( 'point', '電話が苦手な人は', 'zaitoに載っている' . count( $zaito_ojobs ) . '件のうち、仕事内容と応募条件に「電話」が出てこない求人は' . $zaito_nophone . '件です。こうした求人から探すと始めやすくなります。実際に電話がないかは、募集ページで確かめてください。' ); ?>

<h2 id="jimu">事務・アシスタントの求人</h2>
<?php
zaito_guide_kind( 'jimu-list', '事務・アシスタント', '経理や法律、採用など、専門的な知識を使う事務は時給が高めです。前の仕事で事務をしていた人は、その経験をそのまま活かせます。', zaito_guide_by_pay( $zaito_jimu ), array(), 6 );
?>

<h2 id="cs">カスタマーサポートの求人</h2>
<?php
zaito_guide_kind( 'cs-list', 'カスタマーサポート', 'Webサービスやアプリ、医療・保険などの分野で、お客様や取引先からの問い合わせに対応します。シフト制で、土日や夜も対応する求人もあります。', zaito_guide_by_pay( $zaito_cs ), array(), 6 );
?>

<h2 id="newbie">未経験から始めやすい求人</h2>
<?php if ( $zaito_newbie ) : ?>
  <p>募集ページに「未経験OK」「経験不問」と書かれている求人は<?php echo (int) count( $zaito_newbie ); ?>件です。<?php echo $zaito_student ? '学生が応募できる求人は' . (int) $zaito_student . '件あります。' : ''; ?></p>
  <?php zaito_guide_job_list( zaito_guide_by_pay( $zaito_newbie ), 6 ); ?>
<?php else : ?>
  <p>いま掲載中の事務・カスタマーサポートの求人に、未経験OKのものはありません。ほかの職種で未経験から始められる求人は<a href="<?php echo esc_url( zaito_guide_url( 'mikeiken-zaitaku-baito' ) ); ?>">未経験OKの在宅バイト</a>にまとめています。</p>
<?php endif; ?>
<?php zaito_guide_note( 'point', '未経験の人が見られやすいところ', '見られやすいのは、文章の丁寧さと、連絡の早さです。応募の文面に誤字がないかを読み直し、面接の日程調整にはその日のうちに返事をしましょう。' ); ?>

<h2 id="check">応募前に確かめたい5つのこと</h2>
<?php
zaito_guide_cards( array(
    array( '電話の対応があるか', 'メール・チャットだけか、電話もあるかで、仕事のしやすさが大きく変わります。電話がある場合は、家で静かに話せる時間と場所を用意できるかを考えておきましょう。' ),
    array( '働く時間帯とシフトの決め方', '「平日9〜18時の間で週3日」のように時間帯が決まっている求人と、自分で時間を決められる求人があります。カスタマーサポートは、問い合わせの多い時間帯に入れる人を求めることが多いです。' ),
    array( '働く部屋とインターネット環境', 'お客様の個人情報を扱う仕事では、家族を含めて他の人に画面を見られない個室や、自宅の固定回線を条件にしている求人があります。カフェなど外での作業を禁止している会社もあります。' ),
    array( '研修が本当にオンラインか', '「在宅OK」でも、最初の研修だけ出社が必要な求人があります。zaitoには、研修や会議も含めて出社がないことを確かめた求人だけを載せています（<a href="' . esc_url( zaito_guide_url( 'zaitaku-ok-kanzen-zaitaku' ) ) . '">「在宅OK」と「完全在宅」の違い</a>）。' ),
    array( '契約の形', 'アルバイトとして雇われる求人と、業務委託の求人があります。業務委託は時給が保証されないことや、収入によっては自分で確定申告が必要になることがあります。' ),
) );
zaito_guide_note( 'warn', '「データ入力だけで高収入」に注意', '「データ入力だけで高収入」「先に教材費が必要」のような求人は、詐欺の可能性があります。見分け方は<a href="' . esc_url( zaito_guide_url( 'zaitaku-baito-sagi' ) ) . '">在宅バイトの詐欺の見分け方</a>にまとめています。' );
?>

<?php zaito_guide_cta( '出社なしの事務・カスタマーサポートの求人を見る', '時給・週の日数・学生OKなどで絞り込めます。', $zaito_all, '事務・サポートの求人を見る' ); ?>
