// posts.json から 1080x1080 の投稿画像を作る。使い方: node render_cards.js
const path = require('path');
const fs = require('fs');
const { execSync } = require('child_process');
const { chromium } = require(path.join(execSync('npm root -g').toString().trim(), 'playwright'));

const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/\n/g, '<br>');

const card = (p, total) => `<!doctype html><html lang="ja"><head><meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;800&display=swap" rel="stylesheet">
<style>
  * { margin: 0; box-sizing: border-box; }
  body { width: 1080px; height: 1080px; font-family: "Noto Sans JP", sans-serif; background: #f5f1ea; color: #1f2328; }
  .wrap { position: absolute; inset: 64px; display: flex; flex-direction: column; }
  .label { align-self: flex-start; background: #9a3412; color: #fff; font-weight: 500; font-size: 30px; padding: 10px 26px; border-radius: 40px; }
  h1 { margin-top: 60px; font-size: 76px; font-weight: 800; line-height: 1.32; letter-spacing: 0.01em; }
  .body { margin-top: 48px; font-size: 36px; line-height: 1.75; color: #3d4450; }
  .foot { margin-top: auto; border-top: 3px solid #1f2328; padding-top: 28px; display: flex; justify-content: space-between; align-items: center; font-size: 32px; font-weight: 500; }
  .foot .n { color: #9a3412; font-weight: 800; }
  .brand { position: absolute; top: 76px; right: 64px; font-size: 26px; color: #6b7280; }
</style></head><body>
<div class="brand">国立 × 飲食店 × AI集客</div>
<div class="wrap">
  <div class="label">${esc(p.label)}</div>
  <h1>${esc(p.title)}</h1>
  <div class="body">${esc(p.body)}</div>
  <div class="foot"><span>${esc(p.foot)}</span><span class="n">${p.id}/${total}</span></div>
</div></body></html>`;

(async () => {
  const posts = JSON.parse(fs.readFileSync(path.join(__dirname, 'posts.json'), 'utf8'));
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1080, height: 1080 } });
  for (const p of posts) {
    await page.setContent(card(p, String(posts.length).padStart(2, '0')), { waitUntil: 'networkidle' });
    await page.evaluate(() => document.fonts.ready);
    const out = path.join(__dirname, `post_${p.id}.png`);
    await page.screenshot({ path: out });
    console.log(out);
  }
  await browser.close();
})();
