// 直接掲載の流れを、ブラウザで端から端まで通す（試験環境・作業環境の両方で使う）。
// 使い方: BASE=https://staging.zaito-work.com HELPER="ssh ... wp --path=... eval-file -" node tests/staging/flow.js
// HELPER は、標準入力の PHP をそのサイトの WordPress の中で動かすコマンド（合鍵・管理者のログイン・本人確認のリンクを作るのに使う）。
// 結果は1行ずつ「OK / NG 段階: 内容」で出す。画面は SHOTS のフォルダに保存する。
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs');

const BASE = (process.env.BASE || 'http://localhost:8088').replace(/\/$/, '');
const SHOTS = process.env.SHOTS || 'shots';
const VIDEO = process.env.VIDEO === '1';
const RUN = process.env.RUN_ID || String(Date.now()).slice(-8);
const TAG = (VIDEO ? 'v' : 'n') + RUN;
const results = [];
fs.mkdirSync(SHOTS, { recursive: true });

function php(code) {
  return execSync(process.env.HELPER, { input: '<?php ' + code, encoding: 'utf8' }).trim();
}
function ok(step, msg) { results.push(['OK', step, msg]); console.log(`OK  ${step}: ${msg}`); }
function ng(step, msg) { results.push(['NG', step, msg]); console.log(`NG  ${step}: ${msg}`); }
// 画面の写真。全体を撮るときは、画面に固定された帯（ヘッダーや応募ボタン）が重ならないよう一時的に隠し、撮ったら元に戻す。
// 登録の窓など、固定された窓が開いているときは full=false で見えている範囲だけ撮る。
async function shot(page, name, full = true) {
  if (full) await page.evaluate(() => document.querySelectorAll('*').forEach(e => { if (getComputedStyle(e).position === 'fixed') { e.dataset.e2eHide = e.style.display; e.style.display = 'none'; } })).catch(() => {});
  await page.screenshot({ path: `${SHOTS}/${TAG}_${name}.png`, fullPage: full }).catch(() => {});
  if (full) await page.evaluate(() => document.querySelectorAll('[data-e2e-hide]').forEach(e => { e.style.display = e.dataset.e2eHide; delete e.dataset.e2eHide; })).catch(() => {});
}
async function go(page, path) {
  await page.goto(path.startsWith('http') ? path : BASE + path, { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(800);
}
async function submit(page, form, btnText) {
  const btn = btnText ? form.locator('button, input[type=submit]', { hasText: btnText }).first() : form.locator('button[type=submit], input[type=submit], button:not([type])').last();
  await Promise.all([page.waitForLoadState('domcontentloaded'), btn.click()]);
  await page.waitForTimeout(1200);
}
async function errorsOn(page) {
  return (await page.locator('.form-errors, .errors, .notice-error, .err, [role=alert]').allInnerTexts().catch(() => [])).join(' / ').trim();
}

if (/^https?:\/\/(www\.)?zaito-work\.com/.test(BASE)) { console.log('本番には使わない（試験環境か作業環境だけ）'); process.exit(2); }

(async () => {
  // 何度も試すと送信回数の制限にかかるので、試験用のサイトでは先に制限を消しておく。
  php(`if ( ! defined( 'ZAITO_STAGING' ) && false === strpos( home_url(), 'localhost' ) ) { exit; } global $wpdb; $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_%zaito\\_lp\\_rl\\_%'" );`);
  const host = new URL(BASE).hostname;
  const secure = BASE.startsWith('https');
  const browser = await chromium.launch({ args: ['--use-fake-ui-for-media-stream', '--use-fake-device-for-media-stream'] });
  const ctxOpts = { viewport: { width: 1280, height: 900 }, locale: 'ja-JP', permissions: ['camera', 'microphone'] };
  const gate = process.env.STG_KEY ? [{ name: 'zaito_stg', value: process.env.STG_KEY, domain: host, path: '/', secure, httpOnly: true }] : [];

  // 運営（管理者）のブラウザ。パスワードは使わず、サーバーの中で1時間だけ有効なログインを作る。
  const cookieCode = (uidExpr) => `
    $u = get_userdata( ${uidExpr} );
    $exp = time() + HOUR_IN_SECONDS;
    $s = ${secure ? 'true' : 'false'};
    echo wp_json_encode( array(
      'auth_name' => $s ? SECURE_AUTH_COOKIE : AUTH_COOKIE,
      'auth' => wp_generate_auth_cookie( $u->ID, $exp, $s ? 'secure_auth' : 'auth' ),
      'li_name' => LOGGED_IN_COOKIE,
      'li' => wp_generate_auth_cookie( $u->ID, $exp, 'logged_in' ),
      'uid' => $u->ID ) );`;
  const cookiesFor = (c) => (process.env.GITHUB_ACTIONS && console.log('::add-mask::' + c.auth + '\n::add-mask::' + c.li), [
    { name: c.auth_name, value: c.auth, domain: host, path: '/wp-admin', secure, httpOnly: true },
    { name: c.auth_name, value: c.auth, domain: host, path: '/wp-content/plugins', secure, httpOnly: true },
    { name: c.li_name, value: c.li, domain: host, path: '/', secure, httpOnly: true }]);
  const adminInfo = JSON.parse(php(cookieCode(`get_users( array( 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID' ) )[0]->ID`)));
  const admin = await browser.newContext(ctxOpts);
  await admin.addCookies([...gate, ...cookiesFor(adminInfo)]);
  const ap = await admin.newPage();
  // 運営の管理画面を開く。エックスサーバーの「国外からの管理画面へのアクセス制限」で止められたとき（GitHubのパソコンは国外）は、
  // サーバーの中で同じ管理画面の部分を、このブラウザのログインに合わせて作り、試験環境のページの上に表示する。
  // ボタンを押したときの送信先（admin-post.php）は国外からも届くので、押したあとの処理は本物の通り道を通る。
  let foreignNoted = false;
  async function adminTab(tab) {
    await go(ap, `/wp-admin/admin.php?page=zaito-direct&tab=${tab}`);
    if (!/アクセスが拒否/.test(await ap.title()) && !process.env.FORCE_FOREIGN) return;
    if (!foreignNoted) { console.log('   （国外からは管理画面が開けないため、管理画面の部分をサーバーで作って表示し、ボタンを押す）'); foreignNoted = true; }
    const html = php(`$_COOKIE[ LOGGED_IN_COOKIE ] = ${JSON.stringify(adminInfo.li)}; wp_set_current_user( ${adminInfo.uid} ); require_once ABSPATH . 'wp-admin/includes/template.php'; ob_start(); zaito_direct_admin_tab_${tab}(); echo ob_get_clean();`);
    await go(ap, '/');
    await ap.setContent('<!doctype html><meta charset="utf-8"><body>' + html + '</body>');
  }

  const company = await browser.newContext(ctxOpts); await company.addCookies(gate);
  const cp = await company.newPage();
  const student = await browser.newContext({ ...ctxOpts, viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true, deviceScaleFactor: 2 });
  await student.addCookies(gate);
  const sp = await student.newPage();

  const coEmail = `e2e-co-${TAG}@example.invalid`;
  const coPass = 'E2e-' + Math.random().toString(36).slice(2) + '-Pw9';
  const coName = `試験株式会社${TAG}`;
  const stEmail = `e2e-st-${TAG}@example.invalid`;
  const jobTitle = `【自動テスト${TAG}】在宅テレアポ（試験用）`;
  let jobId = 0, appId = 0;

  try {
    // 1. 企業の登録
    await go(cp, '/company-register/');
    const f = cp.locator('form[action*="admin-post.php"]').first();
    await f.locator('[name=company_name]').fill(coName);
    await f.locator('[name=company_url]').fill('https://example.com/');
    await f.locator('[name=contact_person]').fill('試験 担当');
    await f.locator('[name=phone]').fill('03-0000-0000');
    await f.locator('[name=email]').fill(coEmail);
    await f.locator('[name=password]').fill(coPass);
    await f.locator('[name=agree_terms]').check();
    await submit(cp, f);
    await shot(cp, '01_company_registered');
    const coId = Number(php(`$u = get_user_by( 'email', '${coEmail}' ); echo $u ? $u->ID : 0;`));
    if (!coId) throw new Error('企業の登録ができなかった: ' + cp.url() + ' ' + await errorsOn(cp));
    ok('1 企業の登録', `登録できた（ユーザー${coId}、状態 ${php(`echo zaito_company_status(${coId});`)}）。登録後の画面 ${cp.url().replace(BASE, '')}`);

    // 2. 運営が企業を承認
    await adminTab('companies');
    await shot(ap, '02_admin_companies');
    const af = ap.locator(`form:has(input[name=user_id][value="${coId}"]):has(input[name=to][value=approved])`).first();
    await submit(ap, af);
    const st = php(`echo zaito_company_status(${coId});`);
    st === 'approved' ? ok('2 運営の企業承認', '管理画面のボタンで承認できた') : ng('2 運営の企業承認', '状態が ' + st);

    // 3. 企業がログインして求人を作る
    await go(cp, '/company-login/');
    // 登録した直後はログインしたままなので、一度ログアウトしてから、メールアドレスとパスワードでログインし直す。
    await company.clearCookies(); await company.addCookies(gate);
    await go(cp, '/company-login/');
    const lf = cp.locator('form[action*="wp-login.php"]').first();
    await lf.locator('[name=log]').fill(coEmail);
    await lf.locator('[name=pwd]').fill(coPass);
    await submit(cp, lf);
    if (/アクセスが拒否/.test(await cp.title())) {
      // ログインの送信先（wp-login.php）も国外からは止められる。日本からは使える（作業環境で確認済み）。ここではサーバーでログインを作る。
      console.log('   （国外からはログインの送信先が止められるため、サーバーで企業のログインを作る）');
      const ck = JSON.parse(php(cookieCode(coId)));
      await company.addCookies(cookiesFor(ck));
      await go(cp, '/company/');
    }
    await shot(cp, '03_company_dashboard');
    cp.url().includes('/company') ? ok('3a 企業のログイン', 'ログインできた → ' + cp.url().replace(BASE, '')) : ng('3a 企業のログイン', 'ログイン後の画面 ' + cp.url());

    await go(cp, '/company-jobs/?new=1');
    const jf = cp.locator('form:has([name=title])').first();
    const fields = await jf.evaluate(f => [...f.elements].filter(e => e.name).map(e => ({ n: e.name, t: e.type, tag: e.tagName, req: e.required, opts: e.tagName === 'SELECT' ? [...e.options].map(o => o.value) : null })));
    console.log('   求人フォームの欄: ' + fields.map(x => x.n + (x.req ? '*' : '')).join(', '));
    // 人が操作するのと同じように、見えている選択肢を押して入れる。
    await jf.locator('[name=title]').fill(jobTitle);
    await jf.locator('[name=summary]').fill('試験用の求人です。応募しても実際の企業には届きません。');
    await jf.locator('[name=content]').fill('プロスポーツチームのスポンサー獲得のためのテレアポです（試験用）。\nトークスクリプトがあります。');
    await jf.locator('label', { hasText: 'テレアポ・営業' }).first().click();
    await jf.locator('label', { hasText: 'アルバイト' }).first().click();
    await jf.locator('[name=pay_type]').selectOption({ label: '時給' }).catch(() => {});
    await jf.locator('[name=pay_min]').fill('1280');
    await jf.locator('[name=hours]').fill('週3日から・1日4時間から');
    await jf.locator('[name=target]').fill('PCの基本操作ができる方');
    await jf.locator('label', { hasText: '学生OK' }).first().click();
    await jf.locator('label', { hasText: '未経験OK' }).first().click();
    await jf.locator('[name=question]').fill('週に何時間くらい働けそうですか？');
    if (!(await jf.locator('[name=auto_reply]').inputValue()).includes('ご応募ありがとうございます')) ng('3b 自動メッセージ', '新しい求人の自動メッセージに既定の文が入っていない');
    await jf.locator('[name=auto_reply]').fill('ご応募ありがとうございます（試験用の自動メッセージ）。');
    if (VIDEO) {
      const vq = jf.locator('[name^=video_q], [name="video_questions[]"]');
      const n = await vq.count();
      if (!n) ng('3b 録画面接の質問', '求人フォームに動画の質問の欄がない');
      for (let k = 0; k < Math.min(n, 2); k++) await vq.nth(k).fill(k ? 'この仕事で活かせそうな経験を教えてください。' : '自己紹介をしてください。');
    }
    // 労働条件（初期値の入っていない欄だけ入れる）。
    for (const [n, v] of [['duty_change', '変更なし'], ['period', '期間の定めあり（3か月・更新あり）'], ['renewal', '勤務成績により判断'], ['trial', 'なし'], ['worktime', '10:00〜17:00の間で1日4時間から'], ['holiday', '土日祝'], ['insurance', '労災保険あり']]) {
      const c = jf.locator(`[name=cond_${n}]`);
      if (await c.count() && !(await c.inputValue())) await c.fill(v);
    }
    await jf.locator('[name=confirm_remote]').check({ force: true });
    await jf.locator('[name=confirm_fee]').check({ force: true });
    await shot(cp, '03b_job_form');
    const btns = await jf.locator('button, input[type=submit]').allInnerTexts();
    console.log('   求人フォームのボタン: ' + btns.join(' | '));
    const sendBtn = btns.find(t => /審査/.test(t)) || btns[btns.length - 1];
    await submit(cp, jf, sendBtn);
    await shot(cp, '04_job_submitted');
    jobId = Number(php(`$p = get_posts( array( 'post_type' => 'job_listing', 'post_status' => 'any', 'title' => ${JSON.stringify(jobTitle)}, 'fields' => 'ids', 'posts_per_page' => 1 ) ); echo $p ? $p[0] : 0;`));
    if (!jobId) throw new Error('求人ができなかった: ' + cp.url() + ' ' + await errorsOn(cp));
    ok('3b 企業が求人を審査に出す', `求人${jobId}、状態 ${php(`echo get_post_status(${jobId});`)}`);

    // 4. 運営が求人を承認
    await adminTab('jobs');
    const jaf = ap.locator(`form:has(input[name=job_id][value="${jobId}"])`).first();
    await shot(ap, '05_admin_jobs');
    await Promise.all([ap.waitForLoadState('domcontentloaded'), jaf.locator('button[value=approve]').click()]);
    await ap.waitForTimeout(1200);
    const ps = php(`echo get_post_status(${jobId});`);
    ps === 'publish' ? ok('4 運営の求人承認', '公開された') : ng('4 運営の求人承認', '状態が ' + ps);

    // 5. 学生の画面に出るか
    const slug = 'd' + jobId;
    // 運営がトップページの一番上に固定する（GWCへの特典の確認）。
    await adminTab('jobs');
    const pinf = ap.locator(`form:has(input[name=job_id][value="${jobId}"]):has(input[name=top_until])`).first();
    if (await pinf.count()) {
      await Promise.all([ap.waitForLoadState('domcontentloaded'), pinf.locator('button[value=pin]').click()]);
      await ap.waitForTimeout(1000);
      ok('5 運営がトップに固定', '固定の最後の日 ' + php(`echo get_post_meta( ${jobId}, '_zaito_top_until', true );`));
    } else ng('5 運営がトップに固定', '固定のフォームが見つからない');
    await go(sp, '/');
    const hrefs = await sp.$$eval('a[href*="/remote/"]', as => as.map(a => a.getAttribute('href')).filter(h => /\/remote\/[^/]+\/$/.test(h) && !/\/remote\/(c|tag|category)\//.test(h)));
    const pos = hrefs.findIndex(h => h.includes(`/remote/${slug}/`));
    pos === 0 ? ok('5a トップページ', 'トップの一覧の一番上に出ている') : pos > 0 ? ng('5a トップページ', `一覧には出ているが ${pos + 1} 番目（一番上は ${hrefs[0]}）`) : ng('5a トップページ', 'トップの一覧に見つからない');
    await shot(sp, '05b_top_page');
    await go(sp, `/remote/${slug}/`);
    await shot(sp, '06_job_detail');
    (await sp.locator('h1').innerText().catch(() => '')).includes('自動テスト') ? ok('5b 求人の詳細', '詳細ページが開けた') : ng('5b 求人の詳細', '詳細ページの見出しが違う: ' + sp.url());
    (await sp.locator('body').innerText()).includes('就業場所の変更の範囲') ? ok('5c 労働条件', '詳細ページに労働条件の表が出ている') : ng('5c 労働条件', '詳細ページに労働条件が出ていない');

    // 6. 学生の登録（「この求人に応募する」→ 登録 → 5問の質問 → 応募ページ）
    await sp.locator('a,button', { hasText: 'この求人に応募する' }).filter({ visible: true }).first().click();
    await sp.waitForTimeout(800);
    const rf = sp.locator('#zsignup');
    await rf.locator('[name=name_last]').fill('試験');
    await rf.locator('[name=name_first]').fill('太郎');
    await rf.locator('[name=school]').fill('テスト大学');
    await rf.locator('[name=email]').fill(stEmail);
    await shot(sp, '07_student_signup', false);
    const t0 = Date.now();
    await rf.locator('button').filter({ hasText: '登録して' }).click();
    await sp.locator('#zprofile').waitFor({ state: 'visible', timeout: 20000 });
    console.log(`   登録ボタンから質問の画面まで ${((Date.now() - t0) / 1000).toFixed(1)}秒`);
    const t1 = Date.now();
    for (let k = 0; k < 8; k++) {
      const save = sp.locator('#zprofile button', { hasText: '保存して応募ページへ進む' });
      if (await save.isVisible()) { await Promise.all([sp.waitForLoadState('domcontentloaded'), save.click()]); await sp.waitForTimeout(1500); break; }
      const opt = sp.locator('#zprofile label:visible').first();
      if (await opt.count()) await opt.click({ timeout: 3000 }).catch(() => {});
      await sp.locator('#zprofile button', { hasText: '次へ' }).filter({ visible: true }).first().click({ timeout: 10000 });
      await sp.waitForTimeout(600);
    }
    console.log(`   5問の質問に答えて応募ページまで ${((Date.now() - t1) / 1000).toFixed(1)}秒`);
    await shot(sp, '07b_student_after_sheet');
    const stId = Number(php(`$p = get_posts( array( 'post_type' => 'zaito_interest', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => 1, 'meta_query' => array( array( 'key' => 'email', 'value' => '${stEmail}' ) ) ) ); echo $p ? $p[0] : 0;`));
    if (!stId) throw new Error('学生の登録ができなかった: ' + sp.url() + ' ' + await errorsOn(sp));
    ok('6a 学生の登録', `登録できた（${stId}）→ ${sp.url().replace(BASE, '')}`);

    // 応募フォームを開く（本人確認の前は、確認のリンクを送る画面になるはず）。
    await go(sp, `/remote/${slug}/apply/`);
    await shot(sp, '07b_apply_before_verify');
    const linkBtn = sp.locator('form:has([name=send_link]) button, button[name=send_link], input[name=send_link]').first();
    if (await linkBtn.count()) {
      await Promise.all([sp.waitForLoadState('domcontentloaded'), linkBtn.click()]); await sp.waitForTimeout(1000);
      ok('6b 本人確認の前の応募', '確認のリンクを送る画面が出て、送れた（メールは info@ で確かめる）');
    } else {
      ok('6b 本人確認の前の応募', '確認のリンクのボタンはなかった → ' + sp.url().replace(BASE, ''));
    }
    // メールのボタンを押したことにする（メールに入るのと同じリンクをサーバーで作る）。
    const login = php(`echo zaito_my_login_url( ${stId} );`);
    await go(sp, login);
    const verified = php(`echo zaito_interest_verified( ${stId} ) ? 1 : 0;`);
    verified === '1' ? ok('6c メールアドレスの確認', 'メールのリンクで確認済みになった') : ng('6c メールアドレスの確認', 'リンクを開いても確認済みにならない');

    // 7. 応募
    await go(sp, `/remote/${slug}/apply/`);
    await shot(sp, '08_apply_form');
    const pf = sp.locator('form:has([name=message])').first();
    if (!(await pf.count())) throw new Error('応募フォームが出ない: ' + sp.url());
    await pf.locator('[name=name_last]').fill('試験');
    await pf.locator('[name=name_first]').fill('太郎');
    await pf.locator('[name=kana_last]').fill('シケン'); // カタカナでも、ひらがなにそろえて受け付ける
    await pf.locator('[name=kana_first]').fill('たろう');
    await pf.locator('label').filter({ hasText: /^\s*大学生\s*$/ }).first().click();
    await pf.locator('label').filter({ hasText: /^\s*2年\s*$/ }).first().click().catch(() => {});
    await pf.locator('[name=school]').fill('テスト大学');
    await pf.locator('[name=birth_y]').selectOption('2006');
    await pf.locator('[name=birth_m]').selectOption('4');
    await pf.locator('[name=birth_d]').selectOption('1');
    await pf.locator('[name=pref]').selectOption('北海道');
    await pf.locator('[name=tel]').fill('090-0000-0000');
    // メッセージは任意。録画面接ありの流れでだけ書き、なしの流れでは空のまま応募できることを確かめる。
    if (VIDEO) await pf.locator('[name=message]').fill('はじめまして。試験用の応募です。スポーツが好きなので応募しました。');
    if (await pf.locator('[name=answer]').count()) await pf.locator('[name=answer]').fill('週に12時間くらいです。');
    await submit(sp, pf);
    await shot(sp, '09_after_apply');
    appId = Number(php(`$p = get_posts( array( 'post_type' => 'zaito_application', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => 1, 'meta_query' => array( array( 'key' => 'job_id', 'value' => ${jobId} ), array( 'key' => 'interest_id', 'value' => ${stId} ) ) ) ); echo $p ? $p[0] : 0;`));
    if (!appId) throw new Error('応募ができなかった: ' + sp.url() + ' ' + await errorsOn(sp));
    ok('7 学生の応募', `応募できた（応募${appId}）→ ${sp.url().replace(BASE, '')}`);

    // 8. 録画面接
    if (VIDEO) {
      if (!/interview/.test(sp.url())) ng('8 録画面接', '応募のあと録画の画面に進まなかった: ' + sp.url());
      else {
        // 同意 → カメラの準備 → 練習の質問 → 本番の質問（1問ずつ 録画 → 答え終わった → 次へ）→ 送信完了。
        await sp.locator('#agree').check();
        await sp.locator('#startBtn').click();
        await sp.locator('#toFirst').waitFor({ state: 'visible', timeout: 20000 });
        await shot(sp, '09v_camera_check', false);
        await sp.locator('#toFirst').click();
        for (let q = 0; q < 8; q++) {
          await Promise.race([
            sp.locator('#recNow').waitFor({ state: 'visible', timeout: 30000 }),
            sp.locator('#s-done').waitFor({ state: 'visible', timeout: 30000 }),
          ]).catch(() => {});
          if (await sp.locator('#s-done').isVisible()) break;
          const err = (await sp.locator('#qErr').innerText().catch(() => '')).trim();
          if (err) { ng('8 録画面接', '画面にエラー: ' + err); break; }
          await sp.locator('#recNow').click();
          await sp.waitForTimeout(3500);
          await sp.locator('#stopRec').click();
          await sp.locator('#nextQ').waitFor({ state: 'visible', timeout: 20000 });
          await shot(sp, `09v_video_q${q}`, false);
          await sp.locator('#nextQ').click();
          await sp.waitForTimeout(1500);
        }
        await sp.locator('#s-done').waitFor({ state: 'visible', timeout: 60000 }).catch(() => {});
        await shot(sp, '09v_video_done', false);
        const vst = php(`echo get_post_meta( ${appId}, 'video_status', true ) . ' ' . count( array_filter( (array) zaito_video_files( ${appId} ) ) );`);
        vst.startsWith('done') ? ok('8 録画面接', `回答を送れた（状態と保存された動画の数: ${vst}）`) : ng('8 録画面接', `回答が送れていない（状態と動画の数: ${vst}）→ ${sp.url().replace(BASE, '')}`);
      }
    }

    // 9. 企業が応募を見る・返信する・内定にする
    await go(cp, '/company-applicants/');
    await shot(cp, '10_company_applicants');
    (await cp.locator(`a[href*="c=${appId}"]`).count()) ? ok('9a 企業の応募者一覧', '応募が一覧に出ている') : ng('9a 企業の応募者一覧', '応募が一覧に見つからない');
    await go(cp, `/chat/?c=${appId}`);
    await shot(cp, '11_company_chat');
    const body = await cp.locator('body').innerText();
    body.includes(VIDEO ? 'スポーツが好き' : '応募しました。') ? ok('9b 企業のメッセージ画面', '応募の内容が見えている') : ng('9b 企業のメッセージ画面', '応募の内容が見えない');
    body.includes('090-0000-0000') && body.includes('北海道') && /\d+歳/.test(body) && body.includes('しけん たろう') ? ok('9b2 応募者の連絡先', 'ふりがな・電話番号・都道府県・年齢が企業に見えている') : ng('9b2 応募者の連絡先', 'ふりがな・電話番号・都道府県・年齢のどれかが企業に見えない');
    if (VIDEO) {
      // 企業のブラウザで、1問目の動画を実際に読み込めるか（動画のファイルが返ってくるか）。
      const purl = php(`echo zaito_video_play_url( ${appId}, 0 );`);
      const r = await cp.request.get(purl);
      const ct = r.headers()['content-type'] || '';
      const len = (await r.body()).length;
      r.status() === 200 && /video/.test(ct) && len > 1000 ? ok('9c 企業の動画の再生', `動画を読み込めた（${ct}、${Math.round(len / 1024)}KB）`) : ng('9c 企業の動画の再生', `読み込めない（${r.status()} ${ct} ${len}バイト）`);
      // 学生のブラウザや、ログインしていない人からは見られないこと。
      const r2 = await sp.request.get(purl);
      r2.status() !== 200 || !/video/.test(r2.headers()['content-type'] || '') ? ok('9c2 動画の見られる人', `学生のブラウザからは見られない（${r2.status()}）`) : ng('9c2 動画の見られる人', '学生のブラウザからも動画が見えてしまう');
    }
    await cp.locator('#ztext').fill('ご応募ありがとうございます。試験用の返信です。面接の日程を決めましょう。');
    await cp.locator('button[aria-label="送信"]').click();
    await cp.waitForTimeout(2500);
    const msgs = Number(php(`echo count( get_posts( array( 'post_type' => 'zaito_message', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => -1, 'meta_query' => array( array( 'key' => 'conversation_id', 'value' => ${appId} ) ) ) ) );`));
    ok('9d 企業からの返信', `メッセージの数 ${msgs}`);
    await cp.locator('#zstatus select[name=status]').selectOption('offer');
    cp.once('dialog', d => d.accept());
    await submit(cp, cp.locator('#zstatus'), '変更');
    await shot(cp, '12_company_offer');
    const ast = php(`echo get_post_meta( ${appId}, 'status', true );`);
    ast === 'offer' ? ok('9e 内定', '応募の状態が「内定」になった') : ng('9e 内定', '応募の状態が ' + ast);

    // 10. 学生が返信を見る
    await go(sp, `/chat/?c=${appId}`);
    await shot(sp, '13_student_chat');
    const sbody = await sp.locator('body').innerText();
    sbody.includes('試験用の返信') ? ok('10 学生のメッセージ画面', '企業からの返信が見えている') : ng('10 学生のメッセージ画面', '企業からの返信が見えない');

    // 運営者が自分で試す用のGWCの試験用の求人があれば、学生から見た画面（トップ・詳細・応募ボタンを押したところ）も撮っておく。
    const gwc = Number(php(`$p = get_posts( array( 'post_type' => 'job_listing', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_zaito_stg_gwc', 'meta_value' => '1' ) ); echo $p ? $p[0] : 0;`) || 0);
    if (gwc && !VIDEO) {
      const g = await student.newPage();
      await go(g, `/remote/d${gwc}/`);
      await shot(g, 'gwc_1_detail');
      await g.locator('a,button', { hasText: 'この求人に応募する' }).filter({ visible: true }).first().click().catch(() => {});
      await g.waitForTimeout(800);
      await shot(g, 'gwc_2_signup', false);
      await g.close();
    }

    // 11. 運営の成果報酬の画面
    await adminTab('offers');
    await shot(ap, '14_admin_fees');
    (await ap.locator(`input[name=app_id][value="${appId}"]`).count()) ? ok('11 運営の成果報酬', '内定が成果報酬の一覧に出ている') : ng('11 運営の成果報酬', '成果報酬の一覧に見つからない');
    fs.writeFileSync(`${SHOTS}/${TAG}_state.json`, JSON.stringify({ coEmail, coId, jobId, stEmail }));
  } catch (e) {
    ng('中断', e.message.split('\n')[0]);
    for (const [n, p] of [['company', cp], ['admin', ap], ['student', sp]]) {
      await shot(p, 'zz_error_' + n);
      const t = (await p.locator('body').innerText().catch(() => '')).replace(/\s+/g, ' ').slice(0, 300);
      console.log(`   [${n}] ${p.url().replace(BASE, '')} 「${await p.title().catch(() => '')}」 ${t}`);
    }
  }
  await browser.close();
  const bad = results.filter(r => r[0] === 'NG').length;
  console.log(`\n結果: OK ${results.length - bad} / NG ${bad}`);
  process.exit(bad ? 1 : 0);
})();
