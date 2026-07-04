<?php
require_once 'config/db.php';

// ── Ambil data dari database ──────────────────────────
$profil     = dbRow($pdo, "SELECT * FROM profil LIMIT 1");
$skills     = dbRows($pdo, "SELECT * FROM skill ORDER BY urutan ASC");
$pendidikan = dbRows($pdo, "SELECT * FROM pendidikan ORDER BY urutan ASC");
$organisasi = dbRows($pdo, "SELECT * FROM organisasi ORDER BY tahun_masuk DESC");
$proyek     = dbRows($pdo, "SELECT * FROM proyek ORDER BY tahun DESC");

// ── Path foto dengan fallback otomatis ───────────────
$fotoDb   = $profil['foto'] ?? '';
$fotoPath = !empty($fotoDb) ? $fotoDb
          : (file_exists('assets/foto.jpg')  ? 'assets/foto.jpg'
          : (file_exists('assets/foto.jpeg') ? 'assets/foto.jpeg'
          : (file_exists('assets/foto.png')  ? 'assets/foto.png'  : '')));
$namaDepan    = e(explode(' ', $profil['nama'] ?? 'User')[0]);
$fotoFallback = "https://ui-avatars.com/api/?name={$namaDepan}&background=3b5bdb&color=fff&size=80";

// ── Hitung ringkasan relasi ───────────────────────────
$totalSkill  = count($skills);
$totalDidik  = count($pendidikan);
$totalOrg    = count($organisasi);
$totalProyek = count($proyek);

// ── Data untuk graf (JSON ke JS) ─────────────────────
$nodes = [];
$edges = [];

// Node utama: Person
$nodes[] = ['id'=>'person','label'=>$namaDepan,'type'=>'person','icon'=>'👤'];

// Node: Universitas
$univ = $profil['universitas'] ?? 'Universitas Halu Oleo';
$nodes[] = ['id'=>'univ','label'=>$univ,'type'=>'univ','icon'=>'🏫'];
$edges[] = ['from'=>'person','to'=>'univ','label'=>'alumniOf'];

// Node: Skill (ambil maks 5)
foreach (array_slice($skills, 0, 5) as $i => $s) {
    $sid = 'skill_'.$i;
    $nodes[] = ['id'=>$sid,'label'=>$s['nama'],'type'=>'skill','icon'=>$s['ikon']??'⚡'];
    $edges[] = ['from'=>'person','to'=>$sid,'label'=>'knowsAbout'];
}

// Node: Organisasi (ambil maks 3)
foreach (array_slice($organisasi, 0, 3) as $i => $o) {
    $oid = 'org_'.$i;
    $nodes[] = ['id'=>$oid,'label'=>mb_strimwidth($o['nama'],0,14,'...'),'type'=>'org','icon'=>$o['ikon']??'🏛️'];
    $edges[] = ['from'=>'person','to'=>$oid,'label'=>'memberOf'];
}

// Node: Proyek (ambil maks 3)
foreach (array_slice($proyek, 0, 3) as $i => $p) {
    $pid = 'proj_'.$i;
    $nodes[] = ['id'=>$pid,'label'=>mb_strimwidth($p['judul'],0,14,'...'),'type'=>'proj','icon'=>$p['ikon']??'🗂️'];
    $edges[] = ['from'=>'person','to'=>$pid,'label'=>'author'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Relasi Semantik — <?= e($profil['nama'] ?? 'Mahasiswa') ?></title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* ── Triple RDF ── */
    .rdf-list {
      display: flex;
      flex-direction: column;
      gap: 5px;
      max-height: 480px;
      overflow-y: auto;
      padding-right: 4px;
    }
    .rdf-list::-webkit-scrollbar { width: 4px; }
    .rdf-list::-webkit-scrollbar-track { background: transparent; }
    .rdf-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

    .rdf-row {
      display: grid;
      grid-template-columns: 100px 128px 1fr;
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid #e2e8f0;
    }
    .rdf-subj {
      padding: 9px 10px;
      background: #eef2ff;
      color: #3b5bdb;
      font-weight: 700;
      font-size: 11px;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      line-height: 1.3;
    }
    .rdf-pred {
      padding: 8px 10px;
      background: #ffffff;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 2px;
      border-left: 1px solid #e2e8f0;
      border-right: 1px solid #e2e8f0;
    }
    .rdf-pred-en {
      font-size: 11px;
      font-weight: 700;
    }
    .rdf-pred-id {
      font-size: 10px;
      color: #94a3b8;
    }
    .rdf-obj {
      padding: 9px 12px;
      font-weight: 600;
      font-size: 11px;
      display: flex;
      align-items: center;
      gap: 5px;
      line-height: 1.3;
    }

    /* warna per relasi */
    .rdf-pred-alumni { color: #16a34a; }
    .rdf-obj-alumni  { background: #f0fdf4; color: #15803d; }

    .rdf-pred-knows  { color: #0369a1; }
    .rdf-obj-knows   { background: #f0f9ff; color: #0369a1; }

    .rdf-pred-member { color: #c2410c; }
    .rdf-obj-member  { background: #fff7ed; color: #c2410c; }

    .rdf-pred-author { color: #7c3aed; }
    .rdf-obj-author  { background: #faf5ff; color: #7c3aed; }

    .rdf-divider {
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 4px 0 2px;
    }
    .rdf-divider-line {
      flex: 1;
      height: 1px;
      background: #e2e8f0;
    }
    .rdf-divider-label {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: #94a3b8;
      white-space: nowrap;
    }

    /* ===== FIX TRIPLE RDF — override CSS global ===== */
    .rdf-card-wrap {
      background: #ffffff !important;
      color: #0f172a !important;
    }
    .rdf-row {
      min-height: 44px;
    }
    .rdf-subj {
      background: #eef2ff !important;
      color: #1e40af !important;
      font-size: 11px !important;
      font-weight: 700 !important;
      line-height: 1.4 !important;
    }
    .rdf-pred {
      background: #f8fafc !important;
    }
    .rdf-pred-en {
      font-size: 11px !important;
      font-weight: 700 !important;
    }
    .rdf-pred-id {
      color: #64748b !important;
      font-size: 10px !important;
    }
    .rdf-obj {
      font-size: 11px !important;
      font-weight: 600 !important;
    }
    .rdf-obj-alumni  { background: #f0fdf4 !important; color: #166534 !important; }
    .rdf-obj-knows   { background: #f0f9ff !important; color: #0369a1 !important; }
    .rdf-obj-member  { background: #fff7ed !important; color: #c2410c !important; }
    .rdf-obj-author  { background: #faf5ff !important; color: #7c3aed !important; }
    .rdf-pred-alumni { color: #16a34a !important; }
    .rdf-pred-knows  { color: #0369a1 !important; }
    .rdf-pred-member { color: #c2410c !important; }
    .rdf-pred-author { color: #7c3aed !important; }
    .rdf-list {
      max-height: 600px !important;
      overflow-y: auto;
    }
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
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Organisasi
    </a>
    <a href="project.php"      class="nav-item">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
      Proyek
    </a>
    <a href="semantic.php"     class="nav-item active">
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

<a href="ontology.php"     class="nav-item">
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
      Selamat datang, <span><?= e($profil['nama'] ?? 'Mahasiswa') ?></span>
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
        <img
          src="<?= e($fotoPath) ?>"
          alt="Foto Profil"
          class="topbar-avatar"
          onerror="this.src='<?= $fotoFallback ?>'">
        <span class="topbar-user-name"><?= $namaDepan ?></span>
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
      </div>
    </div>
  </header>

  <!-- PAGE CONTENT -->
  <main class="page-content">

    <!-- Page Header -->
    <div style="margin-bottom:20px;">
      <div class="page-eyebrow" style="font-size:12px;color:var(--text-secondary);margin-bottom:4px;">Schema.org · RDF · Linked Data</div>
      <h1 class="section-title" style="font-size:22px;">🔗 Relasi Semantik</h1>
      <p class="page-subtitle">Visualisasi graf hubungan antar entitas berdasarkan properti Schema.org yang diterapkan pada profil ini.</p>
    </div>

    <!-- ── Row 1: Stat Relasi ── -->
    <div class="grid-2 stagger" style="margin-bottom:20px;">
      <div class="stat-card">
        <div class="stat-icon blue">⚡</div>
        <div class="stat-label">knowsAbout</div>
        <div class="stat-value" data-target="<?= $totalSkill ?>">0</div>
        <div class="stat-desc">Relasi Skill</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">🎓</div>
        <div class="stat-label">alumniOf</div>
        <div class="stat-value" data-target="<?= $totalDidik ?>">0</div>
        <div class="stat-desc">Relasi Pendidikan</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon purple">🏛️</div>
        <div class="stat-label">memberOf</div>
        <div class="stat-value" data-target="<?= $totalOrg ?>">0</div>
        <div class="stat-desc">Relasi Organisasi</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange">🗂️</div>
        <div class="stat-label">author</div>
        <div class="stat-value" data-target="<?= $totalProyek ?>">0</div>
        <div class="stat-desc">Relasi Proyek</div>
      </div>
    </div>

    <!-- ── Row 2: Graf Interaktif ── -->
    <div class="card" style="margin-bottom:20px; animation:fade-up .5s ease .1s both;">
      <div class="card-body">
        <div class="section-header" style="margin-bottom:16px;">
          <div class="section-icon">🕸️</div>
          <div>
            <div class="section-title">Graf Relasi Semantik</div>
            <div class="page-subtitle">Klik node untuk melihat detail relasi</div>
          </div>
        </div>

        <!-- Legenda -->
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
          <span style="display:flex;align-items:center;gap:5px;font-size:12px;"><span style="width:12px;height:12px;border-radius:50%;background:#3b5bdb;display:inline-block;"></span> Person</span>
          <span style="display:flex;align-items:center;gap:5px;font-size:12px;"><span style="width:12px;height:12px;border-radius:50%;background:#16a34a;display:inline-block;"></span> Universitas</span>
          <span style="display:flex;align-items:center;gap:5px;font-size:12px;"><span style="width:12px;height:12px;border-radius:50%;background:#0ea5e9;display:inline-block;"></span> Skill</span>
          <span style="display:flex;align-items:center;gap:5px;font-size:12px;"><span style="width:12px;height:12px;border-radius:50%;background:#ea580c;display:inline-block;"></span> Organisasi</span>
          <span style="display:flex;align-items:center;gap:5px;font-size:12px;"><span style="width:12px;height:12px;border-radius:50%;background:#7c3aed;display:inline-block;"></span> Proyek</span>
        </div>

        <canvas id="grafCanvas" style="width:100%;height:560px;border-radius:10px;background:#0f172a;cursor:grab;"></canvas>

        <!-- Info box -->
        <div id="nodeInfo" style="display:none;margin-top:12px;padding:12px 16px;border-radius:8px;background:#0f172a;border:1px solid #334155;color:#e2e8f0;font-size:13px;"></div>
      </div>
    </div>

    <!-- ── Row 3: Tabel Properti (full-width sendiri) ── -->
    <div class="card" style="margin-bottom:20px;animation:fade-up .5s ease .15s both;">
      <div class="card-body">
        <div class="section-header" style="margin-bottom:14px;">
          <div class="section-icon">📋</div>
          <div><div class="section-title">Properti Schema.org</div></div>
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
              <tr style="border-bottom:2px solid var(--border-light);">
                <th style="padding:8px;text-align:left;color:var(--text-secondary);">Properti</th>
                <th style="padding:8px;text-align:left;color:var(--text-secondary);">Tipe</th>
                <th style="padding:8px;text-align:left;color:var(--text-secondary);">Jumlah</th>
              </tr>
            </thead>
            <tbody>
              <tr style="border-bottom:1px solid var(--border-light);">
                <td style="padding:9px 8px;font-weight:600;color:#3b5bdb;">knowsAbout</td>
                <td style="padding:9px 8px;color:var(--text-secondary);">Text / Thing</td>
                <td style="padding:9px 8px;"><span style="font-weight:700;color:#3b5bdb;"><?= $totalSkill ?></span></td>
              </tr>
              <tr style="border-bottom:1px solid var(--border-light);">
                <td style="padding:9px 8px;font-weight:600;color:#16a34a;">alumniOf</td>
                <td style="padding:9px 8px;color:var(--text-secondary);">EducationalOrganization</td>
                <td style="padding:9px 8px;"><span style="font-weight:700;color:#16a34a;"><?= $totalDidik ?></span></td>
              </tr>
              <tr style="border-bottom:1px solid var(--border-light);">
                <td style="padding:9px 8px;font-weight:600;color:#ea580c;">memberOf</td>
                <td style="padding:9px 8px;color:var(--text-secondary);">Organization</td>
                <td style="padding:9px 8px;"><span style="font-weight:700;color:#ea580c;"><?= $totalOrg ?></span></td>
              </tr>
              <tr style="border-bottom:1px solid var(--border-light);">
                <td style="padding:9px 8px;font-weight:600;color:#7c3aed;">author</td>
                <td style="padding:9px 8px;color:var(--text-secondary);">SoftwareApplication</td>
                <td style="padding:9px 8px;"><span style="font-weight:700;color:#7c3aed;"><?= $totalProyek ?></span></td>
              </tr>
              <tr>
                <td style="padding:9px 8px;font-weight:600;color:var(--text-primary);">alumniOf (S1)</td>
                <td style="padding:9px 8px;color:var(--text-secondary);">CollegeOrUniversity</td>
                <td style="padding:9px 8px;"><span style="font-weight:700;">1</span></td>
              </tr>
            </tbody>
          </table>
      </div>
    </div>

    <!-- ── Triple RDF — full-width ── -->
    <div class="card rdf-card-wrap" style="margin-bottom:20px;animation:fade-up .5s ease .2s both;">
      <div class="card-body">
          <div class="section-header" style="margin-bottom:14px;">
            <div class="section-icon">🔺</div>
            <div>
              <div class="section-title" style="color:#000;">Triple RDF</div>
<div class="page-subtitle" style="color:#000;">Subjek → Predikat → Objek</div>
            </div>
          </div>

          <!-- 4 kolom grid per kelompok relasi -->
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

            <!-- alumniOf + knowsAbout -->
            <div>
              <div class="rdf-list" style="max-height:none;overflow:visible;">
                <div class="rdf-divider">
                  <div class="rdf-divider-line"></div>
                  <span class="rdf-divider-label">alumniOf</span>
                  <div class="rdf-divider-line"></div>
                </div>
                <?php foreach ($pendidikan as $d): ?>
                <div class="rdf-row">
                  <div class="rdf-subj"><?= e($profil['nama'] ?? '') ?></div>
                  <div class="rdf-pred">
                    <span class="rdf-pred-en rdf-pred-alumni">alumniOf</span>
                    <span class="rdf-pred-id">kuliahDi</span>
                  </div>
                  <div class="rdf-obj rdf-obj-alumni"><?= e($d['institusi']) ?></div>
                </div>
                <?php endforeach; ?>

                <div class="rdf-divider" style="margin-top:10px;">
                  <div class="rdf-divider-line"></div>
                  <span class="rdf-divider-label">knowsAbout</span>
                  <div class="rdf-divider-line"></div>
                </div>
                <?php foreach ($skills as $s): ?>
                <div class="rdf-row">
                  <div class="rdf-subj"><?= e($profil['nama'] ?? '') ?></div>
                  <div class="rdf-pred">
                    <span class="rdf-pred-en rdf-pred-knows">knowsAbout</span>
                    <span class="rdf-pred-id">memiliki</span>
                  </div>
                  <div class="rdf-obj rdf-obj-knows"><?= e($s['ikon']??'') ?> <?= e($s['nama']) ?></div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- memberOf + author -->
            <div>
              <div class="rdf-list" style="max-height:none;overflow:visible;">
                <div class="rdf-divider">
                  <div class="rdf-divider-line"></div>
                  <span class="rdf-divider-label">memberOf</span>
                  <div class="rdf-divider-line"></div>
                </div>
                <?php foreach ($organisasi as $o): ?>
                <div class="rdf-row">
                  <div class="rdf-subj"><?= e($profil['nama'] ?? '') ?></div>
                  <div class="rdf-pred">
                    <span class="rdf-pred-en rdf-pred-member">memberOf</span>
                    <span class="rdf-pred-id">mengikuti</span>
                  </div>
                  <div class="rdf-obj rdf-obj-member"><?= e($o['ikon']??'') ?> <?= e($o['nama']) ?></div>
                </div>
                <?php endforeach; ?>

                <div class="rdf-divider" style="margin-top:10px;">
                  <div class="rdf-divider-line"></div>
                  <span class="rdf-divider-label">author</span>
                  <div class="rdf-divider-line"></div>
                </div>
                <?php foreach ($proyek as $p): ?>
                <div class="rdf-row">
                  <div class="rdf-subj"><?= e($profil['nama'] ?? '') ?></div>
                  <div class="rdf-pred">
                    <span class="rdf-pred-en rdf-pred-author">author</span>
                    <span class="rdf-pred-id">mengerjakan</span>
                  </div>
                  <div class="rdf-obj rdf-obj-author"><?= e($p['ikon']??'') ?> <?= e($p['judul']) ?></div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>

          </div><!-- /2-col grid -->
      </div>
    </div>

    <!-- ── Row 3b: Tabel SPO Formal ── -->
    <div class="card" style="margin-bottom:20px;animation:fade-up .5s ease .22s both;">
      <div class="card-body">
        <div class="section-header" style="margin-bottom:14px;">
          <div class="section-icon">📐</div>
          <div>
            <div class="section-title">Tabel Subject–Predicate–Object</div>
            <div class="page-subtitle">Format formal relasi semantik sesuai konsep RDF</div>
          </div>
        </div>
        <div style="overflow-x:auto;">
          <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
              <tr style="background:#f1f5f9;border-bottom:2px solid var(--border-light);">
                <th style="padding:10px 12px;text-align:left;color:var(--text-secondary);font-weight:700;">Subject (Subjek)</th>
                <th style="padding:10px 12px;text-align:left;color:var(--text-secondary);font-weight:700;">Predicate (Predikat)</th>
                <th style="padding:10px 12px;text-align:left;color:var(--text-secondary);font-weight:700;">Makna (Indonesia)</th>
                <th style="padding:10px 12px;text-align:left;color:var(--text-secondary);font-weight:700;">Object (Objek)</th>
                <th style="padding:10px 12px;text-align:left;color:var(--text-secondary);font-weight:700;">Tipe Schema.org</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pendidikan as $d): ?>
              <tr style="border-bottom:1px solid var(--border-light);">
                <td style="padding:9px 12px;font-weight:600;color:#3b5bdb;"><?= e($profil['nama']??'') ?></td>
                <td style="padding:9px 12px;"><code style="background:#dcfce7;color:#16a34a;padding:2px 6px;border-radius:4px;font-size:11px;">alumniOf</code></td>
                <td style="padding:9px 12px;color:var(--text-secondary);font-style:italic;">kuliahDi</td>
                <td style="padding:9px 12px;font-weight:600;color:#16a34a;"><?= e($d['institusi']) ?></td>
                <td style="padding:9px 12px;font-size:11px;color:var(--text-secondary);"><?= in_array($d['jenjang'],['S1','S2','D3']) ? 'CollegeOrUniversity' : 'EducationalOrganization' ?></td>
              </tr>
              <?php endforeach; ?>
              <?php foreach ($skills as $s): ?>
              <tr style="border-bottom:1px solid var(--border-light);">
                <td style="padding:9px 12px;font-weight:600;color:#3b5bdb;"><?= e($profil['nama']??'') ?></td>
                <td style="padding:9px 12px;"><code style="background:#e0f2fe;color:#0369a1;padding:2px 6px;border-radius:4px;font-size:11px;">knowsAbout</code></td>
                <td style="padding:9px 12px;color:var(--text-secondary);font-style:italic;">memiliki</td>
                <td style="padding:9px 12px;font-weight:600;color:#0369a1;"><?= e($s['ikon']??'') ?> <?= e($s['nama']) ?></td>
                <td style="padding:9px 12px;font-size:11px;color:var(--text-secondary);">DefinedTerm / Text</td>
              </tr>
              <?php endforeach; ?>
              <?php foreach ($organisasi as $o): ?>
              <tr style="border-bottom:1px solid var(--border-light);">
                <td style="padding:9px 12px;font-weight:600;color:#3b5bdb;"><?= e($profil['nama']??'') ?></td>
                <td style="padding:9px 12px;"><code style="background:#ffedd5;color:#ea580c;padding:2px 6px;border-radius:4px;font-size:11px;">memberOf</code></td>
                <td style="padding:9px 12px;color:var(--text-secondary);font-style:italic;">mengikuti</td>
                <td style="padding:9px 12px;font-weight:600;color:#ea580c;"><?= e($o['ikon']??'') ?> <?= e($o['nama']) ?></td>
                <td style="padding:9px 12px;font-size:11px;color:var(--text-secondary);">Organization</td>
              </tr>
              <?php endforeach; ?>
              <?php foreach ($proyek as $p): ?>
              <tr style="border-bottom:1px solid var(--border-light);">
                <td style="padding:9px 12px;font-weight:600;color:#3b5bdb;"><?= e($profil['nama']??'') ?></td>
                <td style="padding:9px 12px;"><code style="background:#ede9fe;color:#7c3aed;padding:2px 6px;border-radius:4px;font-size:11px;">author</code></td>
                <td style="padding:9px 12px;color:var(--text-secondary);font-style:italic;">mengerjakan</td>
                <td style="padding:9px 12px;font-weight:600;color:#7c3aed;"><?= e($p['ikon']??'') ?> <?= e($p['judul']) ?></td>
                <td style="padding:9px 12px;font-size:11px;color:var(--text-secondary);">SoftwareApplication</td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── Row 4: Penjelasan Konsep ── -->
    <div class="card" style="animation:fade-up .5s ease .25s both;">
      <div class="card-body">
        <div class="section-header" style="margin-bottom:16px;">
          <div class="section-icon">📖</div>
          <div><div class="section-title">Konsep Relasi Semantik</div></div>
        </div>
        <div class="grid-2" style="gap:16px;">
          <div style="padding:14px;border-radius:8px;background:#e8edff;border:1px solid #c7d3ff;">
            <div style="font-weight:700;color:#3b5bdb;margin-bottom:6px;">🔗 knowsAbout</div>
            <p style="font-size:13px;color:#3730a3;line-height:1.6;margin:0;">Properti yang menghubungkan seseorang dengan topik, konsep, atau bidang keahlian yang ia kuasai. Digunakan untuk merepresentasikan skill teknis.</p>
          </div>
          <div style="padding:14px;border-radius:8px;background:#dcfce7;border:1px solid #bbf7d0;">
            <div style="font-weight:700;color:#16a34a;margin-bottom:6px;">🎓 alumniOf</div>
            <p style="font-size:13px;color:#166534;line-height:1.6;margin:0;">Properti yang menghubungkan seseorang dengan institusi pendidikan tempat ia pernah atau sedang belajar. Nilainya bertipe EducationalOrganization.</p>
          </div>
          <div style="padding:14px;border-radius:8px;background:#ffedd5;border:1px solid #fed7aa;">
            <div style="font-weight:700;color:#ea580c;margin-bottom:6px;">🏛️ memberOf</div>
            <p style="font-size:13px;color:#9a3412;line-height:1.6;margin:0;">Properti yang menghubungkan seseorang dengan organisasi atau komunitas yang ia ikuti. Nilainya bertipe Organization.</p>
          </div>
          <div style="padding:14px;border-radius:8px;background:#ede9fe;border:1px solid #ddd6fe;">
            <div style="font-weight:700;color:#7c3aed;margin-bottom:6px;">🗂️ author</div>
            <p style="font-size:13px;color:#5b21b6;line-height:1.6;margin:0;">Properti yang menghubungkan seseorang dengan karya atau proyek yang ia buat. Nilainya bertipe SoftwareApplication atau CreativeWork.</p>
          </div>
        </div>
      </div>
    </div>

  </main>
</div><!-- /.main-wrapper -->

<script>
// ── Data dari PHP ──────────────────────────────────────
const nodes = <?= json_encode($nodes, JSON_UNESCAPED_UNICODE) ?>;
const edges = <?= json_encode($edges, JSON_UNESCAPED_UNICODE) ?>;

// ── Warna per tipe node ────────────────────────────────
const typeColor = {
  person : { fill:'#3b5bdb', stroke:'#1e3a8a', text:'#fff' },
  univ   : { fill:'#16a34a', stroke:'#14532d', text:'#fff' },
  skill  : { fill:'#0ea5e9', stroke:'#0369a1', text:'#fff' },
  org    : { fill:'#ea580c', stroke:'#9a3412', text:'#fff' },
  proj   : { fill:'#7c3aed', stroke:'#4c1d95', text:'#fff' },
};

// ── Hitung posisi node (multi-ring per tipe) ─────────
function calcPositions(canvas) {
  const W = canvas.width, H = canvas.height;
  const cx = W / 2, cy = H / 2;
  const pos = {};

  pos['person'] = { x: cx, y: cy };

  const ringDef = [
    { type:'univ',  r:110, centerAngle: -Math.PI/2,       spread: 0 },
    { type:'skill', r:210, centerAngle:  0,                spread: Math.PI * 0.7 },
    { type:'org',   r:190, centerAngle:  Math.PI,          spread: Math.PI * 0.6 },
    { type:'proj',  r:230, centerAngle:  Math.PI/2,        spread: Math.PI * 0.7 },
  ];

  ringDef.forEach(def => {
    const group = nodes.filter(n => n.type === def.type);
    const total = group.length;
    if (total === 0) return;
    group.forEach((n, i) => {
      const angle = total === 1
        ? def.centerAngle
        : def.centerAngle - def.spread/2 + (def.spread * i / (total - 1));
      pos[n.id] = {
        x: cx + def.r * Math.cos(angle),
        y: cy + def.r * Math.sin(angle),
      };
    });
  });

  return pos;
}

// ── Draw graf ─────────────────────────────────────────
const canvas  = document.getElementById('grafCanvas');
const ctx     = canvas.getContext('2d');
let positions = {};
let hoveredNode = null;

function resize() {
  canvas.width  = canvas.offsetWidth;
  canvas.height = canvas.offsetHeight;
  positions = calcPositions(canvas);
  draw();
}

function draw() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  edges.forEach(e => {
    const from = positions[e.from];
    const to   = positions[e.to];
    if (!from || !to) return;
    ctx.beginPath();
    ctx.moveTo(from.x, from.y);
    ctx.lineTo(to.x, to.y);
    ctx.strokeStyle = '#334155';
    ctx.lineWidth   = 1.5;
    ctx.setLineDash([5, 4]);
    ctx.stroke();
    ctx.setLineDash([]);
  });

  edges.forEach(e => {
    const from = positions[e.from];
    const to   = positions[e.to];
    if (!from || !to) return;
    const mx = (from.x + to.x) / 2;
    const my = (from.y + to.y) / 2;
    ctx.font = '9.5px Inter, sans-serif';
    const tw = ctx.measureText(e.label).width;
    const ph = 5, pw = 6;
    ctx.fillStyle   = '#1e293b';
    ctx.strokeStyle = '#334155';
    ctx.lineWidth   = 1;
    roundRect(ctx, mx - tw/2 - pw, my - 7 - ph, tw + pw*2, 14 + ph, 6);
    ctx.fill();
    ctx.stroke();
    ctx.fillStyle    = '#94a3b8';
    ctx.textAlign    = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(e.label, mx, my);
  });

  nodes.forEach(n => {
    const pos = positions[n.id];
    if (!pos) return;
    const c       = typeColor[n.type] || typeColor.proj;
    const r       = n.type === 'person' ? 36 : 26;
    const hovered = hoveredNode === n.id;
    if (hovered) { ctx.shadowColor = c.fill; ctx.shadowBlur = 18; }
    ctx.beginPath();
    ctx.arc(pos.x, pos.y, r + 3, 0, 2 * Math.PI);
    ctx.fillStyle = '#1e293b';
    ctx.fill();
    ctx.beginPath();
    ctx.arc(pos.x, pos.y, r, 0, 2 * Math.PI);
    ctx.fillStyle   = c.fill;
    ctx.fill();
    ctx.strokeStyle = c.stroke;
    ctx.lineWidth   = hovered ? 2.5 : 1.5;
    ctx.stroke();
    ctx.shadowBlur  = 0;
    ctx.font         = n.type === 'person' ? '17px serif' : '13px serif';
    ctx.textAlign    = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillStyle    = '#ffffff';
    ctx.fillText(n.icon, pos.x, pos.y - 3);
    ctx.font = `${n.type === 'person' ? '11px' : '10px'} Inter, sans-serif`;
    const lw  = ctx.measureText(n.label).width;
    const lx  = pos.x - lw/2 - 5;
    const ly  = pos.y + r + 6;
    const lh  = 16;
    const lrw = lw + 10;
    ctx.fillStyle   = '#1e293b';
    ctx.strokeStyle = '#334155';
    ctx.lineWidth   = 1;
    roundRect(ctx, lx, ly, lrw, lh, 8);
    ctx.fill();
    ctx.stroke();
    ctx.fillStyle    = '#e2e8f0';
    ctx.textAlign    = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(n.label, pos.x, ly + lh/2);
  });
}

function roundRect(ctx, x, y, w, h, r) {
  ctx.beginPath();
  ctx.moveTo(x + r, y);
  ctx.lineTo(x + w - r, y);
  ctx.quadraticCurveTo(x + w, y, x + w, y + r);
  ctx.lineTo(x + w, y + h - r);
  ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
  ctx.lineTo(x + r, y + h);
  ctx.quadraticCurveTo(x, y + h, x, y + h - r);
  ctx.lineTo(x, y + r);
  ctx.quadraticCurveTo(x, y, x + r, y);
  ctx.closePath();
}

function getNodeAt(x, y) {
  for (const n of nodes) {
    const pos = positions[n.id];
    if (!pos) continue;
    const r = n.type === 'person' ? 36 : 28;
    const dx = x - pos.x, dy = y - pos.y;
    if (dx * dx + dy * dy <= r * r) return n;
  }
  return null;
}

canvas.addEventListener('mousemove', ev => {
  const rect = canvas.getBoundingClientRect();
  const node = getNodeAt(ev.clientX - rect.left, ev.clientY - rect.top);
  hoveredNode = node ? node.id : null;
  canvas.style.cursor = node ? 'pointer' : 'default';
  draw();
});

canvas.addEventListener('click', ev => {
  const rect = canvas.getBoundingClientRect();
  const node = getNodeAt(ev.clientX - rect.left, ev.clientY - rect.top);
  const box  = document.getElementById('nodeInfo');
  if (node) {
    const relasi = edges.filter(e => e.from === node.id || e.to === node.id);
    const tipeLabel = { person:'Person', univ:'CollegeOrUniversity', skill:'knowsAbout', org:'Organization', proj:'SoftwareApplication' };
    box.style.display = 'block';
    box.innerHTML = `
      <strong style="font-size:14px;">${node.icon} ${node.label}</strong>
      <span style="margin-left:10px;font-size:11px;padding:2px 8px;border-radius:10px;background:${typeColor[node.type]?.fill};color:#fff;">${tipeLabel[node.type]||node.type}</span>
      <div style="margin-top:8px;color:var(--text-secondary);">
        Terhubung melalui: <strong>${relasi.map(e=>e.label).join(', ')}</strong>
      </div>`;
  } else {
    box.style.display = 'none';
  }
});

canvas.addEventListener('touchstart', ev => {
  const rect  = canvas.getBoundingClientRect();
  const touch = ev.touches[0];
  const node  = getNodeAt(touch.clientX - rect.left, touch.clientY - rect.top);
  const box   = document.getElementById('nodeInfo');
  if (node) {
    const relasi = edges.filter(e => e.from === node.id || e.to === node.id);
    const tipeLabel = { person:'Person', univ:'CollegeOrUniversity', skill:'knowsAbout', org:'Organization', proj:'SoftwareApplication' };
    box.style.display = 'block';
    box.innerHTML = `
      <strong>${node.icon} ${node.label}</strong>
      <span style="margin-left:8px;font-size:11px;padding:2px 8px;border-radius:10px;background:${typeColor[node.type]?.fill};color:#fff;">${tipeLabel[node.type]||node.type}</span>
      <div style="margin-top:6px;color:var(--text-secondary);">Relasi: <strong>${relasi.map(e=>e.label).join(', ')}</strong></div>`;
  }
}, { passive: true });

// ── Sidebar toggle ────────────────────────────────────
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

// ── Counter animation ─────────────────────────────────
const counters = document.querySelectorAll('.stat-value[data-target]');
const counterObs = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (!entry.isIntersecting) return;
    const el = entry.target;
    const target = parseInt(el.dataset.target);
    let current = 0;
    const step = Math.max(1, Math.ceil(target / 25));
    const timer = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = current;
      if (current >= target) clearInterval(timer);
    }, 40);
    counterObs.unobserve(el);
  });
}, { threshold: 0.5 });
counters.forEach(el => counterObs.observe(el));

window.addEventListener('resize', resize);
resize();
</script>

</body>
</html>