<?php
/**
 * 企業の会社情報（/company-profile/）。保存は inc/direct-company.php の zaito_handle_update_company_profile。
 * メールアドレスの変更は確認の流れが別に必要なため、ここでは扱わない（運営に連絡してもらう）。
 */
require_once get_template_directory() . '/lp/biz-parts.php';
$zaito_user = zaito_require_company();
zaito_biz_head( '会社情報' );
?>
<body>
<?php zaito_biz_header( 'profile' ); ?>
<main class="wrap bz">
  <div class="biz-title"><div><h1>会社情報</h1><p>会社名は求人に表示されます。電話番号は運営からの連絡にだけ使います。</p></div></div>
  <?php zaito_biz_errors(); ?>
  <?php if ( isset( $_GET['saved'] ) ) : ?>
    <div class="note n-ok"><span class="ms" aria-hidden="true">check_circle</span><div><b>保存しました</b></div></div>
  <?php endif; ?>
  <form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="zaito_update_company_profile">
    <?php wp_nonce_field( 'zaito_update_company_profile' ); ?>
    <div class="fcard">
      <h2 style="margin-bottom:16px">会社の情報</h2>
      <div class="fld"><label for="company_name">会社名<span class="req">必須</span></label><input type="text" id="company_name" name="company_name" value="<?php echo esc_attr( get_user_meta( $zaito_user->ID, 'company_name', true ) ); ?>" required></div>
      <div class="fld"><label for="company_url">会社のWebサイト</label><input type="url" id="company_url" name="company_url" value="<?php echo esc_attr( get_user_meta( $zaito_user->ID, 'company_url', true ) ); ?>" placeholder="https://"></div>
      <div class="fld"><label for="contact_person">ご担当者名<span class="req">必須</span></label><input type="text" id="contact_person" name="contact_person" value="<?php echo esc_attr( $zaito_user->first_name ); ?>" required></div>
      <div class="fld"><label for="phone">電話番号<span class="req">必須</span></label><input type="tel" id="phone" name="phone" value="<?php echo esc_attr( get_user_meta( $zaito_user->ID, 'company_phone', true ) ); ?>" required></div>
      <div class="fld"><span class="lb">ログイン用のメールアドレス</span><p style="margin:0;font-size:15px"><?php echo esc_html( $zaito_user->user_email ); ?></p><p class="hint">変更したいときは info@zaito-work.com までご連絡ください。</p></div>
    </div>
    <div class="fcard">
      <h2>応募直後の自動メッセージ</h2>
      <p>応募があったとき、応募者に自動で送るメッセージです。求人ごとに別の文面も設定できます。空欄なら送りません。</p>
      <div class="fld"><textarea name="auto_reply_message" rows="4" aria-label="応募直後の自動メッセージ" placeholder="例：ご応募ありがとうございます。2営業日以内に、面談の日程についてご連絡します。"><?php echo esc_textarea( get_user_meta( $zaito_user->ID, 'auto_reply_message', true ) ); ?></textarea></div>
    </div>
    <div class="form-acts"><button type="submit" class="btn btn-p btn-lg">保存する</button></div>
  </form>
</main>
<?php zaito_biz_footer(); ?>
</body>
</html>
