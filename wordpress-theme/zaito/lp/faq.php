<?php
/**
 * よくある質問（/faq/）。在宅の仕事探しで多い疑問に、zaitoの求人の実数を添えて答える。
 * 同じ内容を構造化データ（FAQPage）にも使う（検索エンジンやAI検索が答えとして引用しやすいように）。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/remote-parts.php';

$zaito_total = 0;
foreach ( zaito_remote_jobs() as $zaito_job ) {
    if ( empty( $zaito_job['closed'] ) ) {
        $zaito_total++;
    }
}
$zaito_n = function ( $slug ) {
    return function_exists( 'zaito_remote_condition_jobs' ) ? count( zaito_remote_condition_jobs( $slug ) ) : 0;
};
$zaito_link = function ( $slug, $text ) {
    return '<a href="' . esc_url( zaito_remote_condition_url( $slug ) ) . '">' . esc_html( $text ) . '</a>';
};

// 質問, 答え（HTML。構造化データにはタグを外して入れる）
$zaito_faq = array(
    array(
        'zaitoはどんなサイトですか？',
        '<p>zaito（ザイト）は、完全在宅の仕事だけを集めた求人サイトです。運営が企業の募集ページを読み、入社初日・研修・会議を含めて出社がないことを確かめた求人だけを載せています。いま' . (int) $zaito_total . '件を掲載しています。</p><p>大手の求人サイトでは見つけにくい、企業の採用ページやSNSだけで募集している求人も、運営が探して載せています。登録と利用は無料です。</p>',
    ),
    array(
        '「在宅OK」と「完全在宅」は何が違いますか？',
        '<p>「在宅OK」「リモート可」は、在宅で働ける日がある、という意味で使われることが多く、研修期間や週1回の会議、月1回の出社などがある求人も含まれます。「完全在宅」は、働き始めから出社がまったくないことを指します。</p><p>募集ページに「基本はリモート」「原則在宅」と書かれている場合は、例外の出社があるかもしれません。研修の場所や入社初日の流れが書かれているかも確かめると安心です。zaitoが確かめている点は<a href="' . esc_url( home_url( '/kijun/' ) ) . '">掲載基準</a>にまとめています。</p>',
    ),
    array(
        '完全在宅ではどんな仕事がありますか？',
        '<p>zaitoに載っている求人では、事務・オンラインアシスタント、カスタマーサポート、SNS運用・マーケティング、ライター・編集、デザイン・動画編集、エンジニアなどがあります。</p><p>パソコンでのメール・チャット・表計算を使う仕事が中心で、電話やWeb会議でお客様とやりとりする仕事もあります。</p>',
    ),
    array(
        '在宅ワークの詐欺や怪しい求人は、どう見分けますか？',
        '<p>次のような求人には注意してください。</p><ul><li>働く前に、教材費・登録料・研修費などのお金を払う必要がある</li><li>「スマホだけで月30万円」など、仕事内容に比べて報酬が高すぎる</li><li>会社名・所在地・仕事内容がはっきりしない、または個人のLINEに誘導される</li><li>応募前に、口座情報や身分証の画像を送るよう求められる</li></ul><p>zaitoでは、働く人がお金を払う仕組みのある求人は載せていません。応募は各企業の募集ページで行うので、会社の情報もあわせて確かめてください。</p>',
    ),
    array(
        '未経験でもできる在宅の仕事はありますか？',
        '<p>あります。zaitoには、募集ページに「未経験OK」と書かれた出社なしの求人が' . (int) $zaito_n( 'mikeiken' ) . '件あります（' . $zaito_link( 'mikeiken', '未経験OKの在宅求人' ) . '）。</p><p>SNSの投稿づくり、Webマーケティングのアシスタント、カスタマーサポートなど、パソコンとスマホが使えれば始めやすい仕事が中心です。最初は研修や先輩の確認を受けながら進めることが多いです。</p>',
    ),
    array(
        '学生でも応募できますか？',
        '<p>できます。学生の応募を受け付けている出社なしの求人が' . (int) $zaito_n( 'gakusei' ) . '件あります（' . $zaito_link( 'gakusei', '学生OKの在宅求人' ) . '）。長期インターンやアルバイトが中心で、授業の合間に働けるものもあります。</p><p>エンジニアやデータ分析など、経験が必要な求人もあるので、応募できる人の条件を確かめてください。</p>',
    ),
    array(
        '主婦・子育て中でも、短い時間で働けますか？',
        '<p>働けます。週3日以下や1日数時間から始められる出社なしの求人が' . (int) $zaito_n( 'tanjikan' ) . '件あります（' . $zaito_link( 'tanjikan', '短時間OKの在宅求人' ) . '）。社会人・主婦の方が応募できる求人は' . (int) $zaito_n( 'shakaijin' ) . '件です（' . $zaito_link( 'shakaijin', '社会人・主婦OKの在宅求人' ) . '）。</p><p>短時間の仕事でも、平日の日中に連絡がとれることを条件にしている求人があります。働ける曜日と時間帯を先に決めて、募集ページの条件と合うかを確かめてから応募してください。</p>',
    ),
    array(
        'アルバイトと業務委託は何が違いますか？',
        '<p>アルバイト・パートは、会社と雇用契約を結んで働く形です。最低賃金や労働時間のルールが適用され、働く時間などの条件によっては社会保険や雇用保険にも入れます。</p><p>業務委託は、仕事ごとに契約して報酬を受け取る形です。時給が保証されないことがあり、収入によっては自分で確定申告をする必要があります。zaitoでは、募集ページで確かめられた契約の形を各求人に表示しています。</p>',
    ),
    array(
        '応募はどうすればいいですか？',
        '<p>zaitoで気になる求人を見つけたら、求人ページの「応募ページへ進む」から、企業の募集ページに移って応募します。はじめての方は、メールアドレスだけで無料登録してから進めます。</p><p>応募と選考は各企業が行い、zaitoは応募を受け付けたり、取り次いだりしません。</p>',
    ),
    array(
        'お金はかかりますか？',
        '<p>かかりません。求人を見るのも、登録するのも、応募するのも無料です。登録すると、希望に合う新しい在宅求人をメールでお知らせします。メールはいつでも止められます。</p>',
    ),
    array(
        '載っている求人の情報は新しいですか？',
        '<p>運営が2週間に1回、募集が続いているか、条件が変わっていないかを確認し直しています。募集が終わった求人は外し、各求人のページには運営が確認した日を表示しています。</p><p>募集内容は企業の都合で変わることがあるため、応募する前に元の募集ページも確かめてください。</p>',
    ),
    array(
        'どこに住んでいても働けますか？',
        '<p>多くの求人は、日本国内であれば住んでいる場所を問いません。ただし「日本国内に住んでいる方」「海外からの勤務は不可」などの条件がある求人もあるので、各求人の「在宅の条件」や注意書きを確かめてください。</p>',
    ),
);

$zaito_canon = home_url( '/faq/' );
$zaito_entities = array();
foreach ( $zaito_faq as $zaito_qa ) {
    $zaito_entities[] = array(
        '@type'          => 'Question',
        'name'           => $zaito_qa[0],
        'acceptedAnswer' => array(
            '@type' => 'Answer',
            'text'  => trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( str_replace( array( '</p>', '</li>' ), array( "</p>\n", "</li>\n" ), $zaito_qa[1] ) ) ) ),
        ),
    );
}
zaito_remote_head(
    'よくある質問｜完全在宅の仕事探し・在宅ワークの疑問に答えます｜zaito',
    '「在宅OK」と「完全在宅」の違い、在宅ワークの詐欺の見分け方、未経験・学生・主婦でもできる在宅の仕事など、在宅の仕事探しでよくある質問に答えます。',
    $zaito_canon,
    array(
        '@context' => 'https://schema.org',
        '@graph'   => array(
            array(
                '@type'           => 'BreadcrumbList',
                'itemListElement' => array(
                    array( '@type' => 'ListItem', 'position' => 1, 'name' => 'zaito', 'item' => home_url( '/' ) ),
                    array( '@type' => 'ListItem', 'position' => 2, 'name' => 'よくある質問', 'item' => $zaito_canon ),
                ),
            ),
            array(
                '@type'      => 'FAQPage',
                'mainEntity' => $zaito_entities,
            ),
        ),
    )
);
?>
<body>
<?php zaito_remote_header(); ?>
<main class="wrap doc">
  <a class="back" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span class="ms" aria-hidden="true">arrow_back</span>求人一覧へ</a>
  <h1>よくある質問</h1>
  <p class="lead">完全在宅の仕事探しでよくある質問にお答えします。</p>
  <?php foreach ( $zaito_faq as $zaito_qa ) : ?>
  <h2><?php echo esc_html( $zaito_qa[0] ); ?></h2>
  <?php echo wp_kses_post( $zaito_qa[1] ); ?>
  <?php endforeach; ?>
  <p><a class="btn btn-p" href="<?php echo esc_url( zaito_remote_url() ); ?>">完全在宅の求人を見る</a></p>
</main>
<?php zaito_remote_footer(); ?>
</body>
</html>
