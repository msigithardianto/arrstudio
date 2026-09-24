<?php
// app/views/partials/loader.php
?>
<div id="arrrLoader" class="arrr-loader">
  <div class="arrr-loader-inner">

    <div class="arrr-loader-line top"></div>

    <div class="arrr-loader-logo-wrap">
      <img src="<?= asset('logo.png') ?>" alt="ARRR Studio" class="arrr-loader-logo">
    </div>

    <div class="arrr-loader-brand">
      ARRR <span>STUDIO</span>
    </div>

    <div class="arrr-loader-tagline">
      ROBLOX UI CONVERTER
    </div>

    <div class="arrr-loader-progress">
      <div class="arrr-loader-progress-bar" id="arrrLoaderBar"></div>
    </div>

    <div class="arrr-loader-percent" id="arrrLoaderPercent">0%</div>

    <div class="arrr-loader-line bottom"></div>

  </div>
</div>

<script>
  (function () {
    'use strict';
    const loader = document.getElementById('arrrLoader');
    const bar = document.getElementById('arrrLoaderBar');
    const percentEl = document.getElementById('arrrLoaderPercent');
    if (!loader || !bar || !percentEl) return;

    let progress = 0;
    let done = false;

    function tick() {
      if (done) return;
      let increment;
      if (progress < 30)      increment = 4 + Math.random() * 3;
      else if (progress < 70) increment = 1.5 + Math.random() * 2;
      else if (progress < 95) increment = 0.4 + Math.random() * 0.8;
      else                    increment = 0.15;

      progress = Math.min(progress + increment, 99);
      bar.style.width = progress + '%';
      percentEl.textContent = Math.floor(progress) + '%';

      if (progress < 99) setTimeout(tick, 80 + Math.random() * 120);
    }
    tick();

    window.arrrLoaderFinish = function () {
      if (done) return;
      done = true;
      bar.style.width = '100%';
      percentEl.textContent = '100%';
      setTimeout(() => {
        loader.classList.add('hidden');
        setTimeout(() => {
          if (loader.parentNode) loader.parentNode.removeChild(loader);
        }, 900);
      }, 300);
    };

    if (document.readyState === 'complete') {
      setTimeout(window.arrrLoaderFinish, 1200);
    } else {
      window.addEventListener('load', () => {
        const minDelay = Math.max(0, 1500 - performance.now());
        setTimeout(window.arrrLoaderFinish, minDelay);
      });
    }

    setTimeout(() => { if (!done) window.arrrLoaderFinish(); }, 5000);
  })();
</script>