<?php
/**
 * 企業アカウントの登録（/company-register/）。送信は inc/direct-company.php の zaito_handle_register_company。
 * 登録後は審査中になり、運営が承認すると求人を公開できる。
 */
require_once get_template_directory() . '/lp/biz-parts.php';

if ( is_user_logged_in() && zaito_is_company() ) {
    wp_safe_redirect( home_url( '/company/' ) );
    exit;
}
zaito_biz_head( '企業アカウントの作成' );
?>
<body>
<?php zaito_biz_auth_header( 'register' ); ?>
<div class="biz-auth">
<div class="auth-split">
  <section class="pitch" aria-labelledby="zpitch">
    <h2 id="zpitch">出社なしで働きたい学生に、<br>求人を直接届けられます</h2>
    <ol>
      <li><b>アカウントを作る</b>運営が確認して承認します。</li>
      <li><b>求人を作る</b>出社のない求人だけを掲載します。</li>
      <li><b>応募者とやりとり</b>連絡はzaitoのメッセージで行います。</li>
      <li><b>掲載は無料</b>登録も掲載も無料で始められます。</li>
    </ol>
  </section>

  <main class="auth">
    <h1>企業アカウントの作成</h1>
    <p class="lead">登録は無料です。審査のあと、求人を掲載できます。</p>
    <?php zaito_biz_errors(); ?>
    <form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
      <input type="hidden" name="action" value="zaito_register_company">
      <?php wp_nonce_field( 'zaito_register_company' ); ?>
      <div class="fld"><label for="company_name">会社名<span class="req">必須</span></label><input type="text" id="company_name" name="company_name" autocomplete="organization" required></div>
      <div class="fld"><label for="company_url">会社のWebサイト<span class="req">必須</span></label><input type="url" id="company_url" name="company_url" placeholder="https://" required></div>
      <div class="fld"><label for="contact_person">ご担当者名<span class="req">必須</span></label><input type="text" id="contact_person" name="contact_person" autocomplete="name" required></div>
      <div class="fld"><label for="phone">電話番号<span class="req">必須</span></label><input type="tel" id="phone" name="phone" autocomplete="tel" required></div>
      <div class="fld"><label for="email">メールアドレス<span class="req">必須</span></label><input type="email" id="email" name="email" autocomplete="email" required></div>
      <div class="fld"><label for="password">パスワード<span class="req">必須</span></label><input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required><p class="hint">8文字以上</p></div>
      <label class="check"><input type="checkbox" name="agree_terms" value="1" required><span><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>" target="_blank">利用規約</a>と<a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>" target="_blank">プライバシーポリシー</a>に同意します</span></label>
      <button type="submit" class="btn btn-p btn-lg">アカウントを作成する</button>
    </form>
  </main>
</div>
</div>
<?php zaito_biz_auth_footer(); ?>
</body>
</html>
