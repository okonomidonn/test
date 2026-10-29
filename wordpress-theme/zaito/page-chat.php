<?php
/**
 * 応募ごとのメッセージ（/chat/?c={応募ID}）。企業（求人を出した企業のログイン）と学生（マイページの本人確認）が使う。
 * 運営（管理者）は読むだけ。読み込みと送信は inc/direct.php の admin-ajax（zaito_direct_messages / zaito_direct_send）。
 */
require_once get_template_directory() . '/lp/biz-parts.php';

// 以前のURL（?conversation_id=）も開けるようにする。
if ( isset( $_GET['conversation_id'] ) && ! isset( $_GET['c'] ) ) {
    wp_safe_redirect( zaito_direct_chat_url( (int) $_GET['conversation_id'] ) );
    exit;
}
$zaito_app    = isset( $_GET['c'] ) ? (int) $_GET['c'] : 0;
$zaito_viewer = $zaito_app ? zaito_direct_viewer_role( $zaito_app ) : '';
nocache_headers();

if ( ! $zaito_viewer ) {
    if ( is_user_logged_in() && zaito_is_company() ) {
        wp_safe_redirect( home_url( '/company-applicants/' ) );
        exit;
    }
    // 見る権限がない（ログインしていない）。企業はログイン、学生はマイページのログインへ案内する。
    zaito_biz_head( 'メッセージ' );
    ?>
<body>
<?php zaito_remote_header(); ?>
<main class="auth">
  <h1>メッセージを開くには<br>ログインが必要です</h1>
  <p class="lead">応募した方は、マイページからログインしてください（メールアドレスに届くリンクで、パスワードは不要です）。</p>
  <a class="btn btn-p btn-lg" href="<?php echo esc_url( home_url( '/mypage/' ) ); ?>">マイページからログイン</a>
  <p class="alt">企業の方は <a href="<?php echo esc_url( add_query_arg( 'redirect_to', rawurlencode( home_url( add_query_arg( array() ) ) ), home_url( '/company-login/' ) ) ); ?>">企業ログイン</a></p>
</main>
<?php zaito_remote_footer(); ?>
</body>
</html>
    <?php
    exit;
}

$zaito_job     = get_post( (int) get_post_meta( $zaito_app, 'job_id', true ) );
$zaito_company = (int) get_post_meta( $zaito_app, 'company_id', true );
$zaito_st      = zaito_direct_app_status( $zaito_app );
$zaito_lbl     = zaito_direct_app_statuses();
$zaito_name    = (string) get_post_meta( $zaito_app, 'applicant_name', true );
$zaito_is_co   = 'company' === $zaito_viewer;
$zaito_can     = in_array( $zaito_viewer, array( 'company', 'student' ), true );
// 審査中・停止中の企業は読めるが、送れない。
if ( $zaito_is_co && 'approved' !== zaito_company_status( $zaito_company ) ) {
    $zaito_can = false;
}
// 開いたら既読にする。
if ( $zaito_can ) {
    delete_post_meta( $zaito_app, 'unread_' . $zaito_viewer );
}
$zaito_job_url = $zaito_job && 'publish' === $zaito_job->post_status ? home_url( '/remote/' . zaito_direct_slug( $zaito_job->ID ) . '/' ) : '';

zaito_biz_head( $zaito_is_co ? $zaito_name . 'さんとのメッセージ' : 'メッセージ' );
?>
<body>
<?php
if ( $zaito_is_co ) {
    zaito_biz_header( 'applicants' );
} else {
    zaito_remote_header();
}
?>
<main class="wrap chat">
  <div>
    <?php if ( isset( $_GET['applied'] ) && 'student' === $zaito_viewer ) : ?>
      <div class="note n-ok"><span class="ms" aria-hidden="true">check_circle</span><div><b>応募しました</b><p>企業から返信があると、メールでお知らせします。このページはマイページの「応募した求人」からいつでも開けます。</p></div></div>
    <?php endif; ?>
    <?php if ( 'admin' === $zaito_viewer ) : ?>
      <div class="note n-info"><span class="ms" aria-hidden="true">visibility_off</span><div><b>運営として閲覧しています</b><p>運営は読むだけで、送ることはできません。</p></div></div>
    <?php endif; ?>
    <?php if ( $zaito_is_co && ! $zaito_can ) : ?>
      <div class="note n-warn"><span class="ms" aria-hidden="true">pending</span><div><b>アカウントの審査が終わるまで、メッセージは送れません</b></div></div>
    <?php endif; ?>
    <section class="chat-main" aria-label="メッセージ">
      <div class="chat-top">
        <?php if ( $zaito_is_co ) : ?>
          <a href="<?php echo esc_url( home_url( '/company-applicants/' ) ); ?>" aria-label="応募者一覧に戻る"><span class="ms" aria-hidden="true">arrow_back</span></a>
        <?php elseif ( 'student' === $zaito_viewer ) : ?>
          <a href="<?php echo esc_url( home_url( '/mypage/' ) ); ?>" aria-label="マイページに戻る"><span class="ms" aria-hidden="true">arrow_back</span></a>
        <?php endif; ?>
        <div class="who">
          <b><?php echo esc_html( $zaito_is_co ? $zaito_name . ' さん' : zaito_company_name( $zaito_company ) ); ?></b>
          <span><?php echo esc_html( $zaito_job ? $zaito_job->post_title : '' ); ?></span>
        </div>
        <span id="zst"><?php echo zaito_biz_status_pill( $zaito_st, $zaito_lbl[ $zaito_st ] ); // エスケープ済み ?></span>
      </div>
      <div class="chat-log" id="zlog" aria-live="polite"><p class="chat-day">読み込んでいます…</p></div>
      <?php if ( $zaito_can ) : ?>
        <form class="chat-form" id="zform">
          <textarea id="ztext" rows="1" placeholder="メッセージを入力" aria-label="メッセージ" maxlength="4000"></textarea>
          <button type="submit" class="btn btn-p" aria-label="送信"><span class="ms" aria-hidden="true">send</span></button>
        </form>
        <p class="chat-err" id="zerr" role="alert"></p>
      <?php endif; ?>
    </section>
  </div>

  <aside class="chat-side">
    <?php if ( $zaito_is_co ) : ?>
      <div class="side-card">
        <h3>応募者</h3>
        <dl>
          <dt>名前</dt><dd><?php echo esc_html( $zaito_name ); ?></dd>
          <dt>所属</dt><dd><?php echo esc_html( zaito_direct_applicant_line( $zaito_app ) ); ?></dd>
          <dt>応募日</dt><dd><?php echo esc_html( get_the_date( 'Y年n月j日 H:i', $zaito_app ) ); ?></dd>
          <dt>求人</dt><dd><?php echo esc_html( $zaito_job ? $zaito_job->post_title : '' ); ?></dd>
        </dl>
      </div>
      <div class="side-card">
        <h3>応募の状態</h3>
        <form class="status-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="zstatus">
          <input type="hidden" name="action" value="zaito_direct_set_status">
          <input type="hidden" name="application_id" value="<?php echo (int) $zaito_app; ?>">
          <input type="hidden" name="back" value="<?php echo esc_url( zaito_direct_chat_url( $zaito_app ) ); ?>">
          <?php wp_nonce_field( 'zaito_direct_status_' . $zaito_app ); ?>
          <select name="status" aria-label="応募の状態"<?php disabled( ! $zaito_can ); ?>>
            <?php foreach ( $zaito_lbl as $zaito_k => $zaito_l ) : ?>
              <option value="<?php echo esc_attr( $zaito_k ); ?>"<?php selected( $zaito_st, $zaito_k ); ?>><?php echo esc_html( $zaito_l ); ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-g"<?php disabled( ! $zaito_can ); ?>>変更</button>
        </form>
        <p style="margin-top:10px">「内定」にした時点で、成果報酬が発生します。「内定」と「不採用」は、応募者にメールでも知らせます。</p>
      </div>
      <div class="side-card">
        <h3>やりとりのお願い</h3>
        <ul>
          <li>日程調整と結果の連絡は、このメッセージで</li>
          <li>面接のZoomやGoogle MeetのURLは、ここで送れます</li>
          <li>電話番号やLINEなどの交換は、内定のあとにしてください</li>
        </ul>
      </div>
    <?php else : ?>
      <div class="side-card">
        <h3>応募した求人</h3>
        <dl>
          <dt>会社</dt><dd><?php echo esc_html( zaito_company_name( $zaito_company ) ); ?></dd>
          <dt>求人</dt><dd><?php echo esc_html( $zaito_job ? $zaito_job->post_title : '' ); ?></dd>
          <dt>報酬</dt><dd><?php echo esc_html( $zaito_job ? zaito_direct_pay_text( $zaito_job->ID ) : '' ); ?></dd>
          <dt>応募日</dt><dd><?php echo esc_html( get_the_date( 'Y年n月j日', $zaito_app ) ); ?></dd>
        </dl>
        <?php if ( $zaito_job_url ) : ?><a class="btn btn-g" href="<?php echo esc_url( $zaito_job_url ); ?>">求人の詳細を見る</a><?php endif; ?>
      </div>
      <div class="side-card">
        <h3>安心して進めるために</h3>
        <ul>
          <li>面接はZoomなどオンラインで行われます。出社やオフィスへの訪問を求められたら、zaitoに知らせてください</li>
          <li>電話番号やLINEなどの交換は、内定が出るまで控えてください</li>
          <li>困ったときは <a href="mailto:info@zaito-work.com?subject=<?php echo rawurlencode( 'メッセージについての相談（応募番号 ' . $zaito_app . '）' ); ?>">info@zaito-work.com</a> へ</li>
        </ul>
      </div>
    <?php endif; ?>
  </aside>
</main>
<?php
// 学生の画面には、サイト共通のフッター（登録済みなら「無料登録」を消す fav.js も読む）。
if ( ! $zaito_is_co ) {
    zaito_remote_footer();
}
?>

<script>
(function () {
  var AJAX = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
  var C = <?php echo (int) $zaito_app; ?>;
  var NONCE = <?php echo wp_json_encode( wp_create_nonce( 'zaito_chat_' . $zaito_app ) ); ?>;
  var log = document.getElementById('zlog');
  var form = document.getElementById('zform');
  var text = document.getElementById('ztext');
  var err = document.getElementById('zerr');
  var lastId = 0;

  function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
  // URLだけはリンクにする（面接のZoomなど）。中身はエスケープしてから置き換える。
  function linkify(s) { return esc(s).replace(/https?:\/\/[^\s<]+/g, function (u) { return '<a href="' + u + '" target="_blank" rel="noopener noreferrer nofollow">' + u + '</a>'; }); }
  function render(list) {
    var newest = list.length ? list[list.length - 1].id : 0;
    if (newest === lastId) return;
    var atBottom = log.scrollHeight - log.scrollTop - log.clientHeight < 80;
    lastId = newest;
    if (!list.length) { log.innerHTML = '<p class="chat-day">まだメッセージはありません。</p>'; return; }
    log.innerHTML = list.map(function (m) {
      return '<div class="msg' + (m.own ? ' own' : '') + '"><div class="bub">' + linkify(m.text) + '</div><span class="tm">' + esc(m.time) + '</span></div>';
    }).join('');
    if (atBottom || !log.dataset.ready) { log.scrollTop = log.scrollHeight; log.dataset.ready = '1'; }
  }
  function post(action, extra) {
    var fd = new FormData();
    fd.append('action', action); fd.append('c', C);
    Object.keys(extra || {}).forEach(function (k) { fd.append(k, extra[k]); });
    return fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); });
  }
  function load() {
    return post('zaito_direct_messages').then(function (r) {
      if (!r || !r.success) return;
      render(r.data.messages);
      // 状態（新着→やりとり中など）が変わったら、上のラベルも変える。
      var pill = document.querySelector('#zst .st');
      if (pill && r.data.status_key) { pill.className = 'st st-' + r.data.status_key; pill.textContent = r.data.status; }
    }).catch(function () {});
  }
  load();
  setInterval(function () { if (!document.hidden) load(); }, 8000);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) load(); });

  if (form) {
    function grow() { text.style.height = 'auto'; text.style.height = Math.min(text.scrollHeight, 180) + 'px'; }
    text.addEventListener('input', grow);
    text.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) { e.preventDefault(); form.requestSubmit(); }
    });
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var v = text.value.trim();
      if (!v) return;
      var btn = form.querySelector('button');
      btn.disabled = true; err.textContent = '';
      post('zaito_direct_send', { text: v, nonce: NONCE }).then(function (r) {
        btn.disabled = false;
        if (!r || !r.success) { err.textContent = (r && r.data && r.data.message) || '送れませんでした。もう一度お試しください'; return; }
        text.value = ''; grow();
        log.dataset.ready = '';
        render(r.data.messages);
      }).catch(function () { btn.disabled = false; err.textContent = '送れませんでした。通信を確認してください'; });
    });
  }
  var st = document.getElementById('zstatus');
  if (st) {
    st.addEventListener('submit', function (e) {
      if (st.elements.status.value === 'offer' && !confirm('「内定」にすると、成果報酬の対象になります。応募者にも内定のメールが届きます。よろしいですか？')) { e.preventDefault(); }
    });
  }
})();
</script>
</body>
</html>
