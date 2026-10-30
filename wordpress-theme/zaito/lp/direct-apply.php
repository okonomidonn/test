<?php
/**
 * 直接掲載の求人の応募フォーム（/remote/d{ID}/apply/）。inc/remote-jobs.php から $zaito_remote_job をセットして読み込む。
 *
 * 応募できるのは、本人確認済みのブラウザ（マイページのCookie zaito_my）で、メールアドレスの確認も済んだ登録だけ。
 * - 本人確認済み → フォーム（名前・立場・学年・学校名・メッセージ・企業からの質問）
 * - 登録はあるが本人確認がまだ → 登録したアドレスに、このページを開くリンクを送る
 * - 未登録 → 求人の詳細ページの登録フォームへ戻す
 * 送信は inc/direct.php の zaito_handle_direct_apply。
 *
 * @var array $zaito_remote_job
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/biz-parts.php';

$zaito_j      = $zaito_remote_job;
$zaito_me     = zaito_my_id();
$zaito_member = zaito_remote_member_id();
// 企業とのやりとりはメールで知らせるため、メールアドレスの確認が済んだ登録だけが応募できる。
if ( $zaito_me && ! zaito_interest_verified( $zaito_me ) ) {
    $zaito_member = $zaito_me;
    $zaito_me     = 0;
}
$zaito_detail = zaito_remote_url( $zaito_j['slug'] );

if ( ! $zaito_me && ! $zaito_member ) {
    wp_safe_redirect( $zaito_detail . '#apply' );
    exit;
}
if ( $zaito_me ) {
    $zaito_done = zaito_direct_find_application( $zaito_me, $zaito_j['post_id'] );
    if ( $zaito_done ) {
        wp_safe_redirect( zaito_direct_chat_url( $zaito_done ) );
        exit;
    }
}

// 本人確認のリンクを送る（このページに戻ってこられるよう、マイページを経由してここへ）。
$zaito_sent = false;
if ( ! $zaito_me && isset( $_POST['send_link'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'zaito_apply_link' ) ) {
    $zaito_key = 'zaito_apply_link_' . $zaito_member;
    if ( ! get_transient( $zaito_key ) && ! zaito_lp_rate_limited( 'apply_link', 5 ) ) {
        set_transient( $zaito_key, 1, MINUTE_IN_SECONDS );
        zaito_mail_button(
            (string) get_post_meta( $zaito_member, 'email', true ),
            '【zaito】応募フォームを開くリンク',
            '応募フォームを開く',
            '下のボタンを押すと、「' . $zaito_j['title'] . '」（' . $zaito_j['company'] . '）の応募フォームが開きます。',
            '応募フォームを開く',
            add_query_arg( 'next', rawurlencode( wp_make_link_relative( $zaito_j['url'] ) ), zaito_my_login_url( $zaito_member ) ),
            'ボタンは24時間有効です。',
            'お心当たりがない場合は、このメールを破棄してください。',
            zaito_direct_from()
        );
    }
    $zaito_sent = true;
}

$zaito_opts  = zaito_remote_profile_options();
$zaito_draft = $zaito_me ? get_transient( 'zaito_apply_draft_' . $zaito_me ) : false;
$zaito_v     = array(
    'name'    => $zaito_me ? (string) get_post_meta( $zaito_me, 'name', true ) : '',
    'role'    => $zaito_me ? (string) get_post_meta( $zaito_me, 'role', true ) : '',
    'grade'   => $zaito_me ? (string) get_post_meta( $zaito_me, 'grade', true ) : '',
    'school'  => $zaito_me ? (string) get_post_meta( $zaito_me, 'school', true ) : '',
    'message' => '',
    'answer'  => '',
);
if ( is_array( $zaito_draft ) ) {
    $zaito_v = array_merge( $zaito_v, $zaito_draft );
}

zaito_biz_head( '応募する：' . $zaito_j['title'] );
?>
<body>
<?php zaito_remote_header(); ?>
<main class="wrap apply-page">
  <p class="crumb" style="margin:0"><a href="<?php echo esc_url( $zaito_detail ); ?>"><span class="ms" aria-hidden="true" style="font-size:16px;vertical-align:-3px">arrow_back</span> 求人に戻る</a></p>
  <h1><?php echo esc_html( $zaito_j['title'] ); ?></h1>
  <p class="co"><?php echo esc_html( $zaito_j['company'] ); ?>　／　<?php echo esc_html( $zaito_j['pay'] ); ?></p>

<?php if ( ! $zaito_me ) : ?>
  <?php if ( $zaito_sent ) : ?>
    <div class="note n-ok"><span class="ms" aria-hidden="true">mark_email_read</span><div><b>メールを送りました</b><p>登録したメールアドレスに届いたリンクを押すと、応募フォームが開きます。届かないときは迷惑メールのフォルダも見てください。</p></div></div>
  <?php else : ?>
    <div class="note n-info"><span class="ms" aria-hidden="true">mail</span><div><b>メールのリンクから応募フォームを開いてください</b><p>企業からの返信は、メールでお知らせします。そのため応募の前に、登録したアドレスに届くリンクを押して、アドレスを確認してもらっています。パスワードはいりません。</p>
      <form method="post"><?php wp_nonce_field( 'zaito_apply_link' ); ?><button type="submit" name="send_link" value="1" class="btn btn-p" style="margin-top:4px">応募フォームのリンクを送る</button></form></div></div>
  <?php endif; ?>
<?php else : ?>
  <?php zaito_biz_errors(); ?>
  <form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="zapply">
    <input type="hidden" name="action" value="zaito_direct_apply">
    <input type="hidden" name="job_id" value="<?php echo (int) $zaito_j['post_id']; ?>">
    <?php wp_nonce_field( 'zaito_direct_apply_' . $zaito_j['post_id'] ); ?>

    <div class="fcard">
      <h2>あなたについて</h2>
      <p>企業に伝わるのは、ここに書いた内容とメッセージだけです。メールアドレスは企業に伝わりません。</p>
      <div class="fld"><label for="zname">お名前<span class="req">必須</span></label><input type="text" id="zname" name="name" maxlength="40" autocomplete="name" value="<?php echo esc_attr( $zaito_v['name'] ); ?>" required placeholder="例：山田 花子"></div>
      <fieldset class="fld"><legend>現在の職業<span class="req">必須</span></legend><div class="picks">
        <?php foreach ( $zaito_opts['role'] as $zaito_o ) : ?>
          <label><input type="radio" name="role" value="<?php echo esc_attr( $zaito_o ); ?>"<?php checked( $zaito_v['role'], $zaito_o ); ?> required><span><?php echo esc_html( $zaito_o ); ?></span></label>
        <?php endforeach; ?>
      </div></fieldset>
      <div class="stu"<?php echo preg_match( '/生$/u', $zaito_v['role'] ) || '' === $zaito_v['role'] ? '' : ' hidden'; ?>>
        <fieldset class="fld"><legend>学年<span class="opt">任意</span></legend><div class="picks">
          <?php foreach ( $zaito_opts['grade'] as $zaito_o ) : ?>
            <label><input type="radio" name="grade" value="<?php echo esc_attr( $zaito_o ); ?>"<?php checked( $zaito_v['grade'], $zaito_o ); ?>><span><?php echo esc_html( $zaito_o ); ?></span></label>
          <?php endforeach; ?>
        </div></fieldset>
        <div class="fld"><label for="zschool">学校名<span class="req">必須</span></label><input type="text" id="zschool" name="school" maxlength="80" autocomplete="organization" value="<?php echo esc_attr( $zaito_v['school'] ); ?>" placeholder="例：〇〇大学" data-schools="<?php echo esc_url( zaito_lp_asset( 'schools.json' ) ); ?>"></div>
        <script src="<?php echo esc_url( zaito_lp_asset( 'school-suggest.js' ) ); ?>" defer></script>
      </div>
    </div>

    <div class="fcard">
      <h2>企業へのメッセージ</h2>
      <p>このメッセージが、企業とのやりとりの最初のメッセージになります。</p>
      <div class="fld"><label for="zmsg">自己紹介・応募の理由<span class="req">必須</span></label><textarea id="zmsg" name="message" rows="7" maxlength="2000" required placeholder="例：〇〇大学2年の山田です。授業の合間に週2日ほど働けます。文章を書くのが好きで、ゼミのレポートでは…"><?php echo esc_textarea( $zaito_v['message'] ); ?></textarea><p class="hint">働ける曜日・時間、興味を持った理由、得意なことなどを書くと、返事がもらいやすくなります。</p></div>
      <?php if ( $zaito_j['question'] ) : ?>
        <div class="fld"><label for="zans"><?php echo esc_html( $zaito_j['question'] ); ?><span class="req">必須</span></label><textarea id="zans" name="answer" rows="3" maxlength="1000" required><?php echo esc_textarea( $zaito_v['answer'] ); ?></textarea><p class="hint">企業からの質問です。</p></div>
      <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-p btn-lg">応募する</button>
    <p class="legal">応募すると、<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">利用規約</a>と<a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシーポリシー</a>に同意し、上の内容を<?php echo esc_html( $zaito_j['company'] ); ?>に伝えることに同意したものとみなします。</p>
  </form>
  <script>
  (function () {
    var f = document.getElementById('zapply'), stu = f.querySelector('.stu');
    var school = document.getElementById('zschool');
    // 学生を選んだときだけ、学年と学校名を出す。学校名は学生なら必須。
    function sync(v) { var on = /生$/.test(v); stu.hidden = !on; school.required = on; }
    var cur = f.querySelector('input[name=role]:checked');
    sync(cur ? cur.value : '大学生');
    f.addEventListener('change', function (e) { if (e.target.name === 'role') { sync(e.target.value); } });
    f.addEventListener('submit', function () { f.querySelector('button[type=submit]').disabled = true; });
  })();
  </script>
<?php endif; ?>
</main>
<?php zaito_remote_footer(); ?>
</body>
</html>
