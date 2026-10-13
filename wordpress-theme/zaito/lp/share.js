/*
 * 「友達に教える」（.share）。LINE・X は普通のリンクなので、JSがなくても動く。
 * JSでは「リンクをコピー」と、スマホの共有メニュー（navigator.share）を足す。
 */
(function () {
  function track(how) { if (typeof window.gtag === 'function') window.gtag('event', 'share', { method: how }); }
  document.querySelectorAll('.share').forEach(function (box) {
    box.querySelectorAll('a[data-how]').forEach(function (a) {
      a.addEventListener('click', function () { track(a.getAttribute('data-how')); });
    });
    var copy = box.querySelector('[data-copy]');
    if (copy) {
      copy.hidden = false;
      copy.addEventListener('click', function () {
        var url = copy.getAttribute('data-copy');
        var done = function () { copy.textContent = 'コピーしました'; setTimeout(function () { copy.textContent = 'リンクをコピー'; }, 2000); track('copy'); };
        if (navigator.clipboard) navigator.clipboard.writeText(url).then(done, function () {});
      });
    }
    var native = box.querySelector('[data-native]');
    if (native && navigator.share) {
      native.hidden = false;
      native.addEventListener('click', function () {
        navigator.share({ title: native.getAttribute('data-title'), text: native.getAttribute('data-text'), url: native.getAttribute('data-native') })
          .then(function () { track('native'); }, function () {});
      });
    }
  });
})();
