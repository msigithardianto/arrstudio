/* ============================================================
 *  assets/js/pages/history.js — Riwayat Upload (SPA-ready)
 *  Data: api/spoof.php action "history" / "history_delete" / "grant".
 *  Aksi (copy, izinkan ke game, hapus) berlaku ke baris terpilih,
 *  atau ke semua yang tampil kalau tidak ada yang dipilih.
 * ============================================================ */
(function () {
  'use strict';

  const PAGE      = 200;
  const SPOOF_KEY = 'arrr_spoof_settings';   // sama dengan spoofer.js / ytmp3.js

  const esc = (t) => String(t ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  const $ = (id) => document.getElementById(id);

  let all      = [];         // semua entry dari server (terbaru dulu)
  let selected = new Set();  // entry.id
  let limit    = PAGE;
  let busy     = false;

  const SOURCE = {
    ytmp3:    'YT → MP3',
    reupload: 'Auto Spoof · ID',
    file:     'Auto Spoof · File',
  };

  /* ============================================================
     API
     ============================================================ */
  async function callApi(body) {
    const res = await fetch(window.__apiUrls.spoof, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    let data;
    try {
      data = await res.json();
    } catch (e) {
      throw new Error('Respons server tidak valid (HTTP ' + res.status + ')');
    }
    if (data.error) throw new Error(data.error);
    return data;
  }

  /** Upload yang masih "Menunggu Roblox" → cek status (server mengisi asset ID di riwayat) */
  let checkingPending = false;
  async function resolvePending() {
    const apiKey  = $('hsApiKey') ? $('hsApiKey').value.trim() : '';
    const pending = all.filter(e => !e.assetId && e.operationId).slice(0, 20);
    if (checkingPending || !apiKey || !pending.length) return;
    checkingPending = true;
    let resolved = 0;
    for (const e of pending) {
      try {
        const r = await callApi({ action: 'status', apiKey, operationId: e.operationId });
        if (r.assetId) resolved++;
      } catch (err) { /* biarkan pending — coba lagi saat muat ulang */ }
    }
    checkingPending = false;
    if (resolved) await load(true);
  }

  async function load(skipPending) {
    if (!$('hsRows')) return;
    $('hsCount').textContent = 'Memuat…';
    try {
      const data = await callApi({ action: 'history' });
      all = data.items || [];
      const ids = new Set(all.map(e => e.id));
      selected = new Set([...selected].filter(id => ids.has(id)));
    } catch (e) {
      all = [];
      $('hsCount').textContent = 'Gagal memuat riwayat: ' + e.message;
      $('hsRows').innerHTML = '<tr class="sp-empty"><td colspan="6">—</td></tr>';
      return;
    }
    render();
    if (!skipPending) resolvePending();
  }

  /* ============================================================
     FILTER & RENDER
     ============================================================ */
  function filtered() {
    const q    = $('hsSearch').value.trim().toLowerCase();
    const src  = $('hsSource').value;
    const game = $('hsGame').value;
    return all.filter(e => {
      if (src && e.source !== src) return false;
      if (game === 'yes' && !(e.games || []).length) return false;
      if (game === 'no' && (e.games || []).length) return false;
      if (q) {
        const hay = [e.name, e.assetId, e.ref, e.creatorId].join(' ').toLowerCase();
        if (!hay.includes(q)) return false;
      }
      return true;
    });
  }

  /** Baris yang jadi target aksi: terpilih, atau semua yang tampil */
  function targets() {
    const list = filtered();
    return selected.size ? list.filter(e => selected.has(e.id)) : list;
  }

  function fmtTime(iso) {
    const d = new Date(iso);
    if (isNaN(d)) return '';
    return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: '2-digit', hour: '2-digit', minute: '2-digit' });
  }

  function sourceCell(e) {
    const label = esc(SOURCE[e.source] || e.source || '—');
    if (e.source === 'ytmp3' && /^[A-Za-z0-9_-]{11}$/.test(e.ref || '')) {
      return `<a href="https://www.youtube.com/watch?v=${esc(e.ref)}" target="_blank" rel="noopener" data-no-spa>${label}<small>${esc(e.ref)}</small></a>`;
    }
    return `${label}${e.ref ? `<small>${esc(e.ref)}</small>` : ''}`;
  }

  function assetCell(e) {
    if (!e.assetId) return '<span class="sp-status run">Menunggu Roblox</span>';
    return `<a href="https://create.roblox.com/dashboard/creations/store/${esc(e.assetId)}/configure" target="_blank" rel="noopener" data-no-spa title="Buka di Creator Dashboard">${esc(e.assetId)}</a>`;
  }

  function render() {
    if (!$('hsRows')) return;
    const list = filtered();
    const shown = list.slice(0, limit);

    $('hsRows').innerHTML = shown.length ? shown.map(e => `
      <tr data-id="${esc(e.id)}" class="${selected.has(e.id) ? 'sel' : ''}">
        <td><input type="checkbox" data-pick="${esc(e.id)}" ${selected.has(e.id) ? 'checked' : ''} aria-label="Pilih"></td>
        <td class="hs-time">${esc(fmtTime(e.createdAt))}</td>
        <td class="hs-name">${esc(e.name || '—')}</td>
        <td class="hs-src">${sourceCell(e)}</td>
        <td class="hs-asset">${assetCell(e)}</td>
        <td class="hs-game ${(e.games || []).length ? '' : 'none'}">${(e.games || []).length ? '✓ ' + esc(e.games.join(', ')) : '—'}</td>
      </tr>`).join('')
      : `<tr class="sp-empty"><td colspan="6">${all.length ? 'Tidak ada yang cocok dengan filter.' : 'Belum ada upload. Upload lewat Auto Spoof / YT → MP3 akan tercatat di sini.'}</td></tr>`;

    $('hsMore').hidden = list.length <= limit;
    $('hsMore').textContent = `Tampilkan lebih banyak (${list.length - shown.length} lagi)`;

    const withId = list.filter(e => e.assetId).length;
    $('hsCount').textContent = `${list.length} dari ${all.length} upload · ${withId} punya asset ID`
      + (selected.size ? ` · ${selected.size} dipilih` : '');

    const allChecked = shown.length > 0 && shown.every(e => selected.has(e.id));
    $('hsAll').checked = allChecked;
    $('hsAll').indeterminate = !allChecked && shown.some(e => selected.has(e.id));

    const t = targets();
    $('hsTarget').textContent = selected.size
      ? `${t.length} upload dipilih`
      : `Semua yang tampil (${t.length})`;
    ['hsCopy', 'hsDownload', 'hsGrant', 'hsDelete'].forEach(id => { $(id).disabled = busy || !t.length; });
  }

  /* ============================================================
     AKSI
     ============================================================ */
  function idsText() {
    const list = targets().filter(e => e.assetId);
    const name = (e) => (e.name || e.assetId).replace(/[\r\n]+/g, ' ');
    switch ($('hsFormat').value) {
      case 'ids':   return list.map(e => e.assetId).join('\n');
      case 'comma': return list.map(e => e.assetId).join(', ');
      case 'lua':   return 'return {\n' + list.map(e => `\t[${JSON.stringify(name(e))}] = "rbxassetid://${e.assetId}",`).join('\n') + '\n}';
      default:      return list.map(e => `rbxassetid://${e.assetId} -- ${name(e)}`).join('\n');
    }
  }

  function copyIds() {
    const text = idsText();
    if (!text.trim() || text === 'return {\n\n}') return showToast('Belum ada asset ID', 'warning');
    navigator.clipboard.writeText(text)
      .then(() => showToast('Asset ID di-copy'))
      .catch(() => showToast('Gagal copy — coba .txt', 'error'));
  }

  function downloadIds() {
    const text = idsText();
    if (!text.trim()) return showToast('Belum ada asset ID', 'warning');
    const isLua = $('hsFormat').value === 'lua';
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([text], { type: 'text/plain' }));
    a.download = isLua ? 'UploadedAssets.lua' : 'uploaded-asset-ids.txt';
    a.click();
    setTimeout(() => URL.revokeObjectURL(a.href), 1000);
  }

  function loadSettings() {
    try {
      const s = JSON.parse(localStorage.getItem(SPOOF_KEY) || '{}');
      if (s.apiKey)     $('hsApiKey').value     = s.apiKey;
      if (s.universeId) $('hsUniverseId').value = s.universeId;
    } catch (e) { /* storage diblok → abaikan */ }
  }

  function saveUniverse() {
    try {
      const s = JSON.parse(localStorage.getItem(SPOOF_KEY) || '{}');
      s.universeId = $('hsUniverseId').value.trim();
      localStorage.setItem(SPOOF_KEY, JSON.stringify(s));
    } catch (e) { /* abaikan */ }
  }

  async function grant() {
    const apiKey     = $('hsApiKey').value.trim();
    const universeId = $('hsUniverseId').value.trim();
    const ids = [...new Set(targets().filter(e => e.assetId).map(e => e.assetId))];
    if (!ids.length) return showToast('Tidak ada asset ID untuk diizinkan', 'warning');
    if (!apiKey) { $('hsApiKey').focus(); return showToast('Isi API key Roblox dulu', 'error'); }
    if (!/^\d+$/.test(universeId)) { $('hsUniverseId').focus(); return showToast('Isi Universe ID game (angka)', 'error'); }
    if (typeof window.ArrrGrant !== 'function') return showToast('Modul Auto Spoof belum termuat', 'error');
    saveUniverse();

    busy = true;
    render();
    const box = $('hsResult');
    box.hidden = false;
    box.className = 'sp-check-result';
    box.innerHTML = `<p>Mengizinkan ${ids.length} aset ke game ${esc(universeId)}…</p>`;

    const r = await window.ArrrGrant(apiKey, universeId, ids);
    busy = false;
    if (!$('hsResult')) return;   // pindah halaman (SPA)
    box.className = 'sp-check-result ' + (Object.keys(r.failed).length ? 'err' : 'ok');
    box.innerHTML = window.ArrrGrantSummary ? window.ArrrGrantSummary(r) : `${r.granted.length} diizinkan`;
    showToast(`${r.granted.length}/${ids.length} aset diizinkan ke game`, r.granted.length === ids.length ? 'success' : 'warning', 3500);
    await load(true);   // status game dicatat server
  }

  async function remove() {
    const list = targets();
    if (!list.length) return;
    const everything = !selected.size && list.length === all.length;
    const msg = everything
      ? `Hapus SEMUA ${list.length} catatan riwayat?`
      : `Hapus ${list.length} catatan dari riwayat?`;
    if (!confirm(msg + '\n\nAset di Roblox TIDAK ikut terhapus.')) return;

    busy = true;
    render();
    try {
      const r = await callApi(everything
        ? { action: 'history_delete', all: true }
        : { action: 'history_delete', ids: list.map(e => e.id) });
      showToast(`${r.deleted} catatan dihapus`);
      selected.clear();
    } catch (e) {
      showToast(e.message, 'error', 3500);
    }
    busy = false;
    await load(true);
  }

  /* ============================================================
     INIT
     ============================================================ */
  function initHistory() {
    const root = $('hsRoot');
    if (!root || root.dataset.bound) return;
    root.dataset.bound = '1';

    if (root.dataset.loggedIn !== '1') {
      $('hsCount').textContent = 'Login untuk melihat riwayat.';
      $('hsRows').innerHTML = '<tr class="sp-empty"><td colspan="6">—</td></tr>';
      return;
    }

    const rerender = () => { limit = PAGE; render(); };
    $('hsSearch').addEventListener('input', rerender);
    $('hsSource').addEventListener('change', rerender);
    $('hsGame').addEventListener('change', rerender);
    $('hsRefresh').addEventListener('click', () => load());
    $('hsMore').addEventListener('click', () => { limit += PAGE; render(); });

    $('hsRows').addEventListener('change', (e) => {
      const id = e.target.dataset && e.target.dataset.pick;
      if (!id) return;
      e.target.checked ? selected.add(id) : selected.delete(id);
      render();
    });
    $('hsAll').addEventListener('change', (e) => {
      filtered().slice(0, limit).forEach(x => e.target.checked ? selected.add(x.id) : selected.delete(x.id));
      render();
    });

    $('hsCopy').addEventListener('click', copyIds);
    $('hsDownload').addEventListener('click', downloadIds);
    $('hsGrant').addEventListener('click', grant);
    $('hsDelete').addEventListener('click', remove);
    $('hsUniverseId').addEventListener('change', saveUniverse);

    selected = new Set();
    limit = PAGE;
    loadSettings();
    load();
  }

  window.initHistory = initHistory;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHistory);
  } else {
    initHistory();
  }
  window.addEventListener('spa:navigated', initHistory);
})();
