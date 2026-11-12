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
$zaito_title = '企業の方へ｜大学生の完全在宅バイトを、面接の手間なく採用 | zaito';
$zaito_desc  = '大学生専門の完全在宅バイト求人サイト「zaito」の企業向けページです。応募した学生が質問に録画で答えるので、面接は回答を見て会いたい人を選ぶだけ。掲載は無料で始められます。';

// よくある質問（少なく、ふつうに聞かれることだけ）。料金の金額はページに書かない（問い合わせのときに案内する）。
$zaito_faqs = array(
    array( '費用はかかりますか？', '掲載は無料で始められます。料金の詳しいことは、お問い合わせのときにご案内します。' ),
    array( '求人原稿がなくても大丈夫ですか？', '大丈夫です。募集内容をお伺いし、zaito運営が原稿と面接の質問の案を作ります。掲載前に内容をご確認いただきます。' ),
    array( 'どんな求人を掲載できますか？', '面接・研修・会議も含めて、出社なしで自宅だけで働ける仕事です。アルバイト・業務委託・インターンなど、雇用形態は問いません。' ),
    array( '面接はどのように行いますか？', '応募した学生が、御社の質問に録画で答えます。回答を見て会いたい学生を選び、その学生とだけオンラインで話していただけます。' ),
    array( '掲載までどのくらいかかりますか？', '原稿の内容をご確認いただいてから、数日で掲載できます。' ),
    array( 'すでにzaitoに載っている自社の求人を直したい・消したい', '下のフォームか info@zaito-work.com までご連絡ください。すぐに対応します。' ),
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
<?php zaito_remote_header( 'company' ); ?>

<section class="hero">
  <div class="wrap">
    <div class="hero-in">
      <div class="hero-copy">
        <span class="eb">FOR COMPANIES</span>
        <h1>大学生の在宅バイトを、<br>面接の手間なく採用。</h1>
        <p>zaitoは、出社なしの仕事だけを集めた大学生専門の求人サイトです。応募した学生が質問に録画で答えるので、面接は回答を見て、会いたい人を選ぶだけです。</p>
        <div class="cta-row">
          <a class="btn btn-p btn-lg" href="#contact">無料で相談する<span class="ms" aria-hidden="true">arrow_forward</span></a>
          <a class="btn btn-g btn-lg" href="#interview">面接の画面を見る</a>
        </div>
        <p style="margin-top:12px;font-size:14px">アカウントをお持ちの方は <a href="<?php echo esc_url( home_url( '/company-login/' ) ); ?>">企業ログイン</a></p>
      </div>
    </div>
  </div>
</section>

<main class="wrap">
  <section class="co-sec" id="interview" aria-labelledby="iv-title">
    <h2 id="iv-title">面接はオンラインで、選ぶだけ</h2>
    <ol class="flow4 flow3">
      <li><b>学生が録画で答える</b><span>応募するときに、御社の質問にその場で答えます。</span></li>
      <li><b>回答を見て選ぶ</b><span>空いた時間に回答を見て、会いたい学生を選びます。</span></li>
      <li><b>選んだ学生と話す</b><span>選んだ学生とだけ、オンラインで話せます。</span></li>
    </ol>
    <div class="iv-figs">
      <figure>
        <img src="<?php echo esc_url( zaito_lp_asset( 'iv-student.webp' ) ); ?>" width="1600" height="867" loading="lazy" decoding="async" alt="学生のスマホ画面のイメージ。はじめる前の説明、質問が出て考える時間、録画中、回答を送って完了の4つの画面。">
        <figcaption>学生のスマホ画面（イメージ）</figcaption>
      </figure>
      <figure>
        <img src="<?php echo esc_url( zaito_lp_asset( 'iv-company.webp' ) ); ?>" width="1400" height="912" loading="lazy" decoding="async" alt="企業の画面のイメージ。応募した学生の一覧、回答の動画と質問、再生の速さの切り替え、面接の日程を送るボタン。">
        <figcaption>企業の画面（イメージ）</figcaption>
      </figure>
    </div>
  </section>

  <section class="co-sec" aria-labelledby="flow-title">
    <h2 id="flow-title">掲載から採用までの流れ</h2>
    <ol class="flow4">
      <li><b>相談する</b><span>下のフォームから、募集したい仕事をお知らせください。</span></li>
      <li><b>原稿を確認する</b><span>zaitoが求人原稿と面接の質問を作ります。内容をご確認ください。</span></li>
      <li><b>掲載開始</b><span>求人が公開され、学生から応募が届きます。</span></li>
      <li><b>選んで採用</b><span>録画の回答を見て、会いたい学生と話し、採用を決めます。</span></li>
    </ol>
  </section>

  <section class="co-sec" id="faq" aria-labelledby="faq-title">
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
        <h2 id="contact-title">無料で相談する</h2>
        <p>まだ募集内容が固まっていなくても大丈夫です。zaito運営（info@zaito-work.com）からメールでご連絡します。</p>
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
