// Instagram 用の zaito 紹介カルーセル（1080×1350・8枚）を作る。
// 使い方: node make_carousel.js → slides/01.png〜08.png と キャプション.md
// 数字は ../students/all_jobs.json（募集中の求人の書き出し）から数える。求人が変わったら書き出し直してから実行する。
const { chromium } = require('playwright');
const cp = require('child_process');
const fs = require('fs');
const path = require('path');

const jobs = JSON.parse(fs.readFileSync(path.join(__dirname, '../students/all_jobs.json'), 'utf8'));
const payNum = j => { const m = /時給([\d,]+)/.exec(j.pay || ''); return m ? +m[1].replace(/,/g, '') : 0; };
const N = jobs.length;
const groups = {};
jobs.forEach(j => { groups[j.group] = (groups[j.group] || 0) + 1; });
const stat = {
  student: jobs.filter(j => j.student).length,
  newbie: jobs.filter(j => j.newbie).length,
  short: jobs.filter(j => j.days > 0 && j.days <= 2).length,
  pay1500: jobs.filter(j => payNum(j) >= 1500).length,
};
const W = 1080, H = 1350;
const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

const CSS = `
*{box-sizing:border-box;margin:0}
body{width:${W}px;height:${H}px;overflow:hidden;font-family:'Noto Sans JP',sans-serif;font-feature-settings:'palt';color:#0B1530;background:#F3F5FF}
.s{position:absolute;inset:0;padding:80px 84px;display:flex;flex-direction:column}
.dark{background:#0B1530;color:#fff}
.blue{background:linear-gradient(150deg,#EEF1FF,#F6F3FF)}
.top{display:flex;align-items:center;justify-content:space-between}
.logo{font-family:'Outfit',sans-serif;font-weight:800;font-size:56px;letter-spacing:-.02em}.logo span{color:#3D5AFE}
.dark .logo span{color:#3D5AFE}
.pg{font-family:'Outfit',sans-serif;font-size:30px;font-weight:700;opacity:.5}
.mid{flex:1;display:flex;flex-direction:column;justify-content:center}
.k{font-size:34px;font-weight:900;color:#3D5AFE;letter-spacing:.02em}
.dark .k{color:#A9B8FF}
h1{font-size:92px;font-weight:900;line-height:1.28;letter-spacing:-.02em}
h2{font-size:76px;font-weight:900;line-height:1.3;letter-spacing:-.02em}
h1 em,h2 em{font-style:normal;color:#3D5AFE}
.dark h1 em,.dark h2 em{color:#A9B8FF}

p.lead{margin-top:36px;font-size:40px;font-weight:700;line-height:1.6;opacity:.8}
.list{margin-top:48px;display:flex;flex-direction:column;gap:22px}
.row{display:flex;align-items:center;gap:28px;padding:32px 36px;border-radius:30px;background:#fff;box-shadow:0 30px 60px -45px rgba(11,21,48,.5)}
.dark .row{background:rgba(255,255,255,.08);box-shadow:none}
.row b{font-size:44px;font-weight:900;line-height:1.35}
.row small{display:block;margin-top:4px;font-size:30px;font-weight:700;opacity:.65}
.ic{flex:none;width:84px;height:84px;border-radius:24px;display:grid;place-items:center;font-size:44px;font-weight:900;background:#EEF1FF;color:#3D5AFE}
.ic.ng{background:#FDECEE;color:#C8313B}.ic.ok{background:#E6F7EF;color:#12805A}
.dark .ic.ng{background:rgba(255,125,140,.18);color:#FF9AA6}
.grid{margin-top:48px;display:grid;grid-template-columns:1fr 1fr;gap:20px}
.cell{padding:34px 34px 30px;border-radius:30px;background:#fff;box-shadow:0 30px 60px -45px rgba(11,21,48,.5)}
.cell b{display:block;font-family:'Outfit',sans-serif;font-size:96px;line-height:1;color:#3D5AFE;letter-spacing:-.03em}
.cell b small{font-family:'Noto Sans JP';font-size:38px;margin-left:6px}
.cell span{display:block;margin-top:14px;font-size:36px;font-weight:900}
.cats{margin-top:48px;display:flex;flex-wrap:wrap;gap:18px}
.cat{height:96px;padding:0 34px;border-radius:999px;background:#fff;display:flex;align-items:center;gap:16px;font-size:40px;font-weight:900;box-shadow:0 20px 40px -32px rgba(11,21,48,.5)}
.cat i{font-style:normal;font-family:'Outfit',sans-serif;font-size:36px;color:#3D5AFE}
.note{margin-top:40px;font-size:34px;font-weight:700;opacity:.7;line-height:1.6}
.steps{margin-top:48px;display:flex;flex-direction:column;gap:22px}
.step{display:flex;gap:30px;align-items:flex-start;padding:34px 36px;border-radius:30px;background:#fff;box-shadow:0 30px 60px -45px rgba(11,21,48,.5)}
.step .n{flex:none;width:76px;height:76px;border-radius:50%;background:#0B1530;color:#fff;font-family:'Outfit',sans-serif;font-size:40px;display:grid;place-items:center}
.step b{display:block;font-size:46px;font-weight:900;line-height:1.3}
.step span{display:block;margin-top:8px;font-size:32px;font-weight:700;opacity:.65;line-height:1.5}
.bot{display:flex;align-items:center;justify-content:space-between;font-size:32px;font-weight:900;opacity:.55}
.cta{margin-top:56px;align-self:flex-start;height:132px;padding:0 56px;border-radius:999px;background:#3D5AFE;color:#fff;box-shadow:0 30px 60px -30px rgba(61,90,254,.7);display:flex;align-items:center;font-size:48px;font-weight:900}
.url{margin-top:30px;font-family:'Outfit',sans-serif;font-size:44px;font-weight:700;color:#3D5AFE}
.wm{font-family:'Outfit',sans-serif;font-weight:800;letter-spacing:-.03em;color:#0B1530}.wm span{color:#3D5AFE}
.dark .wm{color:#fff}
`;
const page = (cls, i, body, last) => `<!doctype html><html lang="ja"><head><meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@500;700;900&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
<style>${CSS}</style></head><body><div class="s ${cls}">
<div class="top"><div class="logo">za<span>i</span>to</div><div class="pg">${i} / 8</div></div>
<div class="mid">${body}</div>
<div class="bot"><span>完全在宅の仕事だけを集めたサイト</span><span>${last ? '' : 'スワイプ →'}</span></div>
</div></body></html>`;

const cats = Object.entries(groups).filter(([g]) => g !== 'その他').sort((a, b) => b[1] - a[1]);
const WM = '<span class="wm">za<span>i</span>to</span>';
const SLIDES = [
  ['', `<div class="k">在宅で働きたい人へ</div>
<h1 style="margin-top:28px"><em>完全在宅</em>の<br>仕事だけを<br>集めた求人サイト</h1>
<p class="lead" style="margin-top:40px">${WM}（ザイト）</p>`],
  ['', `<div class="k">zaitoの3つの特長</div>
<div class="list">
<div class="row"><div class="ic">1</div><div><b>完全在宅の仕事だけ</b><small>働く場所がすべて自宅の求人を集めています</small></div></div>
<div class="row"><div class="ic">2</div><div><b>運営がチェックして掲載</b><small>募集ページを読んで、確かめてから載せます</small></div></div>
<div class="row"><div class="ic">3</div><div><b>見つけにくい求人も</b><small>大手求人サイトでは探しにくい求人も載せています</small></div></div>
</div>`],
  ['', `<div class="k">特長 1</div>
<h2 style="margin-top:24px"><em>完全在宅</em>の仕事だけ</h2>
<p class="note" style="margin-top:24px">家から一歩も出ずに働ける仕事だけ。<br>いま <b style="color:#3D5AFE">${N}件</b> から選べます。</p>
<div class="cats">${cats.map(([g, n]) => `<div class="cat">${esc(g)}<i>${n}</i></div>`).join('')}</div>`],
  ['', `<div class="k">特長 2</div>
<h2 style="margin-top:24px">運営が<em>チェック</em>して掲載</h2>
<div class="list">
<div class="row"><div class="ic ok">✓</div><div><b>働く場所がすべて自宅か</b><small>募集ページを読んで確かめます</small></div></div>
<div class="row"><div class="ic ok">✓</div><div><b>仕事内容・報酬・時間</b><small>分かりやすくまとめて載せます</small></div></div>
<div class="row"><div class="ic ok">✓</div><div><b>働く前にお金がかからないか</b><small>教材費・登録料が必要な求人は載せません</small></div></div>
<div class="row"><div class="ic ok">✓</div><div><b>2週間ごとに確認し直す</b><small>募集が終わった求人は外します</small></div></div>
</div>`],
  ['', `<div class="k">特長 3</div>
<h2 style="margin-top:24px"><em>見つけにくい求人</em>も</h2>
<p class="lead" style="margin-top:36px">企業の採用ページやSNSだけで<br>募集している求人も、<br>運営が探して載せています。</p>
<p class="note">大手の求人サイトで探しても<br>出てこない仕事に出会えるかもしれません。</p>`],
  ['', `<div class="k">自分に合うものから探せる</div>
<div class="grid">
<div class="cell"><b>${stat.student}<small>件</small></b><span>学生OK</span></div>
<div class="cell"><b>${stat.newbie}<small>件</small></b><span>未経験OK</span></div>
<div class="cell"><b>${stat.pay1500}<small>件</small></b><span>時給1,500円〜</span></div>
<div class="cell"><b>${stat.short}<small>件</small></b><span>週1〜2日でOK</span></div>
</div>
<p class="note">条件のボタンを押すだけで絞り込めます。</p>`],
  ['', `<div class="k">使い方</div>
<div class="steps">
<div class="step"><div class="n">1</div><div><b>求人を探す</b><span>職種や時給、学生OKなどで絞り込み</span></div></div>
<div class="step"><div class="n">2</div><div><b>気になったら保存</b><span>しおりを押すと、マイページにまとまります</span></div></div>
<div class="step"><div class="n">3</div><div><b>企業のページから応募</b><span>はじめての人はメールアドレスで無料登録</span></div></div>
</div>`],
  ['blue', `<h1>完全在宅の<br>仕事なら、${WM}</h1>
<p class="lead">登録は無料・メールアドレスだけ。<br>希望に合う新しい求人もお知らせします。</p>
<div class="cta">プロフィールのリンクから</div>
<div class="url">zaito-work.com</div>`],
];

(async () => {
  fs.mkdirSync(path.join(__dirname, 'slides'), { recursive: true });
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome' });
  const p = await b.newPage({ viewport: { width: W, height: H } });
  await p.route(/fonts\.(googleapis|gstatic)\.com/, async r => {
    const u = r.request().url();
    const body = cp.execFileSync('curl', ['-sS', '-A', r.request().headers()['user-agent'], u]);
    await r.fulfill({ status: 200, body, headers: { 'content-type': u.includes('googleapis') ? 'text/css' : 'font/woff2', 'access-control-allow-origin': '*' } });
  });
  for (let i = 0; i < SLIDES.length; i++) {
    await p.setContent(page(SLIDES[i][0], i + 1, SLIDES[i][1], i === SLIDES.length - 1), { waitUntil: 'networkidle' });
    await p.evaluate(() => document.fonts.ready);
    await p.screenshot({ path: path.join(__dirname, 'slides', String(i + 1).padStart(2, '0') + '.png') });
  }
  await b.close();

  const caption = `完全在宅の仕事だけを集めた求人サイト、zaito（ザイト）です。

zaitoの3つの特長
1. 完全在宅の仕事だけ（いま${N}件）
2. 運営が募集ページを読んで、チェックしてから掲載
3. 企業の採用ページやSNSだけで募集している、大手求人サイトでは見つけにくい求人も

・学生OK ${stat.student}件
・未経験OK ${stat.newbie}件
・時給1,500円〜 ${stat.pay1500}件
・週1〜2日でOK ${stat.short}件

事務・サポート、SNS運用、エンジニア、ライター、デザインなど。
登録は無料で、メールアドレスだけです。

求人はプロフィールのリンク（zaito-work.com）から

#在宅ワーク #完全在宅 #在宅バイト #主婦の在宅ワーク #大学生バイト`;
  fs.writeFileSync(path.join(__dirname, 'キャプション.md'), `# Instagram 紹介カルーセル（${new Date().toISOString().slice(0, 10)}・求人${N}件の時点）\n\n画像: slides/01.png 〜 08.png を順番に選んで、1つの投稿にする。\n\n## キャプション\n\n\`\`\`\n${caption}\n\`\`\`\n\n## 投稿のコツ\n- 投稿したら、プロフィールの一番上に固定する（はじめて来た人が最初に見る投稿にする）\n- プロフィールのリンクは https://zaito-work.com/?utm_source=instagram&utm_medium=social\n- 求人の数が大きく変わったら、Claudeに「カルーセルを作り直して」と頼む\n`);
  console.log('slides', SLIDES.length, 'jobs', N, stat);
})();
