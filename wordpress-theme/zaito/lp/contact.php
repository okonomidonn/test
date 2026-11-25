<?php
/**
 * お問い合わせ（/contact/）。受け付けは inc/complaints.php。
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/biz-parts.php';

$zaito_sent  = isset( $_GET['sent'] );
$zaito_draft = get_transient( 'zaito_complaint_draft_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) ) );
$zaito_v     = array_merge( array( 'role' => isset( $_GET['role'] ) ? sanitize_key( wp_unslash( $_GET['role'] ) ) : '', 'kind' => '', 'name' => '', 'email' => '', 'job' => '', 'body' => '' ), is_array( $zaito_draft ) ? $zaito_draft : array() );

zaito_biz_head( 'お問い合わせ' );
?>
<body>
<?php zaito_remote_header(); ?>
<main class="wrap apply-page">
  <h1>お問い合わせ</h1>
  <p class="co" style="margin-bottom:16px">zaitoを使っている学生・求職者の方と、求人を載せている企業の方からの、ご質問・ご意見・ご相談を受け付けています。求人やサービスへの苦情も、こちらで受け付けます。</p>

<?php if ( $zaito_sent ) : ?>
  <div class="note n-ok"><span class="ms" aria-hidden="true">mark_email_read</span><div><b>受け付けました</b><p>内容を確認し、原則として3営業日以内に、入力したメールアドレスあてにzaito運営からご返事します。受け付けたことを知らせるメールも送りました。届かないときは、迷惑メールのフォルダも見てください。</p></div></div>
  <p style="margin-top:16px"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">トップページへ戻る</a></p>
<?php else : ?>
  <div class="fcard" style="margin-bottom:16px">
    <h2>たとえば、こんなときにご連絡ください</h2>
    <ul style="margin:0;padding-left:20px;line-height:1.8">
      <li>求人の内容が、実際の条件とちがっていた（出社を求められた、報酬がちがう など）</li>
      <li>企業や応募者とのやりとりで、困ったことがあった</li>
      <li>お金の支払いや、内定の前に身分証・口座の情報を求められた</li>
      <li>自分の個人情報を消してほしい、配信を止めたい</li>
      <li>サイトの使い方がわからない、うまく動かない</li>
    </ul>
    <p style="margin:12px 0 0">いただいた内容は、zaito運営が確認し、必要に応じて求人企業に事実を確かめたうえで、ご返事します。ご相談した人がわかる形で、企業に内容を伝えることはありません（伝える必要があるときは、先にご相談します）。</p>
  </div>

  <?php zaito_biz_errors(); ?>
  <form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="zaito_complaint">
    <?php wp_nonce_field( 'zaito_complaint' ); ?>
    <div style="position:absolute;left:-9999px" aria-hidden="true"><label>Webサイト<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
    <div class="fcard">
      <fieldset class="fld"><legend>あなたの立場<span class="req">必須</span></legend><div class="picks">
        <?php foreach ( zaito_complaint_roles() as $zaito_k => $zaito_l ) : ?>
          <label><input type="radio" name="role" value="<?php echo esc_attr( $zaito_k ); ?>"<?php checked( $zaito_v['role'], $zaito_k ); ?> required><span><?php echo esc_html( $zaito_l ); ?></span></label>
        <?php endforeach; ?>
      </div></fieldset>
      <div class="fld"><label for="zckind">お問い合わせの種類<span class="req">必須</span></label><select id="zckind" name="kind" required>
        <option value="">選んでください</option>
        <?php foreach ( zaito_complaint_kinds() as $zaito_k => $zaito_l ) : ?>
          <option value="<?php echo esc_attr( $zaito_k ); ?>"<?php selected( $zaito_v['kind'], $zaito_k ); ?>><?php echo esc_html( $zaito_l ); ?></option>
        <?php endforeach; ?>
      </select></div>
      <div class="fld"><label for="zcname">お名前<span class="opt">任意</span></label><input type="text" id="zcname" name="name" maxlength="60" autocomplete="name" value="<?php echo esc_attr( $zaito_v['name'] ); ?>"></div>
      <div class="fld"><label for="zcemail">返事を送るメールアドレス<span class="req">必須</span></label><input type="email" id="zcemail" name="email" maxlength="200" autocomplete="email" inputmode="email" value="<?php echo esc_attr( $zaito_v['email'] ); ?>" required></div>
      <div class="fld"><label for="zcjob">関係する求人・企業<span class="opt">任意</span></label><input type="text" id="zcjob" name="job" maxlength="300" value="<?php echo esc_attr( $zaito_v['job'] ); ?>" placeholder="求人のページのURLや、企業名など"></div>
      <div class="fld"><label for="zcbody">内容<span class="req">必須</span></label><textarea id="zcbody" name="body" rows="7" maxlength="4000" required placeholder="いつ・どんなことがあったかを書いてください"><?php echo esc_textarea( $zaito_v['body'] ); ?></textarea></div>
    </div>
    <button type="submit" class="btn btn-p btn-lg">送信する</button>
    <p class="legal">送信すると、<a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシーポリシー</a>に同意したものとみなします。メールでのお問い合わせは <a href="mailto:info@zaito-work.com">info@zaito-work.com</a> でも受け付けています。</p>
  </form>
<?php endif; ?>
</main>
<?php zaito_remote_footer(); ?>
</body>
</html>
