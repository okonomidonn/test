const { chromium } = require('playwright'); const cp = require('child_process');
(async () => {
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome' });
  for (const [name, path] of [['A','/zaitaku/shakaijin/'],['B','/zaitaku/jimu/'],['C','/zaitaku/gakusei/']]) {
    const ctx = await b.newContext({ viewport: { width: 390, height: 780 }, isMobile: true, hasTouch: true, deviceScaleFactor: 3 });
    const p = await ctx.newPage();
    await p.route(/fonts\.(googleapis|gstatic)\.com/, async r => { const u=r.request().url(); const body=cp.execFileSync('curl',['-sS','-A',r.request().headers()['user-agent'],u]); await r.fulfill({status:200,body,headers:{'content-type':u.includes('googleapis')?'text/css':'font/woff2','access-control-allow-origin':'*'}}); });
    await p.route(/googletagmanager/, r => r.abort());
    await p.goto('http://127.0.0.1:8767' + path, { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
    await p.addStyleTag({ content: '.hd{background:#fff!important;backdrop-filter:none!important} .card .co{filter:blur(6px)}' });
    await p.evaluate(() => { const c = document.querySelector('.count'); window.scrollTo(0, c.getBoundingClientRect().top + scrollY - 80); });
    await p.waitForTimeout(400);
    await p.screenshot({ path: `adgen/screens/${name}.png` });
    await ctx.close();
  }
  await b.close();
})();
