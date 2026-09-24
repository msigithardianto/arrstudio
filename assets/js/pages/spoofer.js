/* ============================================================
 *  assets/js/pages/spoofer.js — Auto Spoof massal (SPA-ready)
 *  Tiap aset = 1 request ke api/spoof.php, dijalankan paralel (CONCURRENCY).
 * ============================================================ */
(function () {
  'use strict';

  const CONCURRENCY = 2;
  const POLL_MS     = 2000;
  const POLL_MAX    = 60;
  const STORE_KEY   = 'arrr_spoof_settings';

  const esc = (t) => String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  const $ = (id) => document.getElementById(id);
  const sleep = (ms) => new Promise(r => setTimeout(r, ms));

  let items   = [];     // { source, label, file?, status, newId, error }
  let running = false;
  let stopped = false;
  let files   = [];
  let entries = [];     // hasil parseEntries saat Mulai — dipakai format "Sesuai input"

  /* ============================================================
     STORAGE (per browser) — API key hanya kalau "Ingat" dicentang
     ============================================================ */
  function loadSettings() {
    try {
      const s = JSON.parse(localStorage.getItem(STORE_KEY) || '{}');
      if (s.creatorType) $('spCreatorType').value = s.creatorType;
      if (s.creatorId)   $('spCreatorId').value   = s.creatorId;
      if (s.universeId)  $('spUniverseId').value  = s.universeId;
      $('spAutoGrant').checked = !!s.autoGrant;
      if (s.apiKey) {
        $('spApiKey').value = s.apiKey;
        $('spRemember').checked = true;
      }
    } catch (e) { /* storage diblok → abaikan */ }
  }

  function saveSettings() {
    try {
      const s = {
        creatorType: $('spCreatorType').value,
        creatorId:   $('spCreatorId').value.trim(),
        universeId:  $('spUniverseId').value.trim(),
        autoGrant:   $('spAutoGrant').checked,
      };
      if ($('spRemember').checked) s.apiKey = $('spApiKey').value.trim();
      localStorage.setItem(STORE_KEY, JSON.stringify(s));
    } catch (e) { /* abaikan */ }
  }

  /* ============================================================
     PARSE INPUT
     ============================================================ */
  // Baris "Nama Lagu{123}" / "Nama{123,456}" → entry bernama (format dipertahankan di output).
  // Selain itu: angka pertama tiap token — "123", "rbxassetid://123", ".../library/123/Nama"
  function parseEntries(text) {
    const entries = [];
    text.split(/\r?\n/).forEach(line => {
      const m = line.match(/^(.*?)\s*\{\s*([\d\s,]+)\}\s*$/);
      if (m) {
        const ids = (m[2].match(/\d{3,20}/g) || []);
        if (ids.length) entries.push({ name: m[1].trim(), ids });
        return;
      }
      line.split(/[\s,;]+/).forEach(tok => {
        const n = tok.match(/\d{3,20}/);
        if (n) entries.push({ name: '', ids: [n[0]] });
      });
    });
    return entries;
  }

  // ID unik (urut sesuai input) + nama pertama yang dipakai ID itu
  function parseIds(text) {
    const seen = new Map();
    parseEntries(text).forEach(e => e.ids.forEach(id => {
      if (!seen.has(id)) seen.set(id, e.name);
    }));
    return Array.from(seen, ([id, name]) => ({ id, name }));
  }

  function updateCounts() {
    const ids = parseIds($('spIds').value);
    $('spIdCount').textContent = ids.length + ' ID terdeteksi';
    $('spFileCount').textContent = files.length
      ? files.length + ' file: ' + files.slice(0, 5).map(f => f.name).join(', ') + (files.length > 5 ? ', …' : '')
      : '0 file dipilih';
  }

  function activeTab() {
    const t = document.querySelector('.sp-tab.active');
    return t ? t.dataset.spTab : 'ids';
  }

  /* ============================================================
     RENDER
     ============================================================ */
  const STATUS_LABEL = { wait: 'Antri', run: 'Proses…', ok: 'Berhasil', err: 'Gagal' };

  function renderRows() {
    const body = $('spRows');
    if (!body) return;   // pindah halaman (SPA) saat proses jalan
    if (!items.length) {
      body.innerHTML = '<tr class="sp-empty"><td colspan="4">Hasil muncul di sini.</td></tr>';
      return;
    }
    body.innerHTML = items.map((it, i) => `
      <tr>
        <td>${i + 1}</td>
        <td class="sp-mono">${esc(it.label)}</td>
        <td><span class="sp-status ${it.status}">${esc(it.status === 'err' ? it.error : (it.note || STATUS_LABEL[it.status]))}</span></td>
        <td class="sp-mono">${it.newId ? esc(it.newId) : '—'}</td>
      </tr>`).join('');
  }

  function renderProgress() {
    if (!$('spProgressBar')) return;
    const done = items.filter(i => i.status === 'ok' || i.status === 'err').length;
    const ok   = items.filter(i => i.status === 'ok').length;
    const err  = items.filter(i => i.status === 'err').length;
    $('spProgressBar').style.width = items.length ? (done / items.length * 100) + '%' : '0';
    $('spProgressText').textContent = items.length
      ? `${done}/${items.length} selesai · ${ok} berhasil · ${err} gagal${running ? (stopped ? ' · berhenti…' : ' · jalan') : ''}`
      : 'Belum ada proses';
  }

  function renderOutput() {
    if (!$('spOutput')) return;
    const ok  = items.filter(i => i.status === 'ok');
    const fmt = $('spFormat').value;
    let text  = '';
    switch (fmt) {
      case 'rbx':   text = ok.map(i => 'rbxassetid://' + i.newId).join('\n'); break;
      case 'comma': text = ok.map(i => i.newId).join(', '); break;
      case 'map':   text = ok.map(i => i.label + ' → ' + i.newId).join('\n'); break;
      case 'list': {
        // Sama persis dengan input, ID lama diganti ID baru. Baris yang semua ID-nya gagal dilewati.
        const byId = new Map(ok.map(i => [i.source, i.newId]));
        text = entries.map(e => {
          const ids = e.ids.map(id => byId.get(id)).filter(Boolean);
          if (!ids.length) return null;
          return e.name ? `${e.name}{${ids.join(',')}}` : ids.join('\n');
        }).filter(Boolean).join('\n');
        break;
      }
      case 'lua':
        text = 'return {\n' + ok.map(i => /^\d+$/.test(i.label)
          ? `\t[${i.label}] = ${i.newId},`
          : `\t[${JSON.stringify(i.label)}] = ${i.newId},`).join('\n') + '\n}';
        break;
      default:      text = ok.map(i => i.newId).join('\n');
    }
    $('spOutput').value = ok.length ? text : '';
  }

  function renderAll() {
    renderRows();
    renderProgress();
    renderOutput();
  }

  /* ============================================================
     API
     ============================================================ */
  async function callApi(body, isForm) {
    const res = await fetch(window.__apiUrls.spoof, isForm
      ? { method: 'POST', body }
      : { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
    let data;
    try {
      data = await res.json();
    } catch (e) {
      throw new Error('Respons server tidak valid (HTTP ' + res.status + ')');
    }
    if (data.error) throw new Error(data.error);
    return data;
  }

  async function processItem(it, cfg) {
    it.status = 'run';
    renderAll();

    let data;
    if (it.file) {
      const fd = new FormData();
      fd.append('action', 'upload');
      fd.append('apiKey', cfg.apiKey);
      fd.append('creatorType', cfg.creatorType);
      fd.append('creatorId', cfg.creatorId);
      if (cfg.name) fd.append('name', cfg.name);
      fd.append('file', it.file);
      data = await callApi(fd, true);
    } else {
      data = await callApi({
        action: 'reupload',
        apiKey: cfg.apiKey,
        creatorType: cfg.creatorType,
        creatorId: cfg.creatorId,
        assetId: it.source,
        name: cfg.name ? cfg.name + ' ' + it.source : (it.name || ''),
      });
    }

    // Roblox masih memproses → polling status operasi
    for (let n = 0; !data.assetId && data.operationId && n < POLL_MAX; n++) {
      it.note = 'Menunggu Roblox…';
      renderRows();
      await sleep(POLL_MS);
      data = await callApi({ action: 'status', apiKey: cfg.apiKey, operationId: data.operationId });
    }
    if (!data.assetId) throw new Error('Timeout menunggu Roblox — cek Creator Dashboard nanti');

    it.newId  = String(data.assetId);
    it.note   = '';
    it.status = 'ok';
  }

  async function worker(queue, cfg) {
    while (queue.length && !stopped) {
      const it = queue.shift();
      try {
        await processItem(it, cfg);
      } catch (e) {
        it.status = 'err';
        it.error  = e.message || 'Gagal';
      }
      renderAll();
    }
  }

  /* ============================================================
     START / STOP
     ============================================================ */
  async function start() {
    if (running) return;

    const cfg = {
      apiKey:      $('spApiKey').value.trim(),
      creatorType: $('spCreatorType').value,
      creatorId:   $('spCreatorId').value.trim(),
      name:        $('spName').value.trim(),
    };
    if (!cfg.apiKey) return showToast('Masukkan API key Roblox dulu', 'error');
    if (!/^\d+$/.test(cfg.creatorId)) return showToast('Isi User ID / Group ID kamu (angka)', 'error');

    if (activeTab() === 'files') {
      if (!files.length) return showToast('Pilih file dulu', 'error');
      entries = [];
      items = files.map(f => ({ source: f.name, label: f.name, file: f, status: 'wait' }));
    } else {
      const ids = parseIds($('spIds').value);
      if (!ids.length) return showToast('Tempel minimal 1 asset ID', 'error');
      entries = parseEntries($('spIds').value);
      items = ids.map(({ id, name }) => ({ source: id, name, label: name ? `${name} (${id})` : id, status: 'wait' }));
      if (ids.some(i => i.name)) $('spFormat').value = 'list';
    }

    saveSettings();
    running = true;
    stopped = false;
    $('spStart').disabled = true;
    $('spStop').disabled  = false;
    renderAll();

    const queue = items.slice();
    await Promise.all(Array.from({ length: Math.min(CONCURRENCY, queue.length) }, () => worker(queue, cfg)));

    // Item yang belum sempat jalan karena di-stop
    items.forEach(it => {
      if (it.status === 'wait') { it.status = 'err'; it.error = 'Dibatalkan'; }
    });

    running = false;
    if ($('spStart')) {
      $('spStart').disabled = false;
      $('spStop').disabled  = true;
    }
    renderAll();

    const ok = items.filter(i => i.status === 'ok').length;
    showToast(`${ok}/${items.length} aset berhasil di-upload`, ok === items.length ? 'success' : 'warning', 3500);

    // Otomatis izinkan semua aset baru ke game
    const universeId = $('spUniverseId') ? $('spUniverseId').value.trim() : '';
    if (ok && $('spAutoGrant') && $('spAutoGrant').checked && /^\d+$/.test(universeId)) {
      $('spGrantIds').value = items.filter(i => i.status === 'ok').map(i => i.newId).join('\n');
      await grantFromBox();
    }
  }

  /* ============================================================
     IZIN GAME MASSAL — helper dipakai juga oleh ytmp3.js
     ============================================================ */
  // → {granted:[...], failed:{id: alasan}}
  async function grantAssets(apiKey, universeId, ids, onProgress) {
    const granted = [];
    const failed  = {};
    // Maks 200 per request (batas server)
    for (let i = 0; i < ids.length; i += 200) {
      const part = ids.slice(i, i + 200);
      if (onProgress) onProgress(i, ids.length);
      try {
        const r = await callApi({ action: 'grant', apiKey, universeId, assetIds: part });
        granted.push(...r.granted);
        Object.assign(failed, r.failed || {});
      } catch (e) {
        part.forEach(id => { failed[id] = e.message; });
      }
    }
    return { granted, failed };
  }
  window.ArrrGrant = grantAssets;

  function grantSummaryHtml(r) {
    const failedIds = Object.keys(r.failed);
    // Kelompokkan alasan yang sama biar tidak panjang
    const byReason = {};
    failedIds.forEach(id => { (byReason[r.failed[id]] = byReason[r.failed[id]] || []).push(id); });
    const rows = [`<li class="${r.granted.length ? 'ok' : 'warn'}">${r.granted.length} aset diizinkan</li>`]
      .concat(Object.entries(byReason).map(([reason, list]) =>
        `<li class="err">${list.length} gagal: ${esc(reason)} <span class="sp-mono">(${esc(list.slice(0, 5).join(', '))}${list.length > 5 ? ', …' : ''})</span></li>`));
    return `<p><b>${failedIds.length ? 'Sebagian gagal' : 'Semua aset sudah diizinkan ke game'}</b></p><ul>${rows.join('')}</ul>`;
  }
  window.ArrrGrantSummary = grantSummaryHtml;

  async function grantFromBox() {
    const apiKey     = $('spApiKey').value.trim();
    const universeId = $('spUniverseId').value.trim();
    const ids        = parseIds($('spGrantIds').value);
    if (!apiKey) return showToast('Masukkan API key Roblox dulu', 'error');
    if (!/^\d+$/.test(universeId)) {
      $('spUniverseId').focus();
      return showToast('Isi Universe ID game di card 01', 'error');
    }
    if (!ids.length) return showToast('Tempel minimal 1 asset ID', 'error');
    saveSettings();

    const btn = $('spGrant');
    btn.disabled = true;
    $('spGrantStatus').textContent = `Mengizinkan ${ids.length} aset…`;
    const r = await grantAssets(apiKey, universeId, ids);
    if (!$('spGrant')) return;   // pindah halaman (SPA)
    btn.disabled = false;
    $('spGrantStatus').textContent = `${r.granted.length}/${ids.length} diizinkan`;
    const box = $('spGrantResult');
    box.hidden = false;
    box.className = 'sp-check-result ' + (Object.keys(r.failed).length ? 'err' : 'ok');
    box.innerHTML = grantSummaryHtml(r);
    showToast(`${r.granted.length}/${ids.length} aset diizinkan ke game`, r.granted.length === ids.length ? 'success' : 'warning', 3500);
  }

  /* ============================================================
     CEK KONEKSI API KEY
     ============================================================ */
  async function checkConnection() {
    const apiKey = $('spApiKey').value.trim();
    if (!apiKey) return showToast('Masukkan API key Roblox dulu', 'error');
    const testId = parseIds($('spTestId').value)[0] || '';

    const box = $('spCheckResult');
    const btn = $('spCheck');
    btn.disabled = true;
    btn.textContent = 'Mengecek…';
    box.hidden = false;
    box.className = 'sp-check-result';
    box.innerHTML = '<p>Menghubungi Roblox…</p>';

    const line = (ok, text) => `<li class="${ok === null ? 'warn' : (ok ? 'ok' : 'err')}">${esc(text)}</li>`;
    try {
      const r = await callApi({ action: 'check', apiKey, assetId: testId });
      const rows = [];
      if (r.introspected) {
        rows.push(line(r.enabled !== false && r.expired !== true,
          'API key ' + (r.expired ? 'kedaluwarsa' : (r.enabled === false ? 'nonaktif' : 'valid & aktif'))
          + (r.name ? ' — "' + r.name + '"' : '')));
        if (r.userId) rows.push(line(true, 'Pemilik key: User ID ' + r.userId));
        if (r.scopes.length) {
          ['asset:read', 'asset:write', 'legacy-asset:manage'].forEach(op =>
            rows.push(line(!r.missing.includes(op), 'Scope ' + op + (r.missing.includes(op) ? ' — belum ada' : ''))));
        } else {
          rows.push(line(null, 'Scope tidak bisa dibaca dari Roblox — lihat hasil tes download'));
        }
        if (r.rawScopes) rows.push(line(null, 'Data scope dari Roblox: ' + JSON.stringify(r.rawScopes).slice(0, 300)));
      } else {
        rows.push(line(null, 'Detail API key tidak bisa dibaca (introspect tidak tersedia)'));
      }
      if (r.asset && r.userId && r.asset.id === String(r.userId)) {
        rows.push(line(false, `${r.asset.id} itu User ID kamu, bukan Asset ID. Pakai ID aset (gambar/audio) milikmu — lihat di create.roblox.com → Creations, atau angka di link roblox.com/library/<ID>/...`));
      } else if (r.asset) {
        rows.push(line(r.asset.ok, r.asset.ok
          ? `Tes download ${r.asset.id}: berhasil (${r.asset.kind}, ${(r.asset.bytes / 1024).toFixed(0)} KB)`
          : `Tes download ${r.asset.id}: ${r.asset.error}`));
      } else {
        rows.push(line(null, 'Isi Asset ID tes untuk mengecek akses download'));
      }
      box.classList.add(r.ok ? 'ok' : 'err');
      box.innerHTML = `<p><b>${r.ok ? 'Terhubung ke Roblox Open Cloud' : 'Ada yang perlu dibenahi'}</b></p><ul>${rows.join('')}</ul>`;

      // Isi otomatis User ID kalau masih kosong
      if (r.userId && $('spCreatorType').value === 'user' && !$('spCreatorId').value.trim()) {
        $('spCreatorId').value = r.userId;
        saveSettings();
      }
    } catch (e) {
      box.classList.add('err');
      box.innerHTML = `<p><b>Tidak terhubung</b></p><ul>${line(false, e.message)}</ul>`;
    } finally {
      if ($('spCheck')) {
        btn.disabled = false;
        btn.textContent = 'Cek koneksi';
      }
    }
  }

  function copyOutput() {
    const text = $('spOutput').value;
    if (!text) return showToast('Belum ada hasil', 'warning');
    navigator.clipboard.writeText(text)
      .then(() => showToast('Asset ID di-copy'))
      .catch(() => { $('spOutput').select(); document.execCommand('copy'); showToast('Asset ID di-copy'); });
  }

  function downloadOutput() {
    const text = $('spOutput').value;
    if (!text) return showToast('Belum ada hasil', 'warning');
    const isLua = $('spFormat').value === 'lua';
    const blob = new Blob([text], { type: 'text/plain' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = isLua ? 'SpoofedAssets.lua' : 'spoofed-asset-ids.txt';
    a.click();
    setTimeout(() => URL.revokeObjectURL(a.href), 1000);
  }

  /* ============================================================
     INIT
     ============================================================ */
  function initSpoofer() {
    const root = $('spStart');
    if (!root || root.dataset.bound) return;
    root.dataset.bound = '1';

    loadSettings();

    document.querySelectorAll('.sp-tab').forEach(tab => {
      tab.addEventListener('click', () => {
        document.querySelectorAll('.sp-tab').forEach(t => t.classList.toggle('active', t === tab));
        document.querySelectorAll('.sp-pane').forEach(p => p.classList.toggle('active', p.dataset.spPane === tab.dataset.spTab));
      });
    });

    $('spToggleKey').addEventListener('click', () => {
      const input = $('spApiKey');
      input.type = input.type === 'password' ? 'text' : 'password';
      $('spToggleKey').textContent = input.type === 'password' ? 'Lihat' : 'Tutup';
    });

    $('spRemember').addEventListener('change', saveSettings);
    $('spIds').addEventListener('input', updateCounts);
    $('spFiles').addEventListener('change', (e) => {
      files = Array.from(e.target.files || []);
      updateCounts();
    });

    const drop = $('spDrop');
    ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, () => drop.classList.add('drag')));
    ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, () => drop.classList.remove('drag')));

    root.addEventListener('click', start);
    $('spCheck').addEventListener('click', checkConnection);
    $('spGrant').addEventListener('click', grantFromBox);
    $('spGrantFill').addEventListener('click', () => {
      const ids = items.filter(i => i.status === 'ok').map(i => i.newId);
      if (!ids.length) return showToast('Belum ada hasil upload', 'warning');
      $('spGrantIds').value = ids.join('\n');
    });
    $('spUniverseId').addEventListener('change', saveSettings);
    $('spAutoGrant').addEventListener('change', saveSettings);
    $('spStop').addEventListener('click', () => { stopped = true; renderProgress(); });
    $('spFormat').addEventListener('change', renderOutput);
    $('spCopy').addEventListener('click', copyOutput);
    $('spDownload').addEventListener('click', downloadOutput);

    files = [];
    updateCounts();
    renderAll();
  }

  window.initSpoofer = initSpoofer;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSpoofer);
  } else {
    initSpoofer();
  }
  window.addEventListener('spa:navigated', initSpoofer);
})();
