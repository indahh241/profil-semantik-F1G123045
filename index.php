<?php
require_once 'config/db.php';

// ── Gate: redirect ke welcome jika belum masuk lewat tombol ──
if (empty($_SESSION['entered'])) {
    redirect(APP_URL . '/welcome.php');
}

// ── Ambil data dari database ──────────────────────────
$profil      = dbRow($pdo,  "SELECT * FROM profil LIMIT 1");
$skills      = dbRows($pdo, "SELECT * FROM skill ORDER BY urutan ASC");
$pendidikan  = dbRows($pdo, "SELECT * FROM pendidikan ORDER BY urutan ASC");
$organisasi  = dbRows($pdo, "SELECT * FROM organisasi ORDER BY tahun_masuk DESC");
$proyek      = dbRows($pdo, "SELECT * FROM proyek WHERE featured = 1 ORDER BY tahun DESC LIMIT 3");

// ── Hitung stats ──────────────────────────────────────
$totalSkill = dbCount($pdo, 'skill');
$totalOrg   = dbCount($pdo, 'organisasi');
$totalProyek= dbCount($pdo, 'proyek');
$totalDidik = dbCount($pdo, 'pendidikan');

// ── Teknologi per proyek (string → array) ─────────────
foreach ($proyek as &$p) {
    $p['tags'] = parseTags($p['teknologi'] ?? '');
}
unset($p);

// ── Data stats untuk card ─────────────────────────────
$stats = [
  ['icon'=>'💻','color'=>'blue',  'label'=>'Total Skill',       'value'=>$totalSkill, 'desc'=>'Keterampilan'],
  ['icon'=>'👥','color'=>'purple','label'=>'Total Organisasi',   'value'=>$totalOrg,   'desc'=>'Pengalaman'],
  ['icon'=>'🗂️','color'=>'green', 'label'=>'Total Proyek',       'value'=>$totalProyek,'desc'=>'Proyek'],
  ['icon'=>'🎓','color'=>'orange','label'=>'Jenjang Pendidikan', 'value'=>$totalDidik, 'desc'=>'SD – S1'],
];

// ── JSON-LD: ambil skills dan organisasi untuk schema ──
$skillNames = array_column($skills, 'nama');
$orgName    = !empty($organisasi) ? $organisasi[0]['nama'] : 'HIMAKOM UHO';

// ── Path foto dengan fallback otomatis ───────────────
$fotoDb   = $profil['foto'] ?? '';
$fotoPath = !empty($fotoDb) ? $fotoDb
          : (file_exists('assets/foto.jpg')  ? 'assets/foto.jpg'
          : (file_exists('assets/foto.jpeg') ? 'assets/foto.jpeg'
          : (file_exists('assets/foto.png')  ? 'assets/foto.png'  : '')));
$namaDepan = e(explode(' ', $profil['nama'] ?? 'User')[0]);
$fotoFallback = "https://ui-avatars.com/api/?name={$namaDepan}&background=3b5bdb&color=fff&size=80";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Semantic Profile — <?= e($profil['nama'] ?? 'Mahasiswa') ?></title>
  <link rel="stylesheet" href="style.css">

  <!-- Schema.org JSON-LD (data dari database) -->
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
      "addressLocality": "<?= e($profil['alamat'] ?? '') ?>"
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
    <a href="index.php"        class="nav-item active">
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

    <!-- ── Row 1: Profile Hero + Stats ── -->
    <div class="grid-2" style="margin-bottom:20px;">

      <!-- Profile Hero Card -->
      <div class="card" style="animation:fade-up .5s ease .05s both;">
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

      <!-- Stat Cards -->
      <div class="grid-2 stagger">
        <?php foreach ($stats as $s): ?>
        <div class="stat-card">
          <div class="stat-icon <?= $s['color'] ?>"><?= $s['icon'] ?></div>
          <div class="stat-label"><?= e($s['label']) ?></div>
          <div class="stat-value" data-target="<?= $s['value'] ?>">0</div>
          <div class="stat-desc"><?= e($s['desc']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>

    <!-- ── Row 2: Semantic Web Info + Relation Diagram ── -->
    <div class="grid-2" style="margin-bottom:20px;">

      <!-- Semantic Web Card -->
      <div class="card" style="animation:fade-up .5s ease .2s both;">
        <div class="card-body">
          <div class="section-header">
            <div class="section-icon">🔖</div>
            <div><div class="section-title">Apa itu Semantic Web?</div></div>
          </div>
          <p class="sw-description">
            <strong>Semantic Web</strong> adalah perluasan dari World Wide Web yang memberikan makna (semantik)
            pada data sehingga dapat dibaca dan dipahami oleh mesin. Dengan Semantic Web, informasi
            dapat dihubungkan secara logis dan membentuk jaringan data yang terstruktur.
          </p>
          <div class="sw-features">
            <div class="sw-feature">
              <div class="sw-feature-icon">🔗</div>
              <div class="sw-feature-title">Struktur Data</div>
              <div class="sw-feature-desc">Data terstruktur dan bermakna</div>
            </div>
            <div class="sw-feature">
              <div class="sw-feature-icon">🌐</div>
              <div class="sw-feature-title">Keterhubungan</div>
              <div class="sw-feature-desc">Data saling terhubung</div>
            </div>
            <div class="sw-feature">
              <div class="sw-feature-icon">♻️</div>
              <div class="sw-feature-title">Reusability</div>
              <div class="sw-feature-desc">Data dapat digunakan kembali</div>
            </div>
          </div>
          <div class="sw-quote">
            "The Semantic Web isn't a separate web, but an extension of the current web in which
            information is given well-defined meaning."
            <footer>– Tim Berners-Lee</footer>
          </div>
        </div>
      </div>

      <!-- Relasi Diagram (data dari DB) -->
      <div class="card" style="animation:fade-up .5s ease .25s both;">
        <div class="card-body">
          <div class="section-header">
            <div class="section-icon">🔀</div>
            <div><div class="section-title">Diagram Relasi Semantik</div></div>
          </div>

          <svg viewBox="0 0 420 240" width="100%" style="margin-top:8px;">
            <defs>
              <marker id="arr" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="5" markerHeight="5" orient="auto-start-reverse">
                <path d="M2 1L8 5L2 9" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round"/>
              </marker>
            </defs>

            <!-- Center -->
            <rect x="145" y="97" width="130" height="36" rx="18" fill="#3b5bdb"/>
            <text x="210" y="115" text-anchor="middle" dominant-baseline="central"
                  font-family="Inter,sans-serif" font-size="10.5" font-weight="600" fill="#fff">
              👤 <?= e(explode(' ', $profil['nama'] ?? 'Mahasiswa')[0]) ?>
            </text>

            <!-- Universitas (atas) -->
            <rect x="130" y="14" width="160" height="34" rx="17" fill="#dcfce7" stroke="#16a34a" stroke-width="1.2"/>
            <text x="210" y="31" text-anchor="middle" dominant-baseline="central"
                  font-family="Inter,sans-serif" font-size="9.5" font-weight="600" fill="#16a34a">
              🏫 <?= e($profil['universitas'] ?? 'UHO') ?>
            </text>
            <line x1="210" y1="48" x2="210" y2="97" stroke="#94a3b8" stroke-width="1" stroke-dasharray="4,3" marker-end="url(#arr)"/>
            <text x="218" y="76" font-family="Inter,sans-serif" font-size="9" fill="#64748b">kuliahDi</text>

            <!-- Skill pertama (kiri) -->
            <?php $s1 = $skills[0] ?? ['nama'=>'PHP','ikon'=>'💻']; ?>
            <rect x="18" y="97" width="90" height="34" rx="17" fill="#e8edff" stroke="#3b5bdb" stroke-width="1.2"/>
            <text x="63" y="114" text-anchor="middle" dominant-baseline="central"
                  font-family="Inter,sans-serif" font-size="10" font-weight="600" fill="#3b5bdb">
              <?= e($s1['ikon']) ?> <?= e($s1['nama']) ?>
            </text>
            <line x1="108" y1="114" x2="145" y2="115" stroke="#94a3b8" stroke-width="1" stroke-dasharray="4,3" marker-end="url(#arr)"/>
            <text x="109" y="108" font-family="Inter,sans-serif" font-size="9" fill="#64748b">memiliki</text>

            <!-- Organisasi pertama (kanan) -->
            <?php $o1 = $organisasi[0] ?? ['nama'=>'HIMAKOM','ikon'=>'👥']; ?>
            <rect x="312" y="97" width="95" height="34" rx="17" fill="#ffedd5" stroke="#ea580c" stroke-width="1.2"/>
            <text x="359" y="114" text-anchor="middle" dominant-baseline="central"
                  font-family="Inter,sans-serif" font-size="9.5" font-weight="600" fill="#ea580c">
              <?= e($o1['ikon']) ?> <?= e(mb_strimwidth($o1['nama'], 0, 12, '..')) ?>
            </text>
            <line x1="275" y1="115" x2="312" y2="115" stroke="#94a3b8" stroke-width="1" stroke-dasharray="4,3" marker-end="url(#arr)"/>
            <text x="276" y="109" font-family="Inter,sans-serif" font-size="9" fill="#64748b">mengikuti</text>

            <!-- Proyek/Mempelajari (bawah) -->
            <rect x="140" y="192" width="140" height="34" rx="17" fill="#ede9fe" stroke="#7c3aed" stroke-width="1.2"/>
            <text x="210" y="209" text-anchor="middle" dominant-baseline="central"
                  font-family="Inter,sans-serif" font-size="10" font-weight="600" fill="#7c3aed">
              🌐 Semantic Web
            </text>
            <line x1="210" y1="133" x2="210" y2="192" stroke="#94a3b8" stroke-width="1" stroke-dasharray="4,3" marker-end="url(#arr)"/>
            <text x="215" y="168" font-family="Inter,sans-serif" font-size="9" fill="#64748b">mempelajari</text>
          </svg>
        </div>
      </div>
    </div>

    <!-- ── Row 3: Proyek Featured ── -->
    <?php if (!empty($proyek)): ?>
    <div style="margin-bottom:20px; animation:fade-up .5s ease .3s both;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <div>
          <div class="section-title">Proyek Unggulan</div>
          <div class="page-subtitle">Proyek terbaru yang ditampilkan</div>
        </div>
        <a href="project.php" class="btn btn-outline" style="font-size:12px;padding:7px 16px;">Lihat Semua →</a>
      </div>
      <div class="grid-3 stagger">
        <?php foreach ($proyek as $p): ?>
        <div class="project-card">
          <div class="project-card-icon"><?= e($p['ikon'] ?? '🗂️') ?></div>
          <div class="project-card-title"><?= e($p['judul']) ?></div>
          <div class="project-card-desc"><?= e(mb_strimwidth($p['deskripsi'] ?? '', 0, 90, '...')) ?></div>
          <div class="project-tags">
            <?php foreach (array_slice($p['tags'], 0, 3) as $tag): ?>
            <span class="badge badge-blue"><?= e($tag) ?></span>
            <?php endforeach; ?>
            <?php if (count($p['tags']) > 3): ?>
            <span class="badge badge-blue">+<?= count($p['tags']) - 3 ?></span>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- ── Row 4: Tentang Website ── -->
    <div class="card" style="animation:fade-up .5s ease .35s both;">
      <div class="card-body" style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
        <div class="section-icon" style="width:48px;height:48px;font-size:22px;flex-shrink:0;">🛡️</div>
        <div style="flex:1;min-width:200px;">
          <div class="section-title" style="margin-bottom:8px;">Tentang Website</div>
          <p style="font-size:13.5px;color:var(--text-secondary);line-height:1.7;">
            Website ini merupakan implementasi dari konsep Semantic Web untuk mempresentasikan profil
            mahasiswa secara terstruktur dan bermakna. Dengan Semantic Web, data diri, riwayat pendidikan,
            skill, organisasi, proyek, hingga relasi antar entitas dapat saling terhubung dan dipahami
            oleh mesin maupun manusia dengan lebih baik.
          </p>
          <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
            <span class="badge badge-blue">📋 NIM: <?= e($profil['nim'] ?? '-') ?></span>
            <span class="badge badge-green">🏫 <?= e($profil['universitas'] ?? '-') ?></span>
            <span class="badge badge-purple">📚 <?= e($profil['prodi'] ?? '-') ?></span>
          </div>
        </div>
        <div style="font-size:64px;opacity:.12;flex-shrink:0;">🖥️</div>
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

// ── Counter animation ────────────────────────────────
const counters = document.querySelectorAll('.stat-value[data-target]');
const counterObs = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (!entry.isIntersecting) return;
    const el     = entry.target;
    const target = parseInt(el.dataset.target);
    let current  = 0;
    const step   = Math.max(1, Math.ceil(target / 25));
    const timer  = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = current;
      if (current >= target) clearInterval(timer);
    }, 40);
    counterObs.unobserve(el);
  });
}, { threshold: 0.5 });

counters.forEach(el => counterObs.observe(el));

// ── Skill bar animate on scroll ──────────────────────
document.querySelectorAll('.skill-fill').forEach(bar => {
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        bar.style.width = bar.dataset.width || '0%';
        obs.unobserve(bar);
      }
    });
  }, { threshold: 0.3 });
  obs.observe(bar);
});
</script>

</body>
</html>