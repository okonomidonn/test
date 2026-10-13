// 実際の求人を紹介する Instagram カルーセル（テーマ別・1080×1350）を作る。
// 使い方: node make_job_carousels.js → jobs/<テーマ>/01.png〜 と jobs/キャプション.md
// 求人は ../students/all_jobs.json（募集中の求人の書き出し）から選ぶ。注意書きつきの求人は載せない。
// 色はサイトと同じ（紺 #0B1530・青 #3D5AFE・薄い青の背景）。zaito はロゴと同じ「紺＋青の i」。
const { chromium } = require('playwright');
const cp = require('child_process');
const fs = require('fs');
const path = require('path');

// 件数はサイトと同じく全部の求人で数え、紹介する求人は注意書きのないものから選ぶ。
const every = JSON.parse(fs.readFileSync(path.join(__dirname, '../students/all_jobs.json'), 'utf8'));
const all = every.filter(j => !j.caution);
const payNum = j => { const m = /時給([\d,]+)/.exec(j.pay || ''); return m ? +m[1].replace(/,/g, '') : 0; };
const byPay = (a, b) => payNum(b) - payNum(a) || (b.pay ? 1 : 0) - (a.pay ? 1 : 0);
// 時給の高い順に選ぶが、同じ職種は2件までにして、いろいろな仕事が並ぶようにする。
const pick = (list, n, maxPer = 2) => {
  const out = [], per = {};
  for (const j of list.slice().sort(byPay)) {
    if ((per[j.group] || 0) >= maxPer) continue;
    per[j.group] = (per[j.group] || 0) + 1;
    out.push(j);
    if (out.length === n) break;
  }
  return out;
};

const THEMES = [
  { key: 'gakusei', kicker: '大学生・院生へ', title: '学生OKの\n完全在宅バイト', link: '/zaitaku/gakusei/', filter: j => j.student, tag: '#大学生バイト #長期インターン #リモートインターン' },
  { key: 'mikeiken', kicker: 'はじめての在宅ワークに', title: '未経験OKの\n完全在宅の仕事', link: '/zaitaku/mikeiken/', filter: j => j.newbie, tag: '#未経験OK #在宅ワーク初心者 #主婦の在宅ワーク' },
  { key: 'pay1500', kicker: 'しっかり稼ぎたい人へ', title: '時給1,500円以上の\n完全在宅の仕事', link: '/?pay=1500', filter: j => payNum(j) >= 1500, tag: '#高時給バイト #在宅ワーク #副業' },
  { key: 'tanjikan', kicker: '授業や家事と両立したい人へ', title: '週1〜2日でOKの\n完全在宅の仕事', link: '/?days=2', filter: j => j.days > 0 && j.days <= 2, tag: '#短時間バイト #扶養内 #スキマ時間' },
  { key: 'jimu', kicker: '事務・サポートの仕事を探している人へ', title: '出社なしの\n事務・サポート', link: '/zaitaku/jimu/', filter: j => j.group === '事務・サポート', maxPer: 5, tag: '#在宅事務 #オンライン秘書 #カスタマーサポート' },
];

const W = 1080, H = 1350;
const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
const short = (s, n) => (s.length > n ? s.slice(0, n - 1) + '…' : s);
const payParts = pay => { const m = /^(時給|日給|月給)([\d,]+(?:〜[\d,]+)?)(円[^（(]*)/.exec(pay || ''); return m; };

const CSS = `
*{box-sizing:border-box;margin:0}
body{width:${W}px;height:${H}px;overflow:hidden;font-family:'Noto Sans JP',sans-serif;font-feature-settings:'palt';color:#0B1530;background:#F3F5FF}
.s{position:absolute;inset:0;padding:80px 84px;display:flex;flex-direction:column}
.light{background:linear-gradient(150deg,#EEF1FF,#F6F3FF)}
.dark{background:#0B1530;color:#fff}
.top{display:flex;align-items:center;justify-content:space-between}
.logo{font-family:'Outfit',sans-serif;font-weight:800;font-size:56px;letter-spacing:-.02em}.logo span{color:#3D5AFE}
.pg{font-family:'Outfit',sans-serif;font-size:30px;font-weight:700;opacity:.5}
.mid{flex:1;display:flex;flex-direction:column;justify-content:center}
.k{font-size:36px;font-weight:900;color:#3D5AFE}
.dark .k{color:#A9B8FF}
h1{margin-top:26px;font-size:100px;font-weight:900;line-height:1.25;letter-spacing:-.02em}
h1 em{font-style:normal;color:#3D5AFE}
.dark h1 em{color:#A9B8FF}
.count{margin-top:40px;display:flex;align-items:baseline;gap:12px;font-size:44px;font-weight:900}
.count b{font-family:'Outfit',sans-serif;font-size:150px;line-height:1;color:#3D5AFE;letter-spacing:-.03em}
.dark .count b{color:#A9B8FF}
.sub{margin-top:30px;font-size:36px;font-weight:700;opacity:.75;line-height:1.6}
.card{flex:1;margin-top:40px;display:flex;flex-direction:column;background:#fff;border-radius:44px;padding:54px 58px;box-shadow:0 40px 80px -50px rgba(11,21,48,.45)}
.cat{align-self:flex-start;height:56px;padding:0 24px;border-radius:14px;background:#EEF1FF;color:#3D5AFE;font-size:28px;font-weight:900;display:flex;align-items:center}
.card h2{margin-top:26px;font-size:62px;font-weight:900;line-height:1.3;letter-spacing:-.02em}
.co{margin-top:16px;font-size:32px;font-weight:700;color:#4A5573}
.pay{margin-top:auto;display:flex;align-items:baseline;gap:8px;flex-wrap:wrap;color:#3D5AFE}
.pay .u{font-size:40px;font-weight:900;color:#4A5573}
.pay b{font-family:'Outfit',sans-serif;line-height:1;letter-spacing:-.03em}
.pay .r{font-size:40px;font-weight:900}
.pay .t{font-size:50px;font-weight:900;line-height:1.35}
.facts{margin-top:28px;padding-top:28px;border-top:3px solid #EEF0F4;display:flex;flex-direction:column;gap:14px;font-size:30px;font-weight:700;color:#3A4563;line-height:1.45}
.facts div{display:flex;gap:16px}.facts span{flex:none;width:118px;color:#6B7590}
.tags{margin-top:26px;display:flex;flex-wrap:wrap;gap:12px}
.tags span{height:54px;padding:0 22px;border-radius:999px;display:flex;align-items:center;font-size:26px;font-weight:900;background:#E6F7EF;color:#12805A}
.tags span.b{background:#EEF1FF;color:#3D5AFE}
.foot{height:110px;display:flex;align-items:flex-end;justify-content:space-between;font-size:28px;font-weight:900;color:#6B7590}
.dark .foot{color:rgba(255,255,255,.55)}
.wm{font-family:'Outfit',sans-serif;font-weight:800;letter-spacing:-.03em;color:#0B1530}.wm span{color:#3D5AFE}
.cta{margin-top:54px;align-self:flex-start;height:128px;padding:0 54px;border-radius:999px;background:#3D5AFE;color:#fff;display:flex;align-items:center;font-size:46px;font-weight:900;box-shadow:0 30px 60px -30px rgba(61,90,254,.7)}
.url{margin-top:28px;font-family:'Outfit',sans-serif;font-size:42px;font-weight:700;color:#3D5AFE}
`;
const HEAD = `<!doctype html><html lang="ja"><head><meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@500;700;900&family=Outfit:wght@700;800&display=swap" rel="stylesheet"><style>${CSS}</style></head><body>`;
const frame = (cls, i, n, body, foot) => `${HEAD}<div class="s ${cls}"><div class="top"><div class="logo">za<span>i</span>to</div><div class="pg">${i} / ${n}</div></div>${body}<div class="foot"><span>${foot || '完全在宅の仕事だけを集めたサイト'}</span><span>${i < n ? 'スワイプ →' : ''}</span></div></div></body></html>`;

function cover(t, list, n) {
  return frame('dark', 1, n, `<div class="mid"><div class="k">${esc(t.kicker)}</div><h1>${esc(t.title).replace('\n', '<br>').replace('完全在宅', '<em>完全在宅</em>').replace('出社なし', '<em>出社なし</em>')}</h1>
<div class="count"><b>${t.total}</b>件の中から${list.length}件を紹介</div><p class="sub">運営がチェックした、<br>完全在宅の求人です。</p></div>`);
}
function jobSlide(j, i, n) {
  const pp = payParts(j.pay);
  const pay = pp
    ? `<div class="pay"><span class="u">${pp[1]}</span><b style="font-size:${pp[2].length > 7 ? 96 : 124}px">${esc(pp[2])}</b><span class="r">${esc(pp[3].replace(/[（(].*$/, ''))}</span></div>`
    : `<div class="pay"><span class="t">${esc(j.pay ? short(j.pay, 22) : '報酬は募集ページで確認')}</span></div>`;
  const tags = ['完全在宅'];
  if (j.student) tags.push('学生OK');
  if (j.newbie) tags.push('未経験OK');
  if (j.contract) tags.push(j.contract);
  return frame('', i, n, `<div class="card"><div class="cat">${esc(j.category)}</div><h2>${esc(j.title)}</h2><div class="co">${esc(j.company)}</div>
${pay}
<div class="facts">${j.hours ? `<div><span>働き方</span>${esc(short(j.hours, 40))}</div>` : ''}<div><span>対象</span>${esc(short(j.target, 40))}</div></div>
<div class="tags">${tags.map((t, k) => `<span class="${k ? 'b' : ''}">${esc(t)}</span>`).join('')}</div></div>`, `zaito-work.com/remote/${j.slug}/`);
}
function ctaSlide(t, n) {
  return frame('light', n, n, `<div class="mid"><div class="k">ほかの求人も見る</div><h1 style="font-size:92px">${esc(t.title).replace('\n', '<br>')}<br>いま${t.total}件</h1>
<div class="cta">プロフィールのリンクから</div><div class="url">zaito-work.com</div>
<p class="sub">登録は無料・メールアドレスだけ。<br>しおりで保存して、あとで比べられます。</p></div>`);
}

(async () => {
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome' });
  const p = await b.newPage({ viewport: { width: W, height: H } });
  await p.route(/fonts\.(googleapis|gstatic)\.com/, async r => {
    const u = r.request().url();
    const body = cp.execFileSync('curl', ['-sS', '-A', r.request().headers()['user-agent'], u]);
    await r.fulfill({ status: 200, body, headers: { 'content-type': u.includes('googleapis') ? 'text/css' : 'font/woff2', 'access-control-allow-origin': '*' } });
  });
  const shot = async (html, file) => { await p.setContent(html, { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready); await p.screenshot({ path: file }); };
  let md = `# 求人紹介カルーセル（${new Date().toISOString().slice(0, 10)}）\n\nフォルダごとに 01.png から順番に選んで、1つの投稿にする。週に1〜2本が目安。\n求人が終わったり入れ替わったりしたら、Claude に「求人カルーセルを作り直して」と頼む。\n\n`;
  for (const t of THEMES) {
    const matched = all.filter(t.filter);
    t.total = every.filter(t.filter).length;
    const list = pick(matched, 5, t.maxPer);
    if (list.length < 3) { console.log('skip', t.key, list.length); continue; }
    const n = list.length + 2;
    const dir = path.join(__dirname, 'jobs', t.key);
    fs.rmSync(dir, { recursive: true, force: true });
    fs.mkdirSync(dir, { recursive: true });
    await shot(cover(t, list, n), path.join(dir, '01.png'));
    for (let i = 0; i < list.length; i++) await shot(jobSlide(list[i], i + 2, n), path.join(dir, String(i + 2).padStart(2, '0') + '.png'));
    await shot(ctaSlide(t, n), path.join(dir, String(n).padStart(2, '0') + '.png'));
    const rows = list.map((j, i) => `${i + 1}. ${j.title}（${j.company}）\n　${j.pay ? j.pay : '報酬は募集ページで確認'}${j.hours ? '／' + short(j.hours, 30) : ''}`).join('\n');
    const caption = `${t.title.replace('\n', '')}、いま${t.total}件。その中から${list.length}件を紹介します。\n\n${rows}\n\nどれも、運営が募集ページを読んでチェックした完全在宅の求人です。\n応募は、プロフィールのリンク（zaito-work.com）から各求人のページへ。\n\n※募集内容は変わることがあります。応募前に元の募集ページも確認してください。\n\n#在宅バイト #完全在宅 #在宅ワーク #リモートワーク ${t.tag}`;
    md += `---\n\n## ${t.title.replace('\n', '')}（jobs/${t.key}/・${n}枚）\nリンク先（プロフィールのリンクを一時的に変えるとき）: https://zaito-work.com${t.link}${t.link.includes('?') ? '&' : '?'}utm_source=instagram&utm_medium=social&utm_campaign=carousel_${t.key}\n\n\`\`\`\n${caption}\n\`\`\`\n\n`;
    console.log('wrote', t.key, n, 'slides', list.map(j => j.slug).join(','));
  }
  fs.writeFileSync(path.join(__dirname, 'jobs', 'キャプション.md'), md);
  await b.close();
})();
