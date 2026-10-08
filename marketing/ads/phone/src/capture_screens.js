const { chromium } = require('playwright'); const cp = require('child_process');
(async () => {
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome' });
  for (const [name, path] of [['A','/zaitaku/mikeiken/'],['B','/zaitaku/jimu/'],['C','/zaitaku/gakusei/']]) {
    const ctx = await b.newContext({ viewport: { width: 390, height: 780 }, isMobile: true, hasTouch: true, deviceScaleFactor: 3 });
    const p = await ctx.newPage();
    await p.route(/fonts\.(googleapis|gstatic)\.com/, async r => { const u=r.request().url(); const body=cp.execFileSync('curl',['-sS','-A',r.request().headers()['user-agent'],u]); await r.fulfill({status:200,body,headers:{'content-type':u.includes('googleapis')?'text/css':'font/woff2','access-control-allow-origin':'*'}}); });
    await p.route(/googletagmanager/, r => r.abort());
    await p.goto('http://127.0.0.1:8767' + path, { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
    await p.addStyleTag({ content: '.hd{background:#fff!important;backdrop-filter:none!important} .card .co{filter:blur(6px)}' });
    // 広告では時給1,300円以上の求人だけを見せる（報酬が書かれていない求人・時給の低い求人は画面から外す）。
    await p.evaluate(() => {
      document.querySelectorAll('#zcards .card').forEach(card => {
        const b = card.querySelector('.pay b'); const u = card.querySelector('.pay .u');
        const min = b && u && u.textContent.includes('時給') ? parseInt(b.textContent.replace(/,/g, ''), 10) : 0;
        if (!(min >= 1300)) card.remove();
      });
      // 求人名に入っているサービス名・ブランド名も広告では見せない。
      document.querySelectorAll('#zcards .card h2').forEach(h => {
        h.textContent = h.textContent.replace(/[^「」]*「[^」]*」の?/g, '').replace(/N高グループ\s*/g, 'オンライン高校の').replace(/^\s+/, '');
      });
    });
    await p.evaluate(() => { const c = document.querySelector('.count'); window.scrollTo(0, c.getBoundingClientRect().top + scrollY - 80); });
    await p.waitForTimeout(400);
    await p.screenshot({ path: `adgen/screens/${name}.png` });
    await ctx.close();
  }
  await b.close();
})();
