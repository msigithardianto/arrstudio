/* ============================================================
   assets/js/pages/landing.js — preview tabs + mobile nav landing
   ============================================================ */
(function () {
  'use strict';

  function initLanding() {
    // Guard di elemen dalam <main> (ikut diganti saat navigasi SPA)
    const nav = document.querySelector('.rbx-nav');
    if (!nav || nav.dataset.landingInit === '1') return;
    nav.dataset.landingInit = '1';

    // Preview tabs
    document.querySelectorAll('.rbx-preview-tab').forEach(tab => {
      tab.addEventListener('click', () => {
        const target = tab.dataset.tab;
        document.querySelectorAll('.rbx-preview-tab').forEach(t => t.classList.toggle('active', t === tab));
        document.querySelectorAll('.rbx-preview-body').forEach(p => {
          p.classList.toggle('rbx-hidden', p.dataset.pane !== target);
        });
      });
    });

    // Nav toggle
    const toggle = document.getElementById('rbxNavToggle');
    if (toggle) {
      toggle.addEventListener('click', () => nav.classList.toggle('rbx-nav--open'));
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLanding);
  } else {
    initLanding();
  }
  window.addEventListener('spa:navigated', initLanding);
})();
