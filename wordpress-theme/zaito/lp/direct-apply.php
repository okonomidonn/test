<?php
/**
 * 直接掲載の求人の応募フォーム（/remote/d{ID}/apply/）。inc/remote-jobs.php から $zaito_remote_job をセットして読み込む。
 *
 * 応募できるのは、本人確認済みのブラウザ（マイページのCookie zaito_my）で、メールアドレスの確認も済んだ登録だけ。
 * - 本人確認済み → フォーム（氏名・ふりがな・生年月日・都道府県・電話番号・現在の状況・学年・学校名・メッセージ・企業からの質問）
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
$zaito_meta   = function ( $k ) use ( $zaito_me ) {
    return $zaito_me ? (string) get_post_meta( $zaito_me, $k, true ) : '';
};
$zaito_birth  = $zaito_meta( 'birthdate' );
$zaito_v      = array(
    'name_last'  => $zaito_meta( 'name_last' ),
    'name_first' => $zaito_meta( 'name_first' ),
    'kana_last'  => $zaito_meta( 'kana_last' ),
    'kana_first' => $zaito_meta( 'kana_first' ),
    'role'       => $zaito_meta( 'role' ),
    'grade'      => $zaito_meta( 'grade' ),
    'school'     => $zaito_meta( 'school' ),
    'birth_y'    => $zaito_birth ? (string) (int) substr( $zaito_birth, 0, 4 ) : '',
    'birth_m'    => $zaito_birth ? (string) (int) substr( $zaito_birth, 5, 2 ) : '',
    'birth_d'    => $zaito_birth ? (string) (int) substr( $zaito_birth, 8, 2 ) : '',
    'pref'       => $zaito_meta( 'pref' ),
    'tel'        => $zaito_meta( 'tel' ),
    'message'    => '',
    'answer'     => '',
);
// 姓と名を分けて聞く前に登録した人は、名前を最初の空白で姓と名に分けて入れておく。
if ( '' === $zaito_v['name_last'] && '' !== $zaito_meta( 'name' ) ) {
    $zaito_parts             = preg_split( '/[\s　]+/u', trim( $zaito_meta( 'name' ) ), 2 );
    $zaito_v['name_last']  = $zaito_parts[0];
    $zaito_v['name_first'] = isset( $zaito_parts[1] ) ? $zaito_parts[1] : '';
}
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
    <div style="max-width:420px"><?php if ( zaito_social_buttons( wp_make_link_relative( $zaito_j['url'] ), '続ける' ) ) : ?><p style="font-size:13px;color:var(--z-text-3);margin:8px 0 0">zaitoに登録したメールアドレスと同じアカウントを選んでください。</p><?php endif; ?></div>
  <?php endif; ?>
<?php else : ?>
  <?php zaito_biz_errors(); ?>
  <form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="zapply">
    <input type="hidden" name="action" value="zaito_direct_apply">
    <input type="hidden" name="job_id" value="<?php echo (int) $zaito_j['post_id']; ?>">
    <?php wp_nonce_field( 'zaito_direct_apply_' . $zaito_j['post_id'] ); ?>

    <div class="fcard">
      <h2>応募者情報</h2>
      <p>入力した内容は、応募先の企業に送られます。電話番号は、企業が面接の日程などを連絡するときに使います。メールアドレスは企業には送られません。</p>
      <fieldset class="fld"><legend>氏名<span class="req">必須</span></legend><div class="pair">
        <input type="text" name="name_last" maxlength="20" autocomplete="family-name" aria-label="姓" value="<?php echo esc_attr( $zaito_v['name_last'] ); ?>" required placeholder="姓（例：山田）">
        <input type="text" name="name_first" maxlength="20" autocomplete="given-name" aria-label="名" value="<?php echo esc_attr( $zaito_v['name_first'] ); ?>" required placeholder="名（例：花子）">
      </div></fieldset>
      <fieldset class="fld"><legend>ふりがな<span class="req">必須</span></legend><div class="pair">
        <input type="text" name="kana_last" maxlength="30" aria-label="せい" value="<?php echo esc_attr( $zaito_v['kana_last'] ); ?>" required placeholder="せい（やまだ）">
        <input type="text" name="kana_first" maxlength="30" aria-label="めい" value="<?php echo esc_attr( $zaito_v['kana_first'] ); ?>" required placeholder="めい（はなこ）">
      </div></fieldset>
      <fieldset class="fld"><legend>生年月日<span class="req">必須</span></legend><div class="ymd">
        <select name="birth_y" aria-label="生まれた年" autocomplete="bday-year" required>
          <option value="">年</option>
          <?php for ( $zaito_y = (int) current_time( 'Y' ) - 15; $zaito_y >= (int) current_time( 'Y' ) - 80; $zaito_y-- ) : ?>
            <option value="<?php echo (int) $zaito_y; ?>"<?php selected( $zaito_v['birth_y'], (string) $zaito_y ); ?>><?php echo (int) $zaito_y; ?>年</option>
          <?php endfor; ?>
        </select>
        <select name="birth_m" aria-label="生まれた月" autocomplete="bday-month" required>
          <option value="">月</option>
          <?php for ( $zaito_m = 1; $zaito_m <= 12; $zaito_m++ ) : ?>
            <option value="<?php echo (int) $zaito_m; ?>"<?php selected( $zaito_v['birth_m'], (string) $zaito_m ); ?>><?php echo (int) $zaito_m; ?>月</option>
          <?php endfor; ?>
        </select>
        <select name="birth_d" aria-label="生まれた日" autocomplete="bday-day" required>
          <option value="">日</option>
          <?php for ( $zaito_d = 1; $zaito_d <= 31; $zaito_d++ ) : ?>
            <option value="<?php echo (int) $zaito_d; ?>"<?php selected( $zaito_v['birth_d'], (string) $zaito_d ); ?>><?php echo (int) $zaito_d; ?>日</option>
          <?php endfor; ?>
        </select>
      </div></fieldset>
      <div class="fld"><label for="zpref">お住まいの都道府県<span class="req">必須</span></label><select id="zpref" name="pref" autocomplete="address-level1" required>
        <option value="">選んでください</option>
        <?php foreach ( zaito_direct_prefectures() as $zaito_o ) : ?>
          <option value="<?php echo esc_attr( $zaito_o ); ?>"<?php selected( $zaito_v['pref'], $zaito_o ); ?>><?php echo esc_html( $zaito_o ); ?></option>
        <?php endforeach; ?>
      </select></div>
      <div class="fld"><label for="ztel">電話番号<span class="req">必須</span></label><input type="tel" id="ztel" name="tel" maxlength="20" autocomplete="tel" inputmode="tel" value="<?php echo esc_attr( $zaito_v['tel'] ); ?>" required placeholder="例：090-1234-5678"><p class="hint">日中につながりやすい番号を入力してください。ハイフンはなくてもかまいません。</p></div>
      <fieldset class="fld"><legend>現在の状況<span class="req">必須</span></legend><div class="picks">
        <?php foreach ( zaito_direct_roles() as $zaito_o ) : ?>
          <label><input type="radio" name="role" value="<?php echo esc_attr( $zaito_o ); ?>"<?php checked( $zaito_v['role'], $zaito_o ); ?> required><span><?php echo esc_html( $zaito_o ); ?></span></label>
        <?php endforeach; ?>
      </div></fieldset>
      <div class="stu"<?php echo '学生ではない' === $zaito_v['role'] ? ' hidden' : ''; ?>>
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
      <p>伝えたいことがあれば書いてください。空欄のままでも応募できます。</p>
      <div class="fld"><label for="zmsg">メッセージ<span class="opt">任意</span></label><textarea id="zmsg" name="message" rows="4" maxlength="2000" placeholder="例：授業のない平日の午後に、週3日ほど働けます。"><?php echo esc_textarea( $zaito_v['message'] ); ?></textarea></div>
      <?php if ( $zaito_j['question'] ) : ?>
        <div class="fld"><label for="zans"><?php echo esc_html( $zaito_j['question'] ); ?><span class="req">必須</span></label><textarea id="zans" name="answer" rows="3" maxlength="1000" required><?php echo esc_textarea( $zaito_v['answer'] ); ?></textarea></div>
      <?php endif; ?>
    </div>

    <?php if ( ! empty( $zaito_j['video_q'] ) ) : ?>
      <div class="fcard">
        <h2>応募のあとに、動画で質問に答えます</h2>
        <p>「応募に進む」を押すと、<?php echo esc_html( $zaito_j['company'] ); ?>からの質問<?php echo count( $zaito_j['video_q'] ); ?>問に、この画面の続きで動画で答えます（1問60秒まで・全部で約<?php echo (int) max( 3, ceil( count( $zaito_j['video_q'] ) * 1.5 ) + 1 ); ?>分）。質問は、録画の直前に1問ずつ出ます。</p>
        <p>カメラとマイクを使います。iPhoneはSafari、AndroidはChromeで開いてください。動画は応募先の企業とzaitoの運営だけが見られ、応募から90日で自動で消えます。動画を送り終えるまで、応募は企業に届きません。</p>
      </div>
    <?php endif; ?>
    <button type="submit" class="btn btn-p btn-lg"><?php echo empty( $zaito_j['video_q'] ) ? '応募する' : '応募に進む（このあと動画で答えます）'; ?></button>
    <p class="legal">応募すると、<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">利用規約</a>と<a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシーポリシー</a>に同意したものとし、入力した内容が<?php echo esc_html( $zaito_j['company'] ); ?>に送られます。</p>
  </form>
  <script>
  (function () {
    var f = document.getElementById('zapply'), stu = f.querySelector('.stu');
    var school = document.getElementById('zschool');
    // 学生を選んだときだけ、学年と学校名を出す。学校名は学生なら必須。
    function sync(v) { var on = v !== '学生ではない'; stu.hidden = !on; school.required = on; }
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
