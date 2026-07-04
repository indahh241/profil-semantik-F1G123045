<?php
require_once 'config/db.php';

// ── Ambil data dari database ───────────────────────────
$profil     = dbRow($pdo, "SELECT * FROM profil LIMIT 1");
$skills     = dbRows($pdo, "SELECT * FROM skill ORDER BY urutan ASC");
$pendidikan = dbRows($pdo, "SELECT * FROM pendidikan ORDER BY urutan ASC");
$organisasi = dbRows($pdo, "SELECT * FROM organisasi ORDER BY tahun_masuk DESC");
$proyek     = dbRows($pdo, "SELECT * FROM proyek ORDER BY tahun DESC");

// ── Path foto ─────────────────────────────────────────
$fotoDb   = $profil['foto'] ?? '';
$fotoPath = !empty($fotoDb) ? $fotoDb
          : (file_exists('assets/foto.jpg')  ? 'assets/foto.jpg'
          : (file_exists('assets/foto.jpeg') ? 'assets/foto.jpeg'
          : (file_exists('assets/foto.png')  ? 'assets/foto.png'  : '')));
$namaDepan    = e(explode(' ', $profil['nama'] ?? 'User')[0]);
$fotoFallback = "https://ui-avatars.com/api/?name={$namaDepan}&background=3b5bdb&color=fff&size=80";

$nama  = e($profil['nama']  ?? 'Indah Haerunnisa');
$nim   = e($profil['nim']   ?? 'F1G123045');
$univ  = e($profil['universitas'] ?? 'Universitas Halu Oleo');
$prodi = e($profil['prodi'] ?? 'Informatika');
$email = e($profil['email'] ?? '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ontology OWL · SPARQL · DBpedia — <?= $nama ?></title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* ── Tab system ── */
    .tab-nav {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
      margin-bottom: 20px;
      background: var(--bg-page);
      padding: 6px;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border);
    }
    .tab-btn {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 9px 18px;
      border-radius: 8px;
      border: none;
      background: transparent;
      font-size: 13px;
      font-weight: 600;
      font-family: inherit;
      color: var(--text-secondary);
      cursor: pointer;
      transition: all var(--transition);
    }
    .tab-btn:hover  { background: var(--bg-card); color: var(--text-primary); }
    .tab-btn.active { background: var(--blue); color: #fff; box-shadow: 0 4px 12px rgba(59,91,219,.3); }
    .tab-panel      { display: none; }
    .tab-panel.show { display: block; }

    /* ── Code block (OWL/SPARQL) ── */
    .owl-block, .sparql-block {
      background: #0f172a;
      border-radius: var(--radius-sm);
      padding: 20px 22px;
      overflow-x: auto;
      font-family: 'Courier New', monospace;
      font-size: 12.5px;
      line-height: 1.9;
      position: relative;
      border: 1px solid #1e293b;
    }
    .owl-block::before  { content: 'OWL/Turtle';  position:absolute;top:10px;right:14px;font-size:10px;font-weight:700;color:rgba(255,255,255,.25);letter-spacing:.1em;font-family:'Inter',sans-serif; }
    .sparql-block::before { content: 'SPARQL';    position:absolute;top:10px;right:14px;font-size:10px;font-weight:700;color:rgba(255,255,255,.25);letter-spacing:.1em;font-family:'Inter',sans-serif; }

    .c-prefix  { color: #94a3b8; }    /* @prefix */
    .c-uri     { color: #fbbf24; }    /* <URI> */
    .c-cls     { color: #f9a8d4; }    /* owl:Class */
    .c-prop    { color: #93c5fd; }    /* owl:ObjectProperty */
    .c-ind     { color: #86efac; }    /* individual / literal */
    .c-kw      { color: #c084fc; }    /* a, rdfs:, owl: */
    .c-punct   { color: #64748b; }    /* ; . , */
    .c-comment { color: #475569; font-style:italic; }

    .c-sq-kw   { color: #c084fc; }   /* SELECT WHERE FROM */
    .c-sq-var  { color: #fde68a; }   /* ?var */
    .c-sq-uri  { color: #fbbf24; }   /* <uri> */
    .c-sq-pred { color: #93c5fd; }   /* foaf: schema: */
    .c-sq-str  { color: #86efac; }   /* "literal" */
    .c-sq-cmt  { color: #475569; font-style:italic; } /* # comment */

    /* ── Copy button ── */
    .copy-btn {
      position: absolute;
      top: 10px; right: 70px;
      background: rgba(255,255,255,.07);
      border: 1px solid rgba(255,255,255,.12);
      color: rgba(255,255,255,.5);
      padding: 3px 10px;
      border-radius: 5px;
      font-size: 11px;
      font-family: 'Inter', sans-serif;
      cursor: pointer;
      transition: all .2s ease;
    }
    .copy-btn:hover { background: rgba(255,255,255,.14); color: #fff; }

    /* ── Class diagram card ── */
    .owl-class-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 14px;
      margin-top: 4px;
    }
    .owl-class-card {
      border-radius: var(--radius-sm);
      border: 1.5px solid;
      padding: 14px 16px;
      transition: transform var(--transition), box-shadow var(--transition);
    }
    .owl-class-card:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-sm);
    }
    .owl-class-name  { font-weight: 700; font-size: 13px; margin-bottom: 4px; }
    .owl-class-desc  { font-size: 11.5px; line-height: 1.5; opacity:.75; }
    .owl-class-props { margin-top: 8px; display: flex; flex-wrap: wrap; gap: 4px; }
    .owl-prop-tag {
      font-size: 10px; font-weight: 600;
      padding: 2px 7px; border-radius: 10px;
      background: rgba(0,0,0,.07);
    }

    /* ── SPARQL results table ── */
    .sparql-result-table thead th { color: var(--blue); font-size: 11px; text-transform: uppercase; letter-spacing: .05em; }
    .sparql-result-table .var-col { font-family: monospace; font-size: 12px; color: var(--purple); font-weight: 600; }
    .sparql-result-table .val-col { font-size: 12.5px; }
    .sparql-result-table .uri-val { color: var(--blue); font-size: 11.5px; word-break: break-all; }

    /* ── DBpedia info card ── */
    .dbp-entity {
      display: flex;
      gap: 14px;
      align-items: flex-start;
      padding: 16px;
      border-radius: var(--radius-sm);
      background: var(--bg-page);
      border: 1px solid var(--border);
      margin-bottom: 12px;
      transition: background var(--transition), transform var(--transition);
    }
    .dbp-entity:hover { background: var(--blue-light); transform: translateX(3px); }
    .dbp-entity-icon { font-size: 26px; line-height: 1; flex-shrink: 0; }
    .dbp-entity-name { font-weight: 700; font-size: 13.5px; color: var(--text-primary); margin-bottom: 3px; }
    .dbp-entity-uri  { font-size: 11px; color: var(--blue); word-break: break-all; font-family: monospace; }
    .dbp-entity-desc { font-size: 12px; color: var(--text-secondary); margin-top: 4px; line-height: 1.5; }
    .dbp-triple-tag {
      display: inline-flex; align-items: center; gap: 4px;
      font-size: 10.5px; font-weight: 600;
      padding: 2px 8px; border-radius: 10px;
      margin-top: 6px; margin-right: 4px;
    }

    /* ── Properti / Aksioma ── */
    .axiom-list { display: flex; flex-direction: column; gap: 8px; }
    .axiom-row {
      display: grid;
      grid-template-columns: 160px 1fr;
      gap: 10px;
      padding: 10px 14px;
      background: var(--bg-page);
      border-radius: var(--radius-sm);
      border: 1px solid var(--border);
      font-size: 12.5px;
      align-items: center;
      transition: background var(--transition);
    }
    .axiom-row:hover { background: var(--blue-light); }
    .axiom-type  { font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; }
    .axiom-value { color: var(--text-secondary); line-height: 1.5; }

    /* ── Endpoint card ── */
    .endpoint-card {
      display: flex; align-items: center; gap: 12px;
      padding: 12px 16px;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border);
      background: var(--bg-page);
      margin-bottom: 8px;
      font-size: 12.5px;
      transition: background var(--transition);
    }
    .endpoint-card:hover { background: var(--blue-light); }
    .endpoint-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .endpoint-name { font-weight: 700; color: var(--text-primary); margin-bottom: 1px; }
    .endpoint-url  { font-family: monospace; font-size: 11px; color: var(--blue); }

    /* panel code wrap relative */
    .code-wrap { position: relative; }
  </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">🎓</div>
    <div class="brand-text">
      <h2>Semantic Profile</h2>
      <span>Mahasiswa</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-label">Menu</div>
    <a href="index.php"        class="nav-item">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Dashboard
    </a>
    <a href="profil.php"       class="nav-item">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      Profil
    </a>
    <a href="pendidikan.php"   class="nav-item">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/></svg>
      Riwayat Pendidikan
    </a>
    <a href="skill.php"        class="nav-item">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
      Skill
    </a>
    <a href="organisasi.php"   class="nav-item">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Organisasi
    </a>
    <a href="project.php"      class="nav-item">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
      Proyek
    </a>
    <a href="semantic.php"     class="nav-item">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/></svg>
      Relasi Semantik
    </a>
<a href="schema.php" class="nav-item">
  <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
    <polyline points="16 18 22 12 16 6"/>
    <polyline points="8 6 2 12 8 18"/>
  </svg>
  Schema.org
</a>
    <a href="ontology.php"     class="nav-item active">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4.03 3-9 3S3 13.66 3 12"/><path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/></svg>
      Ontologi
    </a>
    <a href="kontak.php"       class="nav-item">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      Kontak
    </a>
    <a href="about.php" class="nav-item">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      Tentang Website
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-footer-card">
      <div class="sidebar-footer-icon">🎓</div>
      <p>"Knowledge<br>Connects Everything"</p>
    </div>
  </div>
</aside>

<!-- ===== MAIN ===== -->
<div class="main-wrapper">

  <!-- TOPBAR -->
  <header class="topbar">
    <button class="topbar-menu-btn" id="menuBtn" aria-label="Toggle sidebar">
      <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </button>

    <div class="topbar-greeting">
      Selamat datang, <span><?= $nama ?></span>
      <span class="wave">👋</span>
    </div>

    <div class="topbar-search">
      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <input type="text" placeholder="Cari sesuatu..." id="searchInput">
    </div>

    <div class="topbar-actions">
      <a href="admin/index.php" class="topbar-btn" title="Panel Admin" style="text-decoration:none;">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
        </svg>
      </a>
      <div class="topbar-user">
        <img src="<?= e($fotoPath) ?>" alt="Foto" class="topbar-avatar" onerror="this.src='<?= $fotoFallback ?>'">
        <span class="topbar-user-name"><?= $namaDepan ?></span>
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
      </div>
    </div>
  </header>

  <!-- PAGE CONTENT -->
  <main class="page-content">

    <!-- Page Header -->
    <div class="page-header">
      <div style="font-size:12px;color:var(--text-secondary);margin-bottom:4px;">OWL · SPARQL · DBpedia · Linked Data</div>
      <h1 class="page-title">🧬 Ontologi, SPARQL &amp; DBpedia</h1>
      <p class="page-subtitle">Representasi formal ontologi OWL, query SPARQL, dan keterkaitan data dengan DBpedia untuk profil semantik ini.</p>
    </div>

    <!-- ── Stat Ringkasan ── -->
    <div class="grid-4 stagger" style="margin-bottom:24px;">
      <div class="stat-card">
        <div class="stat-icon blue">🧬</div>
        <div class="stat-label">OWL Class</div>
        <div class="stat-value" data-target="6">0</div>
        <div class="stat-desc">Kelas Ontologi</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon purple">🔗</div>
        <div class="stat-label">Object Property</div>
        <div class="stat-value" data-target="5">0</div>
        <div class="stat-desc">Properti Objek</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">🔍</div>
        <div class="stat-label">SPARQL Query</div>
        <div class="stat-value" data-target="4">0</div>
        <div class="stat-desc">Contoh Query</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange">🌐</div>
        <div class="stat-label">DBpedia Link</div>
        <div class="stat-value" data-target="<?= count($skills) + 1 ?>">0</div>
        <div class="stat-desc">Entitas Terhubung</div>
      </div>
    </div>

    <!-- ── Tab Nav ── -->
    <div class="tab-nav" role="tablist">
      <button class="tab-btn active" data-tab="owl" role="tab">🧬 OWL Ontologi</button>
      <button class="tab-btn"        data-tab="sparql" role="tab">🔍 SPARQL Query</button>
      <button class="tab-btn"        data-tab="dbpedia" role="tab">🌐 DBpedia</button>
      <button class="tab-btn"        data-tab="linked" role="tab">🔗 Linked Data</button>
    </div>

    <!-- ═══════════════════════════════════════
         TAB 1 — OWL ONTOLOGI
    ═══════════════════════════════════════ -->
    <div class="tab-panel show" id="tab-owl">

      <!-- Kelas Ontologi -->
      <div class="card" style="margin-bottom:20px; animation:fade-up .4s ease both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:16px;">
            <div class="section-icon">📦</div>
            <div>
              <div class="section-title">Kelas Ontologi (owl:Class)</div>
              <div class="page-subtitle">Hierarki kelas yang mendefinisikan tipe entitas dalam profil semantik ini</div>
            </div>
          </div>
          <div class="owl-class-grid">
            <div class="owl-class-card" style="border-color:#3b5bdb;background:#e8edff;">
              <div class="owl-class-name" style="color:#3b5bdb;">👤 Person</div>
              <div class="owl-class-desc" style="color:#3730a3;">Individu / pemilik profil. Subclass dari foaf:Person dan schema:Person.</div>
              <div class="owl-class-props">
                <span class="owl-prop-tag" style="background:#c7d3ff;color:#3b5bdb;">alumniOf</span>
                <span class="owl-prop-tag" style="background:#c7d3ff;color:#3b5bdb;">knowsAbout</span>
                <span class="owl-prop-tag" style="background:#c7d3ff;color:#3b5bdb;">memberOf</span>
                <span class="owl-prop-tag" style="background:#c7d3ff;color:#3b5bdb;">author</span>
              </div>
            </div>
            <div class="owl-class-card" style="border-color:#16a34a;background:#dcfce7;">
              <div class="owl-class-name" style="color:#16a34a;">🏫 EducationalOrg</div>
              <div class="owl-class-desc" style="color:#166534;">Institusi pendidikan. Subclass dari schema:EducationalOrganization.</div>
              <div class="owl-class-props">
                <span class="owl-prop-tag" style="background:#bbf7d0;color:#16a34a;">name</span>
                <span class="owl-prop-tag" style="background:#bbf7d0;color:#16a34a;">location</span>
              </div>
            </div>
            <div class="owl-class-card" style="border-color:#0891b2;background:#cffafe;">
              <div class="owl-class-name" style="color:#0891b2;">⚡ Skill</div>
              <div class="owl-class-desc" style="color:#155e75;">Kemampuan teknis. Direpresentasikan sebagai schema:DefinedTerm.</div>
              <div class="owl-class-props">
                <span class="owl-prop-tag" style="background:#a5f3fc;color:#0891b2;">termCode</span>
                <span class="owl-prop-tag" style="background:#a5f3fc;color:#0891b2;">level</span>
              </div>
            </div>
            <div class="owl-class-card" style="border-color:#ea580c;background:#ffedd5;">
              <div class="owl-class-name" style="color:#ea580c;">🏛️ Organization</div>
              <div class="owl-class-desc" style="color:#9a3412;">Organisasi / komunitas. Subclass dari schema:Organization.</div>
              <div class="owl-class-props">
                <span class="owl-prop-tag" style="background:#fed7aa;color:#ea580c;">name</span>
                <span class="owl-prop-tag" style="background:#fed7aa;color:#ea580c;">startDate</span>
              </div>
            </div>
            <div class="owl-class-card" style="border-color:#7c3aed;background:#ede9fe;">
              <div class="owl-class-name" style="color:#7c3aed;">🗂️ CreativeWork</div>
              <div class="owl-class-desc" style="color:#5b21b6;">Proyek / karya. Subclass dari schema:CreativeWork / SoftwareApplication.</div>
              <div class="owl-class-props">
                <span class="owl-prop-tag" style="background:#ddd6fe;color:#7c3aed;">name</span>
                <span class="owl-prop-tag" style="background:#ddd6fe;color:#7c3aed;">dateCreated</span>
                <span class="owl-prop-tag" style="background:#ddd6fe;color:#7c3aed;">url</span>
              </div>
            </div>
            <div class="owl-class-card" style="border-color:#64748b;background:#f1f5f9;">
              <div class="owl-class-name" style="color:#475569;">📜 DefinedTerm</div>
              <div class="owl-class-desc" style="color:#64748b;">Term yang didefinisikan dari kumpulan pengetahuan tertentu (DefinedTermSet).</div>
              <div class="owl-class-props">
                <span class="owl-prop-tag" style="background:#e2e8f0;color:#64748b;">termCode</span>
                <span class="owl-prop-tag" style="background:#e2e8f0;color:#64748b;">inDefinedTermSet</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- OWL Turtle syntax -->
      <div class="card" style="margin-bottom:20px; animation:fade-up .4s ease .08s both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:14px;">
            <div class="section-icon">📝</div>
            <div>
              <div class="section-title">Deklarasi Ontologi (Turtle / OWL)</div>
              <div class="page-subtitle">Format Turtle yang merepresentasikan kelas dan properti ontologi profil ini</div>
            </div>
          </div>
          <div class="code-wrap">
            <button class="copy-btn" onclick="copyCode('owlCode')">Salin</button>
            <pre class="owl-block" id="owlCode"><span class="c-comment"># ── Prefix ────────────────────────────────────────────────</span>
<span class="c-prefix">@prefix</span> <span class="c-kw">owl:</span>    <span class="c-uri">&lt;http://www.w3.org/2002/07/owl#&gt;</span> <span class="c-punct">.</span>
<span class="c-prefix">@prefix</span> <span class="c-kw">rdf:</span>    <span class="c-uri">&lt;http://www.w3.org/1999/02/22-rdf-syntax-ns#&gt;</span> <span class="c-punct">.</span>
<span class="c-prefix">@prefix</span> <span class="c-kw">rdfs:</span>   <span class="c-uri">&lt;http://www.w3.org/2000/01/rdf-schema#&gt;</span> <span class="c-punct">.</span>
<span class="c-prefix">@prefix</span> <span class="c-kw">schema:</span> <span class="c-uri">&lt;https://schema.org/&gt;</span> <span class="c-punct">.</span>
<span class="c-prefix">@prefix</span> <span class="c-kw">foaf:</span>   <span class="c-uri">&lt;http://xmlns.com/foaf/0.1/&gt;</span> <span class="c-punct">.</span>
<span class="c-prefix">@prefix</span> <span class="c-kw">sp:</span>     <span class="c-uri">&lt;http://localhost/profil-semantik/ontology#&gt;</span> <span class="c-punct">.</span>

<span class="c-comment"># ── Deklarasi Ontologi ─────────────────────────────────────</span>
<span class="c-uri">&lt;http://localhost/profil-semantik/ontology&gt;</span>
    <span class="c-kw">a</span> <span class="c-cls">owl:Ontology</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:label</span>   <span class="c-ind">"Semantic Profile Ontology"</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:comment</span> <span class="c-ind">"Ontologi untuk profil semantik mahasiswa"</span> <span class="c-punct">.</span>

<span class="c-comment"># ── Kelas: Person ──────────────────────────────────────────</span>
<span class="c-kw">sp:Person</span>
    <span class="c-kw">a</span> <span class="c-cls">owl:Class</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:subClassOf</span> <span class="c-cls">schema:Person</span><span class="c-punct">,</span> <span class="c-cls">foaf:Person</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:label</span>      <span class="c-ind">"Person"</span> <span class="c-punct">.</span>

<span class="c-comment"># ── Kelas: EducationalOrganization ────────────────────────</span>
<span class="c-kw">sp:EducationalOrg</span>
    <span class="c-kw">a</span> <span class="c-cls">owl:Class</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:subClassOf</span> <span class="c-cls">schema:EducationalOrganization</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:label</span>      <span class="c-ind">"Educational Organization"</span> <span class="c-punct">.</span>

<span class="c-comment"># ── Kelas: Skill (DefinedTerm) ─────────────────────────────</span>
<span class="c-kw">sp:Skill</span>
    <span class="c-kw">a</span> <span class="c-cls">owl:Class</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:subClassOf</span> <span class="c-cls">schema:DefinedTerm</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:label</span>      <span class="c-ind">"Skill"</span> <span class="c-punct">.</span>

<span class="c-comment"># ── Kelas: Organization ───────────────────────────────────</span>
<span class="c-kw">sp:Organization</span>
    <span class="c-kw">a</span> <span class="c-cls">owl:Class</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:subClassOf</span> <span class="c-cls">schema:Organization</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:label</span>      <span class="c-ind">"Organization"</span> <span class="c-punct">.</span>

<span class="c-comment"># ── Kelas: CreativeWork ───────────────────────────────────</span>
<span class="c-kw">sp:Project</span>
    <span class="c-kw">a</span> <span class="c-cls">owl:Class</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:subClassOf</span> <span class="c-cls">schema:SoftwareApplication</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:label</span>      <span class="c-ind">"Software Project"</span> <span class="c-punct">.</span>

<span class="c-comment"># ── Object Properties ─────────────────────────────────────</span>
<span class="c-prop">sp:alumniOf</span>
    <span class="c-kw">a</span>                    <span class="c-cls">owl:ObjectProperty</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:subPropertyOf</span>   <span class="c-cls">schema:alumniOf</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:domain</span>          <span class="c-kw">sp:Person</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:range</span>           <span class="c-kw">sp:EducationalOrg</span> <span class="c-punct">.</span>

<span class="c-prop">sp:knowsAbout</span>
    <span class="c-kw">a</span>                    <span class="c-cls">owl:ObjectProperty</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:subPropertyOf</span>   <span class="c-cls">schema:knowsAbout</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:domain</span>          <span class="c-kw">sp:Person</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:range</span>           <span class="c-kw">sp:Skill</span> <span class="c-punct">.</span>

<span class="c-prop">sp:memberOf</span>
    <span class="c-kw">a</span>                    <span class="c-cls">owl:ObjectProperty</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:subPropertyOf</span>   <span class="c-cls">schema:memberOf</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:domain</span>          <span class="c-kw">sp:Person</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:range</span>           <span class="c-kw">sp:Organization</span> <span class="c-punct">.</span>

<span class="c-prop">sp:author</span>
    <span class="c-kw">a</span>                    <span class="c-cls">owl:ObjectProperty</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:subPropertyOf</span>   <span class="c-cls">schema:author</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:domain</span>          <span class="c-kw">sp:Person</span> <span class="c-punct">;</span>
    <span class="c-kw">rdfs:range</span>           <span class="c-kw">sp:Project</span> <span class="c-punct">.</span>

<span class="c-comment"># ── Individual: Pemilik Profil ─────────────────────────────</span>
<span class="c-uri">&lt;http://localhost/profil-semantik/person/<?= urlencode($profil['nama'] ?? 'Indah') ?>&gt;</span>
    <span class="c-kw">a</span>                   <span class="c-kw">sp:Person</span> <span class="c-punct">;</span>
    <span class="c-kw">schema:name</span>         <span class="c-ind">"<?= $nama ?>"</span> <span class="c-punct">;</span>
    <span class="c-kw">schema:identifier</span>   <span class="c-ind">"<?= $nim ?>"</span> <span class="c-punct">;</span>
    <span class="c-kw">schema:alumniOf</span>     <span class="c-uri">&lt;https://dbpedia.org/resource/Halu_Oleo_University&gt;</span> <span class="c-punct">.</span></pre>
          </div>
        </div>
      </div>

      <!-- Aksioma OWL -->
      <div class="card" style="animation:fade-up .4s ease .14s both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:14px;">
            <div class="section-icon">⚙️</div>
            <div><div class="section-title">Aksioma &amp; Constraint OWL</div></div>
          </div>
          <div class="axiom-list">
            <div class="axiom-row">
              <span class="axiom-type" style="color:var(--blue);">owl:disjointWith</span>
              <span class="axiom-value"><code style="font-size:11.5px;">sp:Person owl:disjointWith sp:Organization</code> — Person dan Organization adalah kelas yang saling eksklusif</span>
            </div>
            <div class="axiom-row">
              <span class="axiom-type" style="color:var(--purple);">owl:FunctionalProperty</span>
              <span class="axiom-value"><code style="font-size:11.5px;">schema:name</code> bersifat fungsional — setiap entitas hanya memiliki satu nama utama</span>
            </div>
            <div class="axiom-row">
              <span class="axiom-type" style="color:var(--green);">rdfs:domain</span>
              <span class="axiom-value"><code style="font-size:11.5px;">sp:alumniOf</code> hanya berlaku untuk instance <code>sp:Person</code> (domain constraint)</span>
            </div>
            <div class="axiom-row">
              <span class="axiom-type" style="color:var(--orange);">rdfs:range</span>
              <span class="axiom-value"><code style="font-size:11.5px;">sp:alumniOf</code> hanya mengarah ke instance <code>sp:EducationalOrg</code> (range constraint)</span>
            </div>
            <div class="axiom-row">
              <span class="axiom-type" style="color:var(--cyan);">owl:TransitiveProperty</span>
              <span class="axiom-value"><code style="font-size:11.5px;">rdfs:subClassOf</code> bersifat transitif — subclass dari subclass tetap merupakan subclass</span>
            </div>
          </div>
        </div>
      </div>
    </div><!-- /tab-owl -->


    <!-- ═══════════════════════════════════════
         TAB 2 — SPARQL QUERY
    ═══════════════════════════════════════ -->
    <div class="tab-panel" id="tab-sparql">

      <!-- Query 1 -->
      <div class="card" style="margin-bottom:20px; animation:fade-up .4s ease both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:12px;">
            <div class="section-icon" style="background:var(--green-light);color:var(--green);">Q1</div>
            <div>
              <div class="section-title">Query: Semua Skill yang Dimiliki</div>
              <div class="page-subtitle">Mengambil daftar skill dari profil menggunakan properti schema:knowsAbout</div>
            </div>
          </div>
          <div class="code-wrap" style="margin-bottom:14px;">
            <button class="copy-btn" onclick="copyCode('sq1')">Salin</button>
            <pre class="sparql-block" id="sq1"><span class="c-sq-cmt"># Ambil semua skill yang dimiliki pemilik profil</span>
<span class="c-sq-kw">PREFIX</span> <span class="c-sq-pred">schema:</span> <span class="c-sq-uri">&lt;https://schema.org/&gt;</span>
<span class="c-sq-kw">PREFIX</span> <span class="c-sq-pred">sp:</span>     <span class="c-sq-uri">&lt;http://localhost/profil-semantik/ontology#&gt;</span>

<span class="c-sq-kw">SELECT</span> <span class="c-sq-var">?nama</span> <span class="c-sq-var">?skill</span> <span class="c-sq-var">?level</span>
<span class="c-sq-kw">WHERE</span> <span class="c-punct">{</span>
  <span class="c-sq-var">?person</span>  <span class="c-sq-pred">a</span>                    <span class="c-sq-pred">sp:Person</span> <span class="c-punct">;</span>
           <span class="c-sq-pred">schema:name</span>          <span class="c-sq-var">?nama</span> <span class="c-punct">;</span>
           <span class="c-sq-pred">schema:knowsAbout</span>    <span class="c-sq-var">?skillNode</span> <span class="c-punct">.</span>
  <span class="c-sq-var">?skillNode</span> <span class="c-sq-pred">schema:name</span>       <span class="c-sq-var">?skill</span> <span class="c-punct">;</span>
             <span class="c-sq-pred">schema:termCode</span>   <span class="c-sq-var">?level</span> <span class="c-punct">.</span>
<span class="c-punct">}</span>
<span class="c-sq-kw">ORDER BY</span> <span class="c-sq-kw">DESC</span><span class="c-punct">(</span><span class="c-sq-var">?level</span><span class="c-punct">)</span></pre>
          </div>
          <!-- Hasil simulasi -->
          <div style="font-size:11.5px;font-weight:600;color:var(--text-muted);margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em;">Hasil Simulasi</div>
          <div class="table-wrap">
            <table class="sparql-result-table">
              <thead><tr><th>?nama</th><th>?skill</th><th>?level</th></tr></thead>
              <tbody>
                <?php foreach ($skills as $s): ?>
                <tr>
                  <td class="val-col" style="color:var(--blue);font-weight:600;"><?= $nama ?></td>
                  <td class="val-col"><?= e($s['ikon'] ?? '') ?> <?= e($s['nama']) ?></td>
                  <td class="val-col"><span class="badge badge-blue"><?= e($s['level'] ?? $s['persen'] ?? '—') ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Query 2 -->
      <div class="card" style="margin-bottom:20px; animation:fade-up .4s ease .06s both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:12px;">
            <div class="section-icon" style="background:var(--purple-light);color:var(--purple);">Q2</div>
            <div>
              <div class="section-title">Query: Riwayat Pendidikan (alumniOf)</div>
              <div class="page-subtitle">Mengambil institusi pendidikan beserta jenjang dan tahun</div>
            </div>
          </div>
          <div class="code-wrap" style="margin-bottom:14px;">
            <button class="copy-btn" onclick="copyCode('sq2')">Salin</button>
            <pre class="sparql-block" id="sq2"><span class="c-sq-kw">PREFIX</span> <span class="c-sq-pred">schema:</span> <span class="c-sq-uri">&lt;https://schema.org/&gt;</span>
<span class="c-sq-kw">PREFIX</span> <span class="c-sq-pred">sp:</span>     <span class="c-sq-uri">&lt;http://localhost/profil-semantik/ontology#&gt;</span>

<span class="c-sq-kw">SELECT</span> <span class="c-sq-var">?institusi</span> <span class="c-sq-var">?jenjang</span> <span class="c-sq-var">?tahunMasuk</span> <span class="c-sq-var">?tahunLulus</span>
<span class="c-sq-kw">WHERE</span> <span class="c-punct">{</span>
  <span class="c-sq-var">?person</span>  <span class="c-sq-pred">a</span>               <span class="c-sq-pred">sp:Person</span> <span class="c-punct">;</span>
           <span class="c-sq-pred">schema:alumniOf</span> <span class="c-sq-var">?edu</span> <span class="c-punct">.</span>
  <span class="c-sq-var">?edu</span>     <span class="c-sq-pred">schema:name</span>     <span class="c-sq-var">?institusi</span> <span class="c-punct">;</span>
           <span class="c-sq-pred">sp:jenjang</span>      <span class="c-sq-var">?jenjang</span> <span class="c-punct">;</span>
           <span class="c-sq-pred">sp:tahunMasuk</span>   <span class="c-sq-var">?tahunMasuk</span> <span class="c-punct">.</span>
  <span class="c-sq-kw">OPTIONAL</span> <span class="c-punct">{</span> <span class="c-sq-var">?edu</span> <span class="c-sq-pred">sp:tahunLulus</span> <span class="c-sq-var">?tahunLulus</span> <span class="c-punct">}</span>
<span class="c-punct">}</span>
<span class="c-sq-kw">ORDER BY</span> <span class="c-sq-var">?tahunMasuk</span></pre>
          </div>
          <div class="table-wrap">
            <table class="sparql-result-table">
              <thead><tr><th>?institusi</th><th>?jenjang</th><th>?tahunMasuk</th><th>?tahunLulus</th></tr></thead>
              <tbody>
                <?php foreach ($pendidikan as $d): ?>
                <tr>
                  <td class="val-col" style="font-weight:600;"><?= e($d['institusi']) ?></td>
                  <td class="val-col"><span class="badge badge-green"><?= e($d['jenjang'] ?? '—') ?></span></td>
                  <td class="val-col"><?= e($d['tahun_masuk'] ?? '—') ?></td>
                  <td class="val-col"><?= e($d['tahun_lulus'] ?? 'Sekarang') ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Query 3 -->
      <div class="card" style="margin-bottom:20px; animation:fade-up .4s ease .10s both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:12px;">
            <div class="section-icon" style="background:var(--orange-light);color:var(--orange);">Q3</div>
            <div>
              <div class="section-title">Query: Organisasi &amp; Proyek</div>
              <div class="page-subtitle">Mengambil seluruh keanggotaan organisasi dan proyek yang dikerjakan</div>
            </div>
          </div>
          <div class="code-wrap" style="margin-bottom:14px;">
            <button class="copy-btn" onclick="copyCode('sq3')">Salin</button>
            <pre class="sparql-block" id="sq3"><span class="c-sq-kw">PREFIX</span> <span class="c-sq-pred">schema:</span> <span class="c-sq-uri">&lt;https://schema.org/&gt;</span>
<span class="c-sq-kw">PREFIX</span> <span class="c-sq-pred">sp:</span>     <span class="c-sq-uri">&lt;http://localhost/profil-semantik/ontology#&gt;</span>

<span class="c-sq-kw">SELECT</span> <span class="c-sq-var">?tipe</span> <span class="c-sq-var">?nama</span> <span class="c-sq-var">?tahun</span>
<span class="c-sq-kw">WHERE</span> <span class="c-punct">{</span>
  <span class="c-punct">{</span>
    <span class="c-sq-var">?person</span> <span class="c-sq-pred">schema:memberOf</span> <span class="c-sq-var">?org</span> <span class="c-punct">.</span>
    <span class="c-sq-var">?org</span>    <span class="c-sq-pred">schema:name</span>     <span class="c-sq-var">?nama</span> <span class="c-punct">;</span>
            <span class="c-sq-pred">sp:tahunMasuk</span>   <span class="c-sq-var">?tahun</span> <span class="c-punct">.</span>
    <span class="c-sq-kw">BIND</span><span class="c-punct">(</span><span class="c-sq-str">"Organisasi"</span> <span class="c-sq-kw">AS</span> <span class="c-sq-var">?tipe</span><span class="c-punct">)</span>
  <span class="c-punct">}</span> <span class="c-sq-kw">UNION</span> <span class="c-punct">{</span>
    <span class="c-sq-var">?person</span> <span class="c-sq-pred">schema:author</span> <span class="c-sq-var">?proj</span> <span class="c-punct">.</span>
    <span class="c-sq-var">?proj</span>   <span class="c-sq-pred">schema:name</span>   <span class="c-sq-var">?nama</span> <span class="c-punct">;</span>
            <span class="c-sq-pred">sp:tahun</span>      <span class="c-sq-var">?tahun</span> <span class="c-punct">.</span>
    <span class="c-sq-kw">BIND</span><span class="c-punct">(</span><span class="c-sq-str">"Proyek"</span> <span class="c-sq-kw">AS</span> <span class="c-sq-var">?tipe</span><span class="c-punct">)</span>
  <span class="c-punct">}</span>
<span class="c-punct">}</span>
<span class="c-sq-kw">ORDER BY</span> <span class="c-sq-var">?tipe</span> <span class="c-sq-var">?tahun</span></pre>
          </div>
          <div class="table-wrap">
            <table class="sparql-result-table">
              <thead><tr><th>?tipe</th><th>?nama</th><th>?tahun</th></tr></thead>
              <tbody>
                <?php foreach ($organisasi as $o): ?>
                <tr>
                  <td><span class="badge badge-orange">Organisasi</span></td>
                  <td class="val-col"><?= e($o['ikon'] ?? '') ?> <?= e($o['nama']) ?></td>
                  <td class="val-col"><?= e($o['tahun_masuk'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php foreach ($proyek as $p): ?>
                <tr>
                  <td><span class="badge badge-purple">Proyek</span></td>
                  <td class="val-col"><?= e($p['ikon'] ?? '') ?> <?= e($p['judul']) ?></td>
                  <td class="val-col"><?= e($p['tahun'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Query 4 — ASK -->
      <div class="card" style="animation:fade-up .4s ease .14s both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:12px;">
            <div class="section-icon" style="background:var(--cyan-light);color:var(--cyan);">Q4</div>
            <div>
              <div class="section-title">Query: ASK — Apakah Person merupakan Mahasiswa Aktif?</div>
              <div class="page-subtitle">Tipe query SPARQL ASK menghasilkan nilai boolean (true/false)</div>
            </div>
          </div>
          <div class="code-wrap" style="margin-bottom:14px;">
            <button class="copy-btn" onclick="copyCode('sq4')">Salin</button>
            <pre class="sparql-block" id="sq4"><span class="c-sq-kw">PREFIX</span> <span class="c-sq-pred">schema:</span> <span class="c-sq-uri">&lt;https://schema.org/&gt;</span>
<span class="c-sq-kw">PREFIX</span> <span class="c-sq-pred">sp:</span>     <span class="c-sq-uri">&lt;http://localhost/profil-semantik/ontology#&gt;</span>

<span class="c-sq-kw">ASK</span>
<span class="c-sq-kw">WHERE</span> <span class="c-punct">{</span>
  <span class="c-sq-var">?person</span>
    <span class="c-sq-pred">a</span>                  <span class="c-sq-pred">sp:Person</span> <span class="c-punct">;</span>
    <span class="c-sq-pred">schema:alumniOf</span>    <span class="c-sq-var">?edu</span> <span class="c-punct">.</span>
  <span class="c-sq-var">?edu</span>
    <span class="c-sq-pred">sp:statusAktif</span>     <span class="c-sq-str">"true"</span><span class="c-punct">^^xsd:boolean</span> <span class="c-punct">.</span>
<span class="c-punct">}</span></pre>
          </div>
          <div style="padding:16px 20px;background:var(--green-light);border-radius:var(--radius-sm);border:1px solid #bbf7d0;">
            <span style="font-weight:700;color:var(--green);font-size:14px;">✓ Hasil: <code>true</code></span>
            <span style="font-size:12px;color:var(--green);margin-left:10px;"><?= $nama ?> adalah mahasiswa aktif di <?= $univ ?></span>
          </div>
        </div>
      </div>
    </div><!-- /tab-sparql -->


    <!-- ═══════════════════════════════════════
         TAB 3 — DBPEDIA
    ═══════════════════════════════════════ -->
    <div class="tab-panel" id="tab-dbpedia">

      <div class="card" style="margin-bottom:20px; animation:fade-up .4s ease both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:16px;">
            <div class="section-icon">🌐</div>
            <div>
              <div class="section-title">Entitas DBpedia yang Terhubung</div>
              <div class="page-subtitle">Keterkaitan profil semantik ini dengan Linked Open Data melalui DBpedia</div>
            </div>
          </div>
          <!-- Universitas -->
          <div class="dbp-entity">
            <div class="dbp-entity-icon">🏫</div>
            <div style="flex:1;">
              <div class="dbp-entity-name"><?= $univ ?></div>
              <div class="dbp-entity-uri">https://dbpedia.org/resource/Halu_Oleo_University</div>
              <div class="dbp-entity-desc">Universitas negeri di Kota Kendari, Sulawesi Tenggara, Indonesia. Direpresentasikan sebagai <code>dbo:University</code> dan <code>schema:CollegeOrUniversity</code> di DBpedia.</div>
              <div>
                <span class="dbp-triple-tag" style="background:#dcfce7;color:#16a34a;">alumniOf</span>
                <span class="dbp-triple-tag" style="background:#e0f2fe;color:#0369a1;">dbo:University</span>
                <span class="dbp-triple-tag" style="background:#f0fdf4;color:#15803d;">owl:sameAs</span>
              </div>
            </div>
          </div>
          <!-- Skills dari DB -->
          <?php
          $dbpSkillMap = [
            'PHP'        => ['uri'=>'https://dbpedia.org/resource/PHP', 'desc'=>'Server-side scripting language', 'class'=>'dbo:ProgrammingLanguage'],
            'MySQL'      => ['uri'=>'https://dbpedia.org/resource/MySQL', 'desc'=>'Open-source relational database', 'class'=>'dbo:Software'],
            'Python'     => ['uri'=>'https://dbpedia.org/resource/Python_(programming_language)', 'desc'=>'High-level programming language', 'class'=>'dbo:ProgrammingLanguage'],
            'JavaScript' => ['uri'=>'https://dbpedia.org/resource/JavaScript', 'desc'=>'High-level scripting language for web', 'class'=>'dbo:ProgrammingLanguage'],
            'Laravel'    => ['uri'=>'https://dbpedia.org/resource/Laravel', 'desc'=>'PHP web application framework', 'class'=>'dbo:Software'],
            'HTML'       => ['uri'=>'https://dbpedia.org/resource/HTML', 'desc'=>'Standard markup language for web', 'class'=>'dbo:ProgrammingLanguage'],
            'CSS'        => ['uri'=>'https://dbpedia.org/resource/CSS', 'desc'=>'Style sheet language for web', 'class'=>'dbo:ProgrammingLanguage'],
            'Docker'     => ['uri'=>'https://dbpedia.org/resource/Docker_(software)', 'desc'=>'Platform for developing and running apps', 'class'=>'dbo:Software'],
          ];
          foreach ($skills as $s):
            $sn = $s['nama'];
            $dbp = null;
            foreach ($dbpSkillMap as $kw => $val) {
              if (stripos($sn, $kw) !== false) { $dbp = $val; break; }
            }
          ?>
          <div class="dbp-entity">
            <div class="dbp-entity-icon"><?= e($s['ikon'] ?? '⚡') ?></div>
            <div style="flex:1;">
              <div class="dbp-entity-name"><?= e($sn) ?></div>
              <?php if ($dbp): ?>
                <div class="dbp-entity-uri"><?= $dbp['uri'] ?></div>
                <div class="dbp-entity-desc"><?= $dbp['desc'] ?> — dikaitkan sebagai <code><?= $dbp['class'] ?></code></div>
                <div>
                  <span class="dbp-triple-tag" style="background:#e0f2fe;color:#0891b2;">knowsAbout</span>
                  <span class="dbp-triple-tag" style="background:#ede9fe;color:#7c3aed;"><?= $dbp['class'] ?></span>
                </div>
              <?php else: ?>
                <div class="dbp-entity-uri" style="color:var(--text-muted);">URI DBpedia belum tersedia untuk topik ini</div>
                <div class="dbp-entity-desc">Dapat dipetakan ke <code>schema:DefinedTerm</code> dengan referensi lokal</div>
                <div>
                  <span class="dbp-triple-tag" style="background:#e0f2fe;color:#0891b2;">knowsAbout</span>
                  <span class="dbp-triple-tag" style="background:#f1f5f9;color:#64748b;">schema:DefinedTerm</span>
                </div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- SPARQL ke DBpedia endpoint -->
      <div class="card" style="animation:fade-up .4s ease .08s both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:14px;">
            <div class="section-icon">🔍</div>
            <div>
              <div class="section-title">Contoh Query ke DBpedia SPARQL Endpoint</div>
              <div class="page-subtitle">Query ini dapat dijalankan di <a href="https://dbpedia.org/sparql" target="_blank" style="color:var(--blue);">dbpedia.org/sparql</a></div>
            </div>
          </div>
          <div class="code-wrap" style="margin-bottom:14px;">
            <button class="copy-btn" onclick="copyCode('sqdbp')">Salin</button>
            <pre class="sparql-block" id="sqdbp"><span class="c-sq-cmt"># Query DBpedia untuk mendapatkan info Universitas Halu Oleo</span>
<span class="c-sq-kw">PREFIX</span> <span class="c-sq-pred">dbo:</span>   <span class="c-sq-uri">&lt;http://dbpedia.org/ontology/&gt;</span>
<span class="c-sq-kw">PREFIX</span> <span class="c-sq-pred">dbr:</span>   <span class="c-sq-uri">&lt;http://dbpedia.org/resource/&gt;</span>
<span class="c-sq-kw">PREFIX</span> <span class="c-sq-pred">rdfs:</span>  <span class="c-sq-uri">&lt;http://www.w3.org/2000/01/rdf-schema#&gt;</span>

<span class="c-sq-kw">SELECT</span> <span class="c-sq-var">?label</span> <span class="c-sq-var">?abstract</span> <span class="c-sq-var">?kota</span> <span class="c-sq-var">?negara</span>
<span class="c-sq-kw">WHERE</span> <span class="c-punct">{</span>
  <span class="c-sq-pred">dbr:Halu_Oleo_University</span>
    <span class="c-sq-pred">rdfs:label</span>     <span class="c-sq-var">?label</span> <span class="c-punct">;</span>
    <span class="c-sq-pred">dbo:abstract</span>   <span class="c-sq-var">?abstract</span> <span class="c-punct">;</span>
    <span class="c-sq-pred">dbo:city</span>       <span class="c-sq-var">?kota</span> <span class="c-punct">;</span>
    <span class="c-sq-pred">dbo:country</span>    <span class="c-sq-var">?negara</span> <span class="c-punct">.</span>
  <span class="c-sq-kw">FILTER</span><span class="c-punct">(</span><span class="c-sq-kw">LANG</span><span class="c-punct">(</span><span class="c-sq-var">?label</span><span class="c-punct">)</span> <span class="c-punct">=</span> <span class="c-sq-str">"en"</span>
      <span class="c-sq-kw">&&</span> <span class="c-sq-kw">LANG</span><span class="c-punct">(</span><span class="c-sq-var">?abstract</span><span class="c-punct">)</span> <span class="c-punct">=</span> <span class="c-sq-str">"en"</span><span class="c-punct">)</span>
<span class="c-punct">}</span></pre>
          </div>
          <div style="padding:14px 16px;background:#0f172a;border-radius:8px;border:1px solid #1e293b;font-size:12px;font-family:monospace;color:#86efac;">
            <div style="color:#475569;margin-bottom:6px;"># Hasil (simulasi):</div>
            <div><span style="color:#fbbf24;">?label</span>    → "Halu Oleo University"@en</div>
            <div><span style="color:#fbbf24;">?abstract</span> → "Halu Oleo University (UHO) is a state university in Kendari..."@en</div>
            <div><span style="color:#fbbf24;">?kota</span>     → dbr:Kendari</div>
            <div><span style="color:#fbbf24;">?negara</span>   → dbr:Indonesia</div>
          </div>
        </div>
      </div>
    </div><!-- /tab-dbpedia -->


    <!-- ═══════════════════════════════════════
         TAB 4 — LINKED DATA
    ═══════════════════════════════════════ -->
    <div class="tab-panel" id="tab-linked">

      <div class="card" style="margin-bottom:20px; animation:fade-up .4s ease both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:16px;">
            <div class="section-icon">🔗</div>
            <div>
              <div class="section-title">Prinsip Linked Data</div>
              <div class="page-subtitle">Empat prinsip Linked Data oleh Tim Berners-Lee diterapkan pada profil ini</div>
            </div>
          </div>
          <div class="grid-2" style="gap:16px;">
            <div style="padding:16px;border-radius:8px;background:var(--blue-light);border:1px solid rgba(59,91,219,.2);">
              <div style="font-weight:700;color:var(--blue);margin-bottom:6px;">① Gunakan URI sebagai nama entitas</div>
              <p style="font-size:12.5px;color:var(--text-secondary);line-height:1.6;margin:0;">Setiap entitas diidentifikasi dengan URI unik, contoh: <code>http://localhost/profil-semantik/person/IndahHaerunnisa</code></p>
            </div>
            <div style="padding:16px;border-radius:8px;background:var(--green-light);border:1px solid rgba(22,163,74,.2);">
              <div style="font-weight:700;color:var(--green);margin-bottom:6px;">② Gunakan HTTP URI agar bisa di-lookup</div>
              <p style="font-size:12.5px;color:var(--text-secondary);line-height:1.6;margin:0;">URI dapat diakses melalui HTTP sehingga siapapun bisa mencari informasi tentang entitas tersebut.</p>
            </div>
            <div style="padding:16px;border-radius:8px;background:var(--orange-light);border:1px solid rgba(234,88,12,.2);">
              <div style="font-weight:700;color:var(--orange);margin-bottom:6px;">③ Sediakan informasi berguna (RDF, SPARQL)</div>
              <p style="font-size:12.5px;color:var(--text-secondary);line-height:1.6;margin:0;">Ketika URI di-lookup, kembalikan data dalam format RDF/Turtle atau JSON-LD agar mesin bisa membaca.</p>
            </div>
            <div style="padding:16px;border-radius:8px;background:var(--purple-light);border:1px solid rgba(124,58,237,.2);">
              <div style="font-weight:700;color:var(--purple);margin-bottom:6px;">④ Sertakan link ke URI lain (owl:sameAs)</div>
              <p style="font-size:12.5px;color:var(--text-secondary);line-height:1.6;margin:0;">Hubungkan ke entitas eksternal seperti DBpedia menggunakan <code>owl:sameAs</code> untuk memperluas konteks data.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- SPARQL Endpoint List -->
      <div class="card" style="margin-bottom:20px; animation:fade-up .4s ease .08s both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:14px;">
            <div class="section-icon">🖥️</div>
            <div><div class="section-title">SPARQL Endpoint Publik</div></div>
          </div>
          <div class="endpoint-card">
            <div class="endpoint-dot" style="background:#16a34a;"></div>
            <div>
              <div class="endpoint-name">DBpedia SPARQL Endpoint</div>
              <div class="endpoint-url">https://dbpedia.org/sparql</div>
            </div>
            <a href="https://dbpedia.org/sparql" target="_blank" class="btn btn-outline" style="margin-left:auto;font-size:12px;padding:6px 14px;">Buka →</a>
          </div>
          <div class="endpoint-card">
            <div class="endpoint-dot" style="background:#0891b2;"></div>
            <div>
              <div class="endpoint-name">Wikidata Query Service</div>
              <div class="endpoint-url">https://query.wikidata.org/</div>
            </div>
            <a href="https://query.wikidata.org/" target="_blank" class="btn btn-outline" style="margin-left:auto;font-size:12px;padding:6px 14px;">Buka →</a>
          </div>
          <div class="endpoint-card">
            <div class="endpoint-dot" style="background:#7c3aed;"></div>
            <div>
              <div class="endpoint-name">Schema.org Validator</div>
              <div class="endpoint-url">https://validator.schema.org/</div>
            </div>
            <a href="https://validator.schema.org/" target="_blank" class="btn btn-outline" style="margin-left:auto;font-size:12px;padding:6px 14px;">Buka →</a>
          </div>
        </div>
      </div>

      <!-- owl:sameAs mapping -->
      <div class="card" style="animation:fade-up .4s ease .12s both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:14px;">
            <div class="section-icon">🔁</div>
            <div>
              <div class="section-title">Pemetaan owl:sameAs</div>
              <div class="page-subtitle">Menghubungkan entitas lokal ke Linked Open Data eksternal</div>
            </div>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Entitas Lokal</th>
                  <th>owl:sameAs</th>
                  <th>Sumber LOD</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td style="font-weight:600;color:var(--blue);">sp:IndahHaerunnisa</td>
                  <td style="font-family:monospace;font-size:11.5px;color:var(--blue);">foaf:Person + schema:Person</td>
                  <td><span class="badge badge-blue">Schema.org</span></td>
                </tr>
                <tr>
                  <td style="font-weight:600;color:var(--green);">sp:HaluOleoUniv</td>
                  <td style="font-family:monospace;font-size:11.5px;color:var(--blue);">dbr:Halu_Oleo_University</td>
                  <td><span class="badge badge-green">DBpedia</span></td>
                </tr>
                <?php foreach ($skills as $s):
                  $sn = $s['nama'];
                  $dbp = null;
                  foreach ($dbpSkillMap as $kw => $val) {
                    if (stripos($sn, $kw) !== false) { $dbp = $val; break; }
                  }
                  if (!$dbp) continue;
                ?>
                <tr>
                  <td style="font-weight:600;color:var(--cyan);">sp:<?= preg_replace('/\s+/', '', e($sn)) ?>Skill</td>
                  <td style="font-family:monospace;font-size:11px;color:var(--blue);word-break:break-all;"><?= $dbp['uri'] ?></td>
                  <td><span class="badge badge-purple">DBpedia</span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div><!-- /tab-linked -->

  </main>
</div><!-- /.main-wrapper -->

<script>
// ── Tab system ─────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('show'));
    btn.classList.add('active');
    document.getElementById('tab-' + btn.dataset.tab).classList.add('show');
  });
});

// ── Copy code ──────────────────────────────────────────
function copyCode(id) {
  const el  = document.getElementById(id);
  const txt = el.innerText;
  navigator.clipboard.writeText(txt).then(() => {
    const btn = el.parentElement.querySelector('.copy-btn');
    btn.textContent = 'Tersalin ✓';
    btn.style.color = '#86efac';
    setTimeout(() => { btn.textContent = 'Salin'; btn.style.color = ''; }, 2000);
  });
}

// ── Sidebar toggle ─────────────────────────────────────
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');
const menuBtn = document.getElementById('menuBtn');
menuBtn.addEventListener('click', () => {
  const open = sidebar.classList.toggle('open');
  overlay.classList.toggle('show', open);
  document.body.style.overflow = open ? 'hidden' : '';
});
overlay.addEventListener('click', () => {
  sidebar.classList.remove('open');
  overlay.classList.remove('show');
  document.body.style.overflow = '';
});

// ── Counter animation ──────────────────────────────────
const counters = document.querySelectorAll('.stat-value[data-target]');
const obs = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (!entry.isIntersecting) return;
    const el  = entry.target;
    const tgt = parseInt(el.dataset.target);
    let cur   = 0;
    const step = Math.max(1, Math.ceil(tgt / 25));
    const t = setInterval(() => {
      cur = Math.min(cur + step, tgt);
      el.textContent = cur;
      if (cur >= tgt) clearInterval(t);
    }, 40);
    obs.unobserve(el);
  });
}, { threshold: 0.5 });
counters.forEach(el => obs.observe(el));
</script>

</body>
</html>