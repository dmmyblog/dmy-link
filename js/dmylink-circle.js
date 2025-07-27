/**
 * 实时把 .topic-content 内的外链换成跳转链接
 */
(function () {
  const ROOT = window.location.origin;
  const API = ROOT + '/wp-admin/admin-ajax.php';
  const DOMAIN = window.location.host;

  /** 判断纯外链（含 http/https ，且 host ≠ 当前域名） */
  function isExternal(href) {
    if (!href || (!href.startsWith('http://') && !href.startsWith('https://'))) return false;
    try { return new URL(href).host !== DOMAIN; } catch (e) { return false; }
  }

  function convert(a) {
    if (a.dataset.dmylinkDone) return;            // 避免重复处理
    const href = a.getAttribute('href');
    if (!isExternal(href)) return;

    fetch(API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ 
        action: 'dmylink_convert',
        url: href 
      })
    })
      .then(r => r.json())
      .then(data => { if (data.url) a.setAttribute('href', data.url); })
      .catch(console.error);

    a.dataset.dmylinkDone = '1';
  }

  function scan(root) {
    root.querySelectorAll('a[href]').forEach(convert);
  }

  // 初次扫描已经渲染好的 topic-content
  document.querySelectorAll('.topic-content').forEach(scan);

  // 监听后续 DOM 变化（B2 圈子滚动加载切页都会触发）
  const ob = new MutationObserver(list => {
    list.forEach(m => {
      m.addedNodes.forEach(n => {
        if (n.nodeType !== 1) return;
        if (n.matches('.topic-content')) scan(n);
        n.querySelectorAll && n.querySelectorAll('.topic-content').forEach(scan);
      });
    });
  });
  ob.observe(document.body, { childList: true, subtree: true });
})();
