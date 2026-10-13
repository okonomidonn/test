// zaito そのものを紹介する Instagram カルーセル（1080×1350）を3本作る。
//  faq      … よくある質問
//  chigai   … 「在宅OK」と「完全在宅」の違い
//  tsukaikata … 使い方（実際の画面のスクリーンショット入り。ローカルのサイト 127.0.0.1:8767 から撮る）
// 使い方: node make_about_carousels.js → about/<名前>/01.png〜 と about/キャプション.md
// 色はサイトと同じ（紺 #0B1530・青 #3D5AFE・薄い青の背景）。zaito はロゴと同じ「紺＋青の i」。
const { chromium } = require('playwright');
const cp = require('child_process');
const fs = require('fs');
const path = require('path');

const jobs = JSON.parse(fs.readFileSync(path.join(__dirname, '../students/all_jobs.json'), 'utf8'));
const N = jobs.length;
const W = 1080, H = 1350;
const SITE = 'http://127.0.0.1:8767';
const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
const fontRoute = async r => { const u = r.request().url(); const body = cp.execFileSync('curl', ['-sS', '-A', r.request().headers()['user-agent'], u]); await r.fulfill({ status: 200, body, headers: { 'content-type': u.includes('googleapis') ? 'text/css' : 'font/woff2', 'access-control-allow-origin': '*' } }); };

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
.k{font-size:36px;font-weight:900;color:#3D5AFE}.dark .k{color:#A9B8FF}
h1{margin-top:26px;font-size:96px;font-weight:900;line-height:1.28;letter-spacing:-.02em}
h2{font-size:64px;font-weight:900;line-height:1.35;letter-spacing:-.02em}
h1 em,h2 em{font-style:normal;color:#3D5AFE}.dark h1 em,.dark h2 em{color:#A9B8FF}
.lead{margin-top:34px;font-size:38px;font-weight:700;line-height:1.65;opacity:.8}
.q{display:flex;gap:26px;align-items:flex-start}
.q i{flex:none;width:96px;height:96px;border-radius:28px;display:grid;place-items:center;font-style:normal;font-family:'Outfit',sans-serif;font-size:56px;font-weight:800;background:#0B1530;color:#fff}
.a{margin-top:44px;display:flex;gap:26px;align-items:flex-start;padding:44px 44px;border-radius:36px;background:#fff;box-shadow:0 40px 80px -50px rgba(11,21,48,.45)}
.a i{flex:none;width:96px;height:96px;border-radius:28px;display:grid;place-items:center;font-style:normal;font-family:'Outfit',sans-serif;font-size:56px;font-weight:800;background:#3D5AFE;color:#fff}
.a p{font-size:40px;font-weight:700;line-height:1.65}
.a p b{color:#3D5AFE}
.vs{margin-top:44px;display:grid;grid-template-columns:1fr 1fr;gap:22px}
.col{padding:38px 34px;border-radius:32px;background:#fff;box-shadow:0 40px 80px -50px rgba(11,21,48,.45)}
.col h3{font-size:44px;font-weight:900;margin-bottom:22px}
.col.ng h3{color:#C8313B}.col.ok h3{color:#3D5AFE}
.col li{list-style:none;font-size:32px;font-weight:700;line-height:1.5;padding:14px 0;border-top:2px solid #EEF0F4;display:flex;gap:12px}
.col li:first-child{border-top:0}
.col li span{flex:none;font-weight:900}
.col.ng li span{color:#C8313B}.col.ok li span{color:#12805A}
.words{margin-top:44px;display:flex;flex-wrap:wrap;gap:18px}
.words span{height:100px;padding:0 36px;border-radius:999px;background:#fff;display:flex;align-items:center;font-size:44px;font-weight:900;color:#C8313B;box-shadow:0 20px 40px -32px rgba(11,21,48,.5)}
.shot{position:relative;margin:34px auto 0;width:470px;height:780px;border-radius:64px;background:#0B1530;padding:14px;box-shadow:0 50px 90px -40px rgba(11,21,48,.6)}
.shot img{width:100%;height:100%;object-fit:cover;object-position:top;border-radius:52px;display:block}
.step{display:flex;align-items:center;gap:22px}
.step b{flex:none;width:76px;height:76px;border-radius:50%;background:#3D5AFE;color:#fff;font-family:'Outfit',sans-serif;font-size:42px;display:grid;place-items:center}
.step h2{font-size:58px}
.step+p{margin-top:12px;font-size:32px;font-weight:700;opacity:.7;line-height:1.5}
.cta{margin-top:54px;align-self:flex-start;height:128px;padding:0 54px;border-radius:999px;background:#3D5AFE;color:#fff;display:flex;align-items:center;font-size:46px;font-weight:900;box-shadow:0 30px 60px -30px rgba(61,90,254,.7)}
.url{margin-top:28px;font-family:'Outfit',sans-serif;font-size:42px;font-weight:700;color:#3D5AFE}
.wm{font-family:'Outfit',sans-serif;font-weight:800;letter-spacing:-.03em;color:#0B1530}.wm span{color:#3D5AFE}
.dark .wm{color:#fff}
.foot{height:90px;display:flex;align-items:flex-end;justify-content:space-between;font-size:28px;font-weight:900;color:#6B7590}
.dark .foot{color:rgba(255,255,255,.55)}
`;
const HEAD = `<!doctype html><html lang="ja"><head><meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@500;700;900&family=Outfit:wght@700;800&display=swap" rel="stylesheet"><style>${CSS}</style></head><body>`;
const frame = (cls, i, n, body) => `${HEAD}<div class="s ${cls}"><div class="top"><div class="logo">za<span>i</span>to</div><div class="pg">${i} / ${n}</div></div>${body}<div class="foot"><span>完全在宅の仕事だけを集めたサイト</span><span>${i < n ? 'スワイプ →' : ''}</span></div></div></body></html>`;
const cta = (title) => `<div class="mid"><h1>${title}</h1><p class="lead">登録は無料・メールアドレスだけ。<br>しおりで保存して、あとで比べられます。</p><div class="cta">プロフィールのリンクから</div><div class="url">zaito-work.com</div></div>`;
const WM = '<span class="wm">za<span>i</span>to</span>';

const qa = (q, a) => `<div class="mid"><div class="q"><i>Q</i><h2>${q}</h2></div><div class="a"><i>A</i><p>${a}</p></div></div>`;
const SETS = {
  faq: {
    name: 'よくある質問',
    slides: [
      ['', `<div class="mid"><div class="k">はじめての方へ</div><h1>${'zaito'}って<br>なに？<br><em>よくある質問</em></h1></div>`],
      ['', qa('どんなサイト？', `<b>完全在宅の仕事だけ</b>を集めた求人サイトです。運営がチェックした求人を、いま${N}件載せています。`)],
      ['', qa('お金はかかる？', `<b>無料</b>です。登録も、求人を見るのも、応募するのも、お金はかかりません。`)],
      ['', qa('だれが使える？', `<b>学生・社会人・主婦（主夫）</b>など、だれでも。学生OK・未経験OK・週1〜2日でOKの求人も、ボタンで絞り込めます。`)],
      ['', qa('どうやって応募する？', `求人ページの「応募ページへ進む」から、<b>企業の募集ページで応募</b>します。はじめての人だけ、メールアドレスで無料登録が必要です。`)],
      ['', qa('ほかの求人サイトとの違いは？', `<b>完全在宅の仕事だけ</b>なこと。そして、企業の採用ページやSNSだけで募集している、<b>大手求人サイトでは見つけにくい求人</b>も、運営が探して載せていることです。`)],
      ['light', cta(`完全在宅の<br>仕事なら、${WM}`)],
    ],
    caption: `zaitoってなに？よくある質問をまとめました。

Q. どんなサイト？
完全在宅の仕事だけを集めた求人サイトです。運営がチェックした求人を、いま${N}件。

Q. お金はかかる？
無料です。登録も応募もお金はかかりません。

Q. だれが使える？
学生・社会人・主婦（主夫）など、だれでも。

Q. どうやって応募する？
求人ページから、企業の募集ページで応募します。

Q. ほかの求人サイトとの違いは？
完全在宅の仕事だけなこと。企業の採用ページやSNSだけで募集している、大手求人サイトでは見つけにくい求人も載せています。

求人はプロフィールのリンク（zaito-work.com）から

#在宅バイト #在宅ワーク #完全在宅 #リモートワーク #フルリモート #求人サイト`,
  },
  chigai: {
    name: '「在宅OK」と「完全在宅」の違い',
    slides: [
      ['', `<div class="mid"><div class="k">知っておきたい</div><h1>「在宅OK」と<br><em>「完全在宅」</em>の<br>ちがい</h1></div>`],
      ['', `<div class="mid"><div class="k">求人でよく見るこの言葉</div><div class="words"><span>在宅OK</span><span>リモート可</span><span>基本在宅</span><span>原則リモート</span><span>リモート中心</span></div><p class="lead">どれも「出社がない」とは<br>かぎりません。</p></div>`],
      ['', `<div class="mid"><h2>よく見る例</h2><div class="vs">
<div class="col ng"><h3>「在宅OK」</h3><ul><li><span>×</span>最初の1か月は出社</li><li><span>×</span>研修は本社で</li><li><span>×</span>月1回の定例会</li><li><span>×</span>お客様先へ訪問</li></ul></div>
<div class="col ok"><h3>「完全在宅」</h3><ul><li><span>✓</span>初日から自宅</li><li><span>✓</span>研修もオンライン</li><li><span>✓</span>会議もオンライン</li><li><span>✓</span>外出する仕事なし</li></ul></div></div></div>`],
      ['', `<div class="mid"><div class="k">見分けるコツ</div><h2>募集ページの<br><em>ここ</em>を読む</h2><div class="a" style="margin-top:36px"><i>!</i><p>「研修」「入社初日」「定例会」「出張」「訪問」の書き方。<br>「基本は」「原則」があれば、<b>例外の出社</b>があるかもしれません。</p></div></div>`],
      ['', `<div class="mid"><div class="k">でも、毎回読むのは大変</div><h2>${WM} は、<br>そこを確かめてから<br>載せています</h2><p class="lead">研修・会議も含めて出社なし。<br>「基本は」「原則」の求人は載せません。</p></div>`],
      ['light', cta(`完全在宅の<br>仕事なら、${WM}`)],
    ],
    caption: `「在宅OK」と「完全在宅」のちがい、知っていますか？

求人でよく見る「在宅OK」「リモート可」「基本在宅」は、出社がまったくないとはかぎりません。
・最初の1か月は出社
・研修は本社で
・月1回の定例会は出社
ということもあります。

見分けるコツは、募集ページの「研修」「入社初日」「定例会」「出張」「訪問」の書き方。「基本は」「原則」があれば、例外の出社があるかもしれません。

zaitoは、そこを確かめてから、研修・会議も含めて出社がない求人だけを載せています。

求人はプロフィールのリンク（zaito-work.com）から

#在宅バイト #在宅ワーク #完全在宅 #リモートワーク #フルリモート #求人の見方`,
  },
  tsukaikata: {
    name: '使い方',
    shots: true,
    caption: `zaitoの使い方を、実際の画面で紹介します。

1. 条件で絞り込む（学生OK・時給・週1〜2日など。押す前に件数が分かる）
2. 気になる求人はしおりで保存
3. 求人ページで、出社なしの条件や報酬を確認
4. 「応募ページへ進む」から企業の募集ページで応募（はじめての人はメールアドレスで無料登録）

いま${N}件。求人はプロフィールのリンク（zaito-work.com）から

#在宅バイト #在宅ワーク #完全在宅 #リモートワーク #フルリモート #大学生バイト`,
  },
};

// 実際の画面を、スマホの大きさで撮る（会社名もそのまま。サイトと同じ表示）。
async function captureShots(b) {
  const ctx = await b.newContext({ viewport: { width: 390, height: 646 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true });
  await ctx.route(/fonts\.(googleapis|gstatic)\.com/, fontRoute);
  await ctx.route(/googletagmanager/, r => r.abort());
  await ctx.addInitScript(() => { try { localStorage.setItem('zaito_news_closed', 'x'); } catch (e) {} });
  const p = await ctx.newPage();
  const ready = async () => { await p.evaluate(() => document.fonts.ready); await p.waitForTimeout(400); };
  const out = {};
  // 1. 一覧と絞り込み
  await p.goto(SITE + '/?who=student'); await ready();
  await p.addStyleTag({ content: '.u073{display:none!important}' });
  await p.$eval('.filters', e => e.scrollIntoView({ block: 'start' })); await p.evaluate(() => scrollBy(0, -70)); await p.waitForTimeout(200);
  out.filter = (await p.screenshot()).toString('base64');
  // 2. 保存
  await p.goto(SITE + '/'); await ready();
  await p.addStyleTag({ content: '.u073{display:none!important}' });
  await p.$eval('#zcards', e => e.scrollIntoView({ block: 'start' })); await p.evaluate(() => scrollBy(0, -80));
  await p.click('#zcards li[data-slug] .fav'); await p.waitForTimeout(900);
  out.save = (await p.screenshot()).toString('base64');
  // 3. 求人ページ
  await p.goto(SITE + '/remote/nhigh-net-course-support/'); await ready();
  out.job = (await p.screenshot()).toString('base64');
  // 4. 応募（未登録のときの登録シート）
  await p.click('#zgo-sp'); await p.waitForTimeout(700);
  out.apply = (await p.screenshot()).toString('base64');
  await ctx.close();
  return out;
}
function tsukaikataSlides(s) {
  const st = (no, title, sub, img) => ['', `<div class="mid"><div class="step"><b>${no}</b><h2>${title}</h2></div><p>${sub}</p><div class="shot"><img src="data:image/png;base64,${img}"></div></div>`];
  return [
    ['', `<div class="mid"><div class="k">実際の画面で紹介</div><h1>${'zaito'}の<br><em>使い方</em></h1><p class="lead">完全在宅の仕事を、<br>4ステップで探して応募。</p></div>`],
    st(1, '条件で絞り込む', '学生OK・時給・週1〜2日など。押す前に件数が分かります', s.filter),
    st(2, '気になったら保存', 'しおりを押すと、マイページにまとまります', s.save),
    st(3, '求人をくわしく見る', '在宅の条件・報酬・働き方を、運営がチェックした日と一緒に', s.job),
    st(4, '募集ページから応募', 'はじめての人は、メールアドレスだけで無料登録', s.apply),
    ['light', cta(`完全在宅の<br>仕事なら、${WM}`)],
  ];
}

(async () => {
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome' });
  const shots = await captureShots(b);
  const p = await b.newPage({ viewport: { width: W, height: H } });
  await p.route(/fonts\.(googleapis|gstatic)\.com/, fontRoute);
  let md = `# zaito紹介カルーセル（${new Date().toISOString().slice(0, 10)}）\n\nフォルダごとに 01.png から順番に選んで、1つの投稿にする。\n\n`;
  for (const [key, set] of Object.entries(SETS)) {
    const slides = set.shots ? tsukaikataSlides(shots) : set.slides;
    const dir = path.join(__dirname, 'about', key);
    fs.rmSync(dir, { recursive: true, force: true });
    fs.mkdirSync(dir, { recursive: true });
    for (let i = 0; i < slides.length; i++) {
      await p.setContent(frame(slides[i][0], i + 1, slides.length, slides[i][1].replace(/>zaitoって</, `>${WM}って<`).replace(/>zaitoの</, `>${WM}の<`)), { waitUntil: 'networkidle' });
      await p.evaluate(() => document.fonts.ready);
      await p.screenshot({ path: path.join(dir, String(i + 1).padStart(2, '0') + '.png') });
    }
    md += `---\n\n## ${set.name}（about/${key}/・${slides.length}枚）\n\n\`\`\`\n${set.caption}\n\`\`\`\n\n`;
    console.log('wrote', key, slides.length);
  }
  fs.writeFileSync(path.join(__dirname, 'about', 'キャプション.md'), md);
  await b.close();
})();
