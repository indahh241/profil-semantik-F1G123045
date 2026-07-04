<?php
require_once 'config/db.php';

// ── Ambil data dari database ──────────────────────────
$profil     = dbRow($pdo, "SELECT * FROM profil LIMIT 1");
$skills     = dbRows($pdo, "SELECT * FROM skill ORDER BY urutan ASC");
$organisasi = dbRows($pdo, "SELECT * FROM organisasi ORDER BY tahun_masuk DESC");

// ── Path foto dengan fallback otomatis ───────────────
$fotoDb   = $profil['foto'] ?? '';
$fotoPath = !empty($fotoDb) ? $fotoDb
          : (file_exists('assets/foto.jpg')  ? 'assets/foto.jpg'
          : (file_exists('assets/foto.jpeg') ? 'assets/foto.jpeg'
          : (file_exists('assets/foto.png')  ? 'assets/foto.png'  : '')));
$namaDepan    = e(explode(' ', $profil['nama'] ?? 'User')[0]);
$fotoFallback = "https://ui-avatars.com/api/?name={$namaDepan}&background=3b5bdb&color=fff&size=80";

// ── JSON-LD ───────────────────────────────────────────
$skillNames = array_column($skills, 'nama');
$orgName    = !empty($organisasi) ? $organisasi[0]['nama'] : 'HIMAKOM UHO';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil — <?= e($profil['nama'] ?? 'Mahasiswa') ?></title>
  <link rel="stylesheet" href="style.css">

  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Person",
    "name": "<?= e($profil['nama'] ?? '') ?>",
    "identifier": "<?= e($profil['nim'] ?? '') ?>",
    "email": "<?= e($profil['email'] ?? '') ?>",
    "telephone": "<?= e($profil['telepon'] ?? '') ?>",
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "<?= e($profil['alamat'] ?? '') ?>",
      "addressCountry": "ID"
    },
    "description": "<?= addslashes(e($profil['bio'] ?? '')) ?>",
    "image": "<?= e($profil['foto'] ?? '') ?>",
    "url": "<?= APP_URL ?>",
    "sameAs": [
      "<?= e($profil['github'] ?? '') ?>",
      "<?= e($profil['linkedin'] ?? '') ?>"
    ],
    "alumniOf": {
      "@type": "CollegeOrUniversity",
      "name": "<?= e($profil['universitas'] ?? '') ?>",
      "url": "https://uho.ac.id"
    },
    "knowsAbout": <?= json_encode($skillNames, JSON_UNESCAPED_UNICODE) ?>,
    "memberOf": {
      "@type": "Organization",
      "name": "<?= e($orgName) ?>"
    }
  }
  </script>
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
    <a href="profil.php"       class="nav-item active">
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
      <div class="page-eyebrow" style="font-size:12px;color:var(--text-secondary);margin-bottom:4px;">Schema.org · Person</div>
      <h1 class="section-title" style="font-size:22px;">👤 Profil Saya</h1>
      <p class="page-subtitle">Informasi identitas diri yang direpresentasikan secara semantik menggunakan Schema.org Person.</p>
    </div>

    <!-- ── Row 1: Hero Profil ── -->
    <div class="card" style="margin-bottom:20px; animation:fade-up .5s ease .05s both;">
      <div class="card-body">
        <div class="profile-hero">
          <div class="profile-avatar-wrap">
            <img
              src="<?= e($fotoPath) ?>"
              alt="Foto Profil"
              class="profile-avatar"
              onerror="this.src='<?= $fotoFallback ?>'">
            <div class="profile-status"></div>
          </div>
          <div class="profile-info">
            <p class="profile-greeting">Halo! Saya</p>
            <h1 class="profile-name"><?= e($profil['nama'] ?? '-') ?></h1>
            <span class="profile-badge">
              🎓 <?= e($profil['prodi'] ?? '') ?> | Semantic Web Enthusiast
            </span>
            <p class="profile-bio"><?= e($profil['bio'] ?? '') ?></p>
            <div class="profile-social">
              <?php if (!empty($profil['email'])): ?>
              <a href="mailto:<?= e($profil['email']) ?>" class="social-btn" title="Email">✉️</a>
              <?php endif; ?>
              <?php if (!empty($profil['linkedin'])): ?>
              <a href="<?= e($profil['linkedin']) ?>" class="social-btn" title="LinkedIn" target="_blank">in</a>
              <?php endif; ?>
              <?php if (!empty($profil['github'])): ?>
              <a href="<?= e($profil['github']) ?>" class="social-btn" title="GitHub" target="_blank">⌥</a>
              <?php endif; ?>
              <?php if (!empty($profil['website'])): ?>
              <a href="<?= e($profil['website']) ?>" class="social-btn" title="Website" target="_blank">🔗</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Row 2: Data Identitas + JSON-LD ── -->
    <div class="grid-2" style="margin-bottom:20px;">

      <!-- Data Identitas -->
      <div class="card" style="animation:fade-up .5s ease .1s both;">
        <div class="card-body">
          <div class="section-header">
            <div class="section-icon">🪪</div>
            <div><div class="section-title">Data Identitas</div></div>
          </div>
          <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
            <tr style="border-bottom:1px solid var(--border-light);">
              <td style="padding:10px 0;color:var(--text-secondary);width:140px;">Nama Lengkap</td>
              <td style="padding:10px 0;font-weight:600;"><?= e($profil['nama'] ?? '-') ?></td>
            </tr>
            <tr style="border-bottom:1px solid var(--border-light);">
              <td style="padding:10px 0;color:var(--text-secondary);">NIM</td>
              <td style="padding:10px 0;font-weight:600;"><?= e($profil['nim'] ?? '-') ?></td>
            </tr>
            <tr style="border-bottom:1px solid var(--border-light);">
              <td style="padding:10px 0;color:var(--text-secondary);">Program Studi</td>
              <td style="padding:10px 0;"><?= e($profil['prodi'] ?? '-') ?></td>
            </tr>
            <tr style="border-bottom:1px solid var(--border-light);">
              <td style="padding:10px 0;color:var(--text-secondary);">Fakultas</td>
              <td style="padding:10px 0;"><?= e($profil['fakultas'] ?? '-') ?></td>
            </tr>
            <tr style="border-bottom:1px solid var(--border-light);">
              <td style="padding:10px 0;color:var(--text-secondary);">Universitas</td>
              <td style="padding:10px 0;"><?= e($profil['universitas'] ?? '-') ?></td>
            </tr>
            <tr style="border-bottom:1px solid var(--border-light);">
              <td style="padding:10px 0;color:var(--text-secondary);">Email</td>
              <td style="padding:10px 0;">
                <?php if (!empty($profil['email'])): ?>
                <a href="mailto:<?= e($profil['email']) ?>" style="color:var(--primary);"><?= e($profil['email']) ?></a>
                <?php else: ?>-<?php endif; ?>
              </td>
            </tr>
            <tr style="border-bottom:1px solid var(--border-light);">
              <td style="padding:10px 0;color:var(--text-secondary);">Telepon</td>
              <td style="padding:10px 0;"><?= e($profil['telepon'] ?? '-') ?></td>
            </tr>
            <tr style="border-bottom:1px solid var(--border-light);">
              <td style="padding:10px 0;color:var(--text-secondary);">Alamat</td>
              <td style="padding:10px 0;"><?= e($profil['alamat'] ?? '-') ?></td>
            </tr>
            <tr>
              <td style="padding:10px 0;color:var(--text-secondary);">GitHub</td>
              <td style="padding:10px 0;">
                <?php if (!empty($profil['github'])): ?>
                <a href="<?= e($profil['github']) ?>" target="_blank" style="color:var(--primary);"><?= e($profil['github']) ?></a>
                <?php else: ?>-<?php endif; ?>
              </td>
            </tr>
          </table>
        </div>
      </div>

      <!-- JSON-LD Person -->
      <div class="card" style="animation:fade-up .5s ease .15s both;">
        <div class="card-body">
          <div class="section-header">
            <div class="section-icon">💾</div>
            <div><div class="section-title">JSON-LD: Person</div></div>
          </div>
          <p style="font-size:12px;color:var(--text-secondary);margin-bottom:10px;">Markup semantik untuk halaman ini</p>
          <div class="code-block" style="font-size:12px;background:var(--bg-code,#f8fafc);border-radius:8px;padding:14px;overflow-x:auto;line-height:1.8;">
<span style="color:#7c3aed;">"@type"</span>: <span style="color:#16a34a;">"Person"</span>,<br>
<span style="color:#7c3aed;">"name"</span>: <span style="color:#16a34a;">"<?= e($profil['nama'] ?? '') ?>"</span>,<br>
<span style="color:#7c3aed;">"identifier"</span>: <span style="color:#16a34a;">"<?= e($profil['nim'] ?? '') ?>"</span>,<br>
<span style="color:#7c3aed;">"email"</span>: <span style="color:#16a34a;">"<?= e($profil['email'] ?? '') ?>"</span>,<br>
<span style="color:#7c3aed;">"telephone"</span>: <span style="color:#16a34a;">"<?= e($profil['telepon'] ?? '') ?>"</span>,<br>
<span style="color:#7c3aed;">"address"</span>: {<br>
&nbsp;&nbsp;<span style="color:#7c3aed;">"@type"</span>: <span style="color:#16a34a;">"PostalAddress"</span>,<br>
&nbsp;&nbsp;<span style="color:#7c3aed;">"addressLocality"</span>: <span style="color:#16a34a;">"<?= e($profil['alamat'] ?? '') ?>"</span>,<br>
&nbsp;&nbsp;<span style="color:#7c3aed;">"addressCountry"</span>: <span style="color:#16a34a;">"ID"</span><br>
},<br>
<span style="color:#7c3aed;">"alumniOf"</span>: {<br>
&nbsp;&nbsp;<span style="color:#7c3aed;">"@type"</span>: <span style="color:#16a34a;">"CollegeOrUniversity"</span>,<br>
&nbsp;&nbsp;<span style="color:#7c3aed;">"name"</span>: <span style="color:#16a34a;">"<?= e($profil['universitas'] ?? '') ?>"</span><br>
},<br>
<span style="color:#7c3aed;">"sameAs"</span>: [<br>
&nbsp;&nbsp;<span style="color:#16a34a;">"<?= e($profil['github'] ?? '') ?>"</span><br>
]
          </div>
        </div>
      </div>

    </div>

  </main>
</div><!-- /.main-wrapper -->

<script>
// ── Sidebar toggle (mobile) ──────────────────────────
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
</script>

</body>
</html>