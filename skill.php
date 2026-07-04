<?php
require_once 'config/db.php';

// ── Ambil data dari database ──────────────────────────
$profil = dbRow($pdo, "SELECT * FROM profil LIMIT 1");
$skills = dbRows($pdo, "SELECT * FROM skill ORDER BY urutan ASC");

// ── Path foto dengan fallback otomatis ───────────────
$fotoDb   = $profil['foto'] ?? '';
$fotoPath = !empty($fotoDb) ? $fotoDb
          : (file_exists('assets/foto.jpg')  ? 'assets/foto.jpg'
          : (file_exists('assets/foto.jpeg') ? 'assets/foto.jpeg'
          : (file_exists('assets/foto.png')  ? 'assets/foto.png'  : '')));
$namaDepan    = e(explode(' ', $profil['nama'] ?? 'User')[0]);
$fotoFallback = "https://ui-avatars.com/api/?name={$namaDepan}&background=3b5bdb&color=fff&size=80";

// ── Kelompokkan skill per kategori ────────────────────
$grouped = [];
foreach ($skills as $s) {
    $grouped[$s['kategori']][] = $s;
}

// ── Warna per kategori ────────────────────────────────
$katColor = [
    'Frontend' => ['bg'=>'#e8edff','color'=>'#3b5bdb','bar'=>'#3b5bdb'],
    'Backend'  => ['bg'=>'#dcfce7','color'=>'#16a34a','bar'=>'#16a34a'],
    'Database' => ['bg'=>'#ffedd5','color'=>'#ea580c','bar'=>'#ea580c'],
    'Tools'    => ['bg'=>'#ede9fe','color'=>'#7c3aed','bar'=>'#7c3aed'],
    'Lainnya'  => ['bg'=>'#f1f5f9','color'=>'#475569','bar'=>'#475569'],
];

// ── JSON-LD ───────────────────────────────────────────
$skillNames = array_column($skills, 'nama');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Skill — <?= e($profil['nama'] ?? 'Mahasiswa') ?></title>
  <link rel="stylesheet" href="style.css">

  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Person",
    "name": "<?= e($profil['nama'] ?? '') ?>",
    "identifier": "<?= e($profil['nim'] ?? '') ?>",
    "knowsAbout": <?= json_encode($skillNames, JSON_UNESCAPED_UNICODE) ?>
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
    <a href="profil.php"       class="nav-item">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      Profil
    </a>
    <a href="pendidikan.php"   class="nav-item">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/></svg>
      Riwayat Pendidikan
    </a>
    <a href="skill.php"        class="nav-item active">
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
      <div class="page-eyebrow" style="font-size:12px;color:var(--text-secondary);margin-bottom:4px;">Schema.org · knowsAbout</div>
      <h1 class="section-title" style="font-size:22px;">⚡ Skill & Kemampuan</h1>
      <p class="page-subtitle">Keterampilan teknis yang direpresentasikan dengan properti <code>knowsAbout</code> pada Schema.org.</p>
    </div>

    <!-- ── Row 1: Skill per Kategori ── -->
    <?php foreach ($grouped as $kategori => $items):
      $kc = $katColor[$kategori] ?? $katColor['Lainnya'];
    ?>
    <div class="card" style="margin-bottom:20px; animation:fade-up .5s ease both;">
      <div class="card-body">
        <div class="section-header" style="margin-bottom:16px;">
          <div class="section-icon" style="background:<?= $kc['bg'] ?>;color:<?= $kc['color'] ?>;">
            <?php
              $icons = ['Frontend'=>'🌐','Backend'=>'⚙️','Database'=>'🗄️','Tools'=>'🔧','Lainnya'=>'📦'];
              echo $icons[$kategori] ?? '📦';
            ?>
          </div>
          <div>
            <div class="section-title"><?= e($kategori) ?></div>
            <div class="page-subtitle"><?= count($items) ?> skill</div>
          </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:14px;">
          <?php foreach ($items as $s): ?>
          <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
              <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:18px;"><?= e($s['ikon'] ?? '') ?></span>
                <span style="font-weight:600;font-size:14px;color:var(--text-primary);"><?= e($s['nama']) ?></span>
              </div>
              <span style="font-size:13px;font-weight:700;color:<?= $kc['color'] ?>;"><?= e($s['level']) ?>%</span>
            </div>
            <div style="height:8px;background:var(--border-light);border-radius:99px;overflow:hidden;">
              <div class="skill-fill" data-width="<?= e($s['level']) ?>%"
                   style="height:100%;width:0%;background:<?= $kc['bar'] ?>;border-radius:99px;transition:width .8s ease;"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- ── Row 2: JSON-LD ── -->
    <div class="card" style="animation:fade-up .5s ease .2s both;">
      <div class="card-body">
        <div class="section-header">
          <div class="section-icon">💾</div>
          <div><div class="section-title">JSON-LD: knowsAbout</div></div>
        </div>
        <div style="background:var(--bg-code,#f8fafc);border-radius:8px;padding:16px;margin-top:12px;overflow-x:auto;font-size:12px;line-height:1.8;font-family:monospace;">
<span style="color:#7c3aed;">"@type"</span>: <span style="color:#16a34a;">"Person"</span>,<br>
<span style="color:#7c3aed;">"name"</span>: <span style="color:#16a34a;">"<?= e($profil['nama'] ?? '') ?>"</span>,<br>
<span style="color:#7c3aed;">"knowsAbout"</span>: [<br>
<?php foreach ($skills as $i => $s):
  $isLast = $i === count($skills) - 1;
?>
&nbsp;&nbsp;<span style="color:#16a34a;">"<?= e($s['nama']) ?>"</span><?= $isLast ? '' : ',' ?><br>
<?php endforeach; ?>
]
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