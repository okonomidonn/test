<?php
/**
 * 動画で答える応募の録画画面（/interview/?c={応募ID}）。応募した学生だけが開ける。
 * 仕組みは inc/video-interview.php。画面の流れは demo/interview.html（試作）と同じ：
 * 確認 → カメラの確認 → 練習の質問（記録しない）→ 質問ごとに「考える20秒 → 答える60秒 → 見直し（撮り直し1回）→ 送る」→ 完了。
 * 質問はサーバーから1問ずつ受け取る（前の質問に答え終わるまで、次の質問は見えない）。
 */
require_once get_template_directory() . '/lp/biz-parts.php';

$zaito_app = isset( $_GET['c'] ) ? (int) $_GET['c'] : 0;
nocache_headers();
if ( ! $zaito_app || 'student' !== zaito_direct_viewer_role( $zaito_app ) ) {
	wp_safe_redirect( $zaito_app ? zaito_direct_chat_url( $zaito_app ) : home_url( '/mypage/' ) );
	exit;
}
if ( 'pending' !== zaito_video_status( $zaito_app ) || 'declined' === zaito_direct_app_status( $zaito_app ) ) {
	wp_safe_redirect( zaito_direct_chat_url( $zaito_app ) );
	exit;
}
$zaito_job      = get_post( (int) get_post_meta( $zaito_app, 'job_id', true ) );
$zaito_company  = zaito_company_name( (int) get_post_meta( $zaito_app, 'company_id', true ) );
$zaito_total    = count( zaito_video_questions( $zaito_app ) );
$zaito_answered = zaito_video_answered_count( $zaito_app );
$zaito_cfg      = array(
	'ajax'     => admin_url( 'admin-ajax.php' ),
	'c'        => $zaito_app,
	'nonce'    => wp_create_nonce( 'zaito_video_' . $zaito_app ),
	'total'    => $zaito_total,
	'answered' => $zaito_answered,
	'think'    => zaito_video_think_seconds(),
	'answer'   => zaito_video_answer_seconds(),
	'maxBytes' => zaito_video_max_bytes(),
	'chat'     => zaito_direct_chat_url( $zaito_app ),
);

zaito_biz_head( '動画で質問に答える' );
?>
<body>
<?php zaito_remote_header(); ?>
<style>
.iv{max-width:560px;margin:0 auto;padding:16px 16px 40px}
.iv [hidden]{display:none!important}
.iv-card{background:#fff;border:1px solid var(--z-line,#E3E7EF);border-radius:16px;padding:20px}
.iv-card+.iv-card{margin-top:12px}
.iv h1{font-size:21px;line-height:1.45;margin:0 0 6px}
.iv h2{font-size:17px;margin:0 0 10px}
.iv p{margin:0 0 10px;font-size:15px;line-height:1.75}
.iv .iv-note{font-size:13px;color:var(--z-text-3,#6B7590);line-height:1.6}
.iv .iv-co{font-size:13px;color:var(--z-text-3,#6B7590);margin-bottom:8px}
.iv-flow{list-style:none;margin:12px 0 0;padding:0;display:grid;gap:8px}
.iv-flow li{display:flex;gap:10px;align-items:flex-start;font-size:14px;line-height:1.6}
.iv-flow b{flex:none;width:22px;height:22px;border-radius:999px;background:#EEF1FF;color:#3D5AFE;font-size:12px;display:flex;align-items:center;justify-content:center;margin-top:1px}
.iv-btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;min-height:54px;border:0;border-radius:14px;background:#3D5AFE;color:#fff;font:inherit;font-size:16px;font-weight:700;cursor:pointer;text-decoration:none;padding:0 16px}
.iv-btn:disabled{opacity:.45;cursor:default}
.iv-btn.sub{background:#fff;color:#0B1530;box-shadow:inset 0 0 0 1.5px #E3E7EF}
.iv-btn.rec{background:#E5484D}
.iv-btn:active:not(:disabled){transform:translateY(1px)}
.iv-row{display:flex;gap:10px}
.iv-row>*{flex:1}
.iv-check{display:flex;gap:10px;align-items:flex-start;margin:14px 0;font-size:14px;line-height:1.6;cursor:pointer}
.iv-check input{width:20px;height:20px;margin-top:2px;accent-color:#3D5AFE;flex:none}
.iv-stage{position:relative;border-radius:16px;overflow:hidden;background:#0B1530;aspect-ratio:3/4;max-height:62vh;width:100%;margin:0 auto 12px}
.iv-stage video{width:100%;height:100%;object-fit:cover;display:block;transform:scaleX(-1)}
.iv-stage video.play{transform:none;object-fit:contain;background:#000}
.iv-q{position:absolute;left:0;right:0;top:0;padding:16px 16px 28px;background:linear-gradient(180deg,rgba(11,21,48,.86),rgba(11,21,48,0));color:#fff}
.iv-q .qn{font-size:12px;font-weight:700;opacity:.8;margin-bottom:4px}
.iv-q .qt{font-size:18px;font-weight:800;line-height:1.5}
.iv-timer{position:absolute;left:50%;bottom:16px;transform:translateX(-50%);display:flex;align-items:center;gap:8px;padding:8px 14px;border-radius:999px;background:rgba(11,21,48,.72);color:#fff;font-size:15px;font-weight:700;white-space:nowrap}
.iv-timer .dot{width:10px;height:10px;border-radius:999px;background:#E5484D;animation:ivblink 1s infinite}
.iv-timer.think .dot{background:#FFB547;animation:none}
@keyframes ivblink{50%{opacity:.25}}
@media (prefers-reduced-motion:reduce){.iv-timer .dot{animation:none}}
.iv-meter{height:6px;border-radius:999px;background:#E6EAF3;overflow:hidden;margin:6px 0 14px}
.iv-meter i{display:block;height:100%;width:0;background:#1A7F55;transition:width .1s}
.iv-steps{display:flex;gap:6px;margin-bottom:12px}
.iv-steps i{flex:1;height:5px;border-radius:999px;background:#E1E6F0}
.iv-steps i.on{background:#3D5AFE}
.iv-done{width:56px;height:56px;border-radius:999px;background:#EEF9F3;color:#1A7F55;display:flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:10px}
.iv-err{color:#C62828;font-size:14px;line-height:1.6;margin-top:8px}
.iv-bar{height:8px;border-radius:999px;background:#E6EAF3;overflow:hidden;margin:10px 0 4px}
.iv-bar i{display:block;height:100%;width:0;background:#3D5AFE}
</style>
<main class="iv">

  <!-- 1. はじめる前 -->
  <div id="s-intro">
    <div class="iv-card">
      <div class="iv-co"><?php echo esc_html( $zaito_company ); ?></div>
      <h1><?php echo esc_html( $zaito_job ? $zaito_job->post_title : '' ); ?></h1>
      <p>応募の内容は保存しました。最後に、<?php echo esc_html( $zaito_company ); ?>からの質問に動画で答えると、応募が企業に届きます。</p>
      <ol class="iv-flow">
        <li><b>1</b><span>質問<?php echo (int) $zaito_total; ?>問に、1問60秒以内で答えます<?php echo $zaito_answered ? '（' . (int) $zaito_answered . '問は答え済み。続きから始めます）' : ''; ?>。</span></li>
        <li><b>2</b><span>企業が回答を見て、話してみたい人にはzaitoのメッセージで連絡します。</span></li>
      </ol>
    </div>
    <div class="iv-card">
      <h2>答え方</h2>
      <p>質問は、録画の直前に1問ずつ画面に出て、読み上げられます。考える時間が<?php echo (int) zaito_video_think_seconds(); ?>秒あり、答える時間は<?php echo (int) zaito_video_answer_seconds(); ?>秒です。撮り直しは1問につき1回までです。最初に、記録されない練習の質問が1問あります。</p>
      <p class="iv-note">静かで、顔が明るく映る場所で答えてください。通信の量は1問あたり5〜8MBほどです。</p>
      <label class="iv-check"><input type="checkbox" id="agree">動画は<?php echo esc_html( $zaito_company ); ?>とzaitoの運営だけが見られ、応募から90日（<?php echo esc_html( zaito_video_expire_label( $zaito_app ) ); ?>）で自動で消えることを確認しました。</label>
      <button type="button" class="iv-btn" id="startBtn" disabled>カメラを準備する</button>
      <p class="iv-note" style="margin-top:10px">今は答えずに、あとでマイページの「応募した求人」から続けることもできます。答え終わるまで、応募は企業に届きません。</p>
    </div>
  </div>

  <!-- 2. カメラの確認 -->
  <div id="s-check" hidden>
    <div class="iv-card">
      <h2>カメラとマイクの確認</h2>
      <div class="iv-stage"><video id="cam" playsinline muted autoplay></video></div>
      <span class="iv-note">声の大きさ</span>
      <div class="iv-meter"><i id="lvl"></i></div>
      <p class="iv-note">顔が明るく映っていて、声を出すと緑のバーが動けば準備できています。</p>
      <button type="button" class="iv-btn" id="toFirst">練習の質問へ進む</button>
    </div>
  </div>

  <!-- 3. 質問（練習と本番） -->
  <div id="s-q" hidden>
    <div class="iv-steps" id="steps"></div>
    <div class="iv-stage">
      <video id="qCam" playsinline muted autoplay></video>
      <video id="qPlay" class="play" playsinline controls hidden></video>
      <div class="iv-q"><div class="qn" id="qNum"></div><div class="qt" id="qText"></div></div>
      <div class="iv-timer" id="timer" hidden><span class="dot"></span><span id="tText"></span></div>
    </div>
    <div id="qThink" hidden>
      <button type="button" class="iv-btn rec" id="recNow">録画を始める</button>
      <p class="iv-note" style="margin-top:8px;text-align:center"><?php echo (int) zaito_video_think_seconds(); ?>秒たつと自動で録画が始まります</p>
    </div>
    <div id="qRec" hidden><button type="button" class="iv-btn" id="stopRec">答え終わった</button></div>
    <div id="qReview" hidden>
      <div class="iv-row"><button type="button" class="iv-btn sub" id="retake">撮り直す</button><button type="button" class="iv-btn" id="nextQ">次へ</button></div>
      <p class="iv-note" id="retakeNote" style="margin-top:8px;text-align:center"></p>
    </div>
    <div id="qSend" hidden>
      <p class="iv-note" id="sendText">回答を送っています…</p>
      <div class="iv-bar"><i id="sendBar"></i></div>
    </div>
    <p class="iv-err" id="qErr" role="alert"></p>
    <button type="button" class="iv-btn sub" id="resend" hidden>もう一度送る</button>
  </div>

  <!-- 4. 完了 -->
  <div id="s-done" hidden>
    <div class="iv-card">
      <div class="iv-done" aria-hidden="true">✓</div>
      <h1>回答を送りました</h1>
      <p>応募が<?php echo esc_html( $zaito_company ); ?>に届きました。企業から返信があると、メールでお知らせします。</p>
      <a class="iv-btn" href="<?php echo esc_url( zaito_direct_chat_url( $zaito_app ) ); ?>">メッセージの画面を開く</a>
    </div>
  </div>

  <div class="iv-card" id="s-nocam" hidden>
    <h2>カメラを使えませんでした</h2>
    <p id="nocamWhy"></p>
    <p class="iv-note">iPhoneはSafari、AndroidはChromeで開き、カメラとマイクを「許可」にしてください。LINEやInstagramの中で開いたページでは使えないことがあります。</p>
    <button type="button" class="iv-btn sub" id="retryCam">もう一度試す</button>
  </div>
</main>
<?php zaito_remote_footer(); ?>
<script>
(function () {
  'use strict';
  var CFG = <?php echo wp_json_encode( $zaito_cfg ); ?>;
  var PRACTICE = '練習：好きな食べ物と、その理由を教えてください（この回答は記録されません）。';
  function $(id) { return document.getElementById(id); }
  function show(id) { ['s-intro', 's-check', 's-q', 's-done', 's-nocam'].forEach(function (x) { $(x).hidden = x !== id; }); window.scrollTo(0, 0); }
  function post(action, extra, onProgress) {
    return new Promise(function (res, rej) {
      var fd = new FormData();
      fd.append('action', action); fd.append('c', CFG.c); fd.append('nonce', CFG.nonce);
      Object.keys(extra || {}).forEach(function (k) { if (extra[k] instanceof Blob) fd.append(k, extra[k], 'answer'); else fd.append(k, extra[k]); });
      var x = new XMLHttpRequest();
      x.open('POST', CFG.ajax); x.withCredentials = true;
      if (onProgress && x.upload) x.upload.onprogress = function (e) { if (e.lengthComputable) onProgress(e.loaded / e.total); };
      x.onload = function () { var r = null; try { r = JSON.parse(x.responseText); } catch (e) {} if (r && r.success) res(r.data); else rej((r && r.data && r.data.message) || '送れませんでした。通信を確認して、もう一度お試しください'); };
      x.onerror = function () { rej('送れませんでした。通信を確認して、もう一度お試しください'); };
      x.send(fd);
    });
  }
  // 録画中・送信中にページを閉じようとしたら確認する。
  var busy = false;
  window.addEventListener('beforeunload', function (e) { if (busy) { e.preventDefault(); e.returnValue = ''; } });

  /* ---- カメラ ---- */
  var stream = null, raf = 0;
  $('agree').onchange = function () { $('startBtn').disabled = !this.checked; };
  function startCam() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || typeof MediaRecorder === 'undefined') {
      $('nocamWhy').textContent = 'このブラウザは、ページの中での録画に対応していません。';
      show('s-nocam'); return;
    }
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }, audio: true })
      .then(function (s) { stream = s; $('cam').srcObject = s; $('qCam').srcObject = s; show('s-check'); meter(); })
      .catch(function (e) {
        $('nocamWhy').textContent = e && e.name === 'NotAllowedError' ? 'カメラとマイクの使用が許可されていません。' : 'カメラかマイクが見つからないか、ほかのアプリが使っています。';
        show('s-nocam');
      });
  }
  function meter() {
    try {
      var ac = new (window.AudioContext || window.webkitAudioContext)();
      var an = ac.createAnalyser(); an.fftSize = 256; ac.createMediaStreamSource(stream).connect(an);
      var buf = new Uint8Array(an.frequencyBinCount);
      (function loop() { an.getByteTimeDomainData(buf); var m = 0; for (var i = 0; i < buf.length; i++) m = Math.max(m, Math.abs(buf[i] - 128)); $('lvl').style.width = Math.min(100, m * 1.6) + '%'; raf = requestAnimationFrame(loop); })();
    } catch (e) {}
  }
  function stopCam() { cancelAnimationFrame(raf); if (stream) stream.getTracks().forEach(function (t) { t.stop(); }); }
  $('startBtn').onclick = startCam;
  $('retryCam').onclick = startCam;

  /* ---- 質問の流れ ---- */
  var idx = CFG.answered;   // いま答えている本番の質問（0から）。練習中は practice = true
  var practice = CFG.answered === 0;
  var question = '', retakes = 0, recorder = null, chunks = [], tick = 0, left = 0, lastBlob = null;
  if (!practice) $('toFirst').textContent = '質問' + (idx + 1) + 'へ進む';
  var mime = (function () {
    var c = ['video/mp4;codecs=avc1', 'video/mp4', 'video/webm;codecs=vp8,opus', 'video/webm'];
    for (var i = 0; i < c.length; i++) { try { if (MediaRecorder.isTypeSupported(c[i])) return c[i]; } catch (e) {} }
    return '';
  })();
  function stepsBar() {
    var h = '';
    for (var i = 0; i < CFG.total; i++) h += '<i class="' + (!practice && i <= idx ? 'on' : '') + '"></i>';
    $('steps').innerHTML = h; $('steps').hidden = practice;
  }
  function speak(t) { try { speechSynthesis.cancel(); var u = new SpeechSynthesisUtterance(t); u.lang = 'ja-JP'; u.rate = 1.05; speechSynthesis.speak(u); } catch (e) {} }
  function setTimer(mode, sec) {
    $('timer').hidden = false; $('timer').className = 'iv-timer' + (mode === 'think' ? ' think' : '');
    $('tText').textContent = (mode === 'think' ? '考える時間 ' : '録画中 ') + sec + '秒';
  }
  function err(t) { $('qErr').textContent = t || ''; }
  function openQ() {
    clearInterval(tick); retakes = 0; lastBlob = null; err(''); $('resend').hidden = true; $('qSend').hidden = true;
    $('qReview').hidden = true; $('qThink').hidden = true; $('qRec').hidden = true; $('timer').hidden = true;
    stepsBar(); show('s-q');
    if (practice) { question = PRACTICE; begin(); return; }
    $('qNum').textContent = '質問 ' + (idx + 1) + ' / ' + CFG.total; $('qText').textContent = '質問を読み込んでいます…';
    post('zaito_video_question', { i: idx }).then(function (d) { question = d.question; begin(); })
      .catch(function (m) { $('qText').textContent = ''; err(m); $('resend').hidden = false; $('resend').onclick = openQ; });
  }
  function begin() {
    $('qNum').textContent = practice ? '練習の質問' : '質問 ' + (idx + 1) + ' / ' + CFG.total;
    $('qText').textContent = question;
    speak(practice ? PRACTICE.replace(/（.*?）/, '') : question);
    think();
  }
  function think() {
    err(''); busy = !practice;
    $('qPlay').hidden = true; $('qPlay').removeAttribute('src'); $('qCam').hidden = false;
    $('qThink').hidden = false; $('qRec').hidden = true; $('qReview').hidden = true;
    left = CFG.think; setTimer('think', left);
    clearInterval(tick);
    tick = setInterval(function () { left--; setTimer('think', left); if (left <= 0) startRec(); }, 1000);
  }
  function startRec() {
    clearInterval(tick); try { speechSynthesis.cancel(); } catch (e) {}
    chunks = [];
    try { recorder = new MediaRecorder(stream, mime ? { mimeType: mime, videoBitsPerSecond: 800000 } : undefined); }
    catch (e) { err('この端末では録画を始められませんでした。'); return; }
    recorder.ondataavailable = function (e) { if (e.data && e.data.size) chunks.push(e.data); };
    recorder.onstop = review;
    recorder.start(1000);
    $('qThink').hidden = true; $('qRec').hidden = false;
    left = CFG.answer; setTimer('rec', left);
    tick = setInterval(function () { left--; setTimer('rec', left); if (left <= 0) stopRec(); }, 1000);
  }
  function stopRec() { clearInterval(tick); if (recorder && recorder.state !== 'inactive') recorder.stop(); }
  function review() {
    $('timer').hidden = true; $('qRec').hidden = true;
    lastBlob = new Blob(chunks, { type: (recorder && recorder.mimeType) || mime || 'video/webm' });
    var v = $('qPlay'); v.src = URL.createObjectURL(lastBlob); v.hidden = false; $('qCam').hidden = true;
    $('qReview').hidden = false;
    var can = retakes < 1;
    $('retake').disabled = !can;
    $('retakeNote').textContent = can ? '撮り直しはあと1回できます（' + (CFG.answer - left) + '秒の回答）' : '撮り直しは使いました';
    $('nextQ').textContent = practice ? '本番の質問へ進む' : (idx === CFG.total - 1 ? 'この回答で送る' : 'この回答で次へ');
  }
  $('recNow').onclick = startRec;
  $('stopRec').onclick = stopRec;
  $('retake').onclick = function () { retakes++; think(); };
  $('toFirst').onclick = openQ;
  function send() {
    if (lastBlob.size > CFG.maxBytes) { err('動画が大きすぎます。撮り直してください。'); return; }
    err(''); $('resend').hidden = true; $('qReview').hidden = true; $('qSend').hidden = false; $('sendBar').style.width = '0';
    $('sendText').textContent = '回答を送っています…（通信が切れても、この質問だけ送り直せます）';
    post('zaito_video_upload', { i: idx, video: lastBlob }, function (p) { $('sendBar').style.width = Math.round(p * 100) + '%'; })
      .then(function () {
        $('qSend').hidden = true;
        if (idx === CFG.total - 1) { finish(); return; }
        idx++; openQ();
      })
      .catch(function (m) { $('qSend').hidden = true; err(m); $('resend').hidden = false; $('resend').onclick = send; $('qReview').hidden = false; });
  }
  function finish() {
    $('qSend').hidden = false; $('sendText').textContent = '応募を企業に届けています…'; $('sendBar').style.width = '100%';
    post('zaito_video_finish', {}).then(function () { busy = false; stopCam(); show('s-done'); })
      .catch(function (m) { $('qSend').hidden = true; err(m); $('resend').hidden = false; $('resend').onclick = finish; });
  }
  $('nextQ').onclick = function () {
    if (practice) { practice = false; openQ(); return; }
    send();
  };
  // すべて送ってあるのに「回答を送る」の前で止まっていたら、そこから。
  if (CFG.answered >= CFG.total) { show('s-q'); $('qNum').textContent = ''; $('qText').textContent = ''; finish(); }
})();
</script>
</body>
</html>
