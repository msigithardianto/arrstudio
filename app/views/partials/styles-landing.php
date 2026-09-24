<?php
// app/views/partials/styles-landing.php
?>
<style data-spa-style="landing">
/* ============================================================
   ARRR STUDIO — MODERN CLEAN LANDING
   ============================================================ */

html:has(.rbx) body,
body:has(.rbx),
body.landing-active {
  overflow-y: auto !important;
  height: auto !important;
  min-height: 100vh;
  display: block !important;
  background-color: #0b0b0f !important;
}

.rbx {
  --bg: #0b0b0f;
  --bg-2: #101015;
  --surface: #16161d;
  --surface-2: #1c1c24;
  --surface-3: #23232d;

  --border: rgba(255, 255, 255, 0.07);
  --border-2: rgba(255, 255, 255, 0.12);

  --text: #f2f2f4;
  --text-2: #a3a3ad;
  --text-3: #5f5f6a;

  --gold: #f4c430;
  --gold-2: #d4af37;
  --gold-soft: rgba(244, 196, 48, 0.12);
  --gold-border: rgba(244, 196, 48, 0.3);
  --gold-grad: linear-gradient(135deg, #ffd95e 0%, #f4c430 40%, #d4af37 100%);

  --green: #22c55e;

  --radius-sm: 6px;
  --radius: 10px;
  --radius-lg: 16px;
  --radius-xl: 20px;

  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.3);
  --shadow: 0 4px 16px rgba(0, 0, 0, 0.35);
  --shadow-lg: 0 20px 50px -20px rgba(0, 0, 0, 0.7);

  min-height: 100vh;
  background: var(--bg);
  color: var(--text);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-size: 15px;
  line-height: 1.55;
  -webkit-font-smoothing: antialiased;
  text-rendering: optimizeLegibility;
}

.rbx * { box-sizing: border-box; }
.rbx-hidden { display: none !important; }

/* ============================================================
   BUTTONS
   ============================================================ */
.rbx-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 18px;
  font-family: inherit;
  font-size: 14px;
  font-weight: 600;
  letter-spacing: 0.005em;
  text-decoration: none;
  border-radius: var(--radius);
  border: 1px solid transparent;
  cursor: pointer;
  transition: transform 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
  white-space: nowrap;
  user-select: none;
}

.rbx-btn--sm { padding: 7px 14px; font-size: 13px; border-radius: 8px; }
.rbx-btn--lg { padding: 13px 24px; font-size: 15px; border-radius: 12px; }

.rbx-btn--primary {
  background: var(--gold-grad);
  color: #0a0a0a;
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.4),
    0 2px 0 rgba(138, 115, 40, 0.6),
    0 4px 16px rgba(244, 196, 48, 0.25);
}
.rbx-btn--primary:hover {
  transform: translateY(-1px);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.5),
    0 3px 0 rgba(138, 115, 40, 0.7),
    0 8px 24px rgba(244, 196, 48, 0.35);
}
.rbx-btn--primary:active { transform: translateY(1px); box-shadow: 0 1px 0 rgba(138, 115, 40, 0.6); }

.rbx-btn--ghost {
  background: var(--surface);
  color: var(--text);
  border-color: var(--border-2);
}
.rbx-btn--ghost:hover {
  background: var(--surface-2);
  border-color: rgba(255, 255, 255, 0.2);
}

/* ============================================================
   NAVBAR
   ============================================================ */
.rbx-nav {
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(11, 11, 15, 0.85);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-bottom: 1px solid var(--border);
}

.rbx-nav-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 12px 20px;
  display: flex;
  align-items: center;
  gap: 24px;
  position: relative;
}

.rbx-logo {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: var(--text);
  flex-shrink: 0;
}

.rbx-logo-mark {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: var(--gold-grad);
  color: #0a0a0a;
  font-weight: 900;
  font-size: 17px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 2px 8px rgba(244, 196, 48, 0.25);
}
.rbx-logo-mark--sm { width: 28px; height: 28px; font-size: 15px; border-radius: 7px; }

.rbx-logo-text {
  font-size: 15px;
  font-weight: 600;
  letter-spacing: 0.01em;
}
.rbx-logo-text strong {
  background: var(--gold-grad);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  font-weight: 800;
}

.rbx-nav-links {
  display: flex;
  gap: 4px;
  margin-left: 8px;
}

.rbx-nav-links a {
  padding: 8px 12px;
  font-size: 13.5px;
  font-weight: 500;
  color: var(--text-2);
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.15s ease;
}
.rbx-nav-links a:hover {
  color: var(--text);
  background: var(--surface);
}

.rbx-nav-actions {
  margin-left: auto;
  display: flex;
  gap: 8px;
}

.rbx-nav-toggle {
  display: none;
  width: 40px;
  height: 40px;
  border: none;
  background: transparent;
  cursor: pointer;
  padding: 10px;
  flex-direction: column;
  justify-content: space-between;
  margin-left: auto;
  border-radius: 8px;
}
.rbx-nav-toggle:hover { background: var(--surface); }
.rbx-nav-toggle span {
  display: block;
  height: 2px;
  background: var(--text);
  border-radius: 2px;
  transition: transform 0.2s ease, opacity 0.2s ease;
}

/* ============================================================
   HERO
   ============================================================ */
.rbx-hero {
  max-width: 1200px;
  margin: 0 auto;
  padding: 80px 20px 60px;
}

.rbx-hero-grid {
  display: grid;
  grid-template-columns: 1.05fr 1fr;
  gap: 60px;
  align-items: center;
}

.rbx-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 5px 12px;
  font-size: 12px;
  font-weight: 600;
  color: var(--gold);
  background: var(--gold-soft);
  border: 1px solid var(--gold-border);
  border-radius: 99px;
  margin-bottom: 24px;
}

.rbx-badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--gold);
  box-shadow: 0 0 8px var(--gold);
  animation: rbx-pulse 2s ease-in-out infinite;
}

@keyframes rbx-pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(0.85); }
}

.rbx-title {
  font-size: clamp(34px, 4.5vw, 56px);
  font-weight: 800;
  line-height: 1.05;
  letter-spacing: -0.035em;
  color: #ffffff;
  margin: 0 0 20px;
}

.rbx-title-hl {
  background: var(--gold-grad);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

.rbx-subtitle {
  font-size: 16px;
  line-height: 1.65;
  color: var(--text-2);
  max-width: 480px;
  margin: 0 0 32px;
}

.rbx-hero-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 32px;
}

.rbx-hero-meta {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  font-size: 13px;
  color: var(--text-2);
}

.rbx-meta-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.rbx-meta-item svg { color: var(--gold); flex-shrink: 0; }

/* ═══════════ PREVIEW ═══════════ */
.rbx-preview {
  background: var(--surface);
  border: 1px solid var(--border-2);
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-lg);
}

.rbx-preview-head {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  background: rgba(0, 0, 0, 0.25);
  border-bottom: 1px solid var(--border);
}

.rbx-preview-dots { display: flex; gap: 5px; }
.rbx-preview-dots span { width: 10px; height: 10px; border-radius: 50%; }
.rbx-preview-dots span:nth-child(1) { background: #ff5f56; }
.rbx-preview-dots span:nth-child(2) { background: #ffbd2e; }
.rbx-preview-dots span:nth-child(3) { background: #27c93f; }

.rbx-preview-tabs {
  display: flex;
  gap: 2px;
  flex: 1;
  min-width: 0;
}

.rbx-preview-tab {
  padding: 5px 11px;
  font-family: 'JetBrains Mono', 'Courier New', monospace;
  font-size: 11.5px;
  font-weight: 500;
  color: var(--text-3);
  background: transparent;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.rbx-preview-tab:hover { color: var(--text-2); background: rgba(255, 255, 255, 0.04); }
.rbx-preview-tab.active { color: var(--gold); background: var(--gold-soft); }

.rbx-preview-live {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 9px;
  font-size: 10.5px;
  font-weight: 700;
  color: var(--green);
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.25);
  border-radius: 99px;
  flex-shrink: 0;
}

.rbx-preview-live-dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: var(--green);
  box-shadow: 0 0 6px var(--green);
  animation: rbx-pulse 2s ease-in-out infinite;
}

.rbx-preview-body {
  padding: 20px;
  min-height: 200px;
  background: rgba(0, 0, 0, 0.15);
}

.rbx-code {
  margin: 0;
  font-family: 'JetBrains Mono', 'Courier New', monospace;
  font-size: 12.5px;
  line-height: 1.7;
  color: #d4d4d8;
  white-space: pre;
  overflow-x: auto;
}

.rbx-tree {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-family: 'JetBrains Mono', 'Courier New', monospace;
  font-size: 13px;
  color: var(--text-2);
}

.rbx-tree-row {
  padding: 6px 10px;
  border-radius: 6px;
  transition: all 0.15s ease;
}
.rbx-tree-row:hover { background: var(--gold-soft); color: var(--text); }
.rbx-tree-l0 { color: #fff; font-weight: 700; }
.rbx-tree-l1 { padding-left: 22px; }
.rbx-tree-l2 { padding-left: 44px; }

.rbx-preview-foot {
  padding: 14px 20px;
  background: rgba(0, 0, 0, 0.25);
  border-top: 1px solid var(--border);
}

.rbx-preview-stats { display: flex; gap: 28px; }

.rbx-preview-stat { display: flex; align-items: baseline; gap: 6px; }

.rbx-preview-stat-num {
  font-size: 20px;
  font-weight: 800;
  background: var(--gold-grad);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  letter-spacing: -0.02em;
}

.rbx-preview-stat-lbl {
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--text-3);
}

/* ============================================================
   STEPS
   ============================================================ */
.rbx-steps {
  display: grid;
  grid-template-columns: 1fr auto 1fr auto 1fr;
  gap: 20px;
  align-items: center;
}

.rbx-step {
  padding: 24px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  transition: all 0.25s ease;
}
.rbx-step:hover {
  border-color: var(--gold-border);
  background: var(--surface-2);
}

.rbx-step-num {
  display: inline-block;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.15em;
  color: var(--gold);
  margin-bottom: 12px;
  padding: 3px 9px;
  background: var(--gold-soft);
  border: 1px solid var(--gold-border);
  border-radius: 6px;
}

.rbx-step-title {
  font-size: 16px;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 8px;
}

.rbx-step-desc {
  font-size: 13.5px;
  line-height: 1.6;
  color: var(--text-2);
  margin: 0;
}

.rbx-step-arrow {
  color: var(--text-3);
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ============================================================
   SECTIONS
   ============================================================ */
.rbx-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 80px 20px;
}

.rbx-section-head {
  text-align: center;
  max-width: 640px;
  margin: 0 auto 48px;
}

.rbx-h2 {
  font-size: clamp(26px, 3.5vw, 40px);
  font-weight: 800;
  line-height: 1.15;
  letter-spacing: -0.03em;
  color: #ffffff;
  margin: 0 0 12px;
}

.rbx-section-sub {
  font-size: 16px;
  color: var(--text-2);
  margin: 0;
}

/* ============================================================
   COMPARISON
   ============================================================ */
.rbx-compare {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.rbx-compare-card {
  padding: 28px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  display: flex;
  flex-direction: column;
  transition: all 0.25s ease;
}

.rbx-compare-card--best {
  background: linear-gradient(180deg, rgba(244, 196, 48, 0.04), var(--surface));
  border-color: var(--gold-border);
  box-shadow: 0 20px 50px -20px rgba(244, 196, 48, 0.25);
}

.rbx-compare-head { margin-bottom: 24px; }

.rbx-compare-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--text);
}

.rbx-compare-list {
  list-style: none;
  padding: 0;
  margin: 0 0 24px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  flex: 1;
}

.rbx-compare-list li {
  display: flex;
  gap: 12px;
  align-items: flex-start;
}

.rbx-compare-list li > div {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.rbx-compare-list strong {
  font-size: 14px;
  font-weight: 600;
  color: var(--text);
}

.rbx-compare-list span {
  font-size: 12.5px;
  color: var(--text-3);
}

.rbx-ok, .rbx-no {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 800;
  margin-top: 1px;
}

.rbx-ok { background: var(--gold-soft); color: var(--gold); border: 1px solid var(--gold-border); }
.rbx-no { background: rgba(255, 255, 255, 0.04); color: var(--text-3); border: 1px solid var(--border-2); }

.rbx-compare-foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 18px;
  border-top: 1px solid var(--border);
}

.rbx-compare-foot-lbl {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--text-3);
}

.rbx-compare-foot-val {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-2);
}
.rbx-compare-foot-val--best {
  font-size: 18px;
  background: var(--gold-grad);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* ============================================================
   FEATURES
   ============================================================ */
.rbx-features {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}

.rbx-feature {
  padding: 24px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.rbx-feature:hover {
  background: var(--surface-2);
  border-color: var(--gold-border);
  transform: translateY(-3px);
  box-shadow: 0 12px 32px -12px rgba(0, 0, 0, 0.6);
}

.rbx-feature-icon {
  width: 42px;
  height: 42px;
  border-radius: 11px;
  background: var(--gold-soft);
  border: 1px solid var(--gold-border);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--gold);
  margin-bottom: 18px;
  transition: all 0.25s ease;
}

.rbx-feature:hover .rbx-feature-icon {
  transform: scale(1.06);
  box-shadow: 0 6px 20px rgba(244, 196, 48, 0.2);
}

.rbx-feature-icon svg { width: 20px; height: 20px; }

.rbx-feature h3 {
  font-size: 16px;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 8px;
  letter-spacing: -0.01em;
}

.rbx-feature p {
  font-size: 13.5px;
  line-height: 1.6;
  color: var(--text-2);
  margin: 0;
}

/* ============================================================
   CTA
   ============================================================ */
.rbx-cta {
  position: relative;
  padding: 64px 40px;
  background:
    radial-gradient(ellipse 70% 90% at 50% 0%, rgba(244, 196, 48, 0.12), transparent 60%),
    linear-gradient(180deg, var(--surface) 0%, var(--bg-2) 100%);
  border: 1px solid var(--gold-border);
  border-radius: var(--radius-xl);
  overflow: hidden;
  text-align: center;
  box-shadow: 0 20px 60px -30px rgba(244, 196, 48, 0.3);
}

.rbx-cta-inner {
  position: relative;
  max-width: 560px;
  margin: 0 auto;
}

.rbx-cta-title {
  font-size: clamp(24px, 3.5vw, 34px);
  font-weight: 800;
  letter-spacing: -0.03em;
  color: #ffffff;
  margin: 0 0 12px;
  line-height: 1.15;
}

.rbx-cta-sub {
  font-size: 15px;
  color: var(--text-2);
  margin: 0 0 28px;
  line-height: 1.6;
}

.rbx-cta-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 24px;
}

.rbx-cta-note {
  display: flex;
  justify-content: center;
  gap: 20px;
  flex-wrap: wrap;
  font-size: 12.5px;
  color: var(--text-3);
}

.rbx-cta-note-item {
  display: inline-flex;
  align-items: center;
  gap: 5px;
}
.rbx-cta-note-item svg { color: var(--gold); }

/* ============================================================
   FOOTER
   ============================================================ */
.rbx-footer {
  border-top: 1px solid var(--border);
  padding: 48px 20px 32px;
  background: var(--bg);
}

.rbx-footer-inner {
  max-width: 1200px;
  margin: 0 auto 32px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 32px;
  flex-wrap: wrap;
}

.rbx-footer-brand {
  display: flex;
  align-items: center;
  gap: 12px;
}

.rbx-footer-name {
  font-size: 15px;
  font-weight: 600;
  color: var(--text);
}
.rbx-footer-name strong {
  background: var(--gold-grad);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  font-weight: 800;
}

.rbx-footer-tag {
  font-size: 12px;
  color: var(--text-3);
  margin-top: 2px;
}

.rbx-footer-links {
  display: flex;
  gap: 24px;
  flex-wrap: wrap;
}

.rbx-footer-links a {
  font-size: 13.5px;
  color: var(--text-2);
  text-decoration: none;
  transition: color 0.15s ease;
}
.rbx-footer-links a:hover { color: var(--gold); }

.rbx-footer-bottom {
  max-width: 1200px;
  margin: 0 auto;
  padding-top: 24px;
  border-top: 1px solid var(--border);
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  font-size: 12px;
  color: var(--text-3);
}

.rbx-footer-sep { color: var(--text-3); }

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 1024px) {
  .rbx-hero-grid {
    grid-template-columns: 1fr;
    gap: 48px;
  }
  .rbx-hero { padding: 60px 20px 40px; }
  .rbx-features { grid-template-columns: repeat(2, 1fr); }

  .rbx-steps {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  .rbx-step-arrow {
    transform: rotate(90deg);
    padding: 4px 0;
  }
}

@media (max-width: 820px) {
  .rbx-nav-inner { gap: 12px; padding: 10px 16px; }
  .rbx-nav-links,
  .rbx-nav-actions { display: none; }
  .rbx-nav-toggle { display: flex; }

  .rbx-nav--open .rbx-nav-links {
    display: flex;
    flex-direction: column;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--bg);
    border-bottom: 1px solid var(--border);
    padding: 12px 16px;
    gap: 4px;
    margin-left: 0;
  }
  .rbx-nav--open .rbx-nav-links a { padding: 12px; font-size: 15px; }

  .rbx-nav--open .rbx-nav-actions {
    display: flex;
    flex-direction: column;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--bg);
    border-bottom: 1px solid var(--border);
    padding: 76px 16px 16px;
    gap: 8px;
    margin-left: 0;
  }

  .rbx-hero { padding: 48px 16px 32px; }
  .rbx-section { padding: 60px 16px; }
  .rbx-section-head { margin-bottom: 36px; }

  .rbx-compare { grid-template-columns: 1fr; }
  .rbx-features { grid-template-columns: 1fr; }

  .rbx-cta { padding: 48px 24px; border-radius: var(--radius-lg); }

  .rbx-footer-inner { flex-direction: column; align-items: flex-start; gap: 24px; }
  .rbx-footer-links { gap: 16px; }
}

@media (max-width: 480px) {
  .rbx-hero-actions { flex-direction: column; }
  .rbx-hero-actions .rbx-btn { width: 100%; }

  .rbx-cta-actions { flex-direction: column; }
  .rbx-cta-actions .rbx-btn { width: 100%; }

  .rbx-hero-meta { gap: 12px; font-size: 12px; }

  .rbx-preview-head { padding: 8px 10px; gap: 8px; }
  .rbx-preview-live { display: none; }
  .rbx-preview-body { padding: 14px; min-height: 160px; }
  .rbx-code { font-size: 11.5px; }
  .rbx-preview-stats { gap: 18px; }

  .rbx-title { font-size: 30px; }
  .rbx-h2 { font-size: 24px; }
}

@media (prefers-reduced-motion: reduce) {
  .rbx *, .rbx *::before, .rbx *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
</style>