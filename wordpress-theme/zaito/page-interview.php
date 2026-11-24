<?php
/**
 * 動画で答える応募の録画画面（/interview/?c={応募ID}）。応募した学生だけが開ける。
 * 仕組みは inc/video-interview.php。画面の流れは demo/interview.html（試作）と同じ：
 * 確認 → カメラの確認 → 練習の質問（記録しない）→ 質問ごとに「考える10秒 → 答える60秒 → 見直し（撮り直し1回）→ 送る」→ 完了。
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
	// 練習の質問と、その読み上げの音声（inc/tts.php。作れなければ空で、端末の声で読む）。
	'practice'      => zaito_tts_practice()['text'],
	'practiceSpeak' => zaito_tts_practice()['speak'],
	'practiceAudio' => $zaito_answered ? '' : zaito_tts_url( zaito_tts_practice()['speak'] ),
	// 背景のぼかしで使う、人物を切り抜くモデル（Googleの MediaPipe の selfie_segmenter。lp/ に置いたもの）。
	'segModel'      => get_template_directory_uri() . '/lp/selfie_segmenter.tflite',
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
.iv-say{display:inline-flex;align-items:center;gap:4px;margin-top:8px;padding:4px 10px 4px 8px;border:0;border-radius:999px;background:rgba(255,255,255,.18);color:#fff;font:inherit;font-size:12px;font-weight:700;cursor:pointer}
.iv-say .ms{font-size:16px}
.iv-timer{position:absolute;left:50%;bottom:16px;transform:translateX(-50%);display:flex;align-items:center;gap:8px;padding:8px 14px;border-radius:999px;background:rgba(11,21,48,.72);color:#fff;font-size:15px;font-weight:700;white-space:nowrap}
.iv-timer .dot{width:10px;height:10px;border-radius:999px;background:#E5484D;animation:ivblink 1s infinite}
.iv-timer.think .dot{background:#FFB547;animation:none}
@keyframes ivblink{50%{opacity:.25}}
@media (prefers-reduced-motion:reduce){.iv-timer .dot{animation:none}}
.iv-meter{height:10px;border-radius:999px;background:#E6EAF3;overflow:hidden;margin:6px 0 6px}
.iv-meter i{display:block;height:100%;width:0;background:#1A7F55;transition:width .08s}
.iv-lvl-t{font-size:13px;font-weight:700;color:var(--z-text-3,#6B7590);margin:0 0 14px}
.iv-lvl-t.ok{color:#1A7F55}
/* 質問の画面の右下に出す、マイクの音の大きさ（5本のバー） */
.iv-mic{position:absolute;right:12px;bottom:16px;display:flex;align-items:flex-end;gap:3px;height:36px;padding:6px 8px;border-radius:10px;background:rgba(11,21,48,.72)}
.iv-mic i{width:4px;border-radius:2px;background:rgba(255,255,255,.3)}
.iv-mic i:nth-child(1){height:20%}.iv-mic i:nth-child(2){height:40%}.iv-mic i:nth-child(3){height:60%}.iv-mic i:nth-child(4){height:80%}.iv-mic i:nth-child(5){height:100%}
.iv-mic i.on{background:#3DDC84}
.iv-blur{display:flex;align-items:center;gap:10px;margin:4px 0 6px;font-size:15px;font-weight:700;cursor:pointer}
.iv-blur input{width:20px;height:20px;accent-color:#3D5AFE;flex:none;margin:0}
.iv-raw{position:absolute;width:2px;height:2px;opacity:0;pointer-events:none}
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
      <label class="iv-blur"><input type="checkbox" id="blurBg">背景をぼかす</label>
      <p class="iv-note" id="blurNote" aria-live="polite" style="margin:0 0 12px">部屋を映したくないときに使えます。録画にも、ぼかしたまま残ります。</p>
      <video id="raw" class="iv-raw" playsinline muted autoplay aria-hidden="true"></video>
      <span class="iv-note">マイクの音の大きさ</span>
      <div class="iv-meter"><i id="lvl"></i></div>
      <p class="iv-lvl-t" id="lvlText" aria-live="polite">声を出してみてください</p>
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
      <div class="iv-q"><div class="qn" id="qNum"></div><div class="qt" id="qText"></div><button type="button" class="iv-say" id="say" hidden><span class="ms" aria-hidden="true">volume_up</span>もう一度聞く</button></div>
      <div class="iv-timer" id="timer" hidden><span class="dot"></span><span id="tText"></span></div>
      <div class="iv-mic" id="qMic" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
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
  var PRACTICE = CFG.practice;
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
      .then(function (s) {
        stream = s; preview(); show('s-check'); meter();
        var keep = false; try { keep = localStorage.getItem('zaito_iv_blur') === '1'; } catch (e) {}
        if (keep) { $('blurBg').checked = true; setBlur(true); }
      })
      .catch(function (e) {
        $('nocamWhy').textContent = e && e.name === 'NotAllowedError' ? 'カメラとマイクの使用が許可されていません。' : 'カメラかマイクが見つからないか、ほかのアプリが使っています。';
        show('s-nocam');
      });
  }
  // マイクの音の大きさ。カメラの確認の画面のバーと、質問の画面の右下の5本のバーの両方に出す。
  var level = 0, heard = false, recPeak = 0;
  function meter() {
    try {
      var ac = new (window.AudioContext || window.webkitAudioContext)();
      if (ac.state === 'suspended' && ac.resume) ac.resume(); // iPhoneでは止まった状態で始まることがある
      var an = ac.createAnalyser(); an.fftSize = 512; ac.createMediaStreamSource(stream).connect(an);
      var buf = new Uint8Array(an.fftSize);
      var bars = [].slice.call(document.querySelectorAll('#qMic i'));
      (function loop() {
        an.getByteTimeDomainData(buf);
        var sum = 0; for (var i = 0; i < buf.length; i++) { var d = (buf[i] - 128) / 128; sum += d * d; }
        var now = Math.min(1, Math.sqrt(sum / buf.length) * 6);   // ふつうの話し声で半分から上くらい
        level = Math.max(now, level * 0.9);                        // 下がるときはゆっくり
        $('lvl').style.width = Math.round(level * 100) + '%';
        bars.forEach(function (b, k) { b.className = level > (k + 0.5) / 6 ? 'on' : ''; });
        if (level > 0.15 && !heard) { heard = true; $('lvlText').textContent = '声が届いています'; $('lvlText').className = 'iv-lvl-t ok'; }
        if (recorder && recorder.state === 'recording') recPeak = Math.max(recPeak, now);
        raf = requestAnimationFrame(loop);
      })();
    } catch (e) {}
  }
  function stopCam() { cancelAnimationFrame(raf); cancelAnimationFrame(blur.raf); if (stream) stream.getTracks().forEach(function (t) { t.stop(); }); }

  /* ---- 背景のぼかし ----
   * 学生の端末の中だけで、人と背景を分けて（MediaPipe の人物の切り抜き）、背景だけをぼかした映像を作る。映像は外に送らない。
   * ぼかした映像（canvas）とマイクの音を合わせたものを、画面に出して録画する。
   * 重い端末では、自動でぼかしを切る。 */
  var MP = 'https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14';
  var blur = { on: false, seg: null, stream: null, raf: 0, last: -1, frames: 0, slow: 0 };
  function recStream() { return blur.on && blur.stream ? blur.stream : stream; }
  function preview() { var s = recStream(); $('cam').srcObject = s; $('qCam').srcObject = s; }
  function blurNote(t) { $('blurNote').textContent = t; }
  function loadSeg() {
    if (blur.seg) return Promise.resolve(blur.seg);
    return import(MP + '/vision_bundle.mjs').then(function (v) {
      return v.FilesetResolver.forVisionTasks(MP + '/wasm').then(function (fs) {
        function opt(d) { return { baseOptions: { modelAssetPath: CFG.segModel, delegate: d }, runningMode: 'VIDEO', outputCategoryMask: false, outputConfidenceMasks: true }; }
        // 切り抜きの結果を毎回読み出すので、GPUよりCPUのほうが速い（試したところ1コマ70ms→17ms）。CPUで動かなければGPU。
        return v.ImageSegmenter.createFromOptions(fs, opt('CPU')).catch(function () { return v.ImageSegmenter.createFromOptions(fs, opt('GPU')); });
      });
    }).then(function (s) { blur.seg = s; return s; });
  }
  function blurSetup() {
    if (blur.canvas) return;
    blur.canvas = document.createElement('canvas'); blur.ctx = blur.canvas.getContext('2d');
    blur.mask = document.createElement('canvas'); blur.mctx = blur.mask.getContext('2d');
    blur.small = document.createElement('canvas'); blur.sctx = blur.small.getContext('2d');
  }
  function blurLoop() {
    var raw = $('raw'), w = raw.videoWidth, h = raw.videoHeight;
    if (w && h && raw.readyState >= 2) {
      if (blur.canvas.width !== w || blur.canvas.height !== h) {
        blur.canvas.width = w; blur.canvas.height = h;
        blur.small.width = Math.max(16, Math.round(w / 14)); blur.small.height = Math.max(12, Math.round(h / 14));
      }
      var t0 = performance.now(), ts = Math.max(t0, blur.last + 1); blur.last = ts;
      try {
        blur.seg.segmentForVideo(raw, ts, function (r) {
          var m = r.confidenceMasks && r.confidenceMasks[0]; if (!m) return;
          var f = m.getAsFloat32Array();
          if (!blur.img || blur.img.width !== m.width || blur.img.height !== m.height) { blur.mask.width = m.width; blur.mask.height = m.height; blur.img = blur.mctx.createImageData(m.width, m.height); }
          var d = blur.img.data; for (var i = 0, j = 3; i < f.length; i++, j += 4) d[j] = f[i] * 255;
          blur.mctx.putImageData(blur.img, 0, 0);
        });
      } catch (e) { setBlur(false, 'この端末では背景をぼかせませんでした。'); return; }
      var c = blur.ctx;
      c.imageSmoothingEnabled = true;
      c.globalCompositeOperation = 'copy'; c.drawImage(blur.mask, 0, 0, w, h);          // 人のところだけ残す型
      c.globalCompositeOperation = 'source-in'; c.drawImage(raw, 0, 0, w, h);           // 人
      blur.sctx.drawImage(raw, 0, 0, blur.small.width, blur.small.height);              // 小さくして広げると、ぼける
      c.globalCompositeOperation = 'destination-over'; c.drawImage(blur.small, 0, 0, w, h); // 人の後ろに、ぼかした背景
      c.globalCompositeOperation = 'source-over';
      blur.frames++; if (performance.now() - t0 > 60) blur.slow++;
      if (blur.frames === 90 && blur.slow > 45) { $('blurBg').checked = false; setBlur(false, 'この端末では背景のぼかしが重いため、切りました。'); return; }
    }
    blur.raf = requestAnimationFrame(blurLoop);
  }
  function setBlur(on, why) {
    try { localStorage.setItem('zaito_iv_blur', on ? '1' : '0'); } catch (e) {}
    if (!on) {
      cancelAnimationFrame(blur.raf); blur.on = false; preview();
      blurNote(why || '部屋を映したくないときに使えます。録画にも、ぼかしたまま残ります。');
      return;
    }
    if (!stream) return;
    $('blurBg').disabled = true; blurNote('ぼかしの準備をしています…（初回は数秒かかります）');
    blurSetup();
    loadSeg().then(function () {
      var raw = $('raw'); raw.srcObject = stream; var p = raw.play(); if (p && p.catch) p.catch(function () {});
      if (!blur.stream) blur.stream = new MediaStream(blur.canvas.captureStream(30).getVideoTracks().concat(stream.getAudioTracks()));
      blur.frames = 0; blur.slow = 0; blur.on = true; cancelAnimationFrame(blur.raf); blurLoop();
      setTimeout(preview, 300); // 最初の絵ができてから切り替える
      blurNote('背景をぼかしています。録画にも、ぼかしたまま残ります。');
    }).catch(function () {
      $('blurBg').checked = false; blur.on = false; preview();
      blurNote('この端末では背景をぼかせませんでした。そのまま録画できます。');
    }).then(function () { $('blurBg').disabled = false; });
  }
  $('blurBg').onchange = function () { setBlur(this.checked); };
  $('startBtn').onclick = function () { unlockAudio(); startCam(); };
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
  // 質問の読み上げ。質問が出たときに自動で読み、ボタンでもう一度聞ける。
  // 声は、端末にある日本語の声のうち、自然に聞こえるものから選ぶ（Edgeの「Natural」、Chromeの「Google」、iPhoneの拡張・高音質の声など）。
  var canSpeak = 'speechSynthesis' in window && typeof SpeechSynthesisUtterance !== 'undefined';
  function pickVoice() {
    var vs = speechSynthesis.getVoices().filter(function (v) { return /^ja/i.test(v.lang); });
    var pref = [/Natural|Online/i, /Google/i, /Premium|Enhanced|Siri|高音質|拡張/i, /Kyoko|O-ren|Otoya|Hattori/i];
    for (var i = 0; i < pref.length; i++) { for (var k = 0; k < vs.length; k++) { if (pref[i].test(vs[k].name)) return vs[k]; } }
    return vs[0] || null;
  }
  function speak(t) {
    if (!canSpeak) return;
    try { speechSynthesis.cancel(); var u = new SpeechSynthesisUtterance(t); u.lang = 'ja-JP'; u.rate = 1; var v = pickVoice(); if (v) u.voice = v; speechSynthesis.speak(u); } catch (e) {}
  }
  if (canSpeak) { try { speechSynthesis.getVoices(); } catch (e) {} }
  // 読み上げの音声（Googleの声で作ったMP3）があればそれを流し、なければ端末の声で読む。
  // iPhoneは、ボタンを押したときに一度鳴らした音声の部品でないと、あとから自動で鳴らせないため、最初のボタンで準備しておく。
  var player = new Audio(), audioUrl = '';
  player.preload = 'auto';
  var SILENT = 'data:audio/wav;base64,UklGRsQAAABXQVZFZm10IBAAAAABAAEAQB8AAIA+AAACABAAZGF0YaAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
  function unlockAudio() { try { player.src = SILENT; var p = player.play(); if (p && p.catch) p.catch(function () {}); } catch (e) {} }
  function sayNow() {
    var text = practice ? CFG.practiceSpeak : question;
    if (audioUrl) {
      try { if (canSpeak) speechSynthesis.cancel(); } catch (e) {}
      player.src = audioUrl; player.currentTime = 0;
      var p = player.play();
      if (p && p.catch) p.catch(function () { speak(text); });
      return;
    }
    speak(text);
  }
  function stopSay() { try { player.pause(); } catch (e) {} try { if (canSpeak) speechSynthesis.cancel(); } catch (e) {} }
  $('say').onclick = sayNow;
  function setTimer(mode, sec) {
    $('timer').hidden = false; $('timer').className = 'iv-timer' + (mode === 'think' ? ' think' : '');
    $('tText').textContent = (mode === 'think' ? '考える時間 ' : '録画中 ') + sec + '秒';
  }
  function err(t) { $('qErr').textContent = t || ''; }
  function openQ() {
    clearInterval(tick); retakes = 0; lastBlob = null; err(''); $('resend').hidden = true; $('qSend').hidden = true;
    $('qReview').hidden = true; $('qThink').hidden = true; $('qRec').hidden = true; $('timer').hidden = true;
    stepsBar(); show('s-q');
    if (practice) { question = PRACTICE; audioUrl = CFG.practiceAudio || ''; begin(); return; }
    $('qNum').textContent = '質問 ' + (idx + 1) + ' / ' + CFG.total; $('qText').textContent = '質問を読み込んでいます…';
    post('zaito_video_question', { i: idx }).then(function (d) { question = d.question; audioUrl = d.audio || ''; begin(); })
      .catch(function (m) { $('qText').textContent = ''; err(m); $('resend').hidden = false; $('resend').onclick = openQ; });
  }
  function begin() {
    $('qNum').textContent = practice ? '練習の質問' : '質問 ' + (idx + 1) + ' / ' + CFG.total;
    $('qText').textContent = question;
    sayNow();
    think();
  }
  function think() {
    err(''); busy = !practice;
    $('qPlay').hidden = true; $('qPlay').removeAttribute('src'); $('qCam').hidden = false;
    $('qThink').hidden = false; $('qRec').hidden = true; $('qReview').hidden = true;
    $('say').hidden = !(canSpeak || audioUrl);
    left = CFG.think; setTimer('think', left);
    clearInterval(tick);
    tick = setInterval(function () { left--; setTimer('think', left); if (left <= 0) startRec(); }, 1000);
  }
  function startRec() {
    clearInterval(tick); stopSay();
    $('say').hidden = true; // 録画中は読み上げない（声が録画に入るため）
    chunks = [];
    try { recorder = new MediaRecorder(recStream(), mime ? { mimeType: mime, videoBitsPerSecond: 800000 } : undefined); }
    catch (e) { err('この端末では録画を始められませんでした。'); return; }
    recorder.ondataavailable = function (e) { if (e.data && e.data.size) chunks.push(e.data); };
    recorder.onstop = review;
    recorder.start(1000);
    $('qThink').hidden = true; $('qRec').hidden = false;
    left = CFG.answer; setTimer('rec', left); recPeak = 0;
    tick = setInterval(function () {
      left--; setTimer('rec', left);
      // 録画を始めて5秒たっても音がほとんど入っていなければ、知らせる（録画は止めない）。
      if (CFG.answer - left === 5 && recPeak < 0.05) err('マイクの音が入っていないようです。マイクが切れていないか、ほかのアプリが使っていないか確かめてください。');
      if (left <= 0) stopRec();
    }, 1000);
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
  $('toFirst').onclick = function () { unlockAudio(); openQ(); };
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
