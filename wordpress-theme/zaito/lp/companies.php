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
$zaito_title = '企業の方へ｜完全在宅の求人を無料で掲載 | zaito';
$zaito_desc  = '出社なしの仕事だけを集めた求人サイト「zaito」。在宅で働く人の募集を、無料で掲載できます。求人原稿の作成もzaito運営がお手伝いします。';

$zaito_faqs = array(
    array( '掲載料金はかかりますか？', '現在は無料で掲載しています。今後料金が発生する場合は事前にご案内し、ご同意いただかないまま請求することはありません。' ),
    array( '求人原稿がなくても大丈夫ですか？', '大丈夫です。募集内容をお伺いし、zaito運営が原稿を作成します。掲載前に内容をご確認いただきます。' ),
    array( 'どんな求人を掲載できますか？', '出社なしで、自宅だけで働ける求人です。学生・社会人・主婦の方など、対象は問いません。研修期間だけ出社がある場合などは、条件として明記したうえで掲載します。' ),
    array( '応募はどこで受け付けますか？', '現在は、貴社の募集ページ（採用サイトやWantedlyなど）で受け付けていただく形です。zaitoからは、求人を見た方を貴社の募集ページへご案内します。' ),
    array( 'すでにzaitoに載っている自社の求人を直したい・消したい', '公開されている募集ページをもとに、zaito運営がまとめて掲載している求人があります。修正・削除のご希望は、下のフォームか info@zaito-work.com までご連絡ください。すぐに対応します。' ),
);

zaito_remote_head( $zaito_title, $zaito_desc, $zaito_url, array(
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map( function ( $f ) {
        return array( '@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f[1] ) );
    }, $zaito_faqs ),
) );
?>
<body>
<?php zaito_remote_header(); ?>

<section class="hero">
  <div class="wrap">
    <div class="hero-in">
      <div class="hero-copy">
        <span class="eb">FOR COMPANIES</span>
        <h1>在宅で働く人を、<br>無料で募集できます。</h1>
        <p>zaitoは、出社なしの仕事だけを集めた求人サイトです。在宅で働きたい人だけが見に来るので、「フルリモートのはずが出社の話でずれる」ことがありません。求人原稿の作成もお手伝いします。</p>
        <div class="cta-row">
          <a class="btn btn-p btn-lg" href="#contact">無料で掲載を相談する<span class="ms" aria-hidden="true">arrow_forward</span></a>
          <a class="btn btn-g btn-lg" href="<?php echo esc_url( home_url( '/' ) ); ?>">掲載中の求人を見る</a>
        </div>
      </div>
      <dl class="stats">
        <div><dt>掲載中の求人</dt><dd><b><?php echo esc_html( count( $zaito_jobs ) ); ?></b>件</dd></div>
        <div><dt>掲載費</dt><dd><b>0</b>円</dd></div>
        <div><dt>原稿作成</dt><dd><b>無料</b></dd></div>
      </dl>
    </div>
  </div>
</section>

<main class="wrap">
  <section class="co-sec" aria-labelledby="why-title">
    <h2 id="why-title">zaitoに掲載する理由</h2>
    <ul class="why">
      <li class="t-a"><span class="ic ms" aria-hidden="true">home_work</span><b>在宅で働きたい人だけが見る</b><p>載っているのは、運営が出社の有無を確かめた求人だけ。最初から在宅を前提に探している人に届きます。</p></li>
      <li class="t-b"><span class="ic ms" aria-hidden="true">edit_document</span><b>原稿はzaitoが作ります</b><p>募集内容をお伺いして、報酬・稼働時間・在宅の条件が伝わる原稿を作成します。確認していただくだけで掲載できます。</p></li>
      <li class="t-d"><span class="ic ms" aria-hidden="true">insights</span><b>見られた数をお伝えします</b><p>求人ごとに、何人が貴社の募集ページへ進んだかを記録しています。掲載の効果を数字で確かめられます。</p></li>
    </ul>
  </section>

  <section class="co-sec" aria-labelledby="flow-title">
    <h2 id="flow-title">掲載までの流れ</h2>
    <ol class="flow4">
      <li><b>フォームから相談</b><span>会社名と募集したい仕事を送ってください。</span></li>
      <li><b>内容の確認</b><span>メールかオンラインで、条件を確認します。</span></li>
      <li><b>原稿の作成</b><span>zaito運営が原稿を作り、確認していただきます。</span></li>
      <li><b>掲載開始</b><span>求人一覧に載り、新着としてお知らせします。</span></li>
    </ol>
  </section>

  <section class="co-sec" aria-labelledby="rule-title">
    <h2 id="rule-title">掲載できる求人</h2>
    <div class="rules">
      <div class="yes"><b><span class="ms" aria-hidden="true">check_circle</span>掲載できるもの</b>
        <ul><li>出社なしで、自宅だけで働ける仕事</li><li>報酬と働く時間の目安を示せる募集</li><li>アルバイト・業務委託・副業・インターン・正社員など（雇用形態は問いません）</li></ul></div>
      <div class="no"><b><span class="ms" aria-hidden="true">block</span>掲載しないもの</b>
        <ul><li>出社や対面での勤務が前提の仕事</li><li>働く前にお金の支払いが必要な募集</li><li>仕事内容や報酬が確認できない募集</li></ul></div>
    </div>
  </section>

  <section class="co-sec" aria-labelledby="faq-title">
    <h2 id="faq-title">よくある質問</h2>
    <div class="faq">
      <?php foreach ( $zaito_faqs as $zaito_f ) : ?>
        <details><summary><?php echo esc_html( $zaito_f[0] ); ?><span class="ms" aria-hidden="true">add</span></summary><p><?php echo esc_html( $zaito_f[1] ); ?></p></details>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="co-sec contact" id="contact" aria-labelledby="contact-title">
    <div class="contact-in">
      <div>
        <h2 id="contact-title">無料で掲載を相談する</h2>
        <p>まだ募集内容が固まっていなくても大丈夫です。内容を確認し、zaito運営（info@zaito-work.com）からメールでご連絡します。</p>
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
