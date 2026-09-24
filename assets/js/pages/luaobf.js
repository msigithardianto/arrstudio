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
     DEOBFUSCATE FAMILI-A (string-table + PRNG name resolver)
     Contoh: output obfuscator gaya "XI/dI/II" (sejenis yang beredar utk
     script Roblox). SEMUA string didecode secara STATIS (tanpa menjalankan
     kode asing) lalu disisipkan balik → hasil Lua yang bisa dijalankan.
     ============================================================ */
  const LUA_KEYWORDS = new Set(['and','break','do','else','elseif','end','false','for','function','goto','if','in','local','nil','not','or','repeat','return','then','true','until','while']);

  function luaQuote(s) {
    let out = '"';
    for (let i = 0; i < s.length; i++) {
      const c = s.charCodeAt(i);
      if (c === 34) out += '\\"';
      else if (c === 92) out += '\\\\';
      else if (c === 10) out += '\\n';
      else if (c === 13) out += '\\r';
      else if (c === 9) out += '\\t';
      else if (c < 32 || c === 127) out += '\\' + c;
      else out += s[i];
    }
    return out + '"';
  }

  // Ambil daftar literal string dari "local <name>={ ... }"
  function parseStringArray(text, startKey) {
    let i = text.indexOf(startKey);
    if (i < 0) return null;
    i = text.indexOf('{', i) + 1;
    const out = [];
    while (i < text.length) {
      const ch = text[i];
      if (ch === '}') break;
      if (ch === '"' || ch === "'") {
        const q = ch; let s = ''; i++;
        while (i < text.length) {
          const c = text[i];
          if (c === '\\') { const n = text[i + 1]; const map = { '"': '"', "'": "'", '\\': '\\', n: '\n', t: '\t', r: '\r' }; s += (n in map) ? map[n] : n; i += 2; continue; }
          if (c === q) { i++; break; }
          s += c; i++;
        }
        out.push(s);
      } else i++;
    }
    return out;
  }

  // Parse map alfabet {["x"]=N; y=N, ...} → {char: value}
  function parseAlphabet(text, name) {
    const re = new RegExp('local\\s+' + name + '\\s*=\\s*\\{');
    const m = re.exec(text);
    if (!m) return null;
    let i = m.index + m[0].length, depth = 1, body = '';
    while (i < text.length && depth > 0) {
      const c = text[i];
      if (c === '{') depth++;
      else if (c === '}') { depth--; if (depth === 0) break; }
      body += c; i++;
    }
    const map = {};
    const pairRe = /(\[(["'])((?:\\.|.)*?)\2\]|(\w))\s*=\s*(\d+)/g;
    let p;
    while ((p = pairRe.exec(body))) {
      let key = p[4] !== undefined ? p[4] : p[3];
      if (key === '\\\\') key = '\\'; else if (key === '\\"') key = '"'; else if (key === "\\'") key = "'";
      map[key] = parseInt(p[5], 10);
    }
    return map;
  }

  function looksLikeFamilyA(text) {
    return /local\s+\w+\s*=\s*\{\s*["']\*/.test(text)   // tabel string diawali "*"
      && /function\s+\w+\s*\(\s*\w+\s*\)\s*return\s+\w+\[\s*\w+\s*\+\s*\d{3,}\s*\]/.test(text) // dI(c) return XI[c+NNN]
      && /setmetatable\(/.test(text)
      && /%\s*256/.test(text);
  }

  // Deobfuscate famili-A → { ok, code, count } atau { ok:false }
  function deobfFamilyA(text) {
    try {
      const XI = parseStringArray(text, 'local XI=') || parseStringArray(text, '={"*');
      if (!XI || XI.length < 4) return { ok: false };

      // Reverse ranges: for C,G in ipairs({{a,b},...}) do reverse XI[a..b]
      const rr = /ipairs\(\{((?:\s*\{\s*\d+\s*[;,]\s*\d+\s*\}\s*[;,]?)+)\}\)/.exec(text);
      if (rr) {
        for (const mm of rr[1].matchAll(/\{\s*(\d+)\s*[;,]\s*(\d+)\s*\}/g)) {
          let a = +mm[1] - 1, b = +mm[2] - 1;
          while (a < b) { const t = XI[a]; XI[a] = XI[b]; XI[b] = t; a++; b--; }
        }
      }

      const K = parseAlphabet(text, 'K');
      const t = parseAlphabet(text, 't');
      if (!K || !t) return { ok: false };

      const b64 = (s) => {
        const out = []; let I = 0, j = 0;
        for (let p = 0; p < s.length; p++) {
          const c = s[p], T = K[c];
          if (T !== undefined) { I += T * Math.pow(64, 3 - j); j++; if (j === 4) { j = 0; out.push(Math.floor(I / 65536) % 256, Math.floor((I % 65536) / 256) % 256, I % 256); I = 0; } }
          else if (c === '=') { out.push(Math.floor(I / 65536) % 256); if (p + 1 >= s.length || s[p + 1] !== '=') out.push(Math.floor((I % 65536) / 256) % 256); break; }
        }
        return out;
      };
      const b85 = (s) => {
        const out = []; let p = 0; const n = s.length;
        while (p < n) {
          const rem = n - p, T = rem >= 5 ? 5 : rem; let B = 0, ok = T > 1;
          for (let c = 0; c < 5; c++) { let G; if (c < T) { G = t[s[p + c]]; if (G === undefined) { ok = false; break; } } else G = 84; B = B * 85 + G; }
          if (ok) { const by = [Math.floor(B / 16777216) % 256, Math.floor(B / 65536) % 256, Math.floor(B / 256) % 256, B % 256]; for (let z = 0; z < T - 1; z++) out.push(by[z]); }
          p += T;
        }
        return out;
      };
      const dec = XI.map(L => L[0] === '0' ? b64(L.slice(1)) : (L[0] === '*' ? b85(L.slice(1)) : [...L].map(c => c.charCodeAt(0) & 255)));

      const offM = /return\s+\w+\[\s*\w+\s*\+\s*(\d+)\s*\]/.exec(text);
      if (!offM) return { ok: false };
      const OFFSET = +offM[1];
      const dI = (c) => dec[c + OFFSET - 1];

      // Konstanta PRNG (II / A())
      const num = (re) => { const m = re.exec(text); return m ? +m[1] : null; };
      const A_MUL = num(/w\s*\*\s*(\d+)\s*\+/), A_ADD = num(/w\s*\*\s*\d+\s*\+\s*(\d+)/), A_MOD = num(/\)\s*\)\s*%\s*(\d+)/);
      const C_MUL = num(/C\s*\*\s*(\d+)\s*\)\s*%\s*\d+/), C_MOD = num(/C\s*\*\s*\d+\s*\)\s*%\s*(\d+)/);
      const W_MOD = num(/w\s*=\s*K\s*%\s*(\d+)/), C_KMOD = num(/C\s*=\s*K\s*%\s*(\d+)\s*\+/), C_KADD = num(/C\s*=\s*K\s*%\s*\d+\s*\+\s*(\d+)/);
      const M0 = num(/local\s+\w+\s*=\s*(204)\b/) || 204;
      if ([A_MUL, A_ADD, A_MOD, C_MUL, C_MOD, W_MOD, C_KMOD, C_KADD].some(v => v == null)) return { ok: false };

      function makeII() {
        let w = 0, C = 2, U = [];
        function A() {
          if (U.length === 0) {
            w = (w * A_MUL + A_ADD) % A_MOD;
            do { C = (C * C_MUL) % C_MOD; } while (C === 1);
            const nb = C % 32, Kk = 13 - ((C - nb)) / 32;
            const tt = (Math.floor(w / Math.pow(2, Kk)) % 4294967296) / Math.pow(2, nb);
            const T = Math.floor((tt % 1) * 4294967296) + Math.floor(tt);
            const B = T % 65536, AA = (T - B) / 65536;
            U = [B % 256, Math.floor((B - B % 256) / 256), AA % 256, Math.floor((AA - AA % 256) / 256)];
          }
          return U.pop();
        }
        return function (G, Kkey) {
          w = Kkey % W_MOD; C = Kkey % C_KMOD + C_KADD; U = [];
          let m = M0, L = '';
          for (let i = 0; i < G.length; i++) { m = ((G[i] + A()) + m) % 256; L += String.fromCharCode(m); }
          return L;
        };
      }
      const II = makeII();
      const rawStr = (bytes) => bytes.map(b => String.fromCharCode(b)).join('');

      let count = 0;
      // Ganti jI[II(dI(a),k)] → "string terdekripsi"
      let out = text.replace(/jI\s*\[\s*II\s*\(\s*dI\s*\(\s*(-?\d+)\s*\)\s*,\s*(\d+)\s*\)\s*\]/g, (_, a, k) => {
        const g = dI(+a); if (!g) return '""';
        count++;
        return luaQuote(II(g, Number(k)));
      });
      // Sisa bare dI(a) → literal mentah
      out = out.replace(/\bdI\s*\(\s*(-?\d+)\s*\)/g, (_, a) => { const g = dI(+a); return g ? luaQuote(rawStr(g)) : '""'; });

      if (count === 0) return { ok: false };

      // Buang scaffolding decoder: dari "local XI={" s/d awal payload (game: pertama)
      const pg = out.search(/game\s*[:.]/);
      if (pg >= 0) {
        const ls = out.lastIndexOf('local ', pg);
        if (ls > 0) out = 'return(function(...)' + out.slice(ls);
      }


      return { ok: true, code: out, count };
    } catch (e) {
      return { ok: false, error: e.message };
    }
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
      // Bukan format ARRR → coba deobfuscator famili-A (string-table + PRNG)
      if (looksLikeFamilyA(src)) {
        const fa = deobfFamilyA(src);
        if (fa.ok) {
          setOutput(fa.code);
          $('loNote').textContent = `Obfuscator string-table terdeteksi — ${fa.count} string didecode & disisipkan. `
            + `Hasil sudah berupa Lua yang bisa dijalankan (nama variabel lokal tetap singkat).`;
          return showToast(`Berhasil deobfuscate (${fa.count} string)`, 'success', 4000);
        }
      }
      // Sisanya → best-effort / penjelasan jujur
      const be = bestEffort(src);
      setOutput(be.cannot ? '' : be.code);
      $('loNote').textContent = be.note;
      return showToast(be.cannot ? 'Tidak bisa dibuka otomatis — lihat catatan' : 'Bukan format ARRR — hasil best-effort',
        'warning', 5000);
    }
    setOutput(res.code);
    $('loNote').textContent = `Dikupas ${res.layers} layer → kode Lua asli.`;
    showToast(`Dikembalikan (${res.layers} layer)`);
  }

  // Best-effort untuk obfuscator lain. Jujur soal batasan: obfuscator VM /
  // enkripsi-nama (Luraph/MoonSec/IronBrew dsb.) TIDAK bisa dibuka statis —
  // konstanta baru terbentuk saat script dijalankan, dan tool ini tidak
  // menjalankan kode asing. Yang aman: unescape string sederhana.
  function bestEffort(text) {
    // Deteksi obfuscator VM / enkripsi-nama (string-table + resolver runtime)
    const vmSignals =
      (/\bfunction\s+\w*\s*\([^)]*\)\s*return\s+\w+\[?\w*\s*[+\-]\s*\d{3,}/.test(text) ? 1 : 0) + // dI(c) return XI[c+NNN]
      (/local\s+\w+\s*=\s*\{\s*["'*0]/.test(text) && (text.match(/["'][*0][^"']*["']/g) || []).length > 20 ? 1 : 0) + // tabel string besar
      (/setmetatable\(\{\}\s*,\s*\{/.test(text) ? 1 : 0) +
      (/\bstring\.char\b/.test(text) && /\bmath\.floor\b/.test(text) && /%\s*256/.test(text) ? 1 : 0);
    if (vmSignals >= 2) {
      return {
        code: text,
        note: 'Terdeteksi obfuscator VM / enkripsi-nama (mis. sejenis Luraph / MoonSec / IronBrew). '
          + 'Logika programnya sebenarnya sudah berupa Lua biasa — yang disembunyikan hanya konstanta string & nama, '
          + 'yang baru terbentuk SAAT script dijalankan (lewat PRNG di dalamnya). Untuk membukanya harus MENJALANKAN '
          + 'script itu, dan tool ini sengaja tidak menjalankan kode asing (berbahaya). Jadi ini tidak bisa di-deobfuscate '
          + 'otomatis jadi kode rapi. Jaminan “pasti kembali” hanya berlaku untuk hasil obfuscator ARRR ini sendiri.',
        cannot: true,
      };
    }
    // Loader sederhana: ambil literal string terpanjang berisi escape byte lalu unescape
    const candidates = [...text.matchAll(/(["'])((?:\\.|(?!\1).)*)\1/g)].map(m => m[2]);
    let best = '';
    for (const c of candidates) {
      if (/\\\d{1,3}|\\x[0-9A-Fa-f]{2}/.test(c) && c.length > best.length) best = c;
    }
    if (!best) {
      return { code: text, note: 'Bukan format ARRR Studio, dan tidak ada string ter-escape yang bisa dipulihkan otomatis.' };
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
  window.__ArrrLua = { obfuscate, deobfuscate, deobfFamilyA, looksLikeFamilyA };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLuaObf);
  } else {
    initLuaObf();
  }
  window.addEventListener('spa:navigated', initLuaObf);
})();
