const { chromium } = require('playwright'); const cp = require('child_process'); const fs = require('fs');
const OUT = '/home/user/test/marketing/ads';
const ads = [
  { id:'A1', tag:'主婦・子育て中の方へ', h:'「在宅OK」なのに\n出社あり、は\nもうやめよう。', sub:'出社なしの求人だけを集めました。\n子育て中・ブランクOKの求人も。',
    jobs:[['カスタマーサポート','時給','1,300','円','電話なし・経験不問'],['Webマーケの補助','','','','子育て中・ブランク歓迎'],['経理サポート','時給','1,300〜1,500','円','週3〜4日・扶養内OK']] },
  { id:'A2', tag:'主婦・子育て中の方へ', h:'週3日・1日数時間から。\n出社なしの在宅求人', sub:'事務、サポート、SNS運用など。\n出社がないことを運営が確認済み。',
    jobs:[['オンライン秘書','時給','1,500','円','平日週3日・1日2〜3時間'],['経理サポート','時給','1,300〜1,500','円','週3〜4日・扶養内OK'],['カスタマーサポート','時給','1,300','円','電話なし・経験不問']] },
  { id:'B1', tag:'事務経験のある方へ', h:'事務の経験を、\n出社なしで。', sub:'経理、営業事務、オンライン秘書。\n「出社なし」を確認した求人だけ。',
    jobs:[['オンライン秘書','時給','1,500','円','秘書・事務の経験を活かす'],['経理サポート','時給','1,300〜1,500','円','経理経験3年以上'],['生産者サポート（EC）','時給','1,400〜1,600','円','経験不問']] },
  { id:'B2', tag:'通勤に疲れた方へ', h:'通勤ゼロで働く。\n在宅の事務求人', sub:'「リモート可」ではなく「出社なし」。\n応募は各社の募集ページから。',
    jobs:[['リーガルアシスタント','時給','3,000','円程度','不動産契約書の経験'],['カスタマーサポート','時給','1,300','円','電話なし・経験不問'],['生産者サポート（EC）','時給','1,400〜1,600','円','経験不問']] },
  { id:'C1', tag:'大学生・院生へ', h:'地方にいても、\n東京の企業で\n在宅インターン。', sub:'学生OKで、出社なしの求人を\n運営が1件ずつ確認して掲載。',
    jobs:[['高校生のオンライン学習サポート','時給','1,300','円〜','週2日〜'],['AIエンジニア','時給','1,500〜2,500','円','週12時間〜'],['AI・広告運用のマーケ','','','','週5時間〜']] },
  { id:'C2', tag:'大学生・院生へ', h:'通学時間を、\nバイト時間に。', sub:'SNS運用、マーケ、編集、エンジニア。\n自宅から働ける学生向けの求人。',
    jobs:[['学習アプリのマーケ','','','','1・2年生歓迎'],['高校生のオンライン学習サポート','時給','1,300','円〜','週2日〜'],['AIエンジニア','時給','1,500〜2,500','円','週12時間〜']] },
];
const esc = s => s.replace(/&/g,'&amp;').replace(/</g,'&lt;');
function html(ad, w, h) {
  const tall = h > w;
  const cards = ad.jobs.map(j => `<div class="card"><div class="ct">${esc(j[0])}</div><div class="cp">${j[2] ? `<span class="u">${esc(j[1])}</span><b class="${j[2].length>6?'l':''}">${esc(j[2])}</b><span class="u">${esc(j[3])}</span>` : `<span class="nn">報酬は募集ページで確認</span>`}</div><div class="cn"><span class="ok">完全在宅</span>${esc(j[4])}</div></div>`).join('');
  return `<!doctype html><html><head><meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@500;700;900&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0}
body{width:${w}px;height:${h}px;overflow:hidden;font-family:'Noto Sans JP',sans-serif;background:#0B1530;color:#fff;font-feature-settings:'palt'}
.wrap{position:relative;width:100%;height:100%;padding:${tall?'210px 80px 250px':'64px 72px'};display:flex;flex-direction:column}
.bg{position:absolute;inset:0;background:radial-gradient(circle at 85% 10%,rgba(61,90,254,.55),transparent 45%),radial-gradient(circle at 0% 100%,rgba(61,90,254,.35),transparent 50%)}
.top{position:relative;display:flex;justify-content:space-between;align-items:center}
.logo{font-family:'Outfit';font-weight:700;font-size:${tall?60:52}px;letter-spacing:-.045em}
.logo span{color:#7E93FF}
.tag{font-size:${tall?30:26}px;font-weight:700;padding:10px 22px;border-radius:999px;background:rgba(255,255,255,.12);color:#DCE3FF}
h1{position:relative;margin-top:${tall?70:44}px;font-size:${tall?84:78}px;font-weight:900;line-height:1.28;letter-spacing:-.01em;white-space:pre-line}
.sub{position:relative;margin-top:${tall?28:22}px;font-size:${tall?32:30}px;font-weight:500;line-height:1.6;color:#C9D0E4;white-space:pre-line}
.cards{position:relative;margin-top:auto;display:grid;grid-template-columns:${tall?'1fr':'repeat(3,1fr)'};gap:${tall?16:18}px}
.card{background:#fff;color:#0B1530;border-radius:22px;padding:${tall?'22px 30px':'22px 22px'};display:flex;flex-direction:column;gap:${tall?10:8}px}
.ct{font-size:${tall?34:22}px;font-weight:700;line-height:1.35}
.cp{display:flex;align-items:baseline;gap:2px;white-space:nowrap}
.cp b.l{font-size:${tall?52:28}px}
.cp b{font-family:'Outfit';font-size:${tall?52:36}px;line-height:1;margin:0 3px}
.cp .u{font-size:${tall?24:18}px;font-weight:700;color:#4A5573}
.cp .nn{font-size:${tall?24:18}px;color:#6B7590}
.cn{font-size:${tall?24:17}px;color:#3A4563;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.ok{font-size:${tall?22:15}px;font-weight:700;color:#1A7F55;background:#E6F6EE;border-radius:999px;padding:4px 12px}
.cta{position:relative;margin-top:${tall?32:26}px;display:flex;justify-content:space-between;align-items:center}
.btn{font-size:${tall?36:28}px;font-weight:700;background:#3D5AFE;padding:${tall?'22px 40px':'16px 30px'};border-radius:16px}
.url{font-family:'Outfit';font-size:${tall?30:24}px;color:#A9B8FF}
.note{position:absolute;right:${tall?80:72}px;bottom:${tall?200:22}px;font-size:${tall?20:15}px;color:#8C97B5}
</style></head><body><div class="wrap"><div class="bg"></div>
<div class="top"><div class="logo">za<span>i</span>to</div><div class="tag">${esc(ad.tag)}</div></div>
<h1>${esc(ad.h)}</h1><p class="sub">${esc(ad.sub)}</p>
<div class="cards">${cards}</div>
<div class="cta"><div class="btn">出社なしの求人を見る →</div><div class="url">zaito-work.com</div></div>
<div class="note">掲載求人の一例（2026年10月時点）・登録無料</div>
</div></body></html>`;
}
(async () => {
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome' });
  for (const ad of ads) for (const [suf, w, h] of [['square',1080,1080],['story',1080,1920]]) {
    const p = await (await b.newContext({ viewport: { width: w, height: h } })).newPage();
    await p.route(/fonts\.(googleapis|gstatic)\.com/, async r => { const u=r.request().url(); const body=cp.execFileSync('curl',['-sS','-A',r.request().headers()['user-agent'],u]); await r.fulfill({status:200,body,headers:{'content-type':u.includes('googleapis')?'text/css':'font/woff2','access-control-allow-origin':'*'}}); });
    await p.setContent(html(ad, w, h), { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
    const over = await p.evaluate(() => { const c=document.querySelector('.cta').getBoundingClientRect(); const h1=document.querySelector('.sub').getBoundingClientRect(); const cards=document.querySelector('.cards').getBoundingClientRect(); return { ctaBottom: c.bottom, overlap: h1.bottom > cards.top - 10 }; });
    if (over.ctaBottom > h - 40 || over.overlap) console.log('LAYOUT', ad.id, suf, JSON.stringify(over));
    await p.screenshot({ path: `${OUT}/zaito_ad_${ad.id}_${suf}.png` });
  }
  await b.close();
})();
