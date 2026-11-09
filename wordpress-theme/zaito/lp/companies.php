<?php
/**
 * 企業向けページ（/for-companies/）。inc/prelaunch.php から読み込む。
 * 問い合わせフォームは inc/lp-leads.php の zaito_lp_company_inquiry に送る（以前のLPと同じ受け口）。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/remote-parts.php';

$zaito_jobs  = array_filter( zaito_remote_jobs(), function ( $j ) {
    return empty( $j['closed'] );
} );
$zaito_url   = home_url( '/for-companies/' );
$zaito_title = '企業の方へ｜大学生の完全在宅バイトを、録画の一次面接つきで採用 | zaito';
$zaito_desc  = '大学生専門の完全在宅バイト求人サイト「zaito」の企業向けページです。掲載料・初期費用は0円で、費用は内定を出したときの成果報酬（1名10万円）だけ。応募した学生が質問に録画で答える「録画の一次面接」を追加料金なしで使えます。';

// 直近4週間（2026-10-12〜11-08）のGoogleアナリティクス。数字を新しくしたら、日付も書き換える。
$zaito_stats_period = '2026年10月12日〜11月8日';

// よくある質問。カテゴリごとに並べる。答えは料金・規約と食い違わないようにする（料金を変えたら必ずここも直す）。
$zaito_faq_groups = array(
    '料金・お支払い' => array(
        array( '費用はいつ、いくらかかりますか？', '掲載料・初期費用・オプション料金は0円です。zaitoから応募した方に内定を出したときだけ、成果報酬として1名10万円をいただきます。最初にご契約いただく3社は1名5万円です。' ),
        array( '応募が来なかったときや、採用しなかったときも費用はかかりますか？', 'かかりません。費用が発生するのは、zaitoから応募した方に内定を出したときだけです。' ),
        array( '採用した学生がすぐに辞めた場合、返金はありますか？', '返金はありません。内定を出す前に、録画の一次面接やオンラインの面接で、仕事内容や働ける時間を十分に確かめてください。' ),
        array( '支払い方法を教えてください。', '内定を出した月の月末に請求書をお送りします。翌月末までに銀行振込でお支払いください。振込手数料は御社のご負担でお願いします。' ),
        array( '録画の一次面接に料金はかかりますか？', 'かかりません。掲載していただいた求人では、追加の料金なしでお使いいただけます。' ),
    ),
    '掲載について' => array(
        array( '何件まで、いつまで掲載できますか？', '件数にも期間にも限りはありません。募集を止めるときは、企業アカウントの画面からいつでも止められます。最低の契約期間もありません。' ),
        array( '求人原稿がなくても大丈夫ですか？', '大丈夫です。募集内容をお伺いし、zaito運営が原稿と録画の一次面接の質問の案を作ります。掲載前に内容をご確認いただきます。' ),
        array( '掲載までどのくらいかかりますか？', '原稿の内容をご確認いただいてから、2営業日以内を目安に掲載します。出社がないかなど、掲載できる条件を運営が確かめてから公開します。' ),
        array( 'どんな求人を掲載できますか？', '面接・研修・会議も含めて、出社なしで自宅だけで働ける仕事です。アルバイト・業務委託・インターンなど、雇用形態は問いません。zaitoを見ているのは主に大学生なので、週1〜3日など、授業と両立できる働き方の求人が向いています。' ),
        array( '研修の期間だけ出社がある求人は載せられますか？', '載せられません。zaitoは「研修も会議も出社なし」の求人だけを載せると学生に約束しているためです。研修をオンラインに変えられる場合は、ご相談ください。' ),
        array( 'すでにzaitoに載っている自社の求人を直したい・消したい', '公開されている募集ページをもとに、zaito運営がまとめて紹介している求人があります。修正・削除のご希望は、下のフォームか info@zaito-work.com までご連絡ください。すぐに対応します。' ),
    ),
    '集まる学生・応募' => array(
        array( 'どんな人が見ていますか？', '在宅で働きたい大学生が中心です。' . $zaito_stats_period . 'の4週間で1,594人が訪れ、154人が求人の応募ページへ進みました。訪れた人の約64%は東京圏（1都3県）の外からで、全47都道府県から見られています。' ),
        array( '応募はどのくらい来ますか？', '仕事内容・報酬・働く時間によって変わるため、お約束はできません。参考として、紹介している求人のうち多いものは、1週間で7人ほどが応募ページへ進んでいます。未経験OKで、時給と働く時間がはっきりした求人ほど応募が集まりやすい傾向です。' ),
        array( '学生以外から応募が来ることはありますか？', 'zaitoは大学生向けのサイトですが、登録はどなたでもできるため、社会人などから応募が来ることもあります。求人の対象に「大学生のみ」と書いていただくこともできます。' ),
        array( '地方に住む学生の時給は、どの地域の最低賃金を基準にしますか？', '在宅で働く人の最低賃金は、住んでいる地域ではなく、所属する事業場（会社）のある地域の金額が基準になります。東京の会社なら、地方に住む学生にも東京の最低賃金以上をお支払いください。' ),
    ),
    '録画の一次面接・選考' => array(
        array( '録画の一次面接は、必ず使わないといけませんか？', '求人ごとに、使う・使わないを選べます。使わない場合は、応募のあとにzaitoのメッセージで日程を合わせて面接していただけます。' ),
        array( '質問は自由に決められますか？', '求人ごとに3〜5問、自由に決められます。zaitoから質問の案もお出しします。家族・出身地・宗教など、採用の選考で聞いてはいけないことは質問に入れないでください。' ),
        array( '録画した動画は、いつまで見られますか？', '応募から90日間です。見られるのは応募先の企業と、zaito運営だけです。90日たつと自動で消えます。' ),
        array( 'zaitoが学生を選んだり、合否を決めたりしますか？', 'しません。zaitoは求人情報を掲載するサービス（特定募集情報等提供事業）で、学生に点数を付けたり、合否をすすめたりはしません。全員の回答をそのままお見せし、採用を決めるのは御社です。' ),
        array( '面接や連絡はどこで行いますか？', '応募はzaitoに届き、応募者とはzaitoのメッセージでやりとりできます。二次面接はZoomなどのオンラインで行ってください。候補の日時を送ると、学生が選んで日程が決まります。' ),
    ),
);
$zaito_faqs = array();
foreach ( $zaito_faq_groups as $zaito_g ) {
    $zaito_faqs = array_merge( $zaito_faqs, $zaito_g );
}

zaito_remote_head( $zaito_title, $zaito_desc, $zaito_url, array(
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map( function ( $f ) {
        return array( '@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f[1] ) );
    }, $zaito_faqs ),
) );
?>
<body>
<?php zaito_remote_header( 'company' ); ?>

<section class="hero">
  <div class="wrap">
    <div class="hero-in">
      <div class="hero-copy">
        <span class="eb">FOR COMPANIES</span>
        <h1>大学生の在宅バイトを、<br>一次面接まで手間なく採用。</h1>
        <p>zaitoは、面接・研修・会議も含めて出社なしの仕事だけを集めた、大学生専門の求人サイトです。応募した学生が御社の質問に録画で答えるので、日程の調整なしで一次面接が終わります。掲載料・初期費用は0円で、費用は内定を出したときだけです。</p>
        <div class="cta-row">
          <a class="btn btn-p btn-lg" href="#contact">掲載を相談する<span class="ms" aria-hidden="true">arrow_forward</span></a>
          <a class="btn btn-g btn-lg" href="<?php echo esc_url( home_url( '/demo-interview/' ) ); ?>" target="_blank" rel="noopener">録画の一次面接を試す</a>
        </div>
        <p style="margin-top:12px;font-size:14px">企業アカウントの作成は<a href="<?php echo esc_url( home_url( '/company-register/' ) ); ?>">こちら</a>。お持ちの方は <a href="<?php echo esc_url( home_url( '/company-login/' ) ); ?>">企業ログイン</a></p>
      </div>
      <dl class="stats">
        <div><dt>直近4週間に来た人</dt><dd><b>1,594</b>人</dd></div>
        <div><dt>応募ページへ進んだ人</dt><dd><b>154</b>人</dd></div>
        <div><dt>東京圏の外から</dt><dd><b>約64</b>%</dd></div>
      </dl>
    </div>
  </div>
</section>

<main class="wrap">
  <section class="co-sec" aria-labelledby="pain-title">
    <h2 id="pain-title">学生アルバイトの採用で、こんなことはありませんか</h2>
    <ul class="co-pain">
      <li><b>日程の調整に追われる</b><span>応募者1人ごとにメールで面接の日程を合わせ、それだけで1人10分ほどかかる。</span></li>
      <li><b>面接に来ない</b><span>約束をしても来ない人がいて、空けておいた時間が無駄になる。</span></li>
      <li><b>連絡が遅れて他社に決まる</b><span>学生はいくつも並行して応募するので、一次面接が遅いと他社に決まってしまう。</span></li>
      <li><b>都市部の学生は取り合い</b><span>多くの会社が同じ学生を取り合い、募集を出しても応募が集まらない。</span></li>
    </ul>
  </section>

  <section class="co-sec" aria-labelledby="why-title">
    <h2 id="why-title">zaitoの特徴</h2>
    <ul class="why">
      <li class="t-a"><span class="ic ms" aria-hidden="true">home_work</span><b>在宅で働きたい大学生に届く</b><p>載っているのは、運営が出社の有無を確かめた求人だけ。地方にいながら東京の会社で働きたい学生など、最初から在宅を前提に探している人に届きます。</p></li>
      <li class="t-b"><span class="ic ms" aria-hidden="true">schedule</span><b>録画の一次面接</b><p>応募した学生が、御社の質問にその場で録画で答えます。空いた時間にまとめて見て、会いたい学生とだけ話せます。追加の料金はかかりません。</p></li>
      <li class="t-d"><span class="ic ms" aria-hidden="true">edit_document</span><b>原稿と質問はzaitoが作る</b><p>募集内容をお伺いして、報酬・働く時間・在宅の条件が伝わる原稿と、一次面接の質問の案を作ります。確認していただくだけで掲載できます。</p></li>
    </ul>
  </section>

  <section class="co-sec" id="interview" aria-labelledby="iv-title">
    <h2 id="iv-title">録画の一次面接</h2>
    <p class="co-lead">応募したその日に、一次面接まで終わります。採用を決めるのは御社で、zaitoは点数を付けたり、合否をすすめたりしません。</p>
    <ol class="flow4">
      <li><b>質問を登録する</b><span>最初の面接で聞きたいことを3〜5問。求人ごとに設定できます。</span></li>
      <li><b>学生が録画で答える</b><span>応募するときに、その場で答えます。考える時間20秒、答える時間60秒です。</span></li>
      <li><b>まとめて見る</b><span>空いた時間に、全員の回答を1.5倍や2倍の速さで見られます。</span></li>
      <li><b>会いたい人と話す</b><span>候補の日時を送ると、学生が選んで日程が決まります。</span></li>
    </ol>
    <div class="co-calc">
      <div class="cc-num"><span>一次面接にかける時間（応募20人の場合）</span><b>約13時間 <i aria-hidden="true">→</i> 約3時間</b><small>いま：日程の調整1人10分、面接30分と記録5分、2割は当日来ない。録画：質問の登録15分、回答を見るのに1人3分、5人とだけ15分話す。試算です。</small></div>
      <a class="btn btn-g btn-lg" href="<?php echo esc_url( home_url( '/demo-interview/' ) ); ?>" target="_blank" rel="noopener">実際の画面を試す<span class="ms" aria-hidden="true">open_in_new</span></a>
    </div>
  </section>

  <section class="co-sec" id="price" aria-labelledby="price-title">
    <h2 id="price-title">料金</h2>
    <dl class="co-price">
      <div><dt>掲載料・初期費用</dt><dd><b>0円</b>何件でも、期間の限りなく掲載できます。</dd></div>
      <div><dt>成果報酬</dt><dd><b>1名10万円</b>zaitoから応募した方に内定を出したときだけ。最初にご契約いただく3社は1名5万円です。</dd></div>
      <div><dt>録画の一次面接</dt><dd><b>0円</b>追加の料金はかかりません。</dd></div>
      <div><dt>求人原稿・質問の作成</dt><dd><b>0円</b>zaito運営が作ります。</dd></div>
    </dl>
    <p class="co-note">お支払いは、内定を出した月の月末に請求書をお送りし、翌月末までの銀行振込です。採用後の返金はありません。</p>
  </section>

  <section class="co-sec" aria-labelledby="flow-title">
    <h2 id="flow-title">掲載までの流れ</h2>
    <ol class="flow4">
      <li><b>相談・アカウント作成</b><span>フォームからご相談いただくか、企業アカウントを作ります。運営が確認して承認します。</span></li>
      <li><b>原稿と質問を作る</b><span>zaitoが原稿と一次面接の質問の案を作り、御社に確認していただきます。</span></li>
      <li><b>掲載開始</b><span>出社がないかなどを運営が確かめ、2営業日以内を目安に公開します。</span></li>
      <li><b>回答を見て面接</b><span>応募と録画の回答は管理画面に届きます。会いたい学生とだけオンラインで話します。</span></li>
    </ol>
  </section>

  <section class="co-sec" aria-labelledby="rule-title">
    <h2 id="rule-title">掲載できる求人</h2>
    <div class="rules">
      <div class="yes"><b><span class="ms" aria-hidden="true">check_circle</span>掲載できるもの</b>
        <ul><li>面接・研修・会議も含めて、出社なしで自宅だけで働ける仕事</li><li>報酬と働く時間の目安を示せる募集</li><li>アルバイト・業務委託・インターンなど（雇用形態は問いません）</li></ul></div>
      <div class="no"><b><span class="ms" aria-hidden="true">block</span>掲載しないもの</b>
        <ul><li>研修の期間だけでも、出社や対面が必要な仕事</li><li>働く前にお金の支払いが必要な募集</li><li>仕事内容や報酬が確認できない募集</li></ul></div>
    </div>
  </section>

  <section class="co-sec" id="faq" aria-labelledby="faq-title">
    <h2 id="faq-title">よくある質問</h2>
    <?php foreach ( $zaito_faq_groups as $zaito_gname => $zaito_g ) : ?>
      <h3 class="faq-cat"><?php echo esc_html( $zaito_gname ); ?></h3>
      <div class="faq">
        <?php foreach ( $zaito_g as $zaito_f ) : ?>
          <details><summary><?php echo esc_html( $zaito_f[0] ); ?><span class="ms" aria-hidden="true">add</span></summary><p><?php echo esc_html( $zaito_f[1] ); ?></p></details>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </section>

  <section class="co-sec contact" id="contact" aria-labelledby="contact-title">
    <div class="contact-in">
      <div>
        <h2 id="contact-title">掲載を相談する</h2>
        <p>まだ募集内容が固まっていなくても大丈夫です。15分ほどのオンラインの説明もできます。内容を確認し、zaito運営（info@zaito-work.com）からメールでご連絡します。</p>
      </div>
      <form id="zco" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" novalidate>
        <input type="hidden" name="action" value="zaito_lp_company_inquiry">
        <input type="hidden" name="utm_source" value=""><input type="hidden" name="utm_medium" value=""><input type="hidden" name="utm_campaign" value=""><input type="hidden" name="referrer" value="">
        <div class="hp" aria-hidden="true"><label>Webサイト<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
        <div class="row2">
          <label><span class="lt">会社名<em>必須</em></span><input type="text" name="company" required autocomplete="organization" placeholder="株式会社〇〇"></label>
          <label><span class="lt">ご担当者名<em>必須</em></span><input type="text" name="name" required autocomplete="name" placeholder="山田 太郎"></label>
        </div>
        <div class="row2">
          <label><span class="lt">メールアドレス<em>必須</em></span><input type="email" name="email" required autocomplete="email" inputmode="email" placeholder="name@company.co.jp"></label>
          <label>会社のWebサイト<input type="url" name="url" autocomplete="url" placeholder="https://"></label>
        </div>
        <label><span class="lt">募集したい仕事<em>必須</em></span><textarea name="job" rows="3" required placeholder="例：InstagramとXの投稿作成。週10時間ほど、時給1,200円〜"></textarea></label>
        <label>その他・ご質問<textarea name="note" rows="3" placeholder="掲載中の求人の修正・削除のご依頼もこちらからどうぞ"></textarea></label>
        <button type="submit" class="btn btn-p btn-lg">送信する</button>
        <p class="legal">送信すると、<a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシーポリシー</a>に同意したものとみなします。</p>
        <p class="err" id="zcoerr" role="alert"></p>
      </form>
      <div class="done" id="zcodone" hidden>
        <span class="ms" aria-hidden="true">mark_email_read</span>
        <b>お問い合わせありがとうございます</b>
        <p>内容を確認し、zaito運営からメールでご連絡します。</p>
      </div>
    </div>
  </section>
</main>

<?php zaito_remote_footer(); ?>
<script>
(function () {
  var form = document.getElementById('zco');
  var err = document.getElementById('zcoerr');
  var q = new URLSearchParams(location.search);
  ['utm_source', 'utm_medium', 'utm_campaign'].forEach(function (k) { form.elements[k].value = q.get(k) || ''; });
  form.elements.referrer.value = document.referrer || '';
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    err.textContent = '';
    var f = form.elements;
    if (!f.company.value.trim() || !f.name.value.trim() || !f.job.value.trim()) { err.textContent = '必須項目を入力してください'; return; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(f.email.value.trim())) { err.textContent = '正しいメールアドレスを入力してください'; return; }
    var btn = form.querySelector('button[type=submit]');
    btn.disabled = true;
    // form.action は name="action" の入力欄を指してしまうため、属性から読む。
    fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form), credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res || !res.success) { throw new Error(res && res.data && res.data.message ? res.data.message : ''); }
        if (typeof window.gtag === 'function') { window.gtag('event', 'generate_lead', { form: 'company' }); }
        form.hidden = true;
        document.getElementById('zcodone').hidden = false;
      })
      .catch(function (x) {
        btn.disabled = false;
        err.textContent = x.message || '送信できませんでした。時間をおいてもう一度お試しください';
      });
  });
})();
</script>
</body>
</html>
