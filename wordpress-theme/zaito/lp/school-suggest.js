/*
 * 学校名の入力欄（input[data-schools]）に、大学・短大の候補を出す。
 * 候補は lp/schools.json（[学校名, ひらがなの読み, 略称]）。入力欄を初めて触ったときに読み込む。
 * 漢字でも、変換前のひらがなでも、「東大」「早稲田」のような略称でも探せる。候補にない学校もそのまま入力できる。
 */
(function () {
  function hira(s) {
    return s.replace(/[ァ-ヶ]/g, function (c) { return String.fromCharCode(c.charCodeAt(0) - 0x60); });
  }
  // 旧字体や表記ゆれをそろえて比べる。
  function norm(s) {
    return hira(String(s).trim().toLowerCase())
      .replace(/[\s　]/g, '')
      .replace(/應/g, '応').replace(/學/g, '学').replace(/國/g, '国').replace(/澤/g, '沢').replace(/鷗/g, '鴎').replace(/龍/g, '竜').replace(/佛/g, '仏').replace(/獨/g, '独');
  }
  function setup(input) {
    var list = null;
    var loading = null;
    var box = document.createElement('ul');
    box.className = 'school-sg';
    box.id = input.id + '-sg';
    box.setAttribute('role', 'listbox');
    box.hidden = true;
    // 候補は入力欄の下に重ねて出す（質問の高さを変えない）。
    var wrap = document.createElement('span');
    wrap.className = 'school-wrap';
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);
    wrap.appendChild(box);
    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-controls', box.id);
    input.setAttribute('aria-expanded', 'false');
    input.setAttribute('autocomplete', 'off');
    var items = [];
    var cur = -1;

    function load() {
      if (!loading) {
        loading = fetch(input.getAttribute('data-schools'))
          .then(function (r) { return r.json(); })
          .then(function (rows) {
            list = rows.map(function (r) { return { name: r[0], n: norm(r[0]), k: r[1], a: r[2] ? norm(r[2]).split(/[ ,]/) : [] }; });
          })
          .catch(function () { list = []; });
      }
      return loading;
    }
    function close() {
      box.hidden = true;
      box.innerHTML = '';
      items = [];
      cur = -1;
      input.setAttribute('aria-expanded', 'false');
      input.removeAttribute('aria-activedescendant');
    }
    function pick(name) {
      input.value = name;
      close();
      input.dispatchEvent(new Event('change', { bubbles: true }));
    }
    function mark(i) {
      items.forEach(function (li, j) { li.setAttribute('aria-selected', j === i ? 'true' : 'false'); });
      cur = i;
      if (i >= 0) input.setAttribute('aria-activedescendant', items[i].id); else input.removeAttribute('aria-activedescendant');
    }
    function search() {
      var q = norm(input.value);
      if (!q || !list) { close(); return; }
      var hits = [];
      list.forEach(function (s) {
        var score = -1;
        if (s.n === q) score = 0;
        else if (s.a.indexOf(q) !== -1) score = 1;
        else if (s.n.indexOf(q) === 0 || s.k.indexOf(q) === 0) score = 2;
        else if (s.a.some(function (a) { return a.indexOf(q) === 0; })) score = 3;
        // 途中一致は、漢字なら2文字から、ひらがなは読みの切れ目と混ざりやすいので3文字から。
        else if (q.length >= 2 && s.n.indexOf(q) !== -1) score = 4;
        else if (q.length >= 3 && s.k.indexOf(q) !== -1) score = 5;
        if (score >= 0) hits.push([score, s.name.length, s.name]);
      });
      hits.sort(function (a, b) { return a[0] - b[0] || a[1] - b[1]; });
      hits = hits.slice(0, 6);
      // 入力がそのまま学校名と一致していれば、候補は出さない。
      if (!hits.length || (hits.length === 1 && hits[0][2] === input.value.trim())) { close(); return; }
      box.innerHTML = '';
      items = hits.map(function (h, i) {
        var li = document.createElement('li');
        li.id = box.id + '-' + i;
        li.setAttribute('role', 'option');
        li.textContent = h[2];
        // タップでキーボードが閉じる前に選べるよう、pointerdown で選ぶ。
        li.addEventListener('pointerdown', function (e) { e.preventDefault(); pick(h[2]); });
        box.appendChild(li);
        return li;
      });
      cur = -1;
      box.hidden = false;
      input.setAttribute('aria-expanded', 'true');
    }
    input.addEventListener('focus', load);
    input.addEventListener('input', function () { load().then(search); });
    input.addEventListener('keydown', function (e) {
      if (box.hidden || e.isComposing) return;
      if (e.key === 'ArrowDown') { e.preventDefault(); mark((cur + 1) % items.length); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); mark(cur <= 0 ? items.length - 1 : cur - 1); }
      // 候補を選んだEnterで、フォームの「次へ」まで進まないようにする。
      else if (e.key === 'Enter' && cur >= 0) { e.preventDefault(); e.stopPropagation(); pick(items[cur].textContent); }
      else if (e.key === 'Escape') { close(); }
    });
    input.addEventListener('blur', function () { setTimeout(close, 150); });
  }
  function init() { document.querySelectorAll('input[data-schools]').forEach(setup); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
