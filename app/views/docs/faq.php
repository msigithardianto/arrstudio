<?php
// docs/faq.php
?>
<section class="docs-section" data-doc="faq">
  <h1 data-anchor="faq">FAQ <a class="anchor" href="#faq">#</a></h1>
  <p>Pertanyaan yang sering ditanyakan.</p>

  <div class="faq-item">
    <div class="faq-q">
      <span>Kenapa preview Roblox beda dari HTML?</span>
      <span class="faq-arrow">▶</span>
    </div>
    <div class="faq-a">
      Roblox tidak support semua CSS (flex, grid, shadow). Konversi pakai absolute
      positioning jadi ~90% mirip. Untuk hasil terbaik, pastikan elemen pakai
      <code>position:absolute</code> dengan <code>left</code>/<code>top</code> eksplisit.
    </div>
  </div>

  <div class="faq-item">
    <div class="faq-q">
      <span>Image tidak muncul di Roblox?</span>
      <span class="faq-arrow">▶</span>
    </div>
    <div class="faq-a">
      Roblox butuh asset ID (<code>rbxassetid://123456</code>), bukan URL langsung.
      Upload dulu ke Roblox, lalu copy asset ID-nya.
    </div>
  </div>

  <div class="faq-item">
    <div class="faq-q">
      <span>Bisa convert HTML kompleks?</span>
      <span class="faq-arrow">▶</span>
    </div>
    <div class="faq-a">
      Ya, tapi elemen non-supported (canvas, svg, video, iframe) akan di-skip
      dan tercatat di tab <code>Report</code>.
    </div>
  </div>

  <div class="faq-item">
    <div class="faq-q">
      <span>Output-nya bisa langsung dipakai?</span>
      <span class="faq-arrow">▶</span>
    </div>
    <div class="faq-a">
      Bisa. <code>RBXMX</code> → import ke Studio. <code>Plugin</code> → auto-generate.
      <code>Lua</code> → paste ke Script.
    </div>
  </div>

  <div class="faq-item">
    <div class="faq-q">
      <span>Support Roblox Studio Lite (mobile)?</span>
      <span class="faq-arrow">▶</span>
    </div>
    <div class="faq-a">
      Belum. Studio Lite adalah game komunitas, bukan produk resmi Roblox.
      Untuk development serius tetap butuh Roblox Studio desktop (Windows/macOS).
    </div>
  </div>
</section>