// プロフィールアイコン（1080x1080）を3案作る。使い方: node render_icons.js
const path = require('path');
const { execSync } = require('child_process');
const { chromium } = require(path.join(execSync('npm root -g').toString().trim(), 'playwright'));

const FONT = '<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@500;800&display=swap" rel="stylesheet">';
const BASE = '*{margin:0}body{width:1080px;height:1080px;font-family:"Noto Sans JP",sans-serif;display:flex;align-items:center;justify-content:center}';

// 吹き出し（AIへの質問）の中に地図ピン（お店）
const pinInBubble = (bubble, pin, dot) => `
<svg width="620" height="620" viewBox="0 0 100 100">
  <path d="M14 18h72a8 8 0 0 1 8 8v40a8 8 0 0 1-8 8H44l-18 14v-14H14a8 8 0 0 1-8-8V26a8 8 0 0 1 8-8z" fill="${bubble}"/>
  <path d="M50 30c-8.3 0-15 6.4-15 14.4C35 55 50 68 50 68s15-13 15-23.6C65 36.4 58.3 30 50 30z" fill="${pin}"/>
  <circle cx="50" cy="44.5" r="5.6" fill="${dot}"/>
</svg>`;

const icons = {
  a: `<style>${BASE}body{background:#9a3412}</style>${pinInBubble('#f5f1ea', '#9a3412', '#f5f1ea')}`,
  b: `<style>${BASE}body{background:#f5f1ea;flex-direction:column}
      .t{margin-top:10px;font-size:150px;font-weight:800;color:#1f2328;letter-spacing:.06em}</style>
      <div style="transform:scale(.72);margin:-90px 0 -110px">${pinInBubble('#9a3412', '#f5f1ea', '#9a3412')}</div>
      <div class="t">国立</div>`,
  c: `<style>${BASE}body{background:#1f2328;flex-direction:column;color:#f5f1ea}
      .t{font-size:300px;font-weight:800;line-height:1}
      .s{margin-top:40px;font-size:88px;font-weight:500;letter-spacing:.12em;color:#f0b48a}</style>
      <div class="t">AI</div><div class="s">国立 飲食店</div>`,
};

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1080, height: 1080 } });
  for (const [k, html] of Object.entries(icons)) {
    await page.setContent(`<!doctype html><html lang="ja"><head><meta charset="utf-8">${FONT}</head><body>${html}</body></html>`, { waitUntil: 'networkidle' });
    await page.evaluate(() => document.fonts.ready);
    await page.screenshot({ path: path.join(__dirname, `icon_${k}.png`) });
  }
  await browser.close();
})();
