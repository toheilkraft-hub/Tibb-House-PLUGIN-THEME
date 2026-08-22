<?php
/**
 * Template Name: HIPAA Private Page
 *
 * Completely standalone — no WordPress header, footer, or public styles.
 * Noindex, nofollow. Only reachable via the "Enter Secure Space" button
 * on the Contact page.
 *
 * @package Tibbhouse
 */

// Nonce will be fetched client-side via AJAX to avoid caching issues.
$ajax_url  = esc_url( admin_url( 'admin-ajax.php' ) );
$site_home = esc_url( home_url( '/' ) );
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive">
<meta name="referrer" content="no-referrer">
<title>Secure Patient Intake — Tibb House</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Cormorant+Garamond:wght@400;500;600&display=swap" rel="stylesheet">
<style>
/* ── Reset & Tokens ─────────────────────────────────────────────────────── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --navy:       #080f1e;
  --navy-card:  #0d1b2e;
  --navy-panel: #111e30;
  --navy-line:  rgba(255,255,255,.07);
  --gold:       #bc904f;
  --gold-light: #d4a96a;
  --gold-glow:  rgba(188,144,79,.18);
  --emerald:    #1a4a2e;
  --emerald-hi: #22613b;
  --green-text: #4ade80;
  --red-alert:  #f87171;
  --amber:      #fbbf24;
  --text:       #dde6f0;
  --text-muted: #7a8fa8;
  --text-dim:   #4a5e72;
  --radius:     12px;
  --radius-lg:  18px;
  --shadow:     0 8px 32px rgba(0,0,0,.4);
  --transition: .22s cubic-bezier(.4,0,.2,1);
}
html,body{min-height:100%;background:var(--navy);color:var(--text);font-family:'Inter',system-ui,sans-serif;font-size:15px;line-height:1.6}

/* ── Top bar ────────────────────────────────────────────────────────────── */
.si-topbar{display:flex;align-items:center;justify-content:space-between;padding:14px 24px;background:var(--navy-card);border-bottom:1px solid var(--navy-line);position:sticky;top:0;z-index:100}
.si-topbar-brand{display:flex;align-items:center;gap:10px;font-family:'Cormorant Garamond',Georgia,serif;font-size:1.15rem;font-weight:600;color:var(--text);text-decoration:none}
.si-topbar-brand svg{flex-shrink:0}
.si-topbar-badges{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.si-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:99px;font-size:11px;font-weight:600;letter-spacing:.04em;white-space:nowrap}
.si-badge-hipaa{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.25);color:var(--green-text)}
.si-badge-medplum{background:rgba(188,144,79,.1);border:1px solid rgba(188,144,79,.25);color:var(--gold)}
.si-badge-tls{background:rgba(255,255,255,.05);border:1px solid var(--navy-line);color:var(--text-muted)}
.si-back{display:inline-flex;align-items:center;gap:6px;color:var(--text-muted);font-size:13px;text-decoration:none;transition:color var(--transition)}
.si-back:hover{color:var(--gold)}

/* ── Page shell ─────────────────────────────────────────────────────────── */
.si-shell{max-width:780px;margin:0 auto;padding:32px 20px 80px}

/* ── Step wrapper ───────────────────────────────────────────────────────── */
.si-step{display:none;animation:si-fade .3s ease}
.si-step.active{display:block}
@keyframes si-fade{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

/* ── Progress bar ───────────────────────────────────────────────────────── */
.si-progress{margin-bottom:32px}
.si-progress-track{height:3px;background:var(--navy-line);border-radius:99px;overflow:hidden}
.si-progress-fill{height:100%;background:linear-gradient(90deg,var(--gold),var(--gold-light));border-radius:99px;transition:width .4s ease}
.si-progress-labels{display:flex;justify-content:space-between;margin-top:8px}
.si-progress-label{font-size:11px;color:var(--text-dim);font-weight:500}
.si-progress-label.done{color:var(--gold)}

/* ── Card ───────────────────────────────────────────────────────────────── */
.si-card{background:var(--navy-card);border:1px solid var(--navy-line);border-radius:var(--radius-lg);padding:32px;margin-bottom:20px}
.si-card-title{font-family:'Cormorant Garamond',Georgia,serif;font-size:1.55rem;font-weight:600;color:#fff;margin-bottom:6px;display:flex;align-items:center;gap:10px}
.si-card-sub{color:var(--text-muted);font-size:13.5px;margin-bottom:24px;line-height:1.5}

/* ── Gateway ────────────────────────────────────────────────────────────── */
.si-gateway-icon{width:64px;height:64px;background:var(--gold-glow);border:1px solid rgba(188,144,79,.3);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px}
.si-gateway-head{text-align:center;margin-bottom:32px}
.si-gateway-head h1{font-family:'Cormorant Garamond',Georgia,serif;font-size:2rem;font-weight:600;color:#fff;margin-bottom:8px}
.si-gateway-head p{color:var(--text-muted);max-width:480px;margin:0 auto;line-height:1.6}
.si-hipaa-notice{background:var(--navy-panel);border:1px solid rgba(74,222,128,.15);border-radius:var(--radius);padding:16px 20px;margin-bottom:28px;display:flex;gap:12px;align-items:flex-start}
.si-hipaa-notice svg{flex-shrink:0;margin-top:2px;color:var(--green-text)}
.si-hipaa-notice p{font-size:13px;color:var(--text-muted);line-height:1.6}
.si-hipaa-notice strong{color:var(--text)}

/* ── Path chooser ───────────────────────────────────────────────────────── */
.si-paths{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:24px}
@media(max-width:540px){.si-paths{grid-template-columns:1fr}}
.si-path-btn{background:var(--navy-panel);border:2px solid var(--navy-line);border-radius:var(--radius-lg);padding:28px 24px;cursor:pointer;text-align:left;transition:border-color var(--transition),background var(--transition),transform var(--transition);color:inherit;width:100%}
.si-path-btn:hover{border-color:var(--gold);background:var(--navy-card);transform:translateY(-2px)}
.si-path-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:14px}
.si-path-icon.upload{background:rgba(99,179,237,.12);color:#63b3ed}
.si-path-icon.manual{background:var(--gold-glow);color:var(--gold)}
.si-path-btn h3{font-size:1rem;font-weight:600;color:#fff;margin-bottom:6px}
.si-path-btn p{font-size:13px;color:var(--text-muted);line-height:1.5}
.si-path-tag{display:inline-block;margin-top:10px;font-size:11px;font-weight:600;letter-spacing:.04em;padding:3px 8px;border-radius:99px}
.si-path-tag.upload{background:rgba(99,179,237,.1);color:#63b3ed}
.si-path-tag.manual{background:var(--gold-glow);color:var(--gold)}

/* ── Upload zone ────────────────────────────────────────────────────────── */
.si-dropzone{border:2px dashed var(--navy-line);border-radius:var(--radius-lg);padding:48px 24px;text-align:center;transition:border-color var(--transition),background var(--transition);cursor:pointer;position:relative}
.si-dropzone.drag-over{border-color:var(--gold);background:var(--gold-glow)}
.si-dropzone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.si-dropzone-icon{width:52px;height:52px;background:var(--navy-panel);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;color:var(--text-muted)}
.si-dropzone h3{font-size:1rem;font-weight:500;color:#fff;margin-bottom:6px}
.si-dropzone p{font-size:13px;color:var(--text-muted)}
.si-file-types{display:flex;flex-wrap:wrap;justify-content:center;gap:6px;margin-top:14px}
.si-file-type{font-size:11px;font-weight:600;padding:3px 8px;border-radius:99px;background:var(--navy-panel);border:1px solid var(--navy-line);color:var(--text-dim)}
.si-file-list{margin-top:16px;display:flex;flex-direction:column;gap:8px}
.si-file-item{display:flex;align-items:center;gap:10px;background:var(--navy-panel);border:1px solid var(--navy-line);border-radius:8px;padding:10px 14px;font-size:13px}
.si-file-item svg{flex-shrink:0;color:var(--gold)}
.si-file-name{flex:1;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.si-file-size{color:var(--text-muted);font-size:12px;flex-shrink:0}
.si-file-remove{background:none;border:none;color:var(--text-muted);cursor:pointer;padding:2px;line-height:1;transition:color var(--transition)}
.si-file-remove:hover{color:var(--red-alert)}

/* ── AI extraction ──────────────────────────────────────────────────────── */
.si-extract-notice{background:linear-gradient(135deg,rgba(188,144,79,.08),rgba(99,179,237,.06));border:1px solid rgba(188,144,79,.2);border-radius:var(--radius);padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:12px;font-size:13px;color:var(--text-muted)}
.si-extract-dot{width:8px;height:8px;background:var(--gold);border-radius:50%;flex-shrink:0;animation:si-pulse 1.5s ease-in-out infinite}
@keyframes si-pulse{0%,100%{opacity:1}50%{opacity:.3}}
.si-auto-badge{display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:600;letter-spacing:.04em;padding:2px 7px;border-radius:99px;background:rgba(99,179,237,.12);border:1px solid rgba(99,179,237,.25);color:#63b3ed;vertical-align:middle;margin-left:6px}

/* ── Form fields ────────────────────────────────────────────────────────── */
.si-section{margin-bottom:28px}
.si-section-title{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--gold);margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid var(--navy-line)}
.si-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.si-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
.si-full{grid-column:1/-1}
@media(max-width:540px){.si-grid,.si-grid-3{grid-template-columns:1fr}.si-full{grid-column:auto}}
.si-field{display:flex;flex-direction:column;gap:5px}
.si-label{font-size:12.5px;font-weight:500;color:var(--text-muted)}
.si-label .req{color:var(--red-alert);margin-left:2px}
.si-label .opt{color:var(--text-dim);font-size:11px;font-weight:400;margin-left:4px}
.si-input,.si-select,.si-textarea{background:var(--navy-panel);border:1px solid var(--navy-line);border-radius:8px;padding:10px 13px;color:var(--text);font-family:inherit;font-size:14px;width:100%;transition:border-color var(--transition),box-shadow var(--transition);outline:none}
.si-input::placeholder,.si-textarea::placeholder{color:var(--text-dim)}
.si-input:focus,.si-select:focus,.si-textarea:focus{border-color:var(--gold);box-shadow:0 0 0 3px var(--gold-glow)}
.si-select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%237a8fa8' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:34px;cursor:pointer}
.si-select option{background:var(--navy-panel)}
.si-textarea{resize:vertical;min-height:90px;line-height:1.5}
.si-input.autofilled,.si-select.autofilled,.si-textarea.autofilled{border-color:rgba(99,179,237,.4);background:rgba(99,179,237,.05)}
.si-input-wrap{position:relative}
.si-input-wrap .si-input{padding-left:38px}
.si-input-icon{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--text-dim);pointer-events:none}

/* ── Range slider ───────────────────────────────────────────────────────── */
.si-range-wrap{display:flex;align-items:center;gap:12px}
.si-range{flex:1;accent-color:var(--gold)}
.si-range-val{min-width:28px;text-align:center;font-size:13px;font-weight:600;color:var(--gold)}

/* ── Pill toggle ────────────────────────────────────────────────────────── */
.si-pills{display:flex;flex-wrap:wrap;gap:8px}
.si-pill{padding:6px 14px;border-radius:99px;border:1px solid var(--navy-line);background:var(--navy-panel);color:var(--text-muted);font-size:13px;cursor:pointer;transition:all var(--transition);user-select:none}
.si-pill:hover{border-color:var(--gold);color:var(--gold)}
.si-pill.active{border-color:var(--gold);background:var(--gold-glow);color:var(--gold);font-weight:500}
.si-pill input{position:absolute;opacity:0;pointer-events:none}

/* ── Medication repeater ────────────────────────────────────────────────── */
.si-repeater{display:flex;flex-direction:column;gap:8px}
.si-repeater-item{display:grid;grid-template-columns:1fr 120px 100px auto;gap:8px;align-items:center}
@media(max-width:600px){.si-repeater-item{grid-template-columns:1fr 1fr;gap:8px}.si-repeater-item>*:last-child{grid-column:1/-1;justify-self:start}}
.si-repeater-remove{background:none;border:1px solid var(--navy-line);border-radius:6px;color:var(--text-dim);cursor:pointer;padding:9px 10px;transition:all var(--transition);line-height:1}
.si-repeater-remove:hover{border-color:var(--red-alert);color:var(--red-alert)}
.si-add-btn{display:inline-flex;align-items:center;gap:6px;background:none;border:1px dashed var(--navy-line);border-radius:8px;color:var(--text-muted);font-size:13px;cursor:pointer;padding:8px 14px;margin-top:4px;transition:all var(--transition);font-family:inherit}
.si-add-btn:hover{border-color:var(--gold);color:var(--gold)}

/* ── Checklist ──────────────────────────────────────────────────────────── */
.si-checklist{display:flex;flex-direction:column;gap:6px}
.si-check-group{background:var(--navy-panel);border:1px solid var(--navy-line);border-radius:var(--radius);padding:16px 18px}
.si-check-group-title{font-size:12px;font-weight:600;color:var(--text-muted);letter-spacing:.04em;text-transform:uppercase;margin-bottom:10px}
.si-check-item{display:flex;align-items:center;gap:10px;font-size:13px;padding:4px 0}
.si-check-item svg.done{color:var(--green-text)}
.si-check-item svg.warn{color:var(--amber)}
.si-check-item span.done{color:var(--text)}
.si-check-item span.warn{color:var(--text-muted)}

/* ── Review table ───────────────────────────────────────────────────────── */
.si-review-section{margin-bottom:18px}
.si-review-section-title{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--gold);margin-bottom:10px}
.si-review-grid{display:grid;grid-template-columns:160px 1fr;gap:4px 16px}
.si-review-key{font-size:13px;color:var(--text-muted);padding:4px 0}
.si-review-val{font-size:13px;color:var(--text);padding:4px 0;word-break:break-word}
.si-review-edit{display:inline-flex;align-items:center;gap:4px;font-size:12px;color:var(--gold);cursor:pointer;background:none;border:none;font-family:inherit;padding:0;margin-top:8px;transition:opacity var(--transition)}
.si-review-edit:hover{opacity:.7}

/* ── Consent ────────────────────────────────────────────────────────────── */
.si-consent-box{background:var(--navy-panel);border:1px solid rgba(74,222,128,.15);border-radius:var(--radius);padding:20px 22px;margin-bottom:20px}
.si-consent-box p{font-size:13.5px;color:var(--text-muted);line-height:1.7}
.si-checkbox-row{display:flex;align-items:flex-start;gap:12px;margin-top:16px;cursor:pointer}
.si-checkbox-row input[type=checkbox]{width:18px;height:18px;accent-color:var(--gold);flex-shrink:0;margin-top:2px;cursor:pointer}
.si-checkbox-row label{font-size:13.5px;color:var(--text);line-height:1.5;cursor:pointer}
.si-consent-seals{display:flex;gap:12px;flex-wrap:wrap;margin-top:20px;padding-top:16px;border-top:1px solid var(--navy-line)}
.si-seal{display:flex;align-items:center;gap:7px;font-size:12px;color:var(--text-muted)}
.si-seal svg{color:var(--gold);flex-shrink:0}

/* ── Buttons ────────────────────────────────────────────────────────────── */
.si-btn-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:28px}
.si-btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:99px;font-size:14px;font-weight:600;cursor:pointer;transition:all var(--transition);border:none;font-family:inherit}
.si-btn-primary{background:var(--gold);color:#0c1a2e}
.si-btn-primary:hover{background:var(--gold-light);transform:translateY(-1px)}
.si-btn-primary:disabled{opacity:.5;cursor:not-allowed;transform:none}
.si-btn-ghost{background:none;color:var(--text-muted);border:1px solid var(--navy-line)}
.si-btn-ghost:hover{border-color:var(--gold);color:var(--gold)}
.si-btn-danger{background:none;color:var(--red-alert);border:1px solid rgba(248,113,113,.25)}
.si-btn-danger:hover{background:rgba(248,113,113,.08)}

/* ── Success screen ─────────────────────────────────────────────────────── */
.si-success{text-align:center;padding:60px 24px}
.si-success-icon{width:80px;height:80px;background:rgba(74,222,128,.1);border:2px solid rgba(74,222,128,.3);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;animation:si-pop .5s cubic-bezier(.34,1.56,.64,1)}
@keyframes si-pop{from{transform:scale(0);opacity:0}to{transform:scale(1);opacity:1}}
.si-success h2{font-family:'Cormorant Garamond',Georgia,serif;font-size:2rem;font-weight:600;color:#fff;margin-bottom:10px}
.si-success p{color:var(--text-muted);max-width:440px;margin:0 auto 28px;line-height:1.6}
.si-record-id{font-family:'Inter',monospace;font-size:12px;background:var(--navy-panel);border:1px solid var(--navy-line);border-radius:8px;padding:8px 16px;display:inline-block;color:var(--text-muted);margin-bottom:24px}
.si-record-id strong{color:var(--gold)}

/* ── Error / alert ──────────────────────────────────────────────────────── */
.si-alert{background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.25);border-radius:8px;padding:12px 16px;font-size:13px;color:var(--red-alert);display:flex;gap:8px;align-items:flex-start;margin-bottom:16px}

/* ── Scrollbar ──────────────────────────────────────────────────────────── */
::-webkit-scrollbar{width:6px}
::-webkit-scrollbar-track{background:var(--navy)}
::-webkit-scrollbar-thumb{background:var(--navy-line);border-radius:99px}
</style>
</head>
<body>

<!-- ── Top Bar ────────────────────────────────────────────────────────────── -->
<div class="si-topbar">
  <a href="<?php echo $site_home; ?>" class="si-topbar-brand">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    Tibb House
  </a>
  <div class="si-topbar-badges">
    <span class="si-badge si-badge-hipaa">
      <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      HIPAA Compliant
    </span>
    <span class="si-badge si-badge-medplum">
      <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="12" rx="10" ry="4"/><path d="M2 12c0 4 4.5 8 10 8s10-4 10-8"/><path d="M2 12c0-4 4.5-8 10-8s10 4 10 8"/></svg>
      Medplum FHIR Ready
    </span>
    <span class="si-badge si-badge-tls">
      <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      TLS Encrypted
    </span>
  </div>
  <a href="<?php echo $site_home; ?>contact-us/" class="si-back">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Back to Contact
  </a>
</div>

<!-- ── Progress ───────────────────────────────────────────────────────────── -->
<div class="si-shell">
  <div class="si-progress" id="progressWrap" style="display:none">
    <div class="si-progress-track"><div class="si-progress-fill" id="progressFill" style="width:0%"></div></div>
    <div class="si-progress-labels">
      <span class="si-progress-label" id="pl1">Entry</span>
      <span class="si-progress-label" id="pl2">Medical</span>
      <span class="si-progress-label" id="pl3">Review</span>
      <span class="si-progress-label" id="pl4">Confirm</span>
    </div>
  </div>

  <!-- ══ STEP 0: Gateway ══════════════════════════════════════════════════ -->
  <div class="si-step active" id="step-gateway">
    <div class="si-gateway-head">
      <div class="si-gateway-icon">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><circle cx="12" cy="16" r="1" fill="var(--gold)"/></svg>
      </div>
      <h1>Secure Patient Intake</h1>
      <p>You are entering a private, encrypted space for sharing health information with your Tibb House care team.</p>
    </div>

    <div class="si-hipaa-notice">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      <p><strong>HIPAA-aligned confidentiality.</strong> Your information is encrypted in transit, stored securely, and will only be accessed by authorised clinical staff. It is never sold, shared, or used for marketing. All records are managed in compliance with applicable health data protection laws. This system is integrated with <strong>Medplum</strong>, a FHIR R4-compliant health data platform.</p>
    </div>

    <div class="si-card">
      <div class="si-card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 7-9 11-9 11S3 19 3 12V5l9-3 9 3v7z"/></svg>
        How would you like to submit your information?
      </div>
      <div class="si-card-sub">Choose the method that works best for you. Both options result in the same secure record — choose whichever is easiest.</div>

      <div class="si-paths">
        <button class="si-path-btn" onclick="choosePath('upload')">
          <div class="si-path-icon upload">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          </div>
          <h3>Upload Medical Documents</h3>
          <p>Upload existing reports, prescriptions, or records. We'll extract the relevant details automatically for your review.</p>
          <span class="si-path-tag upload">AI-assisted extraction</span>
        </button>
        <button class="si-path-btn" onclick="choosePath('manual')">
          <div class="si-path-icon manual">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </div>
          <h3>Fill In Manually</h3>
          <p>Enter your personal and medical information directly into a structured, guided form at your own pace.</p>
          <span class="si-path-tag manual">Step-by-step guide</span>
        </button>
      </div>
    </div>
  </div>

  <!-- ══ STEP 1A: Upload ══════════════════════════════════════════════════ -->
  <div class="si-step" id="step-upload">
    <div class="si-card">
      <div class="si-card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Upload Medical Documents
      </div>
      <div class="si-card-sub">Accepted: PDF reports, lab results, prescriptions, X-rays, MRI/CT scans, medical images. Up to 20 MB per file.</div>

      <div class="si-dropzone" id="dropzone" onclick="document.getElementById('fileInput').click()">
        <input type="file" id="fileInput" multiple accept=".pdf,.jpg,.jpeg,.png,.heic,.tiff,.dcm" style="position:absolute;inset:0;opacity:0;cursor:pointer;z-index:2">
        <div class="si-dropzone-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        </div>
        <h3>Drop files here or click to browse</h3>
        <p>Your files are processed securely and never stored unencrypted</p>
        <div class="si-file-types">
          <span class="si-file-type">PDF</span><span class="si-file-type">JPG</span><span class="si-file-type">PNG</span>
          <span class="si-file-type">DICOM</span><span class="si-file-type">X-Ray</span><span class="si-file-type">MRI</span>
        </div>
      </div>
      <div class="si-file-list" id="fileList"></div>
    </div>

    <!-- After upload: AI extraction notice + fields -->
    <div id="extractSection" style="display:none">
      <div class="si-extract-notice">
        <div class="si-extract-dot"></div>
        <span>Fields marked <span class="si-auto-badge"><svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Auto-filled</span> were extracted from your document. Please review and correct any errors before continuing.</span>
      </div>
      <div class="si-card">
        <div class="si-card-title">Review Extracted Information</div>
        <div class="si-section">
          <div class="si-section-title">Personal Details</div>
          <div class="si-grid">
            <div class="si-field"><label class="si-label">Full Name<span class="req">*</span></label><input class="si-input" id="u_fullName" placeholder="As on medical record"></div>
            <div class="si-field"><label class="si-label">Date of Birth<span class="req">*</span></label><input class="si-input" type="date" id="u_dob"></div>
            <div class="si-field"><label class="si-label">Blood Group</label><select class="si-select" id="u_blood"><option value="">— Select —</option><option>A+</option><option>A−</option><option>B+</option><option>B−</option><option>AB+</option><option>AB−</option><option>O+</option><option>O−</option><option>Unknown</option></select></div>
            <div class="si-field"><label class="si-label">Gender</label><select class="si-select" id="u_gender"><option value="">— Select —</option><option>Male</option><option>Female</option><option>Non-binary</option><option>Prefer not to say</option></select></div>
          </div>
        </div>
        <div class="si-section">
          <div class="si-section-title">Medical Findings from Document</div>
          <div class="si-grid">
            <div class="si-field si-full"><label class="si-label">Diagnoses / Conditions Mentioned</label><textarea class="si-textarea" id="u_conditions" placeholder="e.g. Type 2 Diabetes, Hypertension" rows="3"></textarea></div>
            <div class="si-field si-full"><label class="si-label">Medications Listed</label><textarea class="si-textarea" id="u_meds" placeholder="e.g. Metformin 500mg twice daily" rows="3"></textarea></div>
            <div class="si-field si-full"><label class="si-label">Any other relevant findings</label><textarea class="si-textarea" id="u_other" placeholder="Lab values, imaging notes, specialist remarks…" rows="3"></textarea></div>
          </div>
        </div>
      </div>
    </div>

    <div class="si-btn-row">
      <button class="si-btn si-btn-ghost" onclick="goStep('step-gateway')">← Back</button>
      <div style="display:flex;gap:10px;align-items:center">
        <button class="si-btn si-btn-ghost" onclick="skipUpload()">Skip — fill manually instead</button>
        <button class="si-btn si-btn-primary" id="uploadNextBtn" onclick="goFromUpload()" disabled>
          Continue
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </button>
      </div>
    </div>
  </div>

  <!-- ══ STEP 1B: Manual — Personal ══════════════════════════════════════ -->
  <div class="si-step" id="step-personal">
    <div class="si-card">
      <div class="si-card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Personal Information
      </div>
      <div class="si-card-sub">Your basic identifying information. All fields marked with * are required.</div>

      <div id="personalAlert"></div>

      <div class="si-section">
        <div class="si-section-title">Identity</div>
        <div class="si-grid">
          <div class="si-field"><label class="si-label">First Name<span class="req">*</span></label><input class="si-input" id="p_firstName" placeholder="Given name" autocomplete="given-name"></div>
          <div class="si-field"><label class="si-label">Last Name<span class="req">*</span></label><input class="si-input" id="p_lastName" placeholder="Family name" autocomplete="family-name"></div>
          <div class="si-field"><label class="si-label">Date of Birth<span class="req">*</span></label><input class="si-input" type="date" id="p_dob" autocomplete="bday"></div>
          <div class="si-field"><label class="si-label">Gender<span class="req">*</span></label>
            <select class="si-select" id="p_gender">
              <option value="">— Select —</option>
              <option>Male</option><option>Female</option><option>Non-binary</option><option>Prefer not to say</option>
            </select>
          </div>
          <div class="si-field"><label class="si-label">Blood Group<span class="opt">(if known)</span></label>
            <select class="si-select" id="p_blood">
              <option value="">— Unknown —</option><option>A+</option><option>A−</option><option>B+</option><option>B−</option><option>AB+</option><option>AB−</option><option>O+</option><option>O−</option>
            </select>
          </div>
          <div class="si-field"><label class="si-label">NHS / Insurance No.<span class="opt">(optional)</span></label><input class="si-input" id="p_nhsNum" placeholder="e.g. 943 476 5919"></div>
        </div>
      </div>

      <div class="si-section">
        <div class="si-section-title">Contact Details</div>
        <div class="si-grid">
          <div class="si-field"><label class="si-label">Email Address<span class="req">*</span></label><input class="si-input" type="email" id="p_email" placeholder="your@email.com" autocomplete="email"></div>
          <div class="si-field"><label class="si-label">Phone Number<span class="req">*</span></label><input class="si-input" type="tel" id="p_phone" placeholder="+44 7700 000000" autocomplete="tel"></div>
          <div class="si-field si-full"><label class="si-label">Address<span class="opt">(optional)</span></label><input class="si-input" id="p_address" placeholder="Street, City, Postcode" autocomplete="street-address"></div>
        </div>
      </div>

      <div class="si-section">
        <div class="si-section-title">Emergency Contact</div>
        <div class="si-grid">
          <div class="si-field"><label class="si-label">Contact Name<span class="opt">(optional)</span></label><input class="si-input" id="p_ecName" placeholder="Full name"></div>
          <div class="si-field"><label class="si-label">Relationship</label><input class="si-input" id="p_ecRel" placeholder="e.g. Spouse, Parent, Sibling"></div>
          <div class="si-field"><label class="si-label">Contact Phone</label><input class="si-input" type="tel" id="p_ecPhone" placeholder="+44 7700 000000"></div>
        </div>
      </div>
    </div>

    <div class="si-btn-row">
      <button class="si-btn si-btn-ghost" onclick="goStep('step-gateway')">← Back</button>
      <button class="si-btn si-btn-primary" onclick="validatePersonal()">
        Next: Medical History
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </button>
    </div>
  </div>

  <!-- ══ STEP 2: Medical History ══════════════════════════════════════════ -->
  <div class="si-step" id="step-medical">
    <div class="si-card">
      <div class="si-card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        Medical History
      </div>
      <div class="si-card-sub">Your health background helps us provide the most appropriate care. Share only what you are comfortable with.</div>

      <div class="si-section">
        <div class="si-section-title">Known Conditions</div>
        <div class="si-field"><label class="si-label">Diagnosed conditions or chronic illnesses<span class="opt">(select all that apply)</span></label>
          <div class="si-pills" id="conditionsPills">
            <?php foreach(['Diabetes','Hypertension','Heart Disease','Asthma','Arthritis','Thyroid disorder','IBS / Digestive issues','Anxiety / Depression','Migraines','Skin conditions','Autoimmune disorder','Cancer (past/present)','None of the above'] as $c): ?>
            <label class="si-pill"><input type="checkbox" name="condition" value="<?php echo esc_attr($c); ?>"><?php echo esc_html($c); ?></label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="si-field" style="margin-top:12px"><label class="si-label">Other conditions not listed above<span class="opt">(optional)</span></label><input class="si-input" id="m_otherConditions" placeholder="e.g. Polycystic ovary syndrome, Coeliac disease"></div>
      </div>

      <div class="si-section">
        <div class="si-section-title">Allergies</div>
        <div class="si-field"><label class="si-label">Known allergies<span class="opt">(medications, foods, environmental)</span></label>
          <div class="si-pills" id="allergyPills">
            <?php foreach(['Penicillin','Sulfa drugs','Aspirin','Ibuprofen','Latex','Peanuts','Tree nuts','Shellfish','Eggs','Dairy','Pollen','Dust mites','No known allergies'] as $a): ?>
            <label class="si-pill"><input type="checkbox" name="allergy" value="<?php echo esc_attr($a); ?>"><?php echo esc_html($a); ?></label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="si-field" style="margin-top:12px"><label class="si-label">Other allergies<span class="opt">(optional)</span></label><input class="si-input" id="m_otherAllergies" placeholder="Describe any other allergies or reactions"></div>
      </div>

      <div class="si-section">
        <div class="si-section-title">Current Medications</div>
        <div class="si-repeater" id="medsRepeater">
          <div class="si-repeater-item">
            <input class="si-input" placeholder="Medication name" data-med="name">
            <input class="si-input" placeholder="Dose (e.g. 500mg)" data-med="dose">
            <input class="si-input" placeholder="Frequency" data-med="freq">
            <button class="si-repeater-remove" onclick="removeMed(this)" title="Remove">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
        </div>
        <button class="si-add-btn" onclick="addMed()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add medication
        </button>
      </div>

      <div class="si-section">
        <div class="si-section-title">Surgical & Hospital History</div>
        <div class="si-grid">
          <div class="si-field si-full"><label class="si-label">Previous surgeries or hospitalisations<span class="opt">(optional)</span></label><textarea class="si-textarea" id="m_surgeries" placeholder="e.g. Appendectomy 2018, Knee surgery 2021 — include approximate dates if known" rows="3"></textarea></div>
          <div class="si-field si-full"><label class="si-label">Family medical history<span class="opt">(optional)</span></label><textarea class="si-textarea" id="m_familyHistory" placeholder="e.g. Mother: Type 2 Diabetes; Father: Hypertension" rows="3"></textarea></div>
        </div>
      </div>
    </div>

    <div class="si-card">
      <div class="si-card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Current Concerns
      </div>
      <div class="si-card-sub">Tell us what has brought you to Tibb House today.</div>

      <div class="si-section">
        <div class="si-grid">
          <div class="si-field si-full"><label class="si-label">Chief complaint / main symptoms<span class="req">*</span></label><textarea class="si-textarea" id="m_chiefComplaint" placeholder="Describe your primary health concern in your own words…" rows="4"></textarea></div>
          <div class="si-field"><label class="si-label">How long have you had this?</label>
            <select class="si-select" id="m_duration">
              <option value="">— Select —</option>
              <option>Less than 1 week</option><option>1–4 weeks</option><option>1–3 months</option>
              <option>3–6 months</option><option>6–12 months</option><option>Over 1 year</option><option>Over 5 years</option>
            </select>
          </div>
          <div class="si-field"><label class="si-label">Severity (1 = mild, 10 = severe)</label>
            <div class="si-range-wrap" style="margin-top:6px">
              <input class="si-range" type="range" min="1" max="10" value="5" id="m_severity" oninput="document.getElementById('sevVal').textContent=this.value">
              <span class="si-range-val" id="sevVal">5</span>
            </div>
          </div>
          <div class="si-field si-full"><label class="si-label">What makes it better or worse?<span class="opt">(optional)</span></label><textarea class="si-textarea" id="m_modifiers" placeholder="e.g. Rest helps; cold worsens; stress triggers it" rows="2"></textarea></div>
          <div class="si-field si-full"><label class="si-label">Previous treatments tried for this concern<span class="opt">(optional)</span></label><textarea class="si-textarea" id="m_prevTreatments" placeholder="e.g. GP prescribed ibuprofen — limited effect; physiotherapy — 3 sessions" rows="2"></textarea></div>
        </div>
      </div>
    </div>

    <div class="si-card">
      <div class="si-card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        Lifestyle &amp; Tibb-Specific
      </div>

      <div class="si-section">
        <div class="si-section-title">Lifestyle</div>
        <div class="si-grid">
          <div class="si-field"><label class="si-label">Diet type</label>
            <select class="si-select" id="l_diet">
              <option value="">— Select —</option>
              <option>Omnivore</option><option>Vegetarian</option><option>Vegan</option><option>Halal</option>
              <option>Halal vegetarian</option><option>Gluten-free</option><option>Other</option>
            </select>
          </div>
          <div class="si-field"><label class="si-label">Exercise frequency</label>
            <select class="si-select" id="l_exercise">
              <option value="">— Select —</option>
              <option>Sedentary (little/none)</option><option>Light (1–2 days/week)</option>
              <option>Moderate (3–4 days/week)</option><option>Active (5+ days/week)</option>
            </select>
          </div>
          <div class="si-field"><label class="si-label">Sleep quality</label>
            <select class="si-select" id="l_sleep">
              <option value="">— Select —</option>
              <option>Good (7–9 hrs, restful)</option><option>Fair (5–7 hrs)</option>
              <option>Poor (under 5 hrs or disrupted)</option><option>Highly disrupted / insomnia</option>
            </select>
          </div>
          <div class="si-field"><label class="si-label">Stress level (1–10)</label>
            <div class="si-range-wrap" style="margin-top:6px">
              <input class="si-range" type="range" min="1" max="10" value="5" id="l_stress" oninput="document.getElementById('stressVal').textContent=this.value">
              <span class="si-range-val" id="stressVal">5</span>
            </div>
          </div>
          <div class="si-field"><label class="si-label">Smoking</label>
            <select class="si-select" id="l_smoking">
              <option value="">— Select —</option>
              <option>Non-smoker</option><option>Former smoker</option><option>Occasional smoker</option><option>Regular smoker</option>
            </select>
          </div>
          <div class="si-field"><label class="si-label">Alcohol consumption</label>
            <select class="si-select" id="l_alcohol">
              <option value="">— Select —</option>
              <option>None</option><option>Rarely</option><option>Socially</option><option>Regularly</option>
            </select>
          </div>
        </div>
      </div>

      <div class="si-section">
        <div class="si-section-title">Previous Tibb / Islamic Medicine Experience</div>
        <div class="si-grid">
          <div class="si-field"><label class="si-label">Previous Hijama (cupping)?</label>
            <select class="si-select" id="t_hijama">
              <option value="">— Select —</option>
              <option>Never tried</option><option>Tried once</option><option>Occasional (few times/year)</option><option>Regular (monthly or more)</option>
            </select>
          </div>
          <div class="si-field"><label class="si-label">Herbal / natural remedies used</label><input class="si-input" id="t_herbs" placeholder="e.g. Black seed, Turmeric, Honey"></div>
          <div class="si-field si-full"><label class="si-label">Other complementary therapies<span class="opt">(optional)</span></label><input class="si-input" id="t_other" placeholder="e.g. Acupuncture, Homeopathy, Osteopathy"></div>
        </div>
      </div>
    </div>

    <div class="si-btn-row">
      <button class="si-btn si-btn-ghost" onclick="goStep('step-personal')">← Back</button>
      <button class="si-btn si-btn-primary" onclick="goToChecklist()">
        Review &amp; Checklist
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </button>
    </div>
  </div>

  <!-- ══ STEP 3: Checklist ════════════════════════════════════════════════ -->
  <div class="si-step" id="step-checklist">
    <div class="si-card">
      <div class="si-card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></polyline></svg>
        Completeness Checklist
      </div>
      <div class="si-card-sub">Your record status at a glance. You can go back to fill in any gaps, or continue to the final review.</div>
      <div class="si-checklist" id="checklist"></div>
    </div>

    <div class="si-btn-row">
      <button class="si-btn si-btn-ghost" onclick="goStep('step-medical')">← Edit</button>
      <button class="si-btn si-btn-primary" onclick="buildReview()">
        Final Review
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </button>
    </div>
  </div>

  <!-- ══ STEP 4: Review ═══════════════════════════════════════════════════ -->
  <div class="si-step" id="step-review">
    <div class="si-card">
      <div class="si-card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Final Review
      </div>
      <div class="si-card-sub">Please confirm your information is accurate before submitting. This is your last opportunity to make changes.</div>
      <div id="reviewContent"></div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px">
        <button class="si-review-edit" onclick="goStep('step-personal')">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit Personal Info
        </button>
        <button class="si-review-edit" onclick="goStep('step-medical')">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit Medical History
        </button>
      </div>
    </div>

    <div class="si-btn-row">
      <button class="si-btn si-btn-ghost" onclick="goStep('step-checklist')">← Back</button>
      <button class="si-btn si-btn-primary" onclick="goStep('step-consent')">
        Proceed to Consent
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </button>
    </div>
  </div>

  <!-- ══ STEP 5: Consent & Submit ═════════════════════════════════════════ -->
  <div class="si-step" id="step-consent">
    <div class="si-card">
      <div class="si-card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        HIPAA Authorisation &amp; Consent
      </div>
      <div class="si-card-sub">Read carefully. Your consent is required before we can securely store and process your health information.</div>

      <div class="si-consent-box">
        <p>By submitting this form, you authorise Tibb House to collect, store, and process the personal and medical information provided above for the purpose of delivering healthcare services. Your data will be stored securely in a HIPAA-aligned, FHIR R4-compliant system (Medplum) and will only be accessed by authorised clinical staff involved in your care.</p>
        <p style="margin-top:10px">You understand that: (1) your data will not be shared with third parties without your explicit consent; (2) you may request access to, correction of, or deletion of your records at any time by contacting Tibb House directly; (3) electronic communications related to your care may be sent to the email address you provided.</p>
        <label class="si-checkbox-row">
          <input type="checkbox" id="consentCheck" onchange="toggleSubmit()">
          <label for="consentCheck">I confirm that the information I have provided is accurate and complete to the best of my knowledge. I authorise Tibb House to securely store and process my medical information for healthcare purposes as described above.</label>
        </label>

        <div class="si-consent-seals">
          <div class="si-seal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            HIPAA-aligned storage
          </div>
          <div class="si-seal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            TLS 1.3 encrypted in transit
          </div>
          <div class="si-seal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="12" rx="10" ry="4"/><path d="M2 12c0 4 4.5 8 10 8s10-4 10-8"/><path d="M2 12c0-4 4.5-8 10-8s10 4 10 8"/></svg>
            Medplum FHIR R4
          </div>
          <div class="si-seal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Right to erasure supported
          </div>
        </div>
      </div>

      <div id="submitAlert"></div>
    </div>

    <div class="si-btn-row">
      <button class="si-btn si-btn-ghost" onclick="goStep('step-review')">← Back to Review</button>
      <button class="si-btn si-btn-primary" id="submitBtn" onclick="submitForm()" disabled>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Submit Secure Record
      </button>
    </div>
  </div>

  <!-- ══ STEP 6: Success ══════════════════════════════════════════════════ -->
  <div class="si-step" id="step-success">
    <div class="si-success">
      <div class="si-success-icon">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--green-text)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <h2>Record Received</h2>
      <p>Your medical intake has been securely submitted to your Tibb House care team. You will receive a confirmation by email, and a practitioner will be in touch to discuss next steps.</p>
      <div class="si-record-id" id="successRecordId">Record ID: <strong>—</strong></div>
      <div class="si-hipaa-notice" style="max-width:480px;margin:0 auto 28px">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <p>Your data is now encrypted and stored in our Medplum FHIR system. If you need to update or delete your record, email us with your record ID above.</p>
      </div>
      <a href="<?php echo $site_home; ?>" class="si-btn si-btn-primary" style="text-decoration:none;display:inline-flex">
        Return to Tibb House
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      </a>
    </div>
  </div>

</div><!-- /.si-shell -->

<script>
// ── Config ───────────────────────────────────────────────────────────────────
const AJAX_URL = '<?php echo $ajax_url; ?>';
let nonce = '';
let path  = 'manual'; // 'upload' | 'manual'

// Fetch nonce on load.
fetch(AJAX_URL + '?action=th_intake_nonce')
  .then(r => r.json())
  .then(d => { if (d.success) nonce = d.data.nonce; })
  .catch(() => {}); // Fail silently; validated server-side

// ── Step navigation ──────────────────────────────────────────────────────────
function goStep(id) {
  document.querySelectorAll('.si-step').forEach(s => s.classList.remove('active'));
  document.getElementById(id).classList.add('active');
  window.scrollTo({ top: 0, behavior: 'smooth' });
  updateProgress(id);
}

const STEP_ORDER = ['step-gateway','step-personal','step-upload','step-medical','step-checklist','step-review','step-consent','step-success'];
const PROGRESS_STEPS = {
  'step-gateway':0,'step-upload':25,'step-personal':25,
  'step-medical':50,'step-checklist':75,'step-review':85,'step-consent':95,'step-success':100
};

function updateProgress(id) {
  const wrap = document.getElementById('progressWrap');
  const fill = document.getElementById('progressFill');
  const inGateway = id === 'step-gateway';
  wrap.style.display = inGateway ? 'none' : 'block';
  fill.style.width = (PROGRESS_STEPS[id] || 0) + '%';
  const labels = ['pl1','pl2','pl3','pl4'];
  const thresholds = [0,50,75,95];
  const pct = PROGRESS_STEPS[id] || 0;
  labels.forEach((l,i) => {
    document.getElementById(l).classList.toggle('done', pct >= thresholds[i]);
  });
}

// ── Path selection ───────────────────────────────────────────────────────────
function choosePath(p) {
  path = p;
  goStep(p === 'upload' ? 'step-upload' : 'step-personal');
}

function skipUpload() { path = 'manual'; goStep('step-personal'); }

// ── File upload handling ─────────────────────────────────────────────────────
let uploadedFiles = [];

document.addEventListener('DOMContentLoaded', () => {
  const dz = document.getElementById('dropzone');
  const fi = document.getElementById('fileInput');
  if (!dz || !fi) return;

  dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('drag-over'); });
  dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
  dz.addEventListener('drop', e => { e.preventDefault(); dz.classList.remove('drag-over'); addFiles(e.dataTransfer.files); });
  fi.addEventListener('change', () => { addFiles(fi.files); fi.value = ''; });
});

function addFiles(files) {
  Array.from(files).forEach(f => {
    if (uploadedFiles.find(u => u.name === f.name && u.size === f.size)) return;
    uploadedFiles.push(f);
  });
  renderFileList();
  if (uploadedFiles.length > 0) {
    document.getElementById('extractSection').style.display = 'block';
    document.getElementById('uploadNextBtn').disabled = false;
    simulateExtraction();
  }
}

function renderFileList() {
  const list = document.getElementById('fileList');
  list.innerHTML = uploadedFiles.map((f,i) => `
    <div class="si-file-item">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      <span class="si-file-name">${esc(f.name)}</span>
      <span class="si-file-size">${(f.size/1024/1024).toFixed(1)} MB</span>
      <button class="si-file-remove" onclick="removeFile(${i})" title="Remove">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>`).join('');
}

function removeFile(i) {
  uploadedFiles.splice(i,1);
  renderFileList();
  if (!uploadedFiles.length) {
    document.getElementById('extractSection').style.display = 'none';
    document.getElementById('uploadNextBtn').disabled = true;
  }
}

// Simulate AI extraction (placeholder — wire to real AI endpoint when ready)
function simulateExtraction() {
  // In production: POST file to an AI extraction endpoint, then populate fields.
  // For now: just mark the fields as ready for manual review.
  const badge = `<span class="si-auto-badge"><svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Review required</span>`;
}

function goFromUpload() {
  // Copy upload path data into personal/medical step fields if we have them.
  const fn = document.getElementById('u_fullName').value.trim();
  if (fn) {
    const parts = fn.split(' ');
    document.getElementById('p_firstName').value = parts[0] || '';
    document.getElementById('p_lastName').value  = parts.slice(1).join(' ') || '';
    ['p_firstName','p_lastName'].forEach(id => document.getElementById(id).classList.add('autofilled'));
  }
  const dob = document.getElementById('u_dob').value;
  if (dob) { document.getElementById('p_dob').value = dob; document.getElementById('p_dob').classList.add('autofilled'); }
  const blood = document.getElementById('u_blood').value;
  if (blood) { document.getElementById('p_blood').value = blood; document.getElementById('p_blood').classList.add('autofilled'); }
  const gender = document.getElementById('u_gender').value;
  if (gender) { document.getElementById('p_gender').value = gender; document.getElementById('p_gender').classList.add('autofilled'); }
  const cond = document.getElementById('u_conditions').value;
  if (cond) { document.getElementById('m_otherConditions').value = cond; document.getElementById('m_otherConditions').classList.add('autofilled'); }
  const meds = document.getElementById('u_meds').value;
  if (meds) {
    const row = document.querySelector('#medsRepeater .si-repeater-item [data-med="name"]');
    if (row) { row.value = meds; row.classList.add('autofilled'); }
  }
  goStep('step-personal');
}

// ── Medications repeater ─────────────────────────────────────────────────────
function addMed() {
  const rep = document.getElementById('medsRepeater');
  const row = document.createElement('div');
  row.className = 'si-repeater-item';
  row.innerHTML = `
    <input class="si-input" placeholder="Medication name" data-med="name">
    <input class="si-input" placeholder="Dose (e.g. 500mg)" data-med="dose">
    <input class="si-input" placeholder="Frequency" data-med="freq">
    <button class="si-repeater-remove" onclick="removeMed(this)" title="Remove">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>`;
  rep.appendChild(row);
}
function removeMed(btn) { btn.closest('.si-repeater-item').remove(); }

// Pill toggles.
document.addEventListener('click', e => {
  if (e.target.matches('.si-pill input') || e.target.closest('.si-pill')) {
    const pill = e.target.closest('.si-pill');
    if (pill) { const cb = pill.querySelector('input'); if(cb){cb.checked = !cb.checked; pill.classList.toggle('active', cb.checked);} }
  }
});

// ── Validation ───────────────────────────────────────────────────────────────
function showAlert(containerId, msg) {
  document.getElementById(containerId).innerHTML = msg
    ? `<div class="si-alert"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>${esc(msg)}</div>`
    : '';
}

function validatePersonal() {
  const fn = v('p_firstName'), ln = v('p_lastName'), dob = v('p_dob'), gender = v('p_gender'), email = v('p_email'), phone = v('p_phone');
  if (!fn) return showAlert('personalAlert','Please enter your first name.');
  if (!ln) return showAlert('personalAlert','Please enter your last name.');
  if (!dob) return showAlert('personalAlert','Please enter your date of birth.');
  if (!gender) return showAlert('personalAlert','Please select your gender.');
  if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return showAlert('personalAlert','Please enter a valid email address.');
  if (!phone) return showAlert('personalAlert','Please enter a phone number.');
  showAlert('personalAlert','');
  goStep('step-medical');
}

// ── Checklist ────────────────────────────────────────────────────────────────
function goToChecklist() {
  buildChecklist();
  goStep('step-checklist');
}

function buildChecklist() {
  const items = [
    { group:'Personal Information', checks:[
      { label:'Full Name',         done: !!(v('p_firstName') && v('p_lastName')) },
      { label:'Date of Birth',     done: !!v('p_dob') },
      { label:'Email Address',     done: !!v('p_email') },
      { label:'Phone Number',      done: !!v('p_phone') },
      { label:'Address',           done: !!v('p_address'), optional:true },
    ]},
    { group:'Emergency Contact', checks:[
      { label:'Emergency contact name',  done: !!v('p_ecName'), optional:true },
      { label:'Emergency contact phone', done: !!v('p_ecPhone'), optional:true },
    ]},
    { group:'Medical History', checks:[
      { label:'Conditions selected',       done: getChecked('condition').length > 0 },
      { label:'Allergies noted',           done: getChecked('allergy').length > 0 },
      { label:'Current medications',       done: getMeds().length > 0, optional:true },
      { label:'Surgical history',          done: !!v('m_surgeries'), optional:true },
      { label:'Family medical history',    done: !!v('m_familyHistory'), optional:true },
    ]},
    { group:'Current Concerns', checks:[
      { label:'Chief complaint described', done: !!v('m_chiefComplaint') },
      { label:'Duration selected',         done: !!v('m_duration'), optional:true },
    ]},
    { group:'Documents', checks:[
      { label: uploadedFiles.length > 0
          ? uploadedFiles.length + ' file(s) uploaded'
          : 'No documents uploaded (optional)',
        done: uploadedFiles.length > 0, optional:true },
    ]},
  ];

  const cl = document.getElementById('checklist');
  cl.innerHTML = items.map(g => `
    <div class="si-check-group">
      <div class="si-check-group-title">${esc(g.group)}</div>
      ${g.checks.map(c => `
        <div class="si-check-item">
          ${c.done
            ? `<svg class="done" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span class="done">${esc(c.label)}</span>`
            : c.optional
              ? `<svg class="warn" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span class="warn">${esc(c.label)} (optional)</span>`
              : `<svg class="warn" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg><span class="warn" style="color:var(--amber)">${esc(c.label)} — missing</span>`
          }
        </div>`).join('')}
    </div>`).join('');
}

// ── Review ───────────────────────────────────────────────────────────────────
function buildReview() {
  const meds = getMeds();
  const conds = getChecked('condition');
  const allgs = getChecked('allergy');
  const rows = (obj) => Object.entries(obj).filter(([,v])=>v).map(([k,v])=>`<div class="si-review-key">${esc(k)}</div><div class="si-review-val">${esc(v)}</div>`).join('');

  document.getElementById('reviewContent').innerHTML = `
    <div class="si-review-section">
      <div class="si-review-section-title">Personal Information</div>
      <div class="si-review-grid">
        ${rows({'Full Name':v('p_firstName')+' '+v('p_lastName'),'Date of Birth':v('p_dob'),'Gender':v('p_gender'),'Blood Group':v('p_blood')||'—','Email':v('p_email'),'Phone':v('p_phone'),'Address':v('p_address')||'—','NHS/Insurance No.':v('p_nhsNum')||'—'})}
      </div>
    </div>
    <div class="si-review-section">
      <div class="si-review-section-title">Emergency Contact</div>
      <div class="si-review-grid">
        ${rows({'Name':v('p_ecName')||'—','Relationship':v('p_ecRel')||'—','Phone':v('p_ecPhone')||'—'})}
      </div>
    </div>
    <div class="si-review-section">
      <div class="si-review-section-title">Medical History</div>
      <div class="si-review-grid">
        <div class="si-review-key">Conditions</div><div class="si-review-val">${esc(conds.join(', ')||'None selected')}</div>
        <div class="si-review-key">Other conditions</div><div class="si-review-val">${esc(v('m_otherConditions')||'—')}</div>
        <div class="si-review-key">Allergies</div><div class="si-review-val">${esc(allgs.join(', ')||'None selected')}</div>
        <div class="si-review-key">Other allergies</div><div class="si-review-val">${esc(v('m_otherAllergies')||'—')}</div>
        <div class="si-review-key">Medications</div><div class="si-review-val">${esc(meds.map(m=>m.name+(m.dose?' '+m.dose:'')+(m.freq?' ('+m.freq+')':'')).join('; ')||'None listed')}</div>
        <div class="si-review-key">Surgeries</div><div class="si-review-val">${esc(v('m_surgeries')||'—')}</div>
        <div class="si-review-key">Family history</div><div class="si-review-val">${esc(v('m_familyHistory')||'—')}</div>
      </div>
    </div>
    <div class="si-review-section">
      <div class="si-review-section-title">Current Concerns</div>
      <div class="si-review-grid">
        ${rows({'Chief complaint':v('m_chiefComplaint'),'Duration':v('m_duration')||'—','Severity':document.getElementById('m_severity').value+'/10','What helps/worsens':v('m_modifiers')||'—','Previous treatments':v('m_prevTreatments')||'—'})}
      </div>
    </div>
    <div class="si-review-section">
      <div class="si-review-section-title">Lifestyle &amp; Tibb</div>
      <div class="si-review-grid">
        ${rows({'Diet':v('l_diet')||'—','Exercise':v('l_exercise')||'—','Sleep':v('l_sleep')||'—','Stress level':document.getElementById('l_stress').value+'/10','Smoking':v('l_smoking')||'—','Alcohol':v('l_alcohol')||'—','Previous Hijama':v('t_hijama')||'—','Herbal remedies':v('t_herbs')||'—','Other therapies':v('t_other')||'—'})}
      </div>
    </div>
    ${uploadedFiles.length ? `<div class="si-review-section"><div class="si-review-section-title">Uploaded Documents</div><div class="si-review-grid"><div class="si-review-key">Files</div><div class="si-review-val">${esc(uploadedFiles.map(f=>f.name).join(', '))}</div></div></div>` : ''}
  `;
  goStep('step-review');
}

// ── Consent ──────────────────────────────────────────────────────────────────
function toggleSubmit() {
  document.getElementById('submitBtn').disabled = !document.getElementById('consentCheck').checked;
}

// ── Submit ───────────────────────────────────────────────────────────────────
async function submitForm() {
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Submitting…`;

  const payload = {
    path,
    personal: {
      firstName: v('p_firstName'), lastName: v('p_lastName'), dob: v('p_dob'),
      gender: v('p_gender'), blood: v('p_blood'), nhsNum: v('p_nhsNum'),
      email: v('p_email'), phone: v('p_phone'), address: v('p_address'),
      ecName: v('p_ecName'), ecRel: v('p_ecRel'), ecPhone: v('p_ecPhone'),
    },
    medical: {
      conditions: getChecked('condition'), otherConditions: v('m_otherConditions'),
      allergies: getChecked('allergy'), otherAllergies: v('m_otherAllergies'),
      medications: getMeds(), surgeries: v('m_surgeries'), familyHistory: v('m_familyHistory'),
      chiefComplaint: v('m_chiefComplaint'), duration: v('m_duration'),
      severity: document.getElementById('m_severity').value,
      modifiers: v('m_modifiers'), prevTreatments: v('m_prevTreatments'),
    },
    lifestyle: {
      diet: v('l_diet'), exercise: v('l_exercise'), sleep: v('l_sleep'),
      stress: document.getElementById('l_stress').value,
      smoking: v('l_smoking'), alcohol: v('l_alcohol'),
    },
    tibb: { hijama: v('t_hijama'), herbs: v('t_herbs'), other: v('t_other') },
    uploadedFileNames: uploadedFiles.map(f => f.name),
    consentGiven: true,
    submittedAt: new Date().toISOString(),
  };

  try {
    const fd = new FormData();
    fd.append('action', 'th_intake_submit');
    fd.append('nonce', nonce);
    fd.append('payload', JSON.stringify(payload));
    uploadedFiles.forEach((file, index) => {
      fd.append('intake_files[]', file, file.name || `medical-document-${index + 1}`);
    });

    const res  = await fetch(AJAX_URL, { method:'POST', body: fd, credentials: 'same-origin' });
    const data = await res.json();

    if (data.success) {
      const rid = data.data?.record_id ? '#' + data.data.record_id : '—';
      document.getElementById('successRecordId').innerHTML = 'Record ID: <strong>' + rid + '</strong>';
      goStep('step-success');
    } else {
      throw new Error(data.data?.message || 'Submission failed. Please try again.');
    }
  } catch(e) {
    showAlert('submitAlert', e.message || 'An error occurred. Please try again or contact us directly.');
    btn.disabled = false;
    btn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> Submit Secure Record`;
    document.getElementById('consentCheck').checked = false;
  }
}

// ── Helpers ──────────────────────────────────────────────────────────────────
function v(id) { const el = document.getElementById(id); return el ? el.value.trim() : ''; }
function esc(s) { if(!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function getChecked(name) { return [...document.querySelectorAll(`input[name="${name}"]:checked`)].map(c=>c.value); }
function getMeds() {
  return [...document.querySelectorAll('#medsRepeater .si-repeater-item')].map(row => ({
    name: (row.querySelector('[data-med="name"]')?.value||'').trim(),
    dose: (row.querySelector('[data-med="dose"]')?.value||'').trim(),
    freq: (row.querySelector('[data-med="freq"]')?.value||'').trim(),
  })).filter(m => m.name);
}

// Pill click init.
document.querySelectorAll('.si-pill input').forEach(cb => {
  cb.closest('.si-pill').classList.toggle('active', cb.checked);
});
</script>
<style>@keyframes spin{to{transform:rotate(360deg)}}</style>
</body>
</html>
