// 学生向けSNS投稿（X・Threads・Instagram）の画像と文面を、求人データ（jobs.json）から作る。
// 使い方: node make_posts.js  → img/*.png と 投稿文.md
// jobs.json は wordpress-theme/zaito/inc/remote-jobs.php から学生OKの求人を書き出したもの。
const { chromium } = require('playwright');
const cp = require('child_process');
const fs = require('fs');
const path = require('path');

const jobs = JSON.parse(fs.readFileSync(path.join(__dirname, 'jobs.json'), 'utf8'));
// 時給の下限（「時給1,500〜3,500円」なら1500）。時給でない報酬は0。
const payNum = j => { const m = /時給([\d,]+)/.exec(j.pay || ''); return m ? +m[1].replace(/,/g, '') : 0; };
const by = Object.fromEntries(jobs.map(j => [j.slug, j]));
const SITE = 'https://zaito-work.com';
const url = (p, src) => `${SITE}${p}${p.includes('?') ? '&' : '?'}utm_source=${src}&utm_medium=social&utm_campaign=students`;

// 1件ずつ紹介する求人（時給が書かれていて、学生に分かりやすいもの）と、ひとこと。
const PICKS = [
  ['nhigh-net-course-support', '通信制高校の生徒を、オンラインで支える仕事。教育に興味がある人に。'],
  ['iflag-ai-agent', 'AIエージェントの開発を、学生のうちから実務で。'],
  ['yappli-data-intern', 'アプリの行動データを分析する、データサイエンスの長期インターン。'],
  ['herp-engineer-intern', '採用管理SaaSの開発に、エンジニアとして入れるインターン。'],
  ['proseltraction-web-ops', 'WordPressやHubSpotを触ったことがあるなら、週2日から。'],
  ['upsider-inside-sales', '未経験から法人営業を経験できる。オンラインで完結。'],
  ['itmedia-nlab-writer', '「ねとらぼ」の編集部で、記事づくりを学生インターンとして。'],
  ['kumono-media-intern', '新しいメディアを立ち上げる側に、週3日・1日3時間から。'],
  ['slogan-internstreet', '長期インターンを紹介するサービスの運営を、中から経験できる。'],
  ['timers-school-sales', 'オンラインスクールの説明会スタッフ。話すのが好きな人に。'],
];

// まとめの投稿。
const ROUNDUPS = [
  { key: 'all', title: '学生OKの完全在宅バイト', sub: '出社なし・オンラインで完結', filter: () => true, link: '/zaitaku/gakusei/' },
  { key: 'pay1500', title: '時給1,500円以上・学生OK', sub: 'すべて完全在宅', filter: j => payNum(j) >= 1500, link: '/?who=student&pay=1500' },
  { key: 'newbie', title: '未経験OK・学生OK', sub: 'はじめての在宅バイトに', filter: j => j.newbie === 1, link: '/?who=student&newbie=1' },
  { key: 'engineer', title: 'エンジニア志望の学生へ', sub: '完全在宅の開発インターン', filter: j => j.group === 'エンジニア', link: '/?who=student&g=' + encodeURIComponent('エンジニア') },
];

const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
const payParts = pay => {
  const m = /^(時給)([\d,]+(?:〜[\d,]+)?)(円.*)$/.exec(pay || '');
  return m ? m : null;
};
const W = 1080, H = 1350;
const HEAD = `<!doctype html><html lang="ja"><head><meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@500;700;900&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0}
body{width:${W}px;height:${H}px;overflow:hidden;font-family:'Noto Sans JP',sans-serif;font-feature-settings:'palt';background:#F3F5FF;color:#0B1530}
.wrap{position:absolute;inset:0;padding:72px 72px 0;display:flex;flex-direction:column}
.top{display:flex;align-items:center;justify-content:space-between}
.logo{font-family:'Outfit',sans-serif;font-weight:800;font-size:64px;letter-spacing:-.02em}.logo span{color:#3D5AFE}
.pill{display:inline-flex;align-items:center;gap:12px;height:64px;padding:0 28px;border-radius:999px;background:#0B1530;color:#fff;font-size:30px;font-weight:900}
.pill i{width:14px;height:14px;border-radius:50%;background:#2EE59D}
.card{margin-top:44px;flex:1;display:flex;flex-direction:column;background:#fff;border-radius:44px;padding:56px 60px;box-shadow:0 40px 80px -50px rgba(11,21,48,.45)}
.cat{align-self:flex-start;height:54px;padding:0 24px;border-radius:14px;background:#EEF1FF;color:#3D5AFE;font-size:28px;font-weight:900;display:flex;align-items:center}
h1{margin-top:28px;font-size:66px;font-weight:900;line-height:1.28;letter-spacing:-.02em}
.co{margin-top:18px;font-size:32px;font-weight:700;color:#4A5573}
.pay{margin-top:auto;display:flex;align-items:baseline;gap:8px;flex-wrap:wrap}
.pay .u{font-size:40px;font-weight:900;color:#4A5573}
.pay b{font-family:'Outfit',sans-serif;font-size:132px;line-height:1;color:#3D5AFE;letter-spacing:-.03em}
.pay .r{font-size:40px;font-weight:900;color:#3D5AFE}
.pay .t{font-size:52px;font-weight:900;line-height:1.35;color:#3D5AFE}
.facts{margin-top:30px;padding-top:30px;border-top:3px solid #EEF0F4;display:flex;flex-direction:column;gap:14px;font-size:30px;font-weight:700;color:#3A4563;line-height:1.45}
.facts div{display:flex;gap:16px}.facts span{flex:none;width:118px;color:#6B7590}
.tags{margin-top:30px;display:flex;flex-wrap:wrap;gap:14px}
.tags span{height:58px;padding:0 24px;border-radius:999px;display:flex;align-items:center;font-size:28px;font-weight:900;background:#E6F7EF;color:#12805A}
.tags span.b{background:#EEF1FF;color:#3D5AFE}
.foot{height:150px;display:flex;align-items:center;justify-content:space-between;font-size:30px;font-weight:900;color:#0B1530}
.foot small{font-size:26px;font-weight:700;color:#4A5573}
.list{margin-top:30px;display:flex;flex-direction:column;gap:16px}
.row{display:flex;align-items:center;gap:24px;padding:22px 28px;border-radius:26px;background:#F6F7FA}
.row .n{flex:none;width:56px;height:56px;border-radius:50%;background:#0B1530;color:#fff;font-family:'Outfit',sans-serif;font-size:30px;display:flex;align-items:center;justify-content:center}
.row .tx{flex:1;min-width:0}.row .tx b{display:block;font-size:34px;font-weight:900;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.row .tx span{display:block;margin-top:4px;font-size:26px;font-weight:700;color:#4A5573;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.big{margin-top:20px;font-size:68px;font-weight:900;line-height:1.2;letter-spacing:-.02em}
.big em{font-style:normal;color:#3D5AFE}
.more{margin-top:auto;padding-top:24px;font-size:30px;font-weight:900;color:#3D5AFE}
</style></head><body>`;
const FOOT = `<div class="foot"><span>出社なしの仕事だけを集めた <span style="color:#3D5AFE">zaito</span></span><small>プロフィールのリンクから</small></div>`;

function jobHtml(j) {
  const pp = payParts(j.pay);
  const pay = pp
    ? `<div class="pay"><span class="u">${pp[1]}</span><b style="font-size:${pp[2].length > 7 ? 100 : 132}px">${esc(pp[2])}</b><span class="r">${esc(pp[3].replace(/（.*$/, ''))}</span></div>`
    : `<div class="pay"><span class="t">${esc(j.pay || '報酬は募集ページで確認')}</span></div>`;
  const tags = ['完全在宅', '学生OK'];
  if (j.newbie) tags.push('未経験OK');
  return `${HEAD}<div class="wrap"><div class="top"><div class="logo">za<span>i</span>to</div><div class="pill"><i></i>出社なし・学生OK</div></div>
<div class="card"><div class="cat">${esc(j.category)}</div><h1>${esc(j.title)}</h1><div class="co">${esc(j.company)}</div>
${pay}
<div class="facts">${j.hours ? `<div><span>働き方</span>${esc(j.hours)}</div>` : ''}<div><span>対象</span>${esc(j.target.length > 34 ? j.target.slice(0, 33) + '…' : j.target)}</div></div>
<div class="tags">${tags.map((t, i) => `<span class="${i > 1 ? 'b' : ''}">${t}</span>`).join('')}</div></div>${FOOT}</div></body></html>`;
}

function roundupHtml(r, list) {
  const rows = list.slice(0, 4).map((j, i) => `<div class="row"><div class="n">${i + 1}</div><div class="tx"><b>${esc(j.title)}</b><span>${esc(j.pay || '報酬は募集ページで確認')}・${esc(j.company)}</span></div></div>`).join('');
  return `${HEAD}<div class="wrap"><div class="top"><div class="logo">za<span>i</span>to</div><div class="pill"><i></i>${esc(r.sub)}</div></div>
<div class="card"><div class="cat">まとめ</div><div class="big">${esc(r.title)}<br><em>${list.length}件</em></div>
<div class="list">${rows}</div>${list.length > 4 ? `<div class="more">ほか${list.length - 4}件は zaito で</div>` : ''}</div>${FOOT}</div></body></html>`;
}

const yen = j => j.pay ? j.pay : '報酬は募集ページで確認';
function jobText(j, hook) {
  const p = `/remote/${j.slug}/`;
  const lines = [`・${yen(j)}`, j.hours ? `・${j.hours}` : '', `・${j.target.length > 40 ? j.target.slice(0, 39) + '…' : j.target}`].filter(Boolean);
  const note = j.caution ? `\n※${j.caution}` : '';
  return {
    x: `【学生OK・完全在宅】${j.title}（${j.company}）\n\n${hook}\n\n${lines.join('\n')}\n\n研修も含めて出社なしを確認済み。\n${url(p, 'x')}`,
    threads: `【学生OK・完全在宅】${j.title}（${j.company}）\n\n${hook}\n\n${lines.join('\n')}${note}\n\n研修も含めて出社なしを、zaitoの運営が募集ページで確かめています。\n${url(p, 'threads')}`,
    ig: `【学生OK・完全在宅】${j.title}\n${j.company}\n\n${hook}\n\n${lines.join('\n')}${note}\n\n通学や移動の時間なしで、自分の部屋から働けます。研修も含めて出社がないことを、zaitoの運営が募集ページで確かめています。\n\n応募はプロフィールのリンク（zaito）から\n\n#在宅バイト #大学生バイト #長期インターン #リモートインターン #完全在宅 #学生ok`,
  };
}
function roundupText(r, list) {
  const rows = list.slice(0, 5).map(j => `・${j.title}（${yen(j)}）`).join('\n');
  return {
    x: `${r.title}、いま${list.length}件。\n\n${rows}\n\nどれも研修を含めて出社なしを確認済み。\n${url(r.link, 'x')}`,
    threads: `${r.title}、いま${list.length}件あります。\n\n${rows}${list.length > 5 ? `\nほか${list.length - 5}件` : ''}\n\n「在宅OK」なのに研修で出社、がない求人だけを集めています。\n${url(r.link, 'threads')}`,
    ig: `${r.title}、いま${list.length}件。\n\n${rows}${list.length > 5 ? `\nほか${list.length - 5}件` : ''}\n\n授業の合間に、自分の部屋から。研修も含めて出社がないことを、zaitoの運営が1件ずつ確かめています。\n\n一覧はプロフィールのリンク（zaito）から\n\n#在宅バイト #大学生バイト #長期インターン #リモートインターン #完全在宅 #学生ok`,
  };
}

(async () => {
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome' });
  const p = await b.newPage({ viewport: { width: W, height: H } });
  await p.route(/fonts\.(googleapis|gstatic)\.com/, async r => {
    const u = r.request().url();
    const body = cp.execFileSync('curl', ['-sS', '-A', r.request().headers()['user-agent'], u]);
    await r.fulfill({ status: 200, body, headers: { 'content-type': u.includes('googleapis') ? 'text/css' : 'font/woff2', 'access-control-allow-origin': '*' } });
  });
  const shot = async (html, name) => {
    await p.setContent(html, { waitUntil: 'networkidle' });
    await p.evaluate(() => document.fonts.ready);
    await p.screenshot({ path: path.join(__dirname, 'img', name) });
  };
  let md = `# 学生向けSNS投稿（${new Date().toISOString().slice(0, 10)}・求人データから自動で作成）\n\n`
    + `- 画像は img/ にある。Instagram はフィード（4:5）にそのまま、X・Threads は画像を付けて投稿する\n`
    + `- リンクには utm_campaign=students を付けてある。GA4 でどの投稿から人が来たか分かる\n`
    + `- 1日1本、20〜22時が目安。まとめ（R）→ 1件紹介 → 1件紹介 → まとめ… の順で回す\n`
    + `- 求人が変わったら \`node make_posts.js\` で作り直す（募集が終わった求人は jobs.json から消える）\n\n`;
  let n = 0;
  for (const r of ROUNDUPS) {
    const list = jobs.filter(r.filter).sort((a, b) => payNum(b) - payNum(a));
    if (!list.length) continue;
    const name = `R_${r.key}.png`;
    await shot(roundupHtml(r, list), name);
    const t = roundupText(r, list);
    md += `---\n\n## R. ${r.title}（${list.length}件）\n画像: img/${name}\n\n**X**\n\n\`\`\`\n${t.x}\n\`\`\`\n\n**Threads**\n\n\`\`\`\n${t.threads}\n\`\`\`\n\n**Instagram**\n\n\`\`\`\n${t.ig}\n\`\`\`\n\n`;
    n++;
  }
  for (const [slug, hook] of PICKS) {
    const j = by[slug];
    if (!j) continue;
    const name = `J_${slug}.png`;
    await shot(jobHtml(j), name);
    const t = jobText(j, hook);
    md += `---\n\n## ${j.title}（${j.company}）\n画像: img/${name}\n\n**X**\n\n\`\`\`\n${t.x}\n\`\`\`\n\n**Threads**\n\n\`\`\`\n${t.threads}\n\`\`\`\n\n**Instagram**\n\n\`\`\`\n${t.ig}\n\`\`\`\n\n`;
    n++;
  }
  fs.writeFileSync(path.join(__dirname, '投稿文.md'), md);
  console.log('posts', n);
  await b.close();
})();
