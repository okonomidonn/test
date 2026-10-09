// リール広告用の縦長動画（1080×1920・8秒・30fps・音なし）を A/B/C の3本作る。
// 1) ローカルのサイト（127.0.0.1:8767）から求人一覧の縦に長い画面を撮る
// 2) 1コマずつHTMLを描いてスクリーンショットし、ffmpegでmp4にする
// 実行: NODE_PATH=/opt/node22/lib/node_modules FFMPEG=/path/to/ffmpeg node make_reels.js
const { chromium } = require('playwright'); const cp = require('child_process'); const fs = require('fs'); const os = require('os'); const path = require('path');
const OUT = path.resolve(__dirname, '..');
const FFMPEG = process.env.FFMPEG || 'ffmpeg';
const W = 1080, H = 1920, FPS = 30, SEC = 8;
const ads = [
  { id:'A', page:'/zaitaku/mikeiken/', tag:'子育て中・ブランクOK', lines:['[出社なし]の','在宅ワーク、','探してみませんか？'], badges:['未経験・ブランクOK','時給1,300円〜'] },
  { id:'B', page:'/zaitaku/jimu/', tag:'事務経験のある方へ', lines:['[事務経験]を','活かして','[在宅]で働く。'], badges:['経理・秘書・営業事務','時給1,300〜3,000円'] },
  { id:'C', page:'/zaitaku/gakusei/', tag:'大学生・院生へ', lines:['[在宅]でできる','インターン、','探してみませんか？'], badges:['学生OK','時給1,300円〜'] },
];
const esc = s => s.replace(/&/g,'&amp;').replace(/</g,'&lt;');
const fontRoute = async r => { const u=r.request().url(); const body=cp.execFileSync('curl',['-sS','-A',r.request().headers()['user-agent'],u]); await r.fulfill({status:200,body,headers:{'content-type':u.includes('googleapis')?'text/css':'font/woff2','access-control-allow-origin':'*'}}); };

// 求人一覧を縦に長く撮る。広告と同じく、時給1,300円未満・報酬なし・注意書きつきの求人と、会社名・サービス名は見せない。
async function captureList(b, page) {
  const ctx = await b.newContext({ viewport: { width: 390, height: 780 }, isMobile: true, hasTouch: true, deviceScaleFactor: 2 });
  const p = await ctx.newPage();
  await p.route(/fonts\.(googleapis|gstatic)\.com/, fontRoute);
  await p.route(/googletagmanager/, r => r.abort());
  await p.goto('http://127.0.0.1:8767' + page, { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
  await p.addStyleTag({ content: '.hd,.spbar{display:none!important} .card .co{filter:blur(6px)}' });
  const clip = await p.evaluate(() => {
    document.querySelectorAll('#zcards .card').forEach(card => {
      const b = card.querySelector('.pay b'); const u = card.querySelector('.pay .u');
      const min = b && u && u.textContent.includes('時給') ? parseInt(b.textContent.replace(/,/g, ''), 10) : 0;
      if (!(min >= 1300) || card.textContent.includes('条件あり')) card.remove();
    });
    document.querySelectorAll('#zcards .card h2').forEach(h => {
      h.textContent = h.textContent.replace(/[^「」]*「[^」]*」の?/g, '').replace(/N高グループ\s*/g, 'オンライン高校の').replace(/^\s+/, '');
    });
    const top = document.querySelector('.count').getBoundingClientRect().top + scrollY - 20;
    const cards = [...document.querySelectorAll('#zcards .card')];
    const bottom = cards[cards.length - 1].getBoundingClientRect().bottom + scrollY + 24;
    return { x: 0, y: top, width: 390, height: Math.min(bottom - top, 3200) };
  });
  const buf = await p.screenshot({ clip, fullPage: true });
  await ctx.close();
  return { b64: buf.toString('base64'), ratio: clip.height / clip.width };
}

// リールは上14%・下35%ほどにアカウント名やボタンが重なるので、文字は y=280〜1250 に収める。
function html(ad, shot) {
  const ln = ad.lines.map((l, i) => {
    const m = l.match(/^\[(.+?)\](.*)$/);
    return `<div class="ln" data-i="${i}">` + (m ? `<span class="box">${esc(m[1])}</span><span class="rest">${esc(m[2])}</span>` : `<span class="plain">${esc(l)}</span>`) + '</div>';
  }).join('');
  return `<!doctype html><html><head><meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@700;900&family=Outfit:wght@800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0}
body{width:${W}px;height:${H}px;overflow:hidden;font-family:'Noto Sans JP',sans-serif;font-feature-settings:'palt'}
.wrap{position:relative;width:100%;height:100%;overflow:hidden;background:linear-gradient(160deg,#FF2E93 0%,#D52BD9 38%,#7B3BF5 70%,#3D5AFE 100%)}
.dots{position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,.22) 2px,transparent 2.5px);background-size:44px 44px;mask-image:linear-gradient(180deg,#000,transparent 70%)}
.ring{position:absolute;border-radius:50%;border:3px solid rgba(255,255,255,.25)}
.copy{position:absolute;left:0;right:0;top:250px;display:flex;flex-direction:column;align-items:center;gap:10px}
.tag{font-size:36px;font-weight:900;color:#C21C7A;background:#fff;padding:10px 28px;border-radius:999px;margin-bottom:20px;box-shadow:0 8px 20px -8px rgba(60,0,90,.5)}
.ln{display:flex;align-items:flex-end;justify-content:center}
.box{background:#fff;color:#E0197F;font-size:124px;font-weight:900;line-height:1.08;padding:6px 18px 12px;letter-spacing:-.02em;box-shadow:10px 10px 0 #1A1650}
.rest,.plain{color:#fff;font-size:76px;font-weight:900;line-height:1.25;letter-spacing:-.01em;text-shadow:0 5px 0 #1A1650,0 0 28px rgba(26,22,80,.45);white-space:nowrap;margin-left:8px}
.phone{position:absolute;left:290px;top:900px;width:500px;height:1040px;border-radius:70px;background:#1b1b22;padding:16px;box-shadow:0 40px 80px -20px rgba(30,0,60,.55),inset 0 0 0 3px #3a3a46}
.phone .scr{width:100%;height:100%;border-radius:56px;overflow:hidden;background:#fff;position:relative}
.phone img{width:100%;display:block;will-change:transform}
.bar{position:absolute;left:0;right:0;top:0;height:120px;z-index:1;background:rgba(255,255,255,.96);border-bottom:1px solid #E6E9F2;display:flex;align-items:flex-end;justify-content:space-between;padding:0 26px 18px}
.blogo{font-family:'Outfit';font-weight:800;font-size:40px;letter-spacing:-.05em;color:#0B1530;line-height:1}.blogo span{color:#3D5AFE}
.bbtn{font-size:20px;font-weight:900;color:#fff;background:#3D5AFE;padding:10px 18px;border-radius:12px}
.phone img{margin-top:120px}
.phone .notch{position:absolute;top:14px;left:50%;transform:translateX(-50%);width:130px;height:34px;border-radius:20px;background:#1b1b22;z-index:2}
.badges{display:flex;justify-content:center;gap:22px;margin-top:34px}
.badge{font-size:36px;font-weight:900;color:#1A1650;background:#FFE600;padding:12px 26px;border-radius:14px;border:5px solid #1A1650;box-shadow:6px 6px 0 #1A1650}
.end{position:absolute;left:0;right:0;top:540px;display:flex;flex-direction:column;align-items:center;gap:22px}
.logo{font-family:'Outfit';font-weight:800;font-size:200px;letter-spacing:-.05em;color:#fff;line-height:1;text-shadow:0 8px 0 #1A1650}
.logo span{color:#FFE600}
.lead{font-size:64px;font-weight:900;color:#fff;text-shadow:0 4px 0 #1A1650;text-align:center;line-height:1.45}
.free{margin-top:26px;font-size:44px;font-weight:900;color:#3D5AFE;background:#fff;box-shadow:6px 6px 0 #1A1650;padding:16px 44px;border-radius:999px}
.note{margin-top:36px;font-size:26px;font-weight:700;color:rgba(255,255,255,.85)}
</style></head><body><div class="wrap"><div class="dots"></div>
<div class="ring" style="width:620px;height:620px;right:-200px;top:-200px"></div><div class="ring" style="width:360px;height:360px;left:-140px;top:900px"></div>
<div class="copy"><div class="tag">${esc(ad.tag)}</div>${ln}<div class="badges">${ad.badges.map(b=>`<span class="badge">${esc(b)}</span>`).join('')}</div></div>
<div class="phone"><div class="notch"></div><div class="scr"><div class="bar"><span class="blogo">za<span>i</span>to</span><span class="bbtn">無料で登録</span></div><img src="data:image/png;base64,${shot.b64}"></div></div>
<div class="end"><div class="lead">完全在宅の仕事なら、</div><div class="logo">za<span>i</span>to</div><div class="free">登録無料</div><div class="note">画面は掲載中の求人の一例（2026年10月）</div></div>
</div>
<script>
const clamp = (v) => Math.max(0, Math.min(1, v));
const out = (v) => 1 - Math.pow(1 - clamp(v), 3);
const back = (v) => { v = clamp(v); const c = 1.7; return 1 + (c + 1) * Math.pow(v - 1, 3) + c * Math.pow(v - 1, 2); };
const scrH = 1080 - 32 - 120, imgH = 488 * ${shot.ratio};
const scroll = Math.max(0, imgH - scrH);
// スマホは見出しとバッジのすぐ下に置く（見出しの行数で高さが変わるため）
const cp = document.querySelector('.copy'); document.querySelector('.phone').style.top = (cp.offsetTop + cp.offsetHeight + 50) + 'px';
window.frame = (t) => {
  const q = (s) => document.querySelector(s);
  // 0〜1.6秒：見出しが順に出る
  const tg = back((t - 0.1) / 0.4); q('.tag').style.cssText = 'opacity:' + clamp((t - 0.1) / 0.2) + ';transform:scale(' + (0.6 + 0.4 * tg) + ')';
  document.querySelectorAll('.ln').forEach((e, i) => { const k = (t - 0.35 - i * 0.28) / 0.45; e.style.cssText = 'opacity:' + clamp(k * 2) + ';transform:translateY(' + (60 * (1 - out(k))) + 'px) scale(' + (0.85 + 0.15 * back(k)) + ')'; });
  // 0.9秒〜：スマホが下から出て、1.8〜6.2秒で求人をスクロール
  const pk = out((t - 0.9) / 0.7);
  const gone = out((t - 6.0) / 0.6);
  q('.phone').style.transform = 'translateY(' + (900 * (1 - pk) + 1100 * gone) + 'px) rotate(-4deg)';
  const sk = clamp((t - 1.8) / 4.4); const se = sk < 0.5 ? 2 * sk * sk : 1 - Math.pow(-2 * sk + 2, 2) / 2;
  q('.phone img').style.transform = 'translateY(' + (-scroll * se) + 'px)';
  // 2.8秒〜：バッジ
  document.querySelectorAll('.badge').forEach((e, i) => { const k = (t - 2.8 - i * 0.35) / 0.4; e.style.cssText = 'opacity:' + clamp(k * 3) + ';transform:scale(' + (0.4 + 0.6 * back(k)) + ') rotate(' + (i % 2 ? 2 : -2) + 'deg)'; });
  // 6.0秒〜：見出しとスマホが下がり、ロゴが出る
  // 6.3秒〜：ロゴと「登録無料」
  const ek = (t - 6.3) / 0.6; const end = q('.end');
  end.style.cssText = 'opacity:' + clamp(ek * 1.5) + ';transform:translateY(' + (60 * (1 - out(ek))) + 'px) scale(' + (0.92 + 0.08 * out(ek)) + ')';
  const fade = clamp((t - 6.0) / 0.4); q('.copy').style.opacity = 1 - fade; q('.copy').style.transform = 'translateY(' + (-40 * fade) + 'px)';
};
</script></body></html>`;
}

(async () => {
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome' });
  const only = process.argv[2];
  for (const ad of ads) {
    if (only && only !== ad.id) continue;
    const shot = await captureList(b, ad.page);
    const p = await (await b.newContext({ viewport: { width: W, height: H } })).newPage();
    await p.route(/fonts\.(googleapis|gstatic)\.com/, fontRoute);
    await p.setContent(html(ad, shot), { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
    const dir = fs.mkdtempSync(path.join(os.tmpdir(), 'reel' + ad.id));
    const total = FPS * SEC;
    for (let f = 0; f < total; f++) {
      await p.evaluate((t) => window.frame(t), f / FPS);
      await p.screenshot({ path: `${dir}/f${String(f).padStart(4, '0')}.jpg`, type: 'jpeg', quality: 92 });
    }
    // 確認用に、要所のコマを書き出す
    if (process.env.PREVIEW) for (const t of [4, 6.4, 7.5]) { await p.evaluate((t) => window.frame(t), t); await p.screenshot({ path: `${process.env.PREVIEW}/${ad.id}_${t}.png` }); }
    const mp4 = `${OUT}/zaito_reel_${ad.id}.mp4`;
    cp.execFileSync(FFMPEG, ['-y', '-loglevel', 'error', '-framerate', String(FPS), '-i', `${dir}/f%04d.jpg`, '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-crf', '20', '-preset', 'slow', '-movflags', '+faststart', mp4]);
    fs.rmSync(dir, { recursive: true, force: true });
    console.log('wrote', mp4);
    await p.context().close();
  }
  await b.close();
})();
