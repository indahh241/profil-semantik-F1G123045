<?php
require_once 'config/db.php';

$profil = dbRow($pdo, "SELECT * FROM profil LIMIT 1");
$nama        = $profil['nama']        ?? 'Indah Haerunnisa';
$nim         = $profil['nim']         ?? 'F1G123045';
$prodi       = $profil['prodi']       ?? 'Ilmu Komputer';
$universitas = $profil['universitas'] ?? 'Universitas Halu Oleo';
$foto        = $profil['foto']        ?? 'assets/foto.jpg';

// Info mata kuliah (sesuaikan)
$matkul      = 'Semantik Web';
$dosen       = 'Natalis Ransi, S.Si., M.Cs.';
$semester    = 'Genap 2025/2026';
$tahun       = date('Y');

// Resolve foto
$fotoSrc      = file_exists($foto) ? $foto : (file_exists(str_replace('.jpg','.jpeg',$foto)) ? str_replace('.jpg','.jpeg',$foto) : null);
$fotoFallback = 'https://ui-avatars.com/api/?name=' . urlencode($nama) . '&background=3b5bdb&color=fff&size=300';
$fotoDisplay  = $fotoSrc ?? $fotoFallback;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($nama) ?> &mdash; Semantic Web Profile</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800;900&display=swap">

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }

    /* ── Root tokens (serasi dengan style.css) ── */
    :root {
      --navy:       #0f1c3a;
      --navy-mid:   #1a2f5e;
      --blue:       #3b5bdb;
      --blue-light: #e8edff;
      --purple:     #7c3aed;
      --green:      #16a34a;
      --border:     rgba(255,255,255,.10);
      --text-dim:   rgba(255,255,255,.55);
    }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--navy);
      color: #fff;
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* ── Stars background ── */
    .bg-stars {
      position: fixed; inset: 0; z-index: 0; overflow: hidden; pointer-events: none;
    }
    .bg-stars span {
      position: absolute; border-radius: 50%;
      background: #fff; opacity: 0;
      animation: star-twinkle var(--dur, 3s) ease-in-out var(--delay, 0s) infinite;
    }
    @keyframes star-twinkle {
      0%,100% { opacity: 0; transform: scale(.6); }
      50%      { opacity: var(--op, .5); transform: scale(1); }
    }

    /* ── Gradient orbs ── */
    .orb {
      position: fixed; border-radius: 50%;
      filter: blur(80px); opacity: .18; pointer-events: none; z-index: 0;
      animation: orb-drift 12s ease-in-out infinite alternate;
    }
    .orb-1 { width: 500px; height: 500px; background: var(--blue);   top: -150px; right: -100px; animation-delay: 0s; }
    .orb-2 { width: 400px; height: 400px; background: var(--purple); bottom: -100px; left: -80px; animation-delay: 3s; }
    .orb-3 { width: 260px; height: 260px; background: #0891b2; top: 40%; left: 40%; animation-delay: 6s; }
    @keyframes orb-drift {
      from { transform: translate(0,0) scale(1); }
      to   { transform: translate(30px, 20px) scale(1.1); }
    }

    /* ── Layout ── */
    .page {
      position: relative; z-index: 1;
      min-height: 100vh;
      display: grid;
      grid-template-columns: 1fr 1fr;
      grid-template-rows: auto 1fr auto;
    }

    /* ── Top bar ── */
    .topbar {
      grid-column: 1 / -1;
      display: flex; align-items: center; justify-content: space-between;
      padding: 20px 48px;
      border-bottom: 1px solid var(--border);
      backdrop-filter: blur(12px);
      background: rgba(15,28,58,.6);
      animation: slide-down .5s ease both;
    }
    @keyframes slide-down {
      from { opacity: 0; transform: translateY(-16px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .topbar-brand {
      display: flex; align-items: center; gap: 12px;
    }
    .brand-dot {
      width: 36px; height: 36px;
      background: var(--blue);
      border-radius: 9px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
      box-shadow: 0 0 0 5px rgba(59,91,219,.2);
      animation: pulse-dot 3s ease-in-out infinite;
    }
    @keyframes pulse-dot {
      0%,100% { box-shadow: 0 0 0 5px rgba(59,91,219,.2); }
      50%      { box-shadow: 0 0 0 10px rgba(59,91,219,.07); }
    }
    .brand-label { font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 700; }
    .brand-label span { display: block; font-size: 10.5px; font-weight: 400; color: var(--text-dim); }

    .topbar-meta {
      display: flex; align-items: center; gap: 20px;
    }
    .meta-chip {
      display: flex; align-items: center; gap: 7px;
      padding: 5px 14px; border-radius: 20px;
      background: rgba(255,255,255,.07);
      border: 1px solid var(--border);
      font-size: 12px; color: var(--text-dim);
    }
    .meta-chip strong { color: #fff; font-weight: 600; }

    /* ── Left column (hero) ── */
    .hero-left {
      display: flex; flex-direction: column; justify-content: center;
      padding: 60px 48px 60px 80px;
      animation: fade-up .7s ease .1s both;
    }
    @keyframes fade-up {
      from { opacity: 0; transform: translateY(28px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .eyebrow {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 6px 16px; border-radius: 20px;
      background: rgba(59,91,219,.18);
      border: 1px solid rgba(59,91,219,.35);
      font-size: 11.5px; font-weight: 600;
      color: #93c5fd;
      letter-spacing: .05em; text-transform: uppercase;
      margin-bottom: 24px; width: fit-content;
    }
    .eyebrow-dot {
      width: 7px; height: 7px; border-radius: 50%; background: #93c5fd;
      animation: blink 1.5s ease-in-out infinite;
    }
    @keyframes blink {
      0%,100% { opacity: 1; } 50% { opacity: .3; }
    }

    .hero-nim {
      font-family: 'Poppins', sans-serif;
      font-size: 13px; font-weight: 600;
      color: rgba(147,197,253,.8);
      letter-spacing: .08em;
      margin-bottom: 8px;
    }

    .hero-name {
      font-family: 'Poppins', sans-serif;
      font-size: clamp(32px, 4vw, 52px);
      font-weight: 900;
      line-height: 1.1;
      margin-bottom: 16px;
      background: linear-gradient(135deg, #fff 30%, #93c5fd 70%, #c4b5fd 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .hero-divider {
      width: 48px; height: 3px;
      background: linear-gradient(90deg, var(--blue), var(--purple));
      border-radius: 2px;
      margin-bottom: 20px;
    }

    .hero-desc {
      font-size: 15px; line-height: 1.75;
      color: rgba(255,255,255,.65);
      max-width: 460px;
      margin-bottom: 36px;
    }
    .hero-desc strong { color: rgba(255,255,255,.9); font-weight: 600; }

    /* Info cards row */
    .info-cards {
      display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
      margin-bottom: 36px;
    }
    .info-card {
      background: rgba(255,255,255,.05);
      border: 1px solid var(--border);
      border-radius: 12px; padding: 14px 16px;
      transition: background .2s, transform .2s;
    }
    .info-card:hover {
      background: rgba(255,255,255,.09);
      transform: translateY(-2px);
    }
    .info-card-label {
      font-size: 10.5px; font-weight: 600;
      text-transform: uppercase; letter-spacing: .08em;
      color: var(--text-dim); margin-bottom: 5px;
    }
    .info-card-value {
      font-size: 13.5px; font-weight: 600; color: #fff;
      line-height: 1.35;
    }
    .info-card-icon { font-size: 18px; margin-bottom: 8px; }

    /* CTA */
    .cta-group { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }

    .btn-enter {
      display: inline-flex; align-items: center; gap: 10px;
      padding: 13px 28px; border-radius: 10px;
      background: var(--blue);
      color: #fff; font-family: 'Poppins', sans-serif;
      font-size: 14px; font-weight: 700;
      text-decoration: none;
      box-shadow: 0 6px 24px rgba(59,91,219,.45);
      transition: transform .2s, box-shadow .2s, background .2s;
      position: relative; overflow: hidden;
    }
    .btn-enter::before {
      content: '';
      position: absolute; inset: 0;
      background: linear-gradient(135deg, rgba(255,255,255,.15), transparent);
      opacity: 0; transition: opacity .2s;
    }
    .btn-enter:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 32px rgba(59,91,219,.6);
      background: #2f4ac8;
    }
    .btn-enter:hover::before { opacity: 1; }
    .btn-enter svg { transition: transform .2s; }
    .btn-enter:hover svg { transform: translateX(4px); }

    .btn-scroll {
      display: inline-flex; align-items: center; gap: 8px;
      font-size: 13px; font-weight: 500;
      color: var(--text-dim);
      text-decoration: none;
      transition: color .2s;
    }
    .btn-scroll:hover { color: #fff; }

    /* ── Right column (visual) ── */
    .hero-right {
      display: flex; align-items: center; justify-content: center;
      padding: 60px 80px 60px 40px;
      animation: fade-up .7s ease .25s both;
    }

    .avatar-stage {
      position: relative; width: 320px; height: 320px;
    }

    /* Rotating rings */
    .ring {
      position: absolute; border-radius: 50%;
      border: 1px solid rgba(59,91,219,.25);
      animation: ring-spin var(--speed, 20s) linear infinite var(--dir, normal);
    }
    .ring-1 { inset: -20px; border-color: rgba(59,91,219,.2); --speed: 18s; }
    .ring-2 { inset: -50px; border-color: rgba(124,58,237,.15); --speed: 28s; --dir: reverse; }
    .ring-3 { inset: -80px; border-color: rgba(255,255,255,.06); --speed: 40s; }

    /* Ring dots */
    .ring::after {
      content: '';
      position: absolute; top: -4px; left: 50%;
      transform: translateX(-50%);
      width: 8px; height: 8px; border-radius: 50%;
      background: var(--blue);
      box-shadow: 0 0 8px var(--blue);
    }
    .ring-2::after { background: var(--purple); box-shadow: 0 0 8px var(--purple); }
    .ring-3::after { width: 5px; height: 5px; background: rgba(255,255,255,.4); box-shadow: none; }

    @keyframes ring-spin {
      from { transform: rotate(0deg); }
      to   { transform: rotate(360deg); }
    }

    /* Avatar photo */
    .avatar-photo {
      position: absolute; inset: 0;
      display: flex; align-items: center; justify-content: center;
    }
    .avatar-photo img {
      width: 220px; height: 220px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid var(--blue);
      box-shadow: 0 0 0 8px rgba(59,91,219,.15), 0 20px 60px rgba(0,0,0,.4);
      animation: float 4s ease-in-out infinite;
    }
    @keyframes float {
      0%,100% { transform: translateY(0); }
      50%      { transform: translateY(-10px); }
    }

    /* Floating badges */
    .float-badge {
      position: absolute;
      background: rgba(15,28,58,.85);
      border: 1px solid var(--border);
      border-radius: 10px; padding: 9px 14px;
      font-size: 11.5px; white-space: nowrap;
      backdrop-filter: blur(12px);
      box-shadow: 0 8px 24px rgba(0,0,0,.3);
      animation: float-badge var(--bf-dur, 5s) ease-in-out var(--bf-delay, 0s) infinite alternate;
    }
    @keyframes float-badge {
      from { transform: translateY(0); }
      to   { transform: translateY(-8px); }
    }
    .float-badge strong { display: block; font-size: 13px; font-weight: 700; color: #fff; }
    .float-badge span   { color: var(--text-dim); }

    .fb-matkul { top: 10px; right: -40px; --bf-dur: 4.5s; --bf-delay: 0s; }
    .fb-nim    { bottom: 30px; left: -50px; --bf-dur: 5.5s; --bf-delay: 1s; }
    .fb-prodi  { top: 50%; right: -55px; transform: translateY(-50%); --bf-dur: 6s; --bf-delay: .5s; }

    /* Schema tag floating */
    .schema-tag {
      position: absolute;
      font-family: 'Courier New', monospace;
      font-size: 10px; color: #86efac;
      background: rgba(15,28,58,.7);
      border: 1px solid rgba(134,239,172,.2);
      padding: 4px 10px; border-radius: 6px;
      backdrop-filter: blur(8px);
      animation: float-badge 7s ease-in-out infinite alternate;
    }
    .st-1 { bottom: 10px; right: -30px; --bf-delay: 2s; color: #93c5fd; border-color: rgba(147,197,253,.2); }
    .st-2 { top: -10px; left: -30px; --bf-delay: 3.5s; color: #c4b5fd; border-color: rgba(196,181,253,.2); }

    /* ── Bottom bar ── */
    .bottom-bar {
      grid-column: 1 / -1;
      display: flex; align-items: center; justify-content: space-between;
      padding: 16px 48px;
      border-top: 1px solid var(--border);
      background: rgba(15,28,58,.6);
      backdrop-filter: blur(12px);
      font-size: 12px; color: var(--text-dim);
    }
    .bottom-bar a { color: var(--text-dim); text-decoration: none; transition: color .2s; }
    .bottom-bar a:hover { color: #fff; }
    .progress-dots { display: flex; gap: 6px; }
    .progress-dot {
      width: 7px; height: 7px; border-radius: 50%;
      background: rgba(255,255,255,.2);
      transition: background .3s;
    }
    .progress-dot.active { background: var(--blue); box-shadow: 0 0 6px var(--blue); }

    /* ── Semantic badge strip ── */
    .semantic-strip {
      grid-column: 1 / -1;
      padding: 0 80px;
      animation: fade-up .7s ease .4s both;
    }
    .strip-inner {
      border-top: 1px solid var(--border);
      padding: 20px 0;
      display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
    }
    .strip-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: var(--text-dim); margin-right: 4px; }
    .schema-pill {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 5px 13px; border-radius: 20px;
      font-size: 11.5px; font-weight: 600;
      border: 1px solid rgba(255,255,255,.1);
      background: rgba(255,255,255,.05);
      transition: background .2s, border-color .2s;
    }
    .schema-pill:hover { background: rgba(59,91,219,.2); border-color: rgba(59,91,219,.4); }
    .schema-pill .dot { width: 6px; height: 6px; border-radius: 50%; }

    /* ── Responsive ── */
    @media (max-width: 900px) {
      .page { grid-template-columns: 1fr; }
      .hero-left  { padding: 40px 32px 20px; align-items: center; text-align: center; }
      .hero-right { padding: 20px 32px 40px; }
      .hero-desc  { max-width: 100%; }
      .info-cards { grid-template-columns: 1fr 1fr; }
      .cta-group  { justify-content: center; }
      .eyebrow    { margin-left: auto; margin-right: auto; }
      .hero-divider { margin-left: auto; margin-right: auto; }
      .topbar     { padding: 16px 24px; }
      .topbar-meta { display: none; }
      .bottom-bar  { padding: 14px 24px; }
      .semantic-strip { padding: 0 24px; }
      .avatar-stage { width: 260px; height: 260px; }
      .avatar-photo img { width: 180px; height: 180px; }
      .fb-matkul  { right: -10px; }
      .fb-prodi   { right: -10px; }
      .fb-nim     { left: -10px; }
    }

    @media (max-width: 480px) {
      .hero-name  { font-size: 28px; }
      .info-cards { grid-template-columns: 1fr; }
      .avatar-stage { width: 220px; height: 220px; }
      .avatar-photo img { width: 150px; height: 150px; }
      .float-badge { display: none; }
      .schema-tag  { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; }
    }
  </style>
</head>
<body>

<!-- Background -->
<div class="bg-stars" id="bgStars"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="page">

  <!-- ── TOP BAR ── -->
  <header class="topbar">
    <div class="topbar-brand">
      <div class="brand-dot">&#x1F393;</div>
      <div class="brand-label">
        Semantic Web Profile
        <span><?= e($universitas) ?></span>
      </div>
    </div>
    <div class="topbar-meta">
      <div class="meta-chip">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Semester <strong><?= e($semester) ?></strong>
      </div>
      <div class="meta-chip">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/></svg>
        <strong><?= e($matkul) ?></strong>
      </div>
      <div class="meta-chip">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        <strong><?= e($dosen) ?></strong>
      </div>
    </div>
  </header>

  <!-- ── HERO LEFT ── -->
  <section class="hero-left">
    <div class="eyebrow">
      <div class="eyebrow-dot"></div>
      Tugas Akhir Semester &mdash; <?= e($matkul) ?>
    </div>

    <p class="hero-nim"><?= e($nim) ?></p>

    <h1 class="hero-name"><?= e($nama) ?></h1>

    <div class="hero-divider"></div>

    <p class="hero-desc">
      Website profil berbasis <strong>Semantic Web</strong> yang menerapkan <strong>Schema.org JSON-LD</strong>,
      relasi semantik <strong>Subject&ndash;Predicate&ndash;Object</strong>, dan struktur data terstruktur
      agar dapat dibaca dan dipahami oleh mesin maupun manusia.
    </p>

    <!-- Info Cards -->
    <div class="info-cards">
      <div class="info-card">
        <div class="info-card-icon">&#x1F3EB;</div>
        <div class="info-card-label">Universitas</div>
        <div class="info-card-value"><?= e($universitas) ?></div>
      </div>
      <div class="info-card">
        <div class="info-card-icon">&#x1F4DA;</div>
        <div class="info-card-label">Program Studi</div>
        <div class="info-card-value"><?= e($prodi) ?></div>
      </div>
      <div class="info-card">
        <div class="info-card-icon">&#x1F4BB;</div>
        <div class="info-card-label">Mata Kuliah</div>
        <div class="info-card-value"><?= e($matkul) ?></div>
      </div>
      <div class="info-card">
        <div class="info-card-icon">&#x1F9D1;&#x200D;&#x1F3EB;</div>
        <div class="info-card-label">Dosen Pengampu</div>
        <div class="info-card-value"><?= e($dosen) ?></div>
      </div>
    </div>

    <!-- CTA -->
    <div class="cta-group">
      <a href="enter.php" class="btn-enter">
        Masuk ke Website
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
          <line x1="5" y1="12" x2="19" y2="12"/>
          <polyline points="12 5 19 12 12 19"/>
        </svg>
      </a>
    </div>
  </section>

  <!-- ── HERO RIGHT ── -->
  <section class="hero-right">
    <div class="avatar-stage">
      <!-- Rings -->
      <div class="ring ring-1"></div>
      <div class="ring ring-2"></div>
      <div class="ring ring-3"></div>

      <!-- Photo -->
      <div class="avatar-photo">
        <img
          src="<?= e($fotoDisplay) ?>"
          alt="<?= e($nama) ?>"
          onerror="this.onerror=null;this.src='<?= e($fotoFallback) ?>'">
      </div>

      <!-- Floating badges -->
      <div class="float-badge fb-matkul">
        <strong><?= e($matkul) ?></strong>
        <span>Mata Kuliah</span>
      </div>
      <div class="float-badge fb-nim">
        <strong><?= e($nim) ?></strong>
        <span>NIM Mahasiswa</span>
      </div>
      <div class="float-badge fb-prodi">
        <strong><?= e($prodi) ?></strong>
        <span>Program Studi</span>
      </div>

      <!-- Schema tags -->
      <div class="schema-tag st-1">"@type": "Person"</div>
      <div class="schema-tag st-2">"@context": "schema.org"</div>
    </div>
  </section>

  <!-- ── SEMANTIC STRIP ── -->
  <div class="semantic-strip" id="tentang">
    <div class="strip-inner">
      <span class="strip-label">Implementasi</span>
      <div class="schema-pill">
        <div class="dot" style="background:#93c5fd;"></div>
        Schema.org Person
      </div>
      <div class="schema-pill">
        <div class="dot" style="background:#86efac;"></div>
        CollegeOrUniversity
      </div>
      <div class="schema-pill">
        <div class="dot" style="background:#c4b5fd;"></div>
        DefinedTerm / Skill
      </div>
      <div class="schema-pill">
        <div class="dot" style="background:#fbbf24;"></div>
        Project
      </div>
      <div class="schema-pill">
        <div class="dot" style="background:#f9a8d4;"></div>
        JSON-LD
      </div>
      <div class="schema-pill">
        <div class="dot" style="background:#6ee7b7;"></div>
        RDF Triple (SPO)
      </div>
    </div>
  </div>


  <!-- ── BOTTOM BAR ── -->
  <footer class="bottom-bar">
    <span>
      &copy; <?= $tahun ?> &nbsp;&mdash;&nbsp; <?= e($nama) ?> &nbsp;&middot;&nbsp; <?= e($nim) ?>
    </span>
    <div class="progress-dots">
      <div class="progress-dot active"></div>
      <div class="progress-dot"></div>
      <div class="progress-dot"></div>
    </div>
    <span>
      <?= e($universitas) ?> &nbsp;&middot;&nbsp; <?= e($semester) ?>
    </span>
  </footer>

</div><!-- /.page -->

<script>
// Generate bintang
(function(){
  const container = document.getElementById('bgStars');
  for (let i = 0; i < 80; i++) {
    const s = document.createElement('span');
    const size = Math.random() * 2.5 + 1;
    s.style.cssText = `
      width:${size}px; height:${size}px;
      top:${Math.random()*100}%; left:${Math.random()*100}%;
      --dur:${(Math.random()*4+2).toFixed(1)}s;
      --delay:${(Math.random()*5).toFixed(1)}s;
      --op:${(Math.random()*.6+.2).toFixed(2)};
    `;
    container.appendChild(s);
  }
})();

// Progress dots animate on scroll
window.addEventListener('scroll', () => {
  const dots = document.querySelectorAll('.progress-dot');
  const progress = window.scrollY / (document.body.scrollHeight - window.innerHeight);
  const idx = Math.min(Math.floor(progress * dots.length), dots.length - 1);
  dots.forEach((d,i) => d.classList.toggle('active', i === idx));
});
</script>

</body>
</html>