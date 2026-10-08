const { chromium } = require('playwright'); const cp = require('child_process'); const fs = require('fs');
const OUT = '/home/user/test/marketing/ads/pivot';
const ads = [
  { id:'A1', tag:'主婦・子育て中の方へ', h:'「在宅OK」なのに\n出社あり、は\n[もうやめよう。]', sub:'出社なしの求人だけを集めました。\n子育て中・ブランクOKの求人も。',
    jobs:[['カスタマーサポート','時給','1,300','円','電話なし・経験不問'],['Webマーケの補助','','','','子育て中・ブランク歓迎'],['経理サポート','時給','1,300〜1,500','円','週3〜4日・扶養内OK']] },
  { id:'A2', tag:'主婦・子育て中の方へ', h:'[週3日]・1日数時間から。\n出社なしの在宅求人', sub:'事務、サポート、SNS運用など。\n出社がないことを運営が確認済み。',
    jobs:[['オンライン秘書','時給','1,500','円','平日週3日・1日2〜3時間'],['経理サポート','時給','1,300〜1,500','円','週3〜4日・扶養内OK'],['AI事務アシスタント','時給','1,300','円〜','週3日・1日4時間〜']] },
  { id:'B1', tag:'事務経験のある方へ', h:'事務の経験を、\n[出社なし]で。', sub:'経理、営業事務、オンライン秘書。\n「出社なし」を確認した求人だけ。',
    jobs:[['オンライン秘書','時給','1,500','円','秘書・事務の経験を活かす'],['経理サポート','時給','1,300〜1,500','円','経理経験3年以上'],['生産者サポート（EC）','時給','1,400〜1,600','円','経験不問']] },
  { id:'B2', tag:'通勤に疲れた方へ', h:'[通勤ゼロ]で働く。\n在宅の事務求人', sub:'「リモート可」ではなく「出社なし」。\n応募は各社の募集ページから。',
    jobs:[['リーガルアシスタント','時給','3,000','円程度','不動産契約書の経験'],['カスタマーサポート','時給','1,300','円','電話なし・経験不問'],['ふるさと納税サイト運営','時給','1,100','円〜','平日9〜16時']] },
  { id:'C1', tag:'大学生・院生へ', h:'地方にいても、\n東京の企業で\n[在宅インターン]。', sub:'学生OKで、出社なしの求人を\n運営が1件ずつ確認して掲載。',
    jobs:[['高校生のオンライン学習サポート','時給','1,300','円〜','週2日〜'],['AIエンジニア','時給','1,500〜2,500','円','週12時間〜'],['AI・広告運用のマーケ','','','','週5時間〜']] },
  { id:'C2', tag:'大学生・院生へ', h:'[通学時間]を、\nバイト時間に。', sub:'SNS運用、マーケ、編集、エンジニア。\n自宅から働ける学生向けの求人。',
    jobs:[['学習アプリのマーケ','','','','1・2年生歓迎'],['高校生のオンライン学習サポート','時給','1,300','円〜','週2日〜'],['AIエンジニア','時給','1,500〜2,500','円','週12時間〜']] },
];
const esc = s => s.replace(/&/g,'&amp;').replace(/</g,'&lt;');
// PIVOT風：マゼンタ〜紫〜青のグラデーション、斜めの帯に白い極太文字、強調は黄色。
function line(t) {
  return esc(t).replace(/\[(.+?)\]/g, '<em>$1</em>');
}
function html(ad, w, h) {
  const tall = h > w;
  const lines = ad.h.split('\n').map(l => `<span class="ln"><span>${line(l)}</span></span>`).join('');
  const cards = ad.jobs.map(j => `<div class="card"><div class="ct">${esc(j[0])}</div><div class="cp">${j[2] ? `<span class="u">${esc(j[1])}</span><b class="${j[2].length>6?'l':''}">${esc(j[2])}</b><span class="u">${esc(j[3])}</span>` : `<span class="nn">報酬は募集ページで確認</span>`}</div><div class="cn"><span class="ok">完全在宅</span>${esc(j[4])}</div></div>`).join('');
  return `<!doctype html><html><head><meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@500;700;900&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0}
body{width:${w}px;height:${h}px;overflow:hidden;font-family:'Noto Sans JP',sans-serif;color:#fff;font-feature-settings:'palt'}
.wrap{position:relative;width:100%;height:100%;padding:${tall?'210px 70px 250px':'56px 64px'};display:flex;flex-direction:column;
 background:linear-gradient(125deg,#FF2E93 0%,#D52BD9 38%,#7B3BF5 70%,#3D5AFE 100%)}
.fx{position:absolute;inset:0;opacity:.35;background:
 conic-gradient(from 210deg at 18% 30%,transparent 0 40deg,rgba(255,255,255,.35) 40deg 70deg,transparent 70deg),
 conic-gradient(from 30deg at 80% 70%,transparent 0 50deg,rgba(255,255,255,.25) 50deg 80deg,transparent 80deg),
 conic-gradient(from 120deg at 60% 15%,transparent 0 30deg,rgba(40,0,90,.35) 30deg 75deg,transparent 75deg)}
.top{position:relative;display:flex;justify-content:space-between;align-items:center}
.logo{font-family:'Outfit';font-weight:800;font-size:${tall?62:54}px;letter-spacing:-.045em;text-shadow:0 4px 18px rgba(60,0,90,.35)}
.tag{font-size:${tall?30:26}px;font-weight:900;padding:10px 24px;border-radius:10px;background:#fff;color:#C21C7A;transform:skew(-8deg)}
.hl{position:relative;margin-top:${tall?70:40}px;display:flex;flex-direction:column;align-items:flex-start;gap:${tall?14:10}px}
.ln{display:inline-block;background:#1A1650;padding:${tall?'6px 26px 10px':'4px 22px 8px'};transform:skew(-8deg) rotate(-2deg);box-shadow:8px 8px 0 rgba(26,22,80,.35)}
.ln>span{display:inline-block;transform:skew(8deg);font-size:${tall?78:70}px;font-weight:900;line-height:1.25;letter-spacing:-.01em;white-space:nowrap}
.ln em{font-style:normal;color:#FFE600}
.sub{position:relative;margin-top:${tall?36:24}px;font-size:${tall?32:28}px;font-weight:900;line-height:1.55;white-space:pre-line;text-shadow:0 2px 0 #1A1650,0 0 18px rgba(26,22,80,.55)}
.cards{position:relative;margin-top:auto;display:grid;grid-template-columns:${tall?'1fr':'repeat(3,1fr)'};gap:${tall?16:16}px}
.card{background:#fff;color:#1A1650;border-radius:18px;padding:${tall?'22px 30px':'20px 20px'};display:flex;flex-direction:column;gap:${tall?10:8}px;border:4px solid #1A1650;box-shadow:6px 6px 0 #1A1650}
.ct{font-size:${tall?34:22}px;font-weight:900;line-height:1.35}
.cp{display:flex;align-items:baseline;gap:2px;white-space:nowrap}
.cp b{font-family:'Outfit';font-weight:800;font-size:${tall?54:36}px;line-height:1;margin:0 3px;color:#D3157F}
.cp b.l{font-size:${tall?54:28}px}
.cp .u{font-size:${tall?24:18}px;font-weight:900;color:#1A1650}
.cp .nn{font-size:${tall?24:17}px;font-weight:700;color:#6B6590}
.cn{font-size:${tall?24:17}px;font-weight:700;color:#3A3563;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.ok{font-size:${tall?22:15}px;font-weight:900;color:#fff;background:#1A8A62;border-radius:6px;padding:3px 10px}
.cta{position:relative;margin-top:${tall?32:24}px;display:flex;justify-content:space-between;align-items:center}
.btn{font-size:${tall?36:28}px;font-weight:900;color:#1A1650;background:#FFE600;padding:${tall?'20px 40px':'14px 28px'};border-radius:14px;border:4px solid #1A1650;box-shadow:6px 6px 0 #1A1650}
.url{font-family:'Outfit';font-weight:800;font-size:${tall?32:26}px;text-shadow:0 2px 0 #1A1650}
.note{position:absolute;right:${tall?70:64}px;bottom:${tall?200:18}px;font-size:${tall?20:15}px;font-weight:700;color:rgba(255,255,255,.85)}
</style></head><body><div class="wrap"><div class="fx"></div>
<div class="top"><div class="logo">zaito</div><div class="tag">${esc(ad.tag)}</div></div>
<div class="hl">${lines}</div><p class="sub">${esc(ad.sub)}</p>
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
    const over = await p.evaluate(() => { const c=document.querySelector('.cta').getBoundingClientRect(); const h1=document.querySelector('.sub').getBoundingClientRect(); const hl=[...document.querySelectorAll('.ln')].some(e=>e.getBoundingClientRect().right>innerWidth-20); if(hl) return {wide:true, ctaBottom:c.bottom, overlap:true}; const cards=document.querySelector('.cards').getBoundingClientRect(); return { ctaBottom: c.bottom, overlap: h1.bottom > cards.top - 10 }; });
    if (over.ctaBottom > h - 40 || over.overlap) console.log('LAYOUT', ad.id, suf, JSON.stringify(over));
    await p.screenshot({ path: `${OUT}/zaito_ad_${ad.id}_${suf}.png` });
  }
  await b.close();
})();
