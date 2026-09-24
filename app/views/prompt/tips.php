<?php
// prompt/tips.php — Tab 5: Tips
?>
<div class="pg-panel" data-panel="tips">
  <div class="pg-section">
    <div class="pg-section-title">
      <span class="num">💡</span>
      Tips Biar HTML Sukses Convert
    </div>
    <div class="pg-section-desc">
      Detail kecil yang bikin konversi HTML → Roblox jadi mulus.
    </div>

    <div class="pg-tips">
      <div class="pg-tip">
        <span class="pg-tip-icon">1️⃣</span>
        <div class="pg-tip-body">
          <strong>Sebutkan "position absolute" di prompt</strong><br>
          Biar AI nggak pakai flex/grid yang bikin convert gagal.
        </div>
      </div>

      <div class="pg-tip">
        <span class="pg-tip-icon">2️⃣</span>
        <div class="pg-tip-body">
          <strong>Sebutkan canvas 800×600</strong><br>
          Biar AI tau batas area. Elemen nggak boleh keluar canvas.
        </div>
      </div>

      <div class="pg-tip">
        <span class="pg-tip-icon">3️⃣</span>
        <div class="pg-tip-body">
          <strong>Kasih ukuran px eksplisit</strong><br>
          "width 200px, height 20px" — jangan "width: 50%".
        </div>
      </div>

      <div class="pg-tip">
        <span class="pg-tip-icon">4️⃣</span>
        <div class="pg-tip-body">
          <strong>Sebutkan tag yang boleh dipakai</strong><br>
          "Cuma div, span, p, button" biar AI nggak pakai canvas/svg.
        </div>
      </div>

      <div class="pg-tip">
        <span class="pg-tip-icon">5️⃣</span>
        <div class="pg-tip-body">
          <strong>Pakai emoji untuk icon</strong><br>
          ⚔️ 🛡️ 🧪 lebih gampang di-convert daripada img dengan URL.
        </div>
      </div>

      <div class="pg-tip">
        <span class="pg-tip-icon">6️⃣</span>
        <div class="pg-tip-body">
          <strong>Sebutkan toggle pattern</strong><br>
          "Pakai data-action='toggle' data-target='idPanel'" biar interaktif.
        </div>
      </div>

      <div class="pg-tip">
        <span class="pg-tip-icon">7️⃣</span>
        <div class="pg-tip-body">
          <strong>Hindari box-shadow</strong><br>
          Roblox nggak support. Pakai border + gradient biar tetap keren.
        </div>
      </div>

      <div class="pg-tip">
        <span class="pg-tip-icon">8️⃣</span>
        <div class="pg-tip-body">
          <strong>Kasih contoh warna hex</strong><br>
          "#d4af37 untuk gold, #0a0a0d untuk background" biar AI nggak ngasal.
        </div>
      </div>

      <div class="pg-tip">
        <span class="pg-tip-icon">👑</span>
        <div class="pg-tip-body">
          <strong>Billboard nametag butuh BillboardGui</strong><br>
          Bukan ScreenGui. Nempel di Head pakai StudsOffsetWorldSpace.
        </div>
      </div>

      <div class="pg-tip bad">
        <span class="pg-tip-icon">❌</span>
        <div class="pg-tip-body">
          <strong>JANGAN pakai flex/grid</strong><br>
          Converter nggak bisa baca layout modern. Hasilnya elemen numpuk di (0,0).
        </div>
      </div>

      <div class="pg-tip bad">
        <span class="pg-tip-icon">❌</span>
        <div class="pg-tip-body">
          <strong>JANGAN pakai % atau vh</strong><br>
          Roblox pakai UDim2 offset/scale. Converter cuma handle px.
        </div>
      </div>

      <div class="pg-tip bad">
        <span class="pg-tip-icon">❌</span>
        <div class="pg-tip-body">
          <strong>JANGAN pakai tag unsupported</strong><br>
          canvas, svg, video, iframe, form, table → di-skip converter.
        </div>
      </div>

      <div class="pg-tip bad">
        <span class="pg-tip-icon">❌</span>
        <div class="pg-tip-body">
          <strong>JANGAN pakai pseudo-element</strong><br>
          ::before, ::after nggak ada di Roblox. Pakai div terpisah.
        </div>
      </div>
    </div>
  </div>
</div>