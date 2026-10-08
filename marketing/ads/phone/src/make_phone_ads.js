const { chromium } = require('playwright'); const cp = require('child_process'); const fs = require('fs');
const OUT = '/home/user/test/marketing/ads/phone';
const SHOTS = __dirname + '/screens';
// 見出しは行ごと。[ ] で囲んだ語は白い箱に入れて特大にする。
const ads = [
  { id:'A', tag:'子育て中・ブランクOK', lines:['[出社なし]の','在宅ワーク、','探してみませんか？'], badges:['未経験・ブランクOK','時給1,300円〜'] },
  { id:'B', tag:'事務経験のある方へ', lines:['[事務経験]を','活かして','[在宅]で働く。'], badges:['経理・秘書・営業事務','時給1,300〜3,000円'] },
  { id:'C', tag:'大学生・院生へ', lines:['[在宅]でできる','インターン、','探してみませんか？'], badges:['学生OK','時給1,300円〜'] },
];
const esc = s => s.replace(/&/g,'&amp;').replace(/</g,'&lt;');
function html(ad, w, h, img) {
  const tall = h > w;
  const ln = ad.lines.map(l => {
    const m = l.match(/^\[(.+?)\](.*)$/);
    return m ? `<div class="ln"><span class="box">${esc(m[1])}</span><span class="rest">${esc(m[2])}</span></div>` : `<div class="ln"><span class="plain">${esc(l)}</span></div>`;
  }).join('');
  return `<!doctype html><html><head><meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@700;900&family=Outfit:wght@800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0}
body{width:${w}px;height:${h}px;overflow:hidden;font-family:'Noto Sans JP',sans-serif;font-feature-settings:'palt'}
.wrap{position:relative;width:100%;height:100%;overflow:hidden;background:linear-gradient(135deg,#FF2E93 0%,#D52BD9 40%,#7B3BF5 72%,#3D5AFE 100%)}
.dots{position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,.22) 2px,transparent 2.5px);background-size:44px 44px;mask-image:linear-gradient(180deg,#000,transparent 80%)}
.ring{position:absolute;border-radius:50%;border:3px solid rgba(255,255,255,.25)}
.wave{position:absolute;left:-10%;right:-10%;bottom:-${tall?120:150}px;height:${tall?330:300}px;background:#fff;border-radius:50% 50% 0 0}
.phone{position:absolute;left:${tall?52:46}px;top:${tall?190:110}px;width:${tall?470:430}px;height:${tall?960:860}px;border-radius:64px;background:#1b1b22;padding:16px;box-shadow:0 40px 80px -20px rgba(30,0,60,.55),inset 0 0 0 3px #3a3a46;transform:rotate(-6deg)}
.phone .scr{width:100%;height:100%;border-radius:50px;overflow:hidden;background:#fff;position:relative}
.phone img{width:100%;display:block}
.phone .notch{position:absolute;top:14px;left:50%;transform:translateX(-50%);width:120px;height:32px;border-radius:20px;background:#1b1b22;z-index:2}
.copy{position:absolute;right:${tall?52:48}px;top:${tall?200:96}px;width:${tall?560:560}px;display:flex;flex-direction:column;align-items:flex-end;gap:${tall?10:6}px}
.tag{font-size:${tall?30:28}px;font-weight:900;color:#C21C7A;background:#fff;padding:8px 22px;border-radius:999px;margin-bottom:${tall?22:16}px;box-shadow:0 8px 20px -8px rgba(60,0,90,.5)}
.ln{display:flex;align-items:flex-end;justify-content:flex-end;flex-wrap:nowrap}
.box{background:#fff;color:#E0197F;font-size:${tall?104:96}px;font-weight:900;line-height:1.08;padding:6px 14px 10px;letter-spacing:-.02em;box-shadow:8px 8px 0 #1A1650}
.rest,.plain{color:#fff;font-size:${tall?62:60}px;font-weight:900;line-height:1.25;letter-spacing:-.01em;text-shadow:0 4px 0 #1A1650,0 0 24px rgba(26,22,80,.45);white-space:nowrap;margin-left:6px}
.badges{margin-top:${tall?28:20}px;display:flex;flex-direction:column;align-items:flex-end;gap:12px}
.badge{font-size:${tall?30:27}px;font-weight:900;color:#1A1650;background:#FFE600;padding:10px 22px;border-radius:12px;border:4px solid #1A1650;box-shadow:5px 5px 0 #1A1650}
.foot{position:absolute;right:${tall?56:52}px;bottom:${tall?60:40}px;display:flex;flex-direction:column;align-items:flex-end;gap:6px}
.logo{font-family:'Outfit';font-weight:800;font-size:${tall?96:84}px;letter-spacing:-.05em;color:#0B1530;line-height:1}
.logo span{color:#3D5AFE}
.lead{font-size:${tall?26:23}px;font-weight:900;color:#0B1530}
.note{position:absolute;left:${tall?56:48}px;bottom:${tall?36:22}px;font-size:${tall?19:16}px;font-weight:700;color:#6B7590}
</style></head><body><div class="wrap"><div class="dots"></div>
<div class="ring" style="width:520px;height:520px;right:-160px;top:-180px"></div><div class="ring" style="width:300px;height:300px;left:-120px;top:${tall?620:520}px"></div>
<div class="wave"></div>
<div class="phone"><div class="notch"></div><div class="scr"><img src="data:image/png;base64,${img}"></div></div>
<div class="copy"><div class="tag">${esc(ad.tag)}</div>${ln}<div class="badges">${ad.badges.map(b=>`<span class="badge">${esc(b)}</span>`).join('')}</div></div>
<div class="foot"><div class="logo">za<span>i</span>to</div><div class="lead">出社なしの仕事だけを集めた求人サイト</div></div>
<div class="note">画面は掲載中の求人の一例（2026年10月）</div>
</div></body></html>`;
}
(async () => {
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome' });
  for (const ad of ads) for (const [suf, w, h] of [['square',1080,1080],['portrait',1080,1350]]) {
    const img = fs.readFileSync(`${SHOTS}/${ad.id}.png`).toString('base64');
    const p = await (await b.newContext({ viewport: { width: w, height: h } })).newPage();
    await p.route(/fonts\.(googleapis|gstatic)\.com/, async r => { const u=r.request().url(); const body=cp.execFileSync('curl',['-sS','-A',r.request().headers()['user-agent'],u]); await r.fulfill({status:200,body,headers:{'content-type':u.includes('googleapis')?'text/css':'font/woff2','access-control-allow-origin':'*'}}); });
    await p.setContent(html(ad, w, h, img), { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
    const bad = await p.evaluate(() => { const c=document.querySelector('.copy').getBoundingClientRect(); const f=document.querySelector('.foot').getBoundingClientRect(); return { copyLeft: c.left, copyBottom: c.bottom, footTop: f.top, wide: [...document.querySelectorAll('.ln')].some(e=>e.getBoundingClientRect().left < document.querySelector('.phone').getBoundingClientRect().right - 40) }; });
    if (bad.copyBottom > bad.footTop - 10 || bad.wide) console.log('LAYOUT', ad.id, suf, JSON.stringify(bad));
    await p.screenshot({ path: `${OUT}/zaito_phone_${ad.id}_${suf}.png` });
  }
  await b.close();
})();
