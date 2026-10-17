// 診断レポートHTMLをA4のPDFにする。連絡先は引数で差し込み、未指定なら手書き用の空欄にする。
// 使い方: node build_pdf.js [担当] [電話] [メール]
const path = require('path');
const fs = require('fs');
const { execSync } = require('child_process');
const { chromium } = require(path.join(execSync('npm root -g').toString().trim(), 'playwright'));

(async () => {
  const [owner = '', tel = '', mail = ''] = process.argv.slice(2);
  const html = fs.readFileSync(path.join(__dirname, 'esola_diagnosis_report.html'), 'utf8')
    .replace('{{担当}}', owner).replace('{{電話}}', tel).replace('{{メール}}', mail);
  const browser = await chromium.launch();
  const page = await browser.newPage();
  await page.setContent(html, { waitUntil: 'networkidle' });
  await page.evaluate(() => document.fonts.ready);
  const out = path.join(__dirname, 'esola_diagnosis_report.pdf');
  await page.pdf({ path: out, format: 'A4', printBackground: true, preferCSSPageSize: true });
  await browser.close();
  console.log(out);
})();
