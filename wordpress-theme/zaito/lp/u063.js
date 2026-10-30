/* Lucian UX/UI Prompt #063 · MIT License (c) 2026 Seokwoooo · https://lucianlabs.dev/uxui-prompt/#063 */
/*
 * 登録直後の希望条件フォーム（form.profile[data-steps]）を、1問ずつ表示する形に組み替える。
 * - 答えた質問は上に小さなチップとしてたたまれ、押すとその質問に戻れる。上のバーが1問ごとに伸びる。
 * - 1つだけ選ぶ質問は、選ぶと自動で次へ進む。選ばずに「次へ」を押すと、欄がゆれて理由を表示する。答えたくない質問は「この質問はとばす」で進める。
 * - 最後の質問で、ページにもともとある送信ボタンを押す（保存とその後の動きはページ側の処理のまま）。
 * - 「学年・学校名」は、立場で学生を選んだときだけ質問に入る（.stu の hidden はページ側が切り替える）。
 * - data-required の質問（お名前）は、とばせない。その質問のあいだは「答えずに閉じる」（.skip）も隠す。
 * JSが動かないときは、元のフォーム（全部の質問が並んだ形）のまま使える。
 */
(function () {
  var NEXT = '次へ';
  var CHECK = '<i aria-hidden="true"><svg viewBox="0 0 10 10"><path d="M2 5.2l2 2 4-4.4"/></svg></i><span></span>';

  function init(form) {
    if (form.getAttribute('data-u063')) return;
    form.setAttribute('data-u063', '1');
    form.classList.add('u063');
    var all = Array.prototype.slice.call(form.querySelectorAll('.pq'));
    if (!all.length) return;
    var orig = form.querySelector('button[type=submit]');
    var finalText = orig ? orig.textContent.trim() : '保存する';
    if (orig) orig.classList.add('u063-orig');
    var uid = 'u063' + Math.random().toString(36).slice(2, 7);

    var bar = document.createElement('div');
    bar.className = 'u063-bar';
    bar.setAttribute('aria-hidden', 'true');
    var chips = document.createElement('div');
    chips.className = 'u063-chips';
    chips.setAttribute('role', 'group');
    chips.setAttribute('aria-label', '答えた内容（押すと選び直せます）');
    var stage = document.createElement('div');
    stage.className = 'u063-stage';
    all[0].parentNode.insertBefore(bar, all[0]);
    bar.parentNode.insertBefore(chips, all[0]);
    chips.parentNode.insertBefore(stage, all[0]);

    all.forEach(function (q, i) {
      stage.appendChild(q);
      q.classList.add('u063-q', 'wait');
      q.inert = true;
      var sm = document.createElement('small');
      q.insertBefore(sm, q.firstChild);
      q.querySelectorAll('.chips,input[type=text]').forEach(function (el) { el.classList.add('u063-in'); });
      var msg = document.createElement('div');
      msg.className = 'u063-msg';
      msg.id = uid + 'm' + i;
      msg.setAttribute('aria-live', 'polite');
      msg.textContent = q.getAttribute('data-hint') || '';
      q.appendChild(msg);
      q.querySelectorAll('fieldset,input[type=text]').forEach(function (el) { el.setAttribute('aria-describedby', msg.id); });
      if (q.getAttribute('data-required')) return;
      var sk = document.createElement('button');
      sk.type = 'button';
      sk.className = 'u063-skipq';
      sk.textContent = 'この質問はとばす';
      sk.addEventListener('click', function () { skip(q); });
      q.appendChild(sk);
    });

    var next = document.createElement('button');
    next.type = 'button';
    next.className = 'u063-next';
    next.innerHTML = '<span>' + NEXT + '</span><svg viewBox="0 0 18 18" aria-hidden="true"><path d="M4 9h10M10 5l4 4-4 4"/></svg>';
    stage.parentNode.insertBefore(next, stage.nextSibling);

    var cur = 0, vals = {}, finalizing = false;
    function active() { return all.filter(function (q) { return !q.hidden; }); }
    function title(q) { var l = q.querySelector('legend'); return l ? l.textContent.replace(/（.*?）/g, '') : ''; }
    function fit() {
      var q = active()[cur];
      if (q) stage.style.height = q.offsetHeight + 'px';
    }
    function bars(act) {
      if (bar.children.length !== act.length) {
        bar.innerHTML = '';
        act.forEach(function () { bar.appendChild(document.createElement('i')); });
      }
      Array.prototype.forEach.call(bar.children, function (b, k) { b.classList.toggle('on', k < cur || finalizing); });
    }
    function show(i) {
      var act = active();
      cur = i;
      all.forEach(function (q) {
        var k = act.indexOf(q);
        q.classList.toggle('out', k !== -1 && k < i);
        q.classList.toggle('wait', k === -1 || k > i);
        q.inert = k !== i;
        if (k !== -1) q.querySelector('small').textContent = (k + 1) + ' / ' + act.length;
      });
      bars(act);
      form.classList.toggle('u063-lock', !!(act[i] && act[i].getAttribute('data-required')));
      next.querySelector('span').textContent = i === act.length - 1 ? finalText : NEXT;
      fit();
      setTimeout(function () {
        if (!form.isConnected || cur !== i || form.hidden) return;
        var first = act[i].querySelector('input:checked') || act[i].querySelector('input:not([type=hidden])');
        if (first) first.focus({ preventScroll: true });
      }, 350);
    }
    function answer(q) {
      var step = q.getAttribute('data-step');
      if (step === 'interests') {
        var picked = Array.prototype.map.call(q.querySelectorAll('input:checked'), function (el) { return el.value; });
        return picked.length ? picked[0] + (picked.length > 1 ? ' ほか' + (picked.length - 1) : '') : '';
      }
      if (step === 'name') {
        return Array.prototype.map.call(q.querySelectorAll('input[type=text]'), function (el) { return el.value.trim(); }).join(' ').trim();
      }
      var parts = [];
      q.querySelectorAll('input:checked').forEach(function (el) { parts.push(el.value); });
      q.querySelectorAll('input[type=text]').forEach(function (el) { if (el.value.trim()) parts.push(el.value.trim()); });
      return parts.join('・');
    }
    function renderChips() {
      chips.innerHTML = '';
      var act = active();
      act.forEach(function (q, i) {
        var v = vals[q.getAttribute('data-step')];
        if (!v || i >= cur) return;
        var c = document.createElement('button');
        c.type = 'button';
        // .skip は remote.css の「答えずに閉じる」リンクと名前がぶつかるため is-skip にする。
        c.className = 'u063-chip' + (v.skip ? ' is-skip' : '');
        c.style.animationDelay = (i === cur - 1 ? 0 : -1) + 's';
        c.innerHTML = CHECK;
        c.querySelector('span').textContent = v.text;
        c.setAttribute('aria-label', '「' + title(q) + '」を選び直す（いま: ' + v.text + '）');
        c.addEventListener('click', function () { next.disabled = false; show(i); renderChips(); });
        chips.appendChild(c);
      });
    }
    function go(n) {
      var act = active();
      if (n >= act.length) { finish(); return; }
      show(n);
      renderChips();
    }
    function bad(q, text) {
      var msg = q.querySelector('.u063-msg');
      q.classList.remove('bad');
      void q.offsetWidth;
      q.classList.add('bad');
      msg.textContent = text;
      q.querySelectorAll('fieldset,input[type=text]').forEach(function (el) { el.setAttribute('aria-invalid', 'true'); });
      var first = q.querySelector('input:not([type=hidden])');
      if (first) first.focus({ preventScroll: true });
    }
    function ok(q) {
      q.classList.remove('bad');
      q.querySelector('.u063-msg').textContent = q.getAttribute('data-hint') || '';
      q.querySelectorAll('[aria-invalid]').forEach(function (el) { el.removeAttribute('aria-invalid'); });
    }
    function advance() {
      var q = active()[cur];
      if (!q) return;
      var t = answer(q);
      if (q.getAttribute('data-required') && Array.prototype.some.call(q.querySelectorAll('input[type=text]'), function (el) { return !el.value.trim(); })) {
        bad(q, '姓と名を入力してください');
        return;
      }
      if (!t && !q.getAttribute('data-optional')) {
        bad(q, (q.getAttribute('data-step') === 'interests' ? '1つ以上選んでください' : '1つ選んでください') + '（答えたくないときは「この質問はとばす」）');
        return;
      }
      ok(q);
      vals[q.getAttribute('data-step')] = { text: t || title(q) + '：未回答', skip: !t };
      go(cur + 1);
    }
    function skip(q) {
      q.querySelectorAll('input:checked').forEach(function (el) { el.checked = false; });
      q.querySelectorAll('input[type=text]').forEach(function (el) { el.value = ''; });
      if (q.getAttribute('data-step') === 'role') {
        // 立場をとばしたら、学生向けの質問も出さない。
        var stu = form.querySelector('.stu');
        if (stu) stu.hidden = true;
      }
      ok(q);
      vals[q.getAttribute('data-step')] = { text: title(q) + '：未回答', skip: true };
      go(cur + 1);
    }
    function finish() {
      finalizing = true;
      bars(active());
      renderChips();
      next.disabled = true;
      if (orig) {
        if (form.requestSubmit) form.requestSubmit(orig); else orig.click();
      }
    }

    // Enterや、ページの送信処理より先に受け取り、最後の質問までは「次へ」として扱う。
    document.addEventListener('submit', function (e) {
      if (e.target !== form || finalizing) return;
      e.preventDefault();
      e.stopImmediatePropagation();
      advance();
    }, true);
    form.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && e.target.tagName === 'INPUT') { e.preventDefault(); advance(); }
    });
    var autoT = 0;
    form.addEventListener('change', function (e) {
      var q = e.target.closest('.u063-q');
      if (q && q.classList.contains('bad')) ok(q);
      // 1つだけ選ぶ質問（立場・日数・時給）は、選んだら少し待って自動で次へ進む。
      // 学年は学校名も入力するので進めない。複数選べる「やってみたい仕事」も進めない。
      if (q && e.target.type === 'radio' && q.getAttribute('data-step') !== 'stu' && active()[cur] === q) {
        clearTimeout(autoT);
        autoT = setTimeout(function () { if (active()[cur] === q) advance(); }, 320);
      }
      // 立場を変えると「学年・学校名」の質問が増減するので、数と高さをそろえ直す。
      // 学生以外に変えたら、学年・学校名の答えも消す。
      if (e.target.name === 'role') setTimeout(function () {
        var stu = form.querySelector('.stu');
        if (stu && stu.hidden) {
          stu.querySelectorAll('input:checked').forEach(function (el) { el.checked = false; });
          stu.querySelectorAll('input[type=text]').forEach(function (el) { el.value = ''; });
          delete vals.stu;
        }
        show(cur);
      }, 0);
    });
    next.addEventListener('click', advance);
    if ('ResizeObserver' in window) {
      var ro = new ResizeObserver(fit);
      all.forEach(function (q) { ro.observe(q); });
    }
    window.addEventListener('resize', fit);
    show(0);
  }

  document.querySelectorAll('form[data-steps]').forEach(init);
})();
