// PIVOT風のリール（1080×1920・10秒・30fps・音なし）を1本作る。
// 太い文字が斜めの帯で次々に出る、テンポの速い文字だけの動画。zaito のロゴはサイトと同じ色（紺＋青の i、白地）。
// 実行: NODE_PATH=/opt/node22/lib/node_modules FFMPEG=/path/to/ffmpeg node make_reel_pivot.js [プレビュー用の秒...]
// 数字は marketing/students/all_jobs.json（募集中の求人の書き出し）から数える。広告と同じく会社名は出さない。
const { chromium } = require('playwright'); const cp = require('child_process'); const fs = require('fs'); const os = require('os'); const path = require('path');
const OUT = path.resolve(__dirname, '..');
const FFMPEG = process.env.FFMPEG || 'ffmpeg';
const W = 1080, H = 1920, FPS = 30, SEC = 10;
const jobs = JSON.parse(fs.readFileSync(path.resolve(__dirname, '../../../students/all_jobs.json'), 'utf8'));
const payNum = j => { const m = /時給([\d,]+)/.exec(j.pay || ''); return m ? +m[1].replace(/,/g, '') : 0; };
const stat = { student: jobs.filter(j => j.student).length, newbie: jobs.filter(j => j.newbie).length, pay1500: jobs.filter(j => payNum(j) >= 1500).length };
// 見せる求人（時給1,500円以上・注意書きなし）。職種と報酬だけを出す。
const PICK = ['knit-online-secretary', 'zeals-ad-designer', 'iflag-ai-agent', 'startupkenpo-cs'];
const cards = PICK.map(s => jobs.find(j => j.slug === s)).filter(j => j && payNum(j) >= 1500 && !j.caution)
  .map(j => ({ cat: j.category.replace(/（.*?）/g, ''), title: j.title, pay: j.pay.replace(/（.*?）/g, '') }));
const esc = s => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;');
const fontRoute = async r => { const u = r.request().url(); const body = cp.execFileSync('curl', ['-sS', '-A', r.request().headers()['user-agent'], u]); await r.fulfill({ status: 200, body, headers: { 'content-type': u.includes('googleapis') ? 'text/css' : 'font/woff2', 'access-control-allow-origin': '*' } }); };

// リールは上14%・下35%ほどにアカウント名やボタンが重なるので、文字は y=260〜1260 に収める。
const html = `<!doctype html><html><head><meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@700;900&family=Outfit:wght@800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0}
body{width:${W}px;height:${H}px;overflow:hidden;font-family:'Noto Sans JP',sans-serif;font-feature-settings:'palt';color:#fff}
.bg{position:absolute;inset:0;background:linear-gradient(160deg,#FF2E93 0%,#D52BD9 36%,#7B3BF5 68%,#3D5AFE 100%)}
.fx{position:absolute;inset:-200px;opacity:.35;background:
 conic-gradient(from 210deg at 18% 30%,transparent 0 40deg,rgba(255,255,255,.35) 40deg 70deg,transparent 70deg),
 conic-gradient(from 30deg at 80% 70%,transparent 0 50deg,rgba(255,255,255,.25) 50deg 80deg,transparent 80deg)}
.sc{position:absolute;left:0;right:0;top:260px;height:1000px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:22px;opacity:0}
.tag{font-size:40px;font-weight:900;color:#C21C7A;background:#fff;padding:12px 30px;border-radius:12px;transform:skew(-8deg)}
.band{display:inline-block;background:#1A1650;padding:10px 34px 18px;transform:skew(-8deg);box-shadow:12px 12px 0 rgba(0,0,0,.18)}
.band>span{display:inline-block;transform:skew(8deg);font-size:104px;font-weight:900;line-height:1.15;letter-spacing:-.02em;white-space:nowrap}
.band em{font-style:normal;color:#1A1650;background:#FFE600;padding:0 14px;margin:0 4px}
.small{font-size:52px;font-weight:900;text-shadow:0 4px 0 #1A1650}
.ng{display:flex;align-items:center;gap:26px;width:900px;padding:26px 36px;border-radius:24px;background:#fff;color:#1A1650;box-shadow:12px 12px 0 #1A1650}
.ng i{flex:none;width:90px;height:90px;border-radius:50%;background:#E5243B;color:#fff;font-style:normal;font-size:64px;font-weight:900;display:grid;place-items:center}
.ng b{font-size:58px;font-weight:900;white-space:nowrap}
.cards{margin-top:24px;display:grid;grid-template-columns:1fr 1fr;gap:20px;width:940px}
.card{padding:26px 28px;border-radius:24px;background:#fff;color:#0B1530;box-shadow:10px 10px 0 #1A1650}
.card small{display:inline-block;font-size:26px;font-weight:900;color:#3D5AFE;background:#EEF1FF;padding:4px 14px;border-radius:8px}
.card p{margin-top:10px;font-size:32px;font-weight:900;line-height:1.35;height:86px;overflow:hidden}
.card b{display:block;margin-top:10px;font-size:40px;font-weight:900;color:#3D5AFE;white-space:nowrap}
.tiles{display:flex;flex-direction:column;gap:22px;width:900px}
.tile{display:flex;align-items:baseline;justify-content:space-between;padding:26px 40px;border-radius:24px;background:#fff;color:#1A1650;box-shadow:12px 12px 0 #1A1650}
.tile span{font-size:58px;font-weight:900}
.tile b{font-family:'Outfit',sans-serif;font-size:120px;line-height:1;color:#3D5AFE;letter-spacing:-.03em}
.tile b small{font-family:'Noto Sans JP';font-size:44px;margin-left:6px}
.endc{width:900px;padding:70px 60px 64px;border-radius:44px;background:#fff;color:#0B1530;display:flex;flex-direction:column;align-items:center;gap:24px;box-shadow:14px 14px 0 #1A1650}
.endc .lead{font-size:64px;font-weight:900}
.wm{font-family:'Outfit',sans-serif;font-weight:800;font-size:210px;line-height:1;letter-spacing:-.04em;color:#0B1530}.wm span{color:#3D5AFE}
.free{margin-top:10px;font-size:50px;font-weight:900;color:#fff;background:#3D5AFE;padding:18px 56px;border-radius:999px}
.url{font-family:'Outfit',sans-serif;font-size:40px;color:#3D5AFE}
</style></head><body><div class="bg"></div><div class="fx" id="fx"></div>

<div class="sc" id="s1">
  <div class="tag">在宅バイトを探している人へ</div>
  <div class="band b1"><span>「在宅OK」なのに</span></div>
  <div class="band b2"><span><em>研修は出社</em>？</span></div>
</div>
<div class="sc" id="s2">
  <div class="small">よくある「在宅OK」</div>
  <div class="ng"><i>×</i><b>最初の1か月は出社</b></div>
  <div class="ng"><i>×</i><b>月1回の定例会は出社</b></div>
  <div class="ng"><i>×</i><b>お客様先への訪問あり</b></div>
</div>
<div class="sc" id="s3">
  <div class="band b1"><span><em>完全在宅</em>の仕事だけ</span></div>
  <div class="band b2"><span>集めました</span></div>
  <div class="cards">${cards.map(c => `<div class="card"><small>${esc(c.cat)}</small><p>${esc(c.title)}</p><b>${esc(c.pay)}</b></div>`).join('')}</div>
</div>
<div class="sc" id="s4">
  <div class="small">研修も会議も、出社なし</div>
  <div class="tiles">
    <div class="tile"><span>学生OK</span><b>${stat.student}<small>件</small></b></div>
    <div class="tile"><span>未経験OK</span><b>${stat.newbie}<small>件</small></b></div>
    <div class="tile"><span>時給1,500円〜</span><b>${stat.pay1500}<small>件</small></b></div>
  </div>
</div>
<div class="sc" id="s5">
  <div class="endc"><div class="lead">完全在宅の仕事なら、</div><div class="wm">za<span>i</span>to</div><div class="free">登録無料</div><div class="url">zaito-work.com</div></div>
</div>

<script>
const clamp = v => Math.max(0, Math.min(1, v));
const out = v => 1 - Math.pow(1 - clamp(v), 3);
const back = v => { v = clamp(v); const c = 1.9; return 1 + (c + 1) * Math.pow(v - 1, 3) + c * Math.pow(v - 1, 2); };
// 各カットの表示時間（秒）
const CUT = { s1: [0, 2.2], s2: [2.2, 4.3], s3: [4.3, 6.9], s4: [6.9, 8.5], s5: [8.5, 10.5] };
const $ = s => document.querySelector(s), $$ = s => [...document.querySelectorAll(s)];
// 下から勢いよく入って、少し傾いて止まる
const slam = (e, k, rot) => { const sk = e.classList.contains('band') || e.classList.contains('tag') ? ' skewX(-8deg)' : ''; e.style.opacity = clamp(k * 3); e.style.transform = 'translateY(' + (90 * (1 - out(k))) + 'px) scale(' + (0.7 + 0.3 * back(k)) + ') rotate(' + (rot || 0) + 'deg)' + sk; };
window.frame = t => {
  $('#fx').style.transform = 'translateX(' + (-40 * Math.sin(t * 0.6)) + 'px) rotate(' + (t * 1.5) + 'deg)';
  for (const id in CUT) {
    const [a, b] = CUT[id]; const s = $('#' + id);
    const inK = clamp((t - a) / 0.12), outK = clamp((t - (b - 0.15)) / 0.15);
    s.style.opacity = id === 's5' ? inK : inK * (1 - outK);
    s.style.transform = 'translateX(' + (-120 * out(outK)) + 'px)';
  }
  const r = (id, sel, d0, step, rot) => $$('#' + id + ' ' + sel).forEach((e, i) => slam(e, (t - CUT[id][0] - d0 - i * step) / 0.35, typeof rot === 'function' ? rot(i) : rot));
  r('s1', '.tag', 0.05, 0, -2); r('s1', '.band', 0.35, 0.45, 0);
  r('s2', '.small', 0.05, 0, 0); r('s2', '.ng', 0.25, 0.45, i => (i % 2 ? 1.5 : -1.5));
  r('s3', '.band', 0.05, 0.3, 0); r('s3', '.card', 0.75, 0.22, i => (i % 2 ? 1.2 : -1.2));
  r('s4', '.small', 0.05, 0, 0); r('s4', '.tile', 0.25, 0.3, 0);
  r('s5', '.endc', 0.05, 0, 0);
  // 件数は0から数え上げる
  $$('#s4 .tile b').forEach((e, i) => { if (!e.dataset.n) e.dataset.n = e.firstChild.textContent; const k = out((t - CUT.s4[0] - 0.3 - i * 0.3) / 0.6); e.firstChild.textContent = Math.round(+e.dataset.n * k); });
};
</script></body></html>`;

(async () => {
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome' });
  const p = await (await b.newContext({ viewport: { width: W, height: H } })).newPage();
  await p.route(/fonts\.(googleapis|gstatic)\.com/, fontRoute);
  await p.setContent(html, { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
  const previews = process.argv.slice(2).map(Number);
  if (previews.length) {
    for (const t of previews) { await p.evaluate(t => window.frame(t), t); await p.screenshot({ path: `${process.env.PREVIEW || '.'}/pivot_${t}.png` }); }
    await b.close(); return;
  }
  const dir = fs.mkdtempSync(path.join(os.tmpdir(), 'reelpivot'));
  for (let f = 0; f < FPS * SEC; f++) {
    await p.evaluate(t => window.frame(t), f / FPS);
    await p.screenshot({ path: `${dir}/f${String(f).padStart(4, '0')}.jpg`, type: 'jpeg', quality: 92 });
  }
  const mp4 = `${OUT}/zaito_reel_pivot.mp4`;
  cp.execFileSync(FFMPEG, ['-y', '-loglevel', 'error', '-framerate', String(FPS), '-i', `${dir}/f%04d.jpg`, '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-crf', '20', '-preset', 'slow', '-movflags', '+faststart', mp4]);
  fs.rmSync(dir, { recursive: true, force: true });
  console.log('wrote', mp4, stat, cards.length);
  await b.close();
})();
