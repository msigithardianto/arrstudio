<?php
// layout/styles-prompt.php — CSS khusus halaman prompt
?>
<style data-spa-style="prompt">
  /* ============================================================
     LAYOUT
     ============================================================ */
  .pg-wrap {
    flex: 1;
    overflow-y: auto;
    background: var(--bg);
    position: relative;
  }
  .pg-inner {
    max-width: 1100px;
    margin: 0 auto;
    padding: 40px 40px 120px;
  }

  .pg-hero {
    text-align: center;
    margin-bottom: 48px;
  }
  .pg-hero-icon {
    font-size: 48px;
    margin-bottom: 12px;
    filter: drop-shadow(0 0 20px rgba(212, 175, 55, 0.4));
  }
  .pg-hero-title {
    font-size: 36px;
    font-weight: 800;
    letter-spacing: 0.02em;
    background: linear-gradient(135deg, #f4d03f, #d4af37);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    -webkit-text-fill-color: transparent;
    margin-bottom: 12px;
  }
  .pg-hero-sub {
    color: var(--text-dim);
    font-size: 15px;
    line-height: 1.6;
    max-width: 680px;
    margin: 0 auto;
  }
  .pg-hero-sub strong { color: var(--gold); }

  .pg-tabs {
    display: flex;
    gap: 4px;
    padding: 4px;
    background: rgba(10, 10, 13, 0.6);
    border: 1px solid var(--edge);
    border-radius: 12px;
    margin-bottom: 28px;
    overflow-x: auto;
    position: relative;
    z-index: 100;
    scrollbar-width: none;
  }
  .pg-tabs::-webkit-scrollbar { display: none; }
  .pg-tab {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: transparent;
    border: none;
    border-radius: 8px;
    color: var(--text-dim);
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
    flex-shrink: 0;
    user-select: none;
    position: relative;
    z-index: 1;
  }
  .pg-tab:hover { color: var(--text); }
  .pg-tab.active {
    color: #f4f4f6;
    background: linear-gradient(180deg,
      rgba(212, 175, 55, 0.15),
      rgba(212, 175, 55, 0.05));
    box-shadow:
      inset 0 1px 0 rgba(212, 175, 55, 0.2),
      0 0 12px rgba(212, 175, 55, 0.1);
  }
  .pg-tab-icon { font-size: 14px; pointer-events: none; }

  .pg-panel { display: none; }
  .pg-panel.active {
    display: block;
    animation: pgFadeIn 0.25s ease;
  }
  @keyframes pgFadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .pg-section { margin-bottom: 32px; }
  .pg-section-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .pg-section-title .num {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: linear-gradient(135deg, #d4af37, #8a7328);
    color: #0a0a0d;
    font-size: 13px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .pg-section-desc {
    font-size: 13.5px;
    color: var(--text-dim);
    line-height: 1.7;
    margin-bottom: 16px;
  }
  .pg-section-desc code {
    font-family: var(--mono);
    background: rgba(212, 175, 55, 0.1);
    color: var(--gold-bright);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
  }

  .pg-compare {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 20px;
  }
  .pg-compare-card {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--edge);
    background: #121216;
  }
  .pg-compare-head {
    padding: 10px 14px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .pg-compare-card.bad .pg-compare-head {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border-bottom: 1px solid rgba(239, 68, 68, 0.2);
  }
  .pg-compare-card.good .pg-compare-head {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
    border-bottom: 1px solid rgba(34, 197, 94, 0.2);
  }
  .pg-compare-body {
    padding: 14px;
    font-family: var(--mono);
    font-size: 12px;
    line-height: 1.6;
    color: var(--text-dim);
    white-space: pre-wrap;
    word-break: break-word;
  }
  .pg-compare-card.good .pg-compare-body { color: var(--text); }

  .pg-cheat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 12px;
  }
  .pg-cheat-card {
    background: #121216;
    border: 1px solid var(--edge);
    border-radius: 10px;
    padding: 14px 16px;
    transition: all 0.2s ease;
  }
  .pg-cheat-card:hover {
    border-color: rgba(212, 175, 55, 0.3);
    box-shadow: 0 0 20px rgba(212, 175, 55, 0.06);
  }
  .pg-cheat-head {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    font-size: 12px;
    font-weight: 700;
    color: var(--gold);
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  .pg-cheat-icon {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.15), rgba(212, 175, 55, 0.03));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
  }
  .pg-cheat-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }
  .pg-cheat-tag {
    padding: 4px 9px;
    background: rgba(192, 192, 200, 0.06);
    border: 1px solid rgba(192, 192, 200, 0.1);
    border-radius: 6px;
    font-size: 11px;
    color: var(--silver);
    font-family: var(--mono);
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
    position: relative;
    z-index: 1;
  }
  .pg-cheat-tag:hover {
    background: rgba(212, 175, 55, 0.12);
    border-color: rgba(212, 175, 55, 0.4);
    color: var(--gold);
  }
  .pg-cheat-tag.copied {
    background: rgba(34, 197, 94, 0.15);
    border-color: rgba(34, 197, 94, 0.4);
    color: #22c55e;
  }
  .pg-cheat-tag.bad {
    background: rgba(239, 68, 68, 0.08);
    border-color: rgba(239, 68, 68, 0.2);
    color: #ef4444;
    text-decoration: line-through;
  }
  .pg-cheat-tag.bad:hover {
    background: rgba(239, 68, 68, 0.15);
    border-color: rgba(239, 68, 68, 0.4);
    color: #f87171;
  }

  .pg-template {
    background: #121216;
    border: 1px solid var(--edge);
    border-radius: 12px;
    margin-bottom: 12px;
    overflow: hidden;
    transition: all 0.2s ease;
  }
  .pg-template.open {
    border-color: rgba(212, 175, 55, 0.3);
    box-shadow: 0 0 30px rgba(212, 175, 55, 0.08);
  }
  .pg-template-head {
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    cursor: pointer;
    user-select: none;
    position: relative;
    z-index: 2;
    background: transparent;
    transition: background 0.15s ease;
  }
  .pg-template-head:hover {
    background: rgba(212, 175, 55, 0.04);
  }
  .pg-template-head:hover .pg-template-name { color: var(--gold); }
  .pg-template-left {
    display: flex;
    align-items: center;
    gap: 12px;
    pointer-events: none;
  }
  .pg-template-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.15), rgba(212, 175, 55, 0.03));
    border: 1px solid rgba(212, 175, 55, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
  }
  .pg-template-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    transition: color 0.15s ease;
  }
  .pg-template-sub {
    font-size: 11px;
    color: var(--text-mute);
    margin-top: 2px;
  }
  .pg-template-arrow {
    color: var(--text-mute);
    transition: transform 0.2s ease;
    font-size: 12px;
    pointer-events: none;
    flex-shrink: 0;
  }
  .pg-template.open .pg-template-arrow {
    transform: rotate(90deg);
    color: var(--gold);
  }
  .pg-template-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s cubic-bezier(.22,1,.36,1);
    position: relative;
    z-index: 1;
  }
  .pg-template.open .pg-template-body {
    max-height: 1600px;
  }
  .pg-template-inner {
    padding: 16px 18px 18px;
    border-top: 1px solid var(--edge);
  }

  .pg-code {
    position: relative;
    background: #0e0e12;
    border: 1px solid var(--edge);
    border-radius: 10px;
    overflow: hidden;
    margin: 12px 0;
  }
  .pg-code-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 14px;
    background: #121216;
    border-bottom: 1px solid var(--edge);
    font-size: 11px;
    color: var(--text-mute);
    font-family: var(--mono);
  }
  .pg-code-head .lang {
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .pg-code-head .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--gold);
    box-shadow: 0 0 6px var(--gold);
  }
  .pg-code-body {
    padding: 14px 16px;
    font-family: var(--mono);
    font-size: 12px;
    line-height: 1.65;
    color: var(--text);
    white-space: pre-wrap;
    word-break: break-word;
    max-height: 500px;
    overflow-y: auto;
  }
  .pg-copy {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    background: rgba(192, 192, 200, 0.06);
    border: 1px solid rgba(192, 192, 200, 0.12);
    border-radius: 6px;
    color: var(--text-dim);
    font-size: 10.5px;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
    position: relative;
    z-index: 10;
  }
  .pg-copy:hover {
    background: rgba(212, 175, 55, 0.15);
    border-color: rgba(212, 175, 55, 0.4);
    color: var(--gold);
  }
  .pg-copy.copied {
    background: rgba(34, 197, 94, 0.15);
    border-color: rgba(34, 197, 94, 0.4);
    color: #22c55e;
  }

  .pg-form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 20px;
  }
  .pg-field { display: flex; flex-direction: column; gap: 6px; }
  .pg-field.full { grid-column: 1 / -1; }
  .pg-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }
  .pg-input,
  .pg-select,
  .pg-textarea {
    padding: 10px 14px;
    background: rgba(10, 10, 13, 0.7);
    border: 1px solid var(--edge);
    border-radius: 8px;
    color: var(--text);
    font-size: 13px;
    font-family: inherit;
    outline: none;
    transition: all 0.15s ease;
    resize: vertical;
  }
  .pg-textarea { min-height: 80px; font-family: var(--mono); font-size: 12px; line-height: 1.5; }
  .pg-input:focus,
  .pg-select:focus,
  .pg-textarea:focus {
    border-color: rgba(212, 175, 55, 0.4);
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.08);
  }
  .pg-input::placeholder { color: var(--text-mute); }

  .pg-checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }
  .pg-checkbox {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: rgba(38, 38, 46, 0.5);
    border: 1px solid var(--edge);
    border-radius: 8px;
    font-size: 12px;
    color: var(--text-dim);
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
    position: relative;
    z-index: 1;
  }
  .pg-checkbox:hover { border-color: rgba(212, 175, 55, 0.3); }
  .pg-checkbox input { display: none; }
  .pg-checkbox.checked {
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.15), rgba(212, 175, 55, 0.05));
    border-color: rgba(212, 175, 55, 0.5);
    color: var(--gold);
  }
  .pg-checkbox-icon {
    width: 14px;
    height: 14px;
    border: 1.5px solid currentColor;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    opacity: 0.5;
    pointer-events: none;
  }
  .pg-checkbox.checked .pg-checkbox-icon { opacity: 1; }

  .pg-btn-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
  }
  .pg-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 22px;
    border-radius: 10px;
    border: 1px solid transparent;
    font-size: 13px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
  }
  .pg-btn:active { transform: scale(0.97); }
  .pg-btn-primary {
    background: linear-gradient(135deg, #8a7328, #d4af37);
    border-color: rgba(244, 208, 63, 0.5);
    color: #0a0a0d;
    box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
  }
  .pg-btn-primary:hover {
    background: linear-gradient(135deg, #d4af37, #f4d03f);
    box-shadow: 0 0 30px rgba(212, 175, 55, 0.5);
  }
  .pg-btn-ghost {
    background: rgba(192, 192, 200, 0.06);
    border-color: rgba(192, 192, 200, 0.15);
    color: var(--silver);
  }
  .pg-btn-ghost:hover {
    background: rgba(192, 192, 200, 0.12);
    color: var(--text);
  }

  .pg-output {
    background: #0e0e12;
    border: 1px solid rgba(212, 175, 55, 0.25);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 0 30px rgba(212, 175, 55, 0.05);
  }
  .pg-output-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    background: linear-gradient(90deg, rgba(212, 175, 55, 0.08), transparent);
    border-bottom: 1px solid var(--edge);
  }
  .pg-output-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 700;
    color: var(--gold);
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }
  .pg-output-title .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #d4af37;
    box-shadow: 0 0 6px #d4af37;
  }
  .pg-output-body {
    padding: 16px;
    font-family: var(--mono);
    font-size: 12.5px;
    line-height: 1.7;
    color: var(--text);
    white-space: pre-wrap;
    word-break: break-word;
    max-height: 500px;
    overflow-y: auto;
    min-height: 80px;
  }
  .pg-output-body:empty::before {
    content: 'Prompt akan muncul di sini setelah kamu klik Generate...';
    color: var(--text-mute);
    font-style: italic;
    font-family: var(--sans);
    font-size: 13px;
  }

  .pg-tips {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 12px;
    margin-top: 16px;
  }
  .pg-tip {
    display: flex;
    gap: 12px;
    padding: 14px 16px;
    background: #121216;
    border: 1px solid var(--edge);
    border-left: 3px solid var(--gold);
    border-radius: 0 10px 10px 0;
  }
  .pg-tip.bad { border-left-color: #ef4444; }
  .pg-tip-icon {
    font-size: 18px;
    flex-shrink: 0;
  }
  .pg-tip-body {
    font-size: 12.5px;
    line-height: 1.6;
    color: var(--text-dim);
  }
  .pg-tip-body strong { color: var(--gold); }
  .pg-tip.bad .pg-tip-body strong { color: #ef4444; }

  @media (max-width: 900px) {
    .pg-compare { grid-template-columns: 1fr; }
    .pg-form { grid-template-columns: 1fr; }
  }
  @media (max-width: 768px) {
    .pg-inner { padding: 24px 20px 80px; }
    .pg-hero-title { font-size: 26px; }
    .pg-hero-icon { font-size: 36px; }
    .pg-tabs { padding: 3px; }
    .pg-tab { padding: 8px 14px; font-size: 12px; }
    .pg-section-title { font-size: 17px; }
  }
</style>