/* ============================================================
 *  assets/js/pages/luaobf.js — Lua Obfuscator / Deobfuscator (SPA-ready)
 *
 *  Semua proses di browser (tidak dikirim ke server). Format ARRR bersifat
 *  reversibel penuh: apa pun yang di-obfuscate bisa dikembalikan persis ke
 *  kode Lua semula oleh deobfuscator ini.
 *
 *  Format (per layer):
 *    --[[ARRROBF:1]] ... loader Lua ... local _ARRR="<base64>" ...
 *    base64 = payload; payload = seed(4B LE, plain) + XOR(source, keystream(seed))
 *    keystream = LCG(seed). XOR simetris → decode = kebalikan encode.
 *  Layer bertingkat: obfuscate lagi hasil layer sebelumnya. Deobfuscate
 *  mengupas tiap layer sampai tidak ada marker ARRR lagi.
 * ============================================================ */
(function () {
  'use strict';

  const $ = (id) => document.getElementById(id);
  const MARKER = '--[[ARRROBF:1]]';
  const B64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';

  /* ============================================================
     BYTE <-> STRING (UTF-8) & BASE64
     ============================================================ */
  const enc = new TextEncoder();
  const dec = new TextDecoder();

  function b64encode(bytes) {
    let out = '';
    for (let i = 0; i < bytes.length; i += 3) {
      const a = bytes[i], b = bytes[i + 1], c = bytes[i + 2];
      const n = (a << 16) | ((b || 0) << 8) | (c || 0);
      out += B64[(n >> 18) & 63] + B64[(n >> 12) & 63]
        + (i + 1 < bytes.length ? B64[(n >> 6) & 63] : '=')
        + (i + 2 < bytes.length ? B64[n & 63] : '=');
    }
    return out;
  }

  // Base64 → bytes (akumulator bit; abaikan '=' — sama persis dengan decoder Lua)
  function base64ToBytes(str) {
    const clean = str.replace(/[^A-Za-z0-9+/]/g, '');
    const out = [];
    let val = 0, cnt = 0;
    for (let i = 0; i < clean.length; i++) {
      val = (val << 6) | B64.indexOf(clean[i]);
      cnt += 6;
      if (cnt >= 8) { cnt -= 8; out.push((val >> cnt) & 255); }
    }
    return Uint8Array.from(out);
  }

  /* ============================================================
     KEYSTREAM (LCG) — sama persis dengan loader Lua
     ============================================================ */
  // state = (state * 1103515245 + 12345) mod 2^31 ; byte = floor(state / 65536) mod 256
  function keystreamXor(bytes, seed) {
    let state = seed >>> 0;
    const out = new Uint8Array(bytes.length);
    for (let i = 0; i < bytes.length; i++) {
      state = (Math.imul(state, 1103515245) + 12345) >>> 0;
      state = state % 2147483648;
      const k = Math.floor(state / 65536) % 256;
      out[i] = bytes[i] ^ k;
    }
    return out;
  }

  /* ============================================================
     LOADER LUA — didekode & dijalankan di Roblox/Lua
     ============================================================ */
  function buildLoader(base64, seed) {
    // Decoder base64 + LCG + XOR, lalu load(src)(). Semua aritmatika dibatasi (Lua = float).
    return `${MARKER}
-- ARRR Studio Lua Obfuscator (reversibel) - jangan ubah baris _ARRR / _S
local _ARRR="${base64}"
local _S=${seed >>> 0}
local function _d(data,seed)
	local b='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'
	local raw,val,cnt={},0,0
	for i=1,#data do
		local c=data:sub(i,i)
		local p=b:find(c,1,true)
		if p then
			val=val*64+(p-1)
			cnt=cnt+6
			if cnt>=8 then
				cnt=cnt-8
				local d=2^cnt
				raw[#raw+1]=math.floor(val/d)%256
				val=val%d
			end
		end
	end
	local st=seed%2147483648
	local res={}
	for i=5,#raw do
		st=(st*1103515245+12345)%2147483648
		local k=math.floor(st/65536)%256
		local x,r,bit=raw[i],0,1
		for _=1,8 do
			local xb,kb=x%2,k%2
			if xb~=kb then r=r+bit end
			x=math.floor(x/2);k=math.floor(k/2);bit=bit*2
		end
		res[#res+1]=string.char(r)
	end
	return table.concat(res)
end
local _src=_d(_ARRR,_S)
local _f=(loadstring or load)(_src)
if _f then return _f() end`;
  }

  /* ============================================================
     OBFUSCATE / DEOBFUSCATE
     ============================================================ */
  function obfuscateOnce(source) {
    const srcBytes = enc.encode(source);
    const seed = (Math.floor(Math.random() * 0x7ffffffe) + 1) >>> 0;
    const xored = keystreamXor(srcBytes, seed);
    const payload = new Uint8Array(4 + xored.length);
    payload[0] = seed & 255;
    payload[1] = (seed >>> 8) & 255;
    payload[2] = (seed >>> 16) & 255;
    payload[3] = (seed >>> 24) & 255;
    payload.set(xored, 4);
    return buildLoader(b64encode(payload), seed);
  }

  function obfuscate(source, layers) {
    let out = source;
    for (let i = 0; i < layers; i++) out = obfuscateOnce(out);
    return out;
  }

  // Kembalikan satu layer, atau null kalau bukan format ARRR
  function peelOnce(text) {
    if (text.indexOf(MARKER) === -1) return null;
    const m = text.match(/_ARRR="([A-Za-z0-9+/=]*)"/);
    if (!m) return null;
    const payload = base64ToBytes(m[1]);
    if (payload.length < 4) return null;
    const seed = (payload[0] | (payload[1] << 8) | (payload[2] << 16) | (payload[3] << 24)) >>> 0;
    const xored = payload.slice(4);
    const srcBytes = keystreamXor(xored, seed);
    return dec.decode(srcBytes);
  }

  function deobfuscate(text) {
    let out = text.trim();
    let peeled = 0;
    while (true) {
      const next = peelOnce(out);
      if (next === null) break;
      out = next.trim();
      peeled++;
      if (peeled > 100) break; // jaga-jaga
    }
    return { code: out, layers: peeled };
  }

  /* ============================================================
     UI
     ============================================================ */
  function setOutput(text) {
    $('loOutput').value = text;
    $('loOutBytes').textContent = new Blob([text]).size + ' byte · ' + text.split(/\r?\n/).length + ' baris';
  }

  function doObfuscate() {
    const src = $('loInput').value;
    if (!src.trim()) return showToast('Tempel kode Lua dulu', 'error');
    const layers = Math.max(1, Math.min(5, parseInt($('loLayers').value, 10) || 1));
    let out;
    try {
      out = obfuscate(src, layers);
    } catch (e) {
      return showToast('Gagal obfuscate: ' + e.message, 'error');
    }
    setOutput(out);
    showToast(`Ter-obfuscate (${layers} layer)`);
  }

  function doDeobfuscate() {
    const src = $('loInput').value;
    if (!src.trim()) return showToast('Tempel kode ter-obfuscate dulu', 'error');
    let res;
    try {
      res = deobfuscate(src);
    } catch (e) {
      return showToast('Gagal deobfuscate: ' + e.message, 'error');
    }
    if (res.layers === 0) {
      // Bukan format ARRR → best-effort: rapikan escape string kalau ada
      const be = bestEffort(src);
      setOutput(be.code);
      $('loNote').textContent = be.note;
      return showToast('Bukan format ARRR — hasil best-effort', 'warning', 4000);
    }
    setOutput(res.code);
    $('loNote').textContent = `Dikupas ${res.layers} layer → kode Lua asli.`;
    showToast(`Dikembalikan (${res.layers} layer)`);
  }

  // Best-effort untuk obfuscator lain: decode string \ddd / \xHH / \z, unescape.
  function bestEffort(text) {
    // Ambil literal string terpanjang yang isinya escape byte (pola umum loader pihak lain)
    const candidates = [...text.matchAll(/(["'])((?:\\.|(?!\1).)*)\1/g)].map(m => m[2]);
    let best = '';
    for (const c of candidates) {
      if (/\\\d{1,3}|\\x[0-9A-Fa-f]{2}/.test(c) && c.length > best.length) best = c;
    }
    if (!best) {
      return { code: text, note: 'Bukan format ARRR Studio. Tidak ada string ter-escape yang dikenali untuk dipulihkan otomatis.' };
    }
    const decoded = best
      .replace(/\\x([0-9A-Fa-f]{2})/g, (_, h) => String.fromCharCode(parseInt(h, 16)))
      .replace(/\\(\d{1,3})/g, (_, d) => String.fromCharCode(parseInt(d, 10)))
      .replace(/\\n/g, '\n').replace(/\\t/g, '\t').replace(/\\r/g, '\r')
      .replace(/\\"/g, '"').replace(/\\'/g, "'").replace(/\\\\/g, '\\');
    return {
      code: decoded,
      note: 'Bukan format ARRR Studio — ini hasil unescape string terbesar (best-effort). Round-trip 100% hanya dijamin untuk hasil obfuscator ini sendiri.',
    };
  }

  /* ============================================================
     AKSI KECIL
     ============================================================ */
  function copyOut() {
    const text = $('loOutput').value;
    if (!text) return showToast('Belum ada hasil', 'warning');
    (window.ArrrCopyText ? window.ArrrCopyText(text) : navigator.clipboard.writeText(text))
      .then(() => showToast('Hasil di-copy'))
      .catch(() => showToast('Gagal copy', 'error'));
  }

  function downloadOut() {
    const text = $('loOutput').value;
    if (!text) return showToast('Belum ada hasil', 'warning');
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([text], { type: 'text/plain' }));
    a.download = 'script.lua';
    a.click();
    setTimeout(() => URL.revokeObjectURL(a.href), 1000);
  }

  function swapInOut() {
    const o = $('loOutput').value;
    if (!o) return;
    $('loInput').value = o;
    setOutput('');
    $('loNote').textContent = '';
    updateInBytes();
  }

  function updateInBytes() {
    const t = $('loInput').value;
    $('loInBytes').textContent = new Blob([t]).size + ' byte · ' + t.split(/\r?\n/).length + ' baris';
  }

  /* ============================================================
     INIT
     ============================================================ */
  function initLuaObf() {
    const root = $('loRoot');
    if (!root || root.dataset.bound) return;
    root.dataset.bound = '1';

    $('loInput').addEventListener('input', updateInBytes);
    $('loObfuscate').addEventListener('click', doObfuscate);
    $('loDeobfuscate').addEventListener('click', doDeobfuscate);
    $('loCopy').addEventListener('click', copyOut);
    $('loDownload').addEventListener('click', downloadOut);
    $('loSwap').addEventListener('click', swapInOut);
    $('loClear').addEventListener('click', () => {
      $('loInput').value = ''; setOutput(''); $('loNote').textContent = ''; updateInBytes();
    });

    updateInBytes();
    setOutput('');
  }

  window.initLuaObf = initLuaObf;
  // Ekspos untuk pengetesan
  window.__ArrrLua = { obfuscate, deobfuscate };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLuaObf);
  } else {
    initLuaObf();
  }
  window.addEventListener('spa:navigated', initLuaObf);
})();
