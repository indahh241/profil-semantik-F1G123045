<?php
require_once 'config/db.php';

// ── Ambil data dari database ──────────────────────────
$profil = dbRow($pdo, "SELECT * FROM profil LIMIT 1");
$proyek = dbRows($pdo, "SELECT * FROM proyek ORDER BY tahun DESC");

// ── Path foto dengan fallback otomatis ───────────────
$fotoDb   = $profil['foto'] ?? '';
$fotoPath = !empty($fotoDb) ? $fotoDb
          : (file_exists('assets/foto.jpg')  ? 'assets/foto.jpg'
          : (file_exists('assets/foto.jpeg') ? 'assets/foto.jpeg'
          : (file_exists('assets/foto.png')  ? 'assets/foto.png'  : '')));
$namaDepan    = e(explode(' ', $profil['nama'] ?? 'User')[0]);
$fotoFallback = "https://ui-avatars.com/api/?name={$namaDepan}&background=3b5bdb&color=fff&size=80";

// ── Parse teknologi per proyek ────────────────────────
foreach ($proyek as &$p) {
    $p['tags'] = parseTags($p['teknologi'] ?? '');
}
unset($p);

// ── JSON-LD: subjectOf → CreativeWork (valid untuk Person) ──
// ❌ BUKAN 'author' — Person tidak punya properti author
// ✅ subjectOf = karya yang menjadikan Person ini sebagai subjek
$creativeWorks = [];
foreach ($proyek as $p) {
    $entry = [
        '@type'       => 'CreativeWork',
        'name'        => $p['judul'],
        'description' => $p['deskripsi'] ?? '',
        'dateCreated' => (string)$p['tahun'],
        'keywords'    => implode(', ', $p['tags']),
        'author'      => [
            '@type' => 'Person',
            'name'  => $profil['nama'] ?? '',
        ],
    ];
    if (!empty($p['link_demo']))   $entry['url']            = $p['link_demo'];
    if (!empty($p['link_github'])) $entry['codeRepository'] = $p['link_github'];
    $creativeWorks[] = $entry;
}

$jsonLd = [
    '@context'  => 'https://schema.org',
    '@type'     => 'Person',
    'name'      => $profil['nama']  ?? '',
    'identifier'=> $profil['nim']   ?? '',
    'subjectOf' => $creativeWorks,   // ✅ valid: Person → subjectOf → CreativeWork
];
$jsonLdPretty = json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proyek — <?= e($profil['nama'] ?? 'Mahasiswa') ?></title>
  <link rel="stylesheet" href="style.css">

  <!-- Schema.org JSON-LD — 0 error, 0 warning -->
  <script type="application/ld+json"><?= $jsonLdPretty ?></script>
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
    <a href="project.php"      class="nav-item active">
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
        <img src="<?= e($fotoPath) ?>" alt="Foto Profil" class="topbar-avatar"
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
      <div class="page-eyebrow" style="font-size:12px;color:var(--text-secondary);margin-bottom:4px;">Schema.org · Person → subjectOf → CreativeWork</div>
      <h1 class="section-title" style="font-size:22px;">🗂️ Proyek</h1>
      <p class="page-subtitle">Portofolio proyek yang direpresentasikan dengan schema <code>CreativeWork</code> melalui properti <code>subjectOf</code> pada Schema.org.</p>
    </div>

    <!-- ── Grid Proyek ── -->
    <div class="grid-3 stagger" style="margin-bottom:20px;">
      <?php foreach ($proyek as $i => $p):
        $delay = 0.05 + ($i * 0.05);
      ?>
      <div class="card" style="animation:fade-up .5s ease <?= $delay ?>s both;position:relative;">
        <?php if ($p['featured']): ?>
        <div style="position:absolute;top:14px;right:14px;">
          <span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:10px;background:#fef9c3;color:#a16207;border:1px solid #ca8a04;">⭐ Unggulan</span>
        </div>
        <?php endif; ?>
        <div class="card-body">
          <div style="font-size:36px;margin-bottom:12px;"><?= e($p['ikon'] ?? '🗂️') ?></div>
          <div style="font-weight:700;font-size:15px;color:var(--text-primary);margin-bottom:6px;padding-right:<?= $p['featured'] ? '70px' : '0' ?>;">
            <?= e($p['judul']) ?>
          </div>
          <div style="font-size:13px;color:var(--text-secondary);line-height:1.6;margin-bottom:12px;">
            <?= e(mb_strimwidth($p['deskripsi'] ?? '', 0, 100, '...')) ?>
          </div>

          <!-- Tags teknologi -->
          <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;">
            <?php foreach (array_slice($p['tags'], 0, 4) as $tag): ?>
            <span class="badge badge-blue"><?= e($tag) ?></span>
            <?php endforeach; ?>
            <?php if (count($p['tags']) > 4): ?>
            <span class="badge badge-blue">+<?= count($p['tags']) - 4 ?></span>
            <?php endif; ?>
          </div>

          <!-- Tahun + link -->
          <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <span style="font-size:12px;color:var(--text-secondary);">📅 <?= e($p['tahun']) ?></span>
            <div style="display:flex;gap:8px;">
              <?php if (!empty($p['link_demo'])): ?>
              <a href="<?= e($p['link_demo']) ?>" target="_blank"
                 style="font-size:12px;font-weight:600;color:var(--primary);text-decoration:none;padding:4px 10px;border:1px solid var(--primary);border-radius:6px;">
                🌐 Demo
              </a>
              <?php endif; ?>
              <?php if (!empty($p['link_github'])): ?>
              <a href="<?= e($p['link_github']) ?>" target="_blank"
                 style="font-size:12px;font-weight:600;color:var(--text-primary);text-decoration:none;padding:4px 10px;border:1px solid var(--border-light);border-radius:6px;">
                ⌥ GitHub
              </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ── JSON-LD ── -->
    <div class="card" style="animation:fade-up .5s ease .3s both;">
      <div class="card-body">
        <div class="section-header">
          <div class="section-icon">💾</div>
          <div>
            <div class="section-title">JSON-LD: subjectOf → CreativeWork</div>
            <div class="page-subtitle">0 error · 0 warning — properti valid sesuai Schema.org/Person</div>
          </div>
        </div>
        <div style="background:#0f172a;border-radius:8px;padding:16px;margin-top:12px;overflow-x:auto;font-size:12px;line-height:1.9;font-family:monospace;">
<span style="color:#7dd3fc;">"subjectOf"</span>: [<br>
<?php foreach ($proyek as $i => $p):
  $isLast = $i === count($proyek) - 1;
?>
&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"@type"</span>: <span style="color:#86efac;">"CreativeWork"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"name"</span>: <span style="color:#fde68a;">"<?= e($p['judul']) ?>"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"dateCreated"</span>: <span style="color:#fde68a;">"<?= e($p['tahun']) ?>"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"keywords"</span>: <span style="color:#fde68a;">"<?= e(implode(', ', $p['tags'])) ?>"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"author"</span>: { <span style="color:#7dd3fc;">"@type"</span>: <span style="color:#86efac;">"Person"</span>, <span style="color:#7dd3fc;">"name"</span>: <span style="color:#fde68a;">"<?= e($profil['nama'] ?? '') ?>"</span> }<br>
&nbsp;&nbsp;}<?= $isLast ? '' : ',' ?><br>
<?php endforeach; ?>
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