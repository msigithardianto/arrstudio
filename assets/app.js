/* ============================================================
 *  HTML → Roblox Converter — Frontend v3.6 (SPA-ready FINAL)
 * ============================================================ */
(function () {
'use strict';

const CANVAS_W = 800;
const CANVAS_H = 600;
const $ = id => document.getElementById(id);

let currentTab = 'script';
let cache = {
  script: '', fullscript: '', tree: '', rbxmx: '',
  plugin: '', billboard: '', report: ''
};
let lastNodes = [];
let viewMode = 'html';
let zoom = 1;
let autoFit = true;
let convertToken = 0;
let isInitialized = false;

/* ============================================================
   LAZY REFS — ambil fresh setiap dipakai
   ============================================================ */
const ref = {
  get htmlIn()            { return $('htmlIn'); },
  get frame()             { return $('previewFrame'); },
  get frameWrap()         { return $('previewFrameWrap'); },
  get robloxWrap()        { return $('robloxFrameWrap'); },
  get robloxCanvas()      { return $('robloxCanvas'); },
  get previewStage()      { return $('previewStage'); },
  get outputBody()        { return $('outputBody'); },
  get badgeNodes()        { return $('badgeNodes'); },
  get badgeWarn()         { return $('badgeWarn'); },
  get luaLines()          { return $('luaLines'); },
  get luaStatus()         { return $('luaStatus'); },
  get zoomLabel()         { return $('zoomLabel'); },
  get vscodeHighlight()   { return $('vscodeHighlight'); },
  get vscodeGutter()      { return $('vscodeGutter'); },
  get vscodeMinimapContent() { return $('vscodeMinimapContent'); },
  get vscodeMinimapViewport() { return $('vscodeMinimapViewport'); },
  get vscodeMinimap()     { return $('vscodeMinimap'); },
  get vscodeCursor()      { return $('vscodeCursor'); },
  get vscodeErrors()      { return $('vscodeErrors'); },
  get vscodeWarnings()    { return $('vscodeWarnings'); },
  get btnToggleMinimap()  { return $('btnToggleMinimap'); },
};

function isConverterPage() {
  return !!document.getElementById('htmlIn');
}

/* ============================================================
   API CALLS
   ============================================================ */
async function apiConvert(html, rectMap) {
  const res = await fetch('api/convert.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ html, rectMap }),
  });
  if (!res.ok) throw new Error('convert.php: ' + res.status);
  return res.json();
}

async function apiGenerate(nodes, billboardConfig = null) {
  const payload = { nodes, canvasW: CANVAS_W, canvasH: CANVAS_H };
  if (billboardConfig) payload.billboard = billboardConfig;

  const res = await fetch('api/generate.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!res.ok) throw new Error('generate.php: ' + res.status);
  return res.json();
}

/* ============================================================
   VIEW MODE
   ============================================================ */
function setViewMode(mode) {
  viewMode = mode;
  const isHtml = mode === 'html';
  const fw = ref.frameWrap;
  const rw = ref.robloxWrap;
  if (fw) fw.classList.toggle('hidden', !isHtml);
  if (rw) rw.classList.toggle('hidden', isHtml);
  const vh = $('viewHTML'); if (vh) vh.classList.toggle('active', isHtml);
  const vr = $('viewRBX'); if (vr) vr.classList.toggle('active', !isHtml);
  if (!isHtml) renderRobloxPreview(lastNodes);
  applyZoom();
}

function applyZoom() {
  const wrap = viewMode === 'html' ? ref.frameWrap : ref.robloxWrap;
  if (!wrap) return;
  wrap.style.transform = `scale(${zoom})`;
  wrap.style.width = CANVAS_W + 'px';
  wrap.style.height = CANVAS_H + 'px';
  wrap.style.marginBottom = ((CANVAS_H * zoom) - CANVAS_H) + 'px';
  wrap.style.marginRight = ((CANVAS_W * zoom) - CANVAS_W) + 'px';
  const zl = ref.zoomLabel;
  if (zl) zl.textContent = Math.round(zoom * 100) + '%';
}

function computeFitZoom() {
  const stage = ref.previewStage;
  if (!stage) return 1;
  const pad = 48;
  const availW = stage.clientWidth - pad;
  const availH = stage.clientHeight - pad;
  return Math.min(availW / CANVAS_W, availH / CANVAS_H, 1);
}

function fitToScreen() { autoFit = true; zoom = computeFitZoom(); applyZoom(); }
function zoomIn()  { autoFit = false; zoom = Math.min(zoom + 0.1, 3); applyZoom(); }
function zoomOut() { autoFit = false; zoom = Math.max(zoom - 0.1, 0.1); applyZoom(); }

window.addEventListener('resize', () => {
  if (autoFit) { zoom = computeFitZoom(); applyZoom(); }
});

/* ============================================================
   CONVERT FLOW
   ============================================================ */
async function convert() {
  const htmlIn = ref.htmlIn;
  const frame  = ref.frame;
  const outputBody = ref.outputBody;
  const badgeNodes = ref.badgeNodes;
  const badgeWarn  = ref.badgeWarn;
  const luaLines   = ref.luaLines;
  const robloxCanvas = ref.robloxCanvas;

  // Guard: kalau elemen nggak ada (bukan di converter), skip
  if (!htmlIn || !frame || !outputBody) return;

  const myToken = ++convertToken;

  const html = htmlIn.value;
  if (!html.trim()) {
    outputBody.innerHTML = '<span class="tok-cmt">-- input kosong --</span>';
    if (badgeNodes) badgeNodes.textContent = '0 nodes';
    if (badgeWarn) badgeWarn.classList.add('hidden');
    if (luaLines) luaLines.textContent = '0 lines';
    if (robloxCanvas) robloxCanvas.innerHTML = '';
    lastNodes = [];
    return;
  }

  if (myToken !== convertToken) return;

  frame.style.width = CANVAS_W + 'px';
  frame.style.height = CANVAS_H + 'px';
  const fd = frame.contentDocument;
  if (!fd) {
    console.error('frame.contentDocument null — iframe belum siap');
    return;
  }

  await new Promise((resolve) => {
    let resolved = false;
    const done = () => { if (!resolved) { resolved = true; resolve(); } };
    frame.onload = done;

    fd.open();
    fd.write(`<!DOCTYPE html><html><head><meta charset="utf-8"><style>
      html,body{margin:0;padding:0;width:${CANVAS_W}px;height:${CANVAS_H}px;overflow:hidden;background:#fff;font-family:Arial,sans-serif;position:relative;}
      *{box-sizing:border-box;}
    </style></head><body>${html}</body></html>`);
    fd.close();
    setTimeout(done, 300);
  });

  if (myToken !== convertToken) return;
  await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));
  if (myToken !== convertToken) return;

  let rectMap = [];
  try {
    rectMap = await measureRectMap(fd.body, fd);
  } catch (e) {
    console.error('Measure error:', e);
  }

  if (myToken !== convertToken) return;

  try {
    const parsed = await apiConvert(html, rectMap);
    if (parsed.error) throw new Error(parsed.error);
    if (myToken !== convertToken) return;
    lastNodes = parsed.nodes;

    const bbConfig = (typeof getBillboardConfig === 'function') ? getBillboardConfig() : null;
    const gen = await apiGenerate(parsed.nodes, bbConfig);
    if (gen.error) throw new Error(gen.error);
    if (myToken !== convertToken) return;

    cache.script     = gen.script     || '';
    cache.fullscript = gen.fullscript || '';
    cache.tree       = gen.tree       || '';
    cache.rbxmx      = gen.rbxmx      || '';
    cache.plugin     = gen.plugin     || '';
    cache.billboard  = gen.billboard  || '';
    cache.report     = gen.report     || '';

    renderTab();
    renderRobloxPreview(lastNodes);
    if (badgeNodes) badgeNodes.textContent = lastNodes.length + ' nodes';

    const totalIssues = lastNodes.reduce((s,n) => s + (n.unsupported ? n.unsupported.length : 0), 0);
    if (badgeWarn) {
      if (totalIssues > 0) {
        badgeWarn.textContent = totalIssues + ' warn';
        badgeWarn.classList.remove('hidden');
      } else {
        badgeWarn.classList.add('hidden');
      }
    }

    const luaStatus = ref.luaStatus;
    if (luaStatus) luaStatus.textContent = 'Generated';
    if (autoFit) { zoom = computeFitZoom(); applyZoom(); }
  } catch (e) {
    console.error(e);
    if (myToken === convertToken && outputBody) {
      outputBody.innerHTML = `<span class="tok-cmt">-- error: ${escapeHtml(e.message)} --</span>`;
      const luaStatus = ref.luaStatus;
      if (luaStatus) luaStatus.textContent = 'Error: ' + e.message;
    }
  }
}

async function measureRectMap(rootEl, iframeDoc) {
  const allEls = [rootEl, ...rootEl.querySelectorAll('*')];

  const hiddenSet = new Set();
  for (const el of allEls) {
    if (el.nodeType !== 1) continue;
    const s = el.style;
    if (s.display === 'none' || s.visibility === 'hidden') hiddenSet.add(el);
  }

  const hiddenRoots = new Set();
  for (const el of hiddenSet) {
    let parent = el.parentElement;
    let parentHidden = false;
    while (parent && parent !== rootEl) {
      if (hiddenSet.has(parent)) { parentHidden = true; break; }
      parent = parent.parentElement;
    }
    if (!parentHidden) hiddenRoots.add(el);
  }

  const saved = [];
  for (const el of hiddenSet) {
    saved.push({ el, display: el.style.display, visibility: el.style.visibility });
    el.style.display = '';
    el.style.visibility = '';
  }

  const transformSaved = [];
  for (const el of allEls) {
    if (el.nodeType !== 1) continue;
    const t = el.style.transform;
    if (t && t !== 'none') {
      transformSaved.push({ el, transform: t });
      el.style.transform = 'none';
    }
  }

  const overflowSaved = [];
  for (const el of allEls) {
    if (el.nodeType !== 1) continue;
    const oy = el.style.overflowY;
    const o = el.style.overflow;
    if (oy === 'auto' || oy === 'scroll' || o === 'auto' || o === 'scroll') {
      overflowSaved.push({ el, overflowY: oy, overflow: o });
      el.style.overflowY = 'visible';
      el.style.overflow = 'visible';
    }
  }

  void rootEl.offsetHeight;
  void iframeDoc.body.offsetHeight;

  await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(() => setTimeout(r, 30))));

  const containerRect = rootEl.getBoundingClientRect();
  const map = [];

  function walk(el, parentIdx) {
    if (el.nodeType !== Node.ELEMENT_NODE) return;
    const tag = el.tagName.toLowerCase();
    const supported = ['div','span','p','button','input','img','h1','h2','h3','h4','h5','h6',
                       'a','label','ul','ol','li','section','header','footer','main',
                       'nav','aside','article','textarea','select'];
    if (!supported.includes(tag)) {
      for (const c of el.children) walk(c, parentIdx);
      return;
    }
    if (tag === 'option') return;

    let x = 0, y = 0;
    let cur = el;
    while (cur && cur !== rootEl) {
      x += cur.offsetLeft || 0;
      y += cur.offsetTop || 0;
      cur = cur.offsetParent;
    }

    const r = el.getBoundingClientRect();
    const rectX = Math.round(r.left - containerRect.left);
    const rectY = Math.round(r.top - containerRect.top);

    const finalX = (x > 0 || rectX === 0) ? Math.round(x) : rectX;
    const finalY = (y > 0 || rectY === 0) ? Math.round(y) : rectY;

    const idx = map.length;
    const wasHidden = hiddenRoots.has(el);

    map.push({
      idx, parentIdx,
      x: finalX, y: finalY,
      w: Math.round(r.width), h: Math.round(r.height),
      selfHidden: wasHidden,
      id: el.id || '',
      className: (typeof el.className === 'string') ? el.className : '',
    });

    for (const c of el.children) walk(c, idx);
  }

  for (const c of rootEl.children) walk(c, -1);

  for (const s of transformSaved) s.el.style.transform = s.transform;
  for (const s of overflowSaved) {
    s.el.style.overflowY = s.overflowY;
    s.el.style.overflow = s.overflow;
  }
  for (const s of saved) {
    s.el.style.display = s.display;
    s.el.style.visibility = s.visibility;
  }

  return map;
}

/* ============================================================
   ROBLOX PREVIEW RENDERER
   ============================================================ */
function renderRobloxPreview(nodes) {
  const robloxCanvas = ref.robloxCanvas;
  if (!robloxCanvas) return;
  robloxCanvas.innerHTML = '';
  if (!nodes || !nodes.length) return;
  const FONT = `'Source Sans 3','Segoe UI',Roboto,sans-serif`;

  const idToNode = {};
  nodes.forEach(n => idToNode[n.id] = n);

  const absPos = (n) => {
    let x = n.x, y = n.y;
    let cur = n;
    while (cur.parentId) {
      cur = idToNode[cur.parentId];
      if (!cur) break;
      x += cur.x; y += cur.y;
    }
    return { x, y };
  };

  const depthOf = (n) => {
    let d = 0, cur = n;
    while (cur && cur.parentId) {
      cur = idToNode[cur.parentId];
      d++;
    }
    return d;
  };
  const sorted = [...nodes].sort((a, b) => {
    const da = depthOf(a), db = depthOf(b);
    if (da !== db) return da - db;
    return a.id - b.id;
  });

  const domMap = {};
  for (const n of sorted) {
    const pos = absPos(n);
    const el = document.createElement('div');
    el.style.position = 'absolute';
    el.style.left = pos.x + 'px';
    el.style.top = pos.y + 'px';
    el.style.width = n.w + 'px';
    el.style.height = n.h + 'px';
    el.style.boxSizing = 'border-box';
    el.style.overflow = 'visible';
    el.style.pointerEvents = 'auto';
    el.style.zIndex = String(n.id);
    el.style.transition = 'opacity .35s ease, transform .35s cubic-bezier(.22,1,.36,1)';

    if (n.gradient && n.gradient.keypoints) {
      const stops = n.gradient.keypoints.map(kp => {
        const c = `rgba(${Math.round(kp.color.r*255)},${Math.round(kp.color.g*255)},${Math.round(kp.color.b*255)},${kp.color.a})`;
        return `${c} ${(kp.pos*100).toFixed(1)}%`;
      }).join(', ');
      el.style.background = `linear-gradient(${n.gradient.rotationCss}deg, ${stops})`;
    } else {
      el.style.background = `rgba(${Math.round(n.bg.r*255)},${Math.round(n.bg.g*255)},${Math.round(n.bg.b*255)},${n.bg.a})`;
    }

    el.style.borderRadius = n.radius + 'px';

    if (n.selfHidden) {
      el.style.opacity = '0';
      el.style.pointerEvents = 'none';
      el.style.transform = 'translateX(30px)';
    }

    if (n.borderW > 0 && n.borderColor.a > 0.001) {
      const bs = Math.max(1, Math.round(n.borderW));
      el.style.border = `${bs}px solid rgba(${Math.round(n.borderColor.r*255)},${Math.round(n.borderColor.g*255)},${Math.round(n.borderColor.b*255)},${n.borderColor.a})`;
    }

    const isText = ['TextLabel','TextButton','TextBox'].includes(n.robloxClass);
    if (isText) {
      const txt = n.text || n.value || n.placeholder || '';
      const isBold = (parseInt(n.fontWeight) || 400) >= 700;
      el.style.display = 'flex';
      el.style.flexDirection = 'column';
      el.style.justifyContent = 'center';
      el.style.alignItems = n.textAlign === 'center' ? 'center'
        : (n.textAlign === 'right' || n.textAlign === 'end') ? 'flex-end' : 'flex-start';
      el.style.padding = `${n.padT}px ${n.padR}px ${n.padB}px ${n.padL}px`;
      el.style.fontFamily = FONT;
      const span = document.createElement('span');
      span.textContent = txt;
      span.style.color = `rgba(${Math.round(n.fg.r*255)},${Math.round(n.fg.g*255)},${Math.round(n.fg.b*255)},${n.fg.a})`;
      span.style.fontFamily = FONT;
      span.style.fontWeight = isBold ? '700' : '400';
      span.style.fontSize = Math.max(6, Math.round(n.fontSize * 0.88)) + 'px';
      span.style.lineHeight = '1.2';
      span.style.whiteSpace = 'pre-wrap';
      span.style.wordBreak = 'break-word';
      span.style.textAlign = n.textAlign === 'center' ? 'center'
        : (n.textAlign === 'right' || n.textAlign === 'end') ? 'right' : 'left';
      span.style.width = '100%';
      el.appendChild(span);
      if (n.robloxClass === 'TextButton') el.style.cursor = 'pointer';
      if (n.robloxClass === 'TextBox') el.style.cursor = 'text';
    }

    if (n.robloxClass === 'ImageLabel' && n.src) {
      const img = document.createElement('img');
      img.src = n.src;
      img.style.width = '100%';
      img.style.height = '100%';
      img.style.objectFit = 'fill';
      el.appendChild(img);
    }

    el.title = `${n.robloxClass} "${n.name}"${n.selfHidden ? ' [hidden]' : ''}`;
    robloxCanvas.appendChild(el);
    domMap[n.id] = el;

    if (n.transition && (n.robloxClass === 'TextButton' || n.isButtonLike)) {
      const baseX = pos.x;
      el.addEventListener('mouseenter', () => { el.style.left = (baseX - 3) + 'px'; });
      el.addEventListener('mouseleave', () => { el.style.left = baseX + 'px'; });
    }
  }

  const buttons = sorted.filter(n => n.robloxClass === 'TextButton' || n.robloxClass === 'ImageButton');
  const hiddenPanels = sorted.filter(n =>
    n.selfHidden &&
    ['Frame','ScrollingFrame'].includes(n.robloxClass) &&
    (n.action !== 'close')
  );

  const usedBtn = new Set();
  const usedPanel = new Set();
  const pairs = [];
  const norm = (s) => String(s || '').toLowerCase().replace(/[^a-z0-9]/g, '');

  hiddenPanels.forEach(panel => {
    if (usedPanel.has(panel.id)) return;
    const panelKeys = [norm(panel.sourceId), norm(panel.id), norm(panel.name)].filter(Boolean);
    let btn = null;

    const sid = norm(panel.sourceId);
    if (sid) {
      btn = buttons.find(b =>
        !usedBtn.has(b.id) && b.action === 'toggle' && norm(b.target) === sid
      );
    }
    if (!btn) {
      btn = buttons.find(b => {
        if (usedBtn.has(b.id)) return false;
        if (b.action !== 'toggle') return false;
        const tgt = norm(b.target);
        if (!tgt) return false;
        return panelKeys.some(pk => pk.includes(tgt) || tgt.includes(pk));
      });
    }
    if (!btn) {
      const panelRoot = (panel.name || '').replace(/Panel\d*$|Popup$|Menu$|Container$|Frame$|Backdrop$/i, '');
      btn = buttons.find(b => {
        if (usedBtn.has(b.id)) return false;
        if (b.action !== 'toggle') return false;
        const btnRoot = (b.name || '').replace(/Button$|Toggle$|Btn$|Icon$/i, '');
        return btnRoot && panelRoot && btnRoot.toLowerCase() === panelRoot.toLowerCase();
      });
    }
    if (btn) {
      usedBtn.add(btn.id);
      usedPanel.add(panel.id);
      pairs.push({ btn, panel });
    }
  });

  const pairByBtn = {};
  pairs.forEach(({ btn, panel }) => {
    if (!pairByBtn[btn.id]) pairByBtn[btn.id] = { btn, panels: [] };
    pairByBtn[btn.id].panels.push(panel);
  });

  Object.values(pairByBtn).forEach(({ btn, panels }) => {
    const btnEl = domMap[btn.id];
    if (!btnEl) return;
    let open = false;
    panels.forEach(p => {
      const el = domMap[p.id];
      if (!el) return;
      const pos = absPos(p);
      el.style.left = (pos.x + 30) + 'px';
    });
    btnEl.addEventListener('click', (e) => {
      e.stopPropagation();
      open = !open;
      panels.forEach(p => {
        const el = domMap[p.id];
        if (!el) return;
        const pos = absPos(p);
        if (open) {
          el.style.opacity = '1';
          el.style.pointerEvents = 'auto';
          el.style.transform = 'translateX(0)';
          requestAnimationFrame(() => { el.style.left = pos.x + 'px'; });
        } else {
          el.style.opacity = '0';
          el.style.pointerEvents = 'none';
          el.style.transform = 'translateX(30px)';
          el.style.left = (pos.x + 30) + 'px';
        }
      });
    });
  });

  sorted.forEach(n => {
    if (n.action !== 'close') return;
    if (!n.selfHidden) return;
    const el = domMap[n.id];
    if (!el) return;
    const tgtNorm = norm(n.target);
    if (!tgtNorm) return;
    const panel = hiddenPanels.find(p => norm(p.sourceId) === tgtNorm);
    if (!panel) return;
    const panelEl = domMap[panel.id];
    if (!panelEl) return;
    el.addEventListener('click', (e) => {
      e.stopPropagation();
      panelEl.style.opacity = '0';
      panelEl.style.pointerEvents = 'none';
      panelEl.style.transform = 'translateX(30px)';
    });
  });
}

/* ============================================================
   OUTPUT TABS
   ============================================================ */
function renderTab() {
  const outputBody = ref.outputBody;
  if (!outputBody) return;
  const content = cache[currentTab] || '';
  if (currentTab === 'rbxmx' || currentTab === 'plugin' || currentTab === 'billboard') {
    outputBody.innerHTML = highlightLua(content);
  } else if (currentTab === 'script' || currentTab === 'fullscript') {
    outputBody.innerHTML = renderCodeWithLines(content, highlightLua);
  } else {
    outputBody.innerHTML = escapeHtml(content);
  }
  const luaLines = ref.luaLines;
  if (luaLines) luaLines.textContent = content.split('\n').length + ' lines';
  document.querySelectorAll('.tab').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.tab === currentTab);
  });
}

function switchTab(tab) {
  currentTab = tab;
  renderTab();

  const bbConfig = document.getElementById('billboardConfig');
  if (bbConfig) {
    bbConfig.classList.toggle('hidden', tab !== 'billboard');
    if (tab === 'billboard' && lastNodes.length && !cache.billboard) {
      setTimeout(() => regenerateBillboard(), 200);
    }
  }
  const pluginBar = document.getElementById('pluginActionBar');
  if (pluginBar) pluginBar.classList.toggle('hidden', tab !== 'plugin');

  const copyBtn = document.getElementById('copyBtn');
  if (copyBtn) copyBtn.classList.toggle('btn-copy-active', !!cache[tab]);
}

function copyCurrent() {
  const txt = cache[currentTab] || '';
  if (!txt) {
    if (typeof showToast === 'function') showToast('Tab kosong — belum ada konten', 'error');
    return;
  }
  navigator.clipboard.writeText(txt).then(() => {
    const luaStatus = ref.luaStatus;
    if (luaStatus) luaStatus.textContent = '✓ Copied!';
    if (typeof showToast === 'function') showToast(`${currentTab} copied!`, 'success');
    setTimeout(() => { if (luaStatus) luaStatus.textContent = 'Ready'; }, 1200);
  });
}

function downloadFile(kind) {
  if (!lastNodes.length) return;
  let content = '', filename = '', mime = 'text/plain';
  if (kind === 'lua')       { content = cache.fullscript; filename = 'GeneratedUI_Full.lua'; }
  if (kind === 'rbxmx')     { content = cache.rbxmx;      filename = 'GeneratedUIPack.rbxmx'; mime = 'application/xml'; }
  if (kind === 'plugin')    { content = cache.plugin;     filename = 'ArrStudioImporter.lua'; }
  if (kind === 'billboard') { content = cache.billboard;  filename = 'BillboardNametag.lua'; }
  if (!content) return;

  const blob = new Blob([content], { type: mime + ';charset=utf-8' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);

  const luaStatus = ref.luaStatus;
  if (luaStatus) {
    luaStatus.textContent = `✓ ${filename} (${(content.length/1024).toFixed(1)} KB)`;
    setTimeout(() => luaStatus.textContent = 'Ready', 2500);
  }
}

/* ============================================================
   SAMPLES
   ============================================================ */
async function loadSample(which) {
  const htmlIn = ref.htmlIn;
  if (!htmlIn) return;
  const res = await fetch('samples/' + which + '.html');
  htmlIn.value = await res.text();
  syncEditorFromTextarea();
  convert();
}
function clearInput() {
  const htmlIn = ref.htmlIn;
  if (!htmlIn) return;
  htmlIn.value = '';
  syncEditorFromTextarea();
  convert();
}

/* ============================================================
   BILLBOARD
   ============================================================ */
function getBillboardConfig() {
  const g = (id, fallback) => {
    const el = document.getElementById(id);
    return el ? el.value : fallback;
  };
  return {
    name:        (g('bbName', 'Player') || 'Player').trim(),
    role:        (g('bbRole', 'Member') || 'Member').trim(),
    level:       parseInt(g('bbLevel', '1')) || 1,
    theme:       g('bbTheme', 'gold') || 'gold',
    offsetY:     parseFloat(g('bbOffsetY', '3.5')) || 3.5,
    maxDistance: parseInt(g('bbMaxDistance', '120')) || 120,
    size:        [220, 70],
  };
}

async function regenerateBillboard() {
  const luaStatus = ref.luaStatus;
  if (!lastNodes || !lastNodes.length) {
    if (luaStatus) luaStatus.textContent = '⚠️ Convert HTML dulu';
    setTimeout(() => { if (luaStatus) luaStatus.textContent = 'Ready'; }, 1800);
    return;
  }
  if (luaStatus) luaStatus.textContent = 'Generating billboard...';
  try {
    const gen = await apiGenerate(lastNodes, getBillboardConfig());
    if (gen.error) throw new Error(gen.error);
    cache.billboard = gen.billboard || '';
    if (currentTab === 'billboard') renderTab();
    if (luaStatus) luaStatus.textContent = '✓ Billboard updated';
    setTimeout(() => { if (luaStatus) luaStatus.textContent = 'Ready'; }, 1500);
  } catch (e) {
    console.error(e);
    if (luaStatus) luaStatus.textContent = 'Error: ' + e.message;
  }
}

/* ============================================================
   HIGHLIGHT & CODE VIEW
   ============================================================ */
const LUA_KEYWORDS = new Set(['and','break','do','else','elseif','end','false','for','function','goto','if','in','local','nil','not','or','repeat','return','then','true','until','while']);
const LUA_BUILTINS = new Set(['print','warn','error','assert','pcall','xpcall','type','typeof','tostring','tonumber','ipairs','pairs','next','select','unpack','setmetatable','getmetatable','rawget','rawset','require','tick','wait','spawn','delay','math','string','table','os','coroutine','task']);
const LUA_GLOBALS = new Set(['game','workspace','script','Instance','Enum','Color3','UDim2','UDim','Vector2','Vector3','CFrame','TweenInfo','TweenService','Players','RunService','UserInputService','ReplicatedStorage','ServerScriptService','StarterGui','StarterPlayer']);

function highlightLua(code) {
  const out = []; let i = 0;
  while (i < code.length) {
    const ch = code[i];
    if (ch === '-' && code[i+1] === '-') {
      if (code[i+2] === '[' && code[i+3] === '[') {
        let j = code.indexOf(']]', i+4);
        if (j === -1) j = code.length; else j += 2;
        out.push(`<span class="tok-cmt">${escapeHtml(code.slice(i,j))}</span>`);
        i = j; continue;
      }
      let j = code.indexOf('\n', i);
      if (j === -1) j = code.length;
      out.push(`<span class="tok-cmt">${escapeHtml(code.slice(i,j))}</span>`);
      i = j; continue;
    }
    if (ch === '"' || ch === "'") {
      let j = i+1;
      while (j < code.length && code[j] !== ch) { if (code[j] === '\\') j++; j++; }
      j++;
      out.push(`<span class="tok-str">${escapeHtml(code.slice(i,j))}</span>`);
      i = j; continue;
    }
    if (/[0-9]/.test(ch) && (i === 0 || !/[a-zA-Z_]/.test(code[i-1]))) {
      let j = i;
      while (j < code.length && /[0-9.]/.test(code[j])) j++;
      out.push(`<span class="tok-num">${escapeHtml(code.slice(i,j))}</span>`);
      i = j; continue;
    }
    if (/[a-zA-Z_]/.test(ch)) {
      let j = i;
      while (j < code.length && /[a-zA-Z0-9_]/.test(code[j])) j++;
      const word = code.slice(i, j);
      const prev = code[i-1];
      const nextNonSpace = code.slice(j).match(/^\s*[:(]/);
      if (LUA_KEYWORDS.has(word))        out.push(`<span class="tok-kw">${word}</span>`);
      else if (LUA_GLOBALS.has(word))    out.push(`<span class="tok-cls">${word}</span>`);
      else if ((prev === '.' || prev === ':') && nextNonSpace) out.push(`<span class="tok-mth">${word}</span>`);
      else if (LUA_BUILTINS.has(word))   out.push(`<span class="tok-blt">${word}</span>`);
      else if (nextNonSpace && prev !== '.' && prev !== ':') out.push(`<span class="tok-fn">${word}</span>`);
      else                                out.push(escapeHtml(word));
      i = j; continue;
    }
    if (/[+\-*/%^#=<>~]/.test(ch)) {
      out.push(`<span class="tok-op">${escapeHtml(ch)}</span>`);
      i++; continue;
    }
    out.push(escapeHtml(ch));
    i++;
  }
  return out.join('');
}

function renderCodeWithLines(code, highlighter) {
  const lines = code.split('\n');
  const gutterLines = lines.map((_, i) => i + 1).join('\n');
  const contentHtml = lines.map(line => {
    const highlighted = highlighter(line);
    return `<span class="code-line">${highlighted || '&nbsp;'}</span>`;
  }).join('\n');
  return `<div class="code-wrap"><div class="code-gutter">${gutterLines}</div><div class="code-content">${contentHtml}</div></div>`;
}

function escapeHtml(s) {
  return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

/* ============================================================
   VSCODE EDITOR
   ============================================================ */
function highlightHTML(code) {
  if (!code) return '';
  let html = escapeHtml(code);
  const tokens = [];

  html = html.replace(/&lt;!--[\s\S]*?--&gt;/g, (m) => {
    tokens.push(`<span class="tok-comment">${m}</span>`);
    return `\x00${tokens.length-1}\x00`;
  });
  html = html.replace(/=&quot;[^&]*?&quot;/g, (m) => {
    tokens.push(`<span class="tok-str">${m}</span>`);
    return `\x01${tokens.length-1}\x01`;
  });
  html = html.replace(/(&lt;\/?)([\w:-]+)/g, '$1<span class="tok-tag">$2</span>');
  html = html.replace(/(&lt;|\/&gt;|&gt;)/g, '<span class="tok-punct">$1</span>');
  html = html.replace(/(\s)([\w:-]+)(=)/g, '$1<span class="tok-attr">$2</span>$3');
  html = html.replace(/\x01(\d+)\x01/g, (_, i) => tokens[i]);
  html = html.replace(/\x00(\d+)\x00/g, (_, i) => tokens[i]);
  return html + '\n';
}

function syncEditorFromTextarea() {
  const htmlIn = ref.htmlIn;
  if (!htmlIn) return;
  const text = htmlIn.value;
  const lines = text.split('\n');

  const vscodeHighlight = ref.vscodeHighlight;
  if (vscodeHighlight) vscodeHighlight.innerHTML = highlightHTML(text);

  const vscodeGutter = ref.vscodeGutter;
  if (vscodeGutter) {
    vscodeGutter.innerHTML = lines.map((_, i) => `<div class="vscode-line-num">${i + 1}</div>`).join('');
  }
  const vscodeMinimapContent = ref.vscodeMinimapContent;
  if (vscodeMinimapContent) vscodeMinimapContent.textContent = text || '';

  const vscodeErrors = ref.vscodeErrors;
  if (vscodeErrors) {
    const openTags = (text.match(/<([a-z][\w-]*)(\s[^>]*)?>/gi) || [])
      .filter(t => !/\/>$/.test(t) && !/^<(br|hr|img|input|meta|link)/i.test(t));
    const closeTags = (text.match(/<\/[a-z][\w-]*>/gi) || []);
    const diff = Math.max(0, openTags.length - closeTags.length);
    vscodeErrors.textContent = diff;
  }
  const vscodeWarnings = ref.vscodeWarnings;
  if (vscodeWarnings) vscodeWarnings.textContent = 0;

  syncEditorScroll();
}

function syncEditorScroll() {
  const htmlIn = ref.htmlIn;
  if (!htmlIn) return;
  const top = htmlIn.scrollTop;
  const left = htmlIn.scrollLeft;

  const vscodeHighlight = ref.vscodeHighlight;
  if (vscodeHighlight) {
    vscodeHighlight.style.transform = `translate(${-left}px, ${-top}px)`;
    const contentW = Math.max(htmlIn.scrollWidth, htmlIn.clientWidth);
    const contentH = Math.max(htmlIn.scrollHeight, htmlIn.clientHeight);
    vscodeHighlight.style.width = contentW + 'px';
    vscodeHighlight.style.height = contentH + 'px';
  }
  const vscodeGutter = ref.vscodeGutter;
  if (vscodeGutter) vscodeGutter.style.transform = `translateY(${-top}px)`;

  updateMinimapViewport();
}

function updateMinimapViewport() {
  const vscodeMinimapViewport = ref.vscodeMinimapViewport;
  const vscodeMinimap = ref.vscodeMinimap;
  const htmlIn = ref.htmlIn;
  if (!vscodeMinimapViewport || !vscodeMinimap || !htmlIn) return;
  if (vscodeMinimap.classList.contains('collapsed')) return;

  const totalH = htmlIn.scrollHeight;
  const viewH  = htmlIn.clientHeight;
  const minimapH = vscodeMinimap.clientHeight - 16;
  if (minimapH <= 0) return;

  if (totalH <= viewH) {
    vscodeMinimapViewport.style.height = minimapH + 'px';
    vscodeMinimapViewport.style.top = '8px';
    return;
  }
  const ratio = minimapH / totalH;
  const vpTop = 8 + (htmlIn.scrollTop * ratio);
  const vpHeight = Math.max(20, viewH * ratio);
  vscodeMinimapViewport.style.top = vpTop + 'px';
  vscodeMinimapViewport.style.height = vpHeight + 'px';
}

function updateCursorPos() {
  const vscodeCursor = ref.vscodeCursor;
  const htmlIn = ref.htmlIn;
  if (!vscodeCursor || !htmlIn) return;
  const pos = htmlIn.selectionStart;
  const before = htmlIn.value.substring(0, pos);
  const line = before.split('\n').length;
  const col  = pos - before.lastIndexOf('\n');
  vscodeCursor.textContent = `Ln ${line}, Col ${col}`;

  const vscodeGutter = ref.vscodeGutter;
  if (vscodeGutter) {
    vscodeGutter.querySelectorAll('.vscode-line-num').forEach((el, i) =>
      el.classList.toggle('active', i === line - 1)
    );
  }
}

/* ============================================================
   RESIZERS
   ============================================================ */
function initResizers() {
  const main = document.querySelector('.app-main');
  const pInput = $('panelInput');
  const pOutput = $('panelOutput');
  if (!main || !pInput || !pOutput) return;
  if (main.dataset.resizersInit === '1') return;
  main.dataset.resizersInit = '1';

  let dragInfo = null;

  document.querySelectorAll('.resizer').forEach(rs => {
    rs.addEventListener('mousedown', startDrag);
    rs.addEventListener('touchstart', startDrag, { passive: false });
  });

  function startDrag(e) {
    e.preventDefault();
    const rs = e.currentTarget;
    dragInfo = {
      rs, which: rs.dataset.target,
      startX: e.touches ? e.touches[0].clientX : e.clientX,
      startInputW: pInput.offsetWidth,
      startOutputW: pOutput.offsetWidth,
      totalW: main.offsetWidth,
    };
    rs.classList.add('active');
    document.body.classList.add('dragging');
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('touchmove', onDrag, { passive: false });
    document.addEventListener('mouseup', endDrag);
    document.addEventListener('touchend', endDrag);
  }
  function onDrag(e) {
    if (!dragInfo) return;
    e.preventDefault();
    const x = e.touches ? e.touches[0].clientX : e.clientX;
    const dx = x - dragInfo.startX;
    if (dragInfo.which === 'input') {
      const newW = Math.max(200, Math.min(dragInfo.startInputW + dx, dragInfo.totalW - 500));
      pInput.style.flex = `0 0 ${newW}px`;
    } else if (dragInfo.which === 'output') {
      const newW = Math.max(220, Math.min(dragInfo.startOutputW - dx, dragInfo.totalW - 500));
      pOutput.style.flex = `0 0 ${newW}px`;
    }
    if (autoFit) { zoom = computeFitZoom(); applyZoom(); }
  }
  function endDrag() {
    if (dragInfo) dragInfo.rs.classList.remove('active');
    document.body.classList.remove('dragging');
    dragInfo = null;
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('touchmove', onDrag);
    document.removeEventListener('mouseup', endDrag);
    document.removeEventListener('touchend', endDrag);
  }
}

/* ============================================================
   BIND LISTENERS — rebind setiap init
   ============================================================ */
function bindEditorListeners() {
  const htmlIn = ref.htmlIn;
  if (!htmlIn) return;
  if (htmlIn.dataset.bound === '1') return;
  htmlIn.dataset.bound = '1';

htmlIn.addEventListener('input', () => {
  // GUEST GUARD
  if (!window.__isLoggedIn && typeof window.GuestLimit !== 'undefined') {
    const text = htmlIn.value;
    const lastLen = parseInt(htmlIn.dataset.lastLen || '0', 10);
    const diff = Math.abs(text.length - lastLen);

    // Cuma hitung kalau perubahan "besar" (paste / ganti konten), bukan ketik 1-2 huruf
    if (diff > 20) {
      if (window.GuestLimit.isExceeded()) {
        htmlIn.dataset.lastLen = String(lastLen);
        htmlIn.value = htmlIn.dataset.lastValue || '';
        syncEditorFromTextarea();
        window.__showLoginGate();
        return;
      }

      const result = window.GuestLimit.consume();
      if (result.ok) {
        htmlIn.dataset.lastLen = String(text.length);
        htmlIn.dataset.lastValue = text;
        window.__updateGuestBadge?.();

        if (result.remaining === 1) {
          showToast('Tersisa 1 kuota gratis. Login untuk unlimited.', 'warning');
        } else if (result.remaining === 0) {
          showToast('Kuota habis. Login untuk lanjut.', 'error');
        }
      }
    } else {
      htmlIn.dataset.lastLen = String(text.length);
    }
  }

  syncEditorFromTextarea();
  updateCursorPos();
  clearTimeout(window.__debounceT);
  window.__debounceT = setTimeout(convert, 350);
});
  htmlIn.addEventListener('scroll', syncEditorScroll, { passive: true });
  htmlIn.addEventListener('keyup', updateCursorPos);
  htmlIn.addEventListener('click', updateCursorPos);
  htmlIn.addEventListener('keydown', (e) => {
    if (e.key === 'Tab') {
      e.preventDefault();
      const start = htmlIn.selectionStart;
      const end = htmlIn.selectionEnd;
      htmlIn.value = htmlIn.value.substring(0, start) + '  ' + htmlIn.value.substring(end);
      htmlIn.selectionStart = htmlIn.selectionEnd = start + 2;
      syncEditorFromTextarea();
      updateCursorPos();
    }
  });

  const vscodeMinimap = ref.vscodeMinimap;
  if (vscodeMinimap) {
    vscodeMinimap.addEventListener('click', (e) => {
      const rect = vscodeMinimap.getBoundingClientRect();
      const ratio = (e.clientY - rect.top) / rect.height;
      const targetScroll = ratio * htmlIn.scrollHeight - htmlIn.clientHeight / 2;
      htmlIn.scrollTop = Math.max(0, Math.min(targetScroll, htmlIn.scrollHeight - htmlIn.clientHeight));
      syncEditorScroll();
    });
  }

  const btnToggleMinimap = ref.btnToggleMinimap;
  if (btnToggleMinimap && vscodeMinimap) {
    const saved = localStorage.getItem('arrr_minimap_collapsed');
    if (saved === '1') {
      vscodeMinimap.classList.add('collapsed');
      btnToggleMinimap.classList.remove('active');
    } else {
      btnToggleMinimap.classList.add('active');
    }
    btnToggleMinimap.addEventListener('click', () => {
      const collapsed = vscodeMinimap.classList.toggle('collapsed');
      btnToggleMinimap.classList.toggle('active', !collapsed);
      localStorage.setItem('arrr_minimap_collapsed', collapsed ? '1' : '0');
      updateMinimapViewport();
    });
  }

  // Billboard auto-regen
  const ids = ['bbName', 'bbRole', 'bbLevel', 'bbTheme', 'bbOffsetY', 'bbMaxDistance'];
  ids.forEach(id => {
    const el = document.getElementById(id);
    if (!el || el.dataset.bound === '1') return;
    el.dataset.bound = '1';
    let t = null;
    const evt = el.tagName === 'SELECT' ? 'change' : 'input';
    el.addEventListener(evt, () => {
      clearTimeout(t);
      t = setTimeout(() => {
        if (currentTab === 'billboard' && lastNodes.length) regenerateBillboard();
      }, 500);
    });
  });
}

/* ============================================================
   INIT CONVERTER APP (dipanggil tiap SPA navigation)
   ============================================================ */
function initConverterApp() {
  // Kalau bukan di converter page, skip
  if (!isConverterPage()) return;

  bindEditorListeners();
  initResizers();

  syncEditorFromTextarea();
  updateCursorPos();
  updateMinimapViewport();

  // Load sample (kalau perlu)
  const htmlIn = ref.htmlIn;
  if (!htmlIn) return;

  const pendingSample = localStorage.getItem('arrr_load_sample');
  if (pendingSample) {
    localStorage.removeItem('arrr_load_sample');
    loadSample(pendingSample).catch((e) => {
      console.error('Failed to load sample:', pendingSample, e);
      loadSample('sidebar');
    });
  } else if (!htmlIn.value.trim()) {
    loadSample('sidebar');
  }

  setTimeout(() => { zoom = computeFitZoom(); applyZoom(); }, 300);
}

/* ============================================================
   EXPOSE GLOBAL
   ============================================================ */
window.setViewMode   = setViewMode;
window.zoomIn        = zoomIn;
window.zoomOut       = zoomOut;
window.fitToScreen   = fitToScreen;
window.loadSample    = loadSample;
window.clearInput    = clearInput;
window.switchTab     = switchTab;
window.copyCurrent   = copyCurrent;
window.downloadFile  = downloadFile;
window.regenerateBillboard = regenerateBillboard;
window.getBillboardConfig  = getBillboardConfig;
window.initConverterApp    = initConverterApp;

/* ============================================================
   AUTO-INIT
   ============================================================ */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initConverterApp);
} else {
  initConverterApp();
}
window.addEventListener('spa:navigated', initConverterApp);

})();