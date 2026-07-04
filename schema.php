<?php
require_once 'config/db.php';

// ── Ambil data dari database ──────────────────────────
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

// ── alumniOf — tanpa startDate/endDate (tidak valid di Organization) ──
$alumniOf = [];
foreach ($pendidikan as $d) {
    $type  = in_array($d['jenjang'], ['S1','S2','D3']) ? 'CollegeOrUniversity' : 'EducationalOrganization';
    $entry = ['@type' => $type, 'name' => $d['institusi']];
    if (!empty($d['jurusan'])) {
        $entry['department'] = ['@type' => 'Organization', 'name' => $d['jurusan']];
    }
    $alumniOf[] = $entry;
}

// ── memberOf ──────────────────────────────────────────
$memberOf = [];
foreach ($organisasi as $o) {
    $memberOf[] = ['@type' => 'Organization', 'name' => $o['nama'], 'description' => $o['deskripsi'] ?? ''];
}

// ── knowsAbout — daftar nama skill ───────────────────
$skillNames = array_column($skills, 'nama');

// ── subjectOf → CreativeWork (valid untuk Person) ────
$creativeWorks = [];
foreach ($proyek as $p) {
    $tags  = parseTags($p['teknologi'] ?? '');
    $entry = [
        '@type'       => 'CreativeWork',
        'name'        => $p['judul'],
        'description' => $p['deskripsi'] ?? '',
        'dateCreated' => (string)$p['tahun'],
        'keywords'    => implode(', ', $tags),
        'author'      => ['@type' => 'Person', 'name' => $profil['nama'] ?? ''],
    ];
    if (!empty($p['link_demo']))   $entry['url']            = $p['link_demo'];
    if (!empty($p['link_github'])) $entry['codeRepository'] = $p['link_github'];
    $creativeWorks[] = $entry;
}

// ── $jsonLd final — 0 error, 0 warning ───────────────
$jsonLd = [
    '@context'   => 'https://schema.org',
    '@type'      => 'Person',
    'name'       => $profil['nama']    ?? '',
    'identifier' => $profil['nim']     ?? '',
    'email'      => $profil['email']   ?? '',
    'telephone'  => $profil['telepon'] ?? '',
    'image'      => $profil['foto']    ?? '',
    'url'        => defined('APP_URL') ? APP_URL : '',
    'address'    => [
        '@type'           => 'PostalAddress',
        'addressLocality' => $profil['alamat'] ?? '',
        'addressCountry'  => 'ID',
    ],
    'alumniOf'   => $alumniOf,
    'memberOf'   => $memberOf,
    'knowsAbout' => $skillNames,
    'subjectOf'  => $creativeWorks,
    'sameAs'     => array_values(array_filter([
        $profil['github']   ?? '',
        $profil['linkedin'] ?? '',
    ])),
];

$jsonLdPretty = json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// ── Properti Schema.org yang dipakai ─────────────────
$properties = [
    ['prop'=>'@type',      'type'=>'Person',                    'desc'=>'Tipe entitas utama',                        'color'=>'blue'],
    ['prop'=>'name',       'type'=>'Text',                      'desc'=>'Nama lengkap individu',                     'color'=>'blue'],
    ['prop'=>'identifier', 'type'=>'Text',                      'desc'=>'Nomor Induk Mahasiswa (NIM)',                'color'=>'blue'],
    ['prop'=>'email',      'type'=>'Text',                      'desc'=>'Alamat email kontak',                       'color'=>'blue'],
    ['prop'=>'telephone',  'type'=>'Text',                      'desc'=>'Nomor telepon',                             'color'=>'blue'],
    ['prop'=>'image',      'type'=>'URL',                       'desc'=>'Foto profil individu',                      'color'=>'blue'],
    ['prop'=>'address',    'type'=>'PostalAddress',              'desc'=>'Alamat tempat tinggal',                     'color'=>'green'],
    ['prop'=>'alumniOf',   'type'=>'EducationalOrganization',   'desc'=>'Institusi pendidikan yang pernah diikuti',  'color'=>'green'],
    ['prop'=>'memberOf',   'type'=>'Organization',              'desc'=>'Organisasi yang diikuti',                   'color'=>'orange'],
    ['prop'=>'knowsAbout', 'type'=>'Text / Thing',              'desc'=>'Bidang keahlian atau topik yang dikuasai',  'color'=>'sky'],
    ['prop'=>'subjectOf',  'type'=>'CreativeWork',              'desc'=>'Proyek sebagai karya kreatif dengan keywords', 'color'=>'purple'],
    ['prop'=>'sameAs',     'type'=>'URL',                       'desc'=>'Tautan ke profil di platform lain',         'color'=>'purple'],
];

$colorMap = [
    'blue'   => ['bg'=>'#e8edff','color'=>'#3b5bdb','border'=>'#c5d0ff'],
    'green'  => ['bg'=>'#dcfce7','color'=>'#16a34a','border'=>'#bbf7d0'],
    'orange' => ['bg'=>'#ffedd5','color'=>'#ea580c','border'=>'#fed7aa'],
    'sky'    => ['bg'=>'#e0f2fe','color'=>'#0369a1','border'=>'#bae6fd'],
    'purple' => ['bg'=>'#ede9fe','color'=>'#7c3aed','border'=>'#ddd6fe'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Schema.org — <?= e($profil['nama'] ?? 'Mahasiswa') ?></title>
  <link rel="stylesheet" href="style.css">

  <!-- JSON-LD lengkap untuk halaman ini -->
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
    <a href="project.php"      class="nav-item">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
      Proyek
    </a>
    <a href="semantic.php"     class="nav-item">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/></svg>
      Relasi Semantik
    </a>
    <a href="schema.php"       class="nav-item active">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
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
      <div class="page-eyebrow" style="font-size:12px;color:var(--text-secondary);margin-bottom:4px;">Structured Data · JSON-LD · Schema.org/Person</div>
      <h1 class="section-title" style="font-size:22px;">🧩 Schema.org</h1>
      <p class="page-subtitle">Markup terstruktur lengkap yang membuat data profil ini dapat dibaca dan dipahami oleh mesin pencari.</p>
    </div>

    <!-- ── Row 1: Stat card ringkasan ── -->
    <div class="grid-2 stagger" style="margin-bottom:20px;">
      <div class="stat-card">
        <div class="stat-icon blue">🧩</div>
        <div class="stat-label">Total Properti</div>
        <div class="stat-value" data-target="<?= count($properties) ?>">0</div>
        <div class="stat-desc">Schema.org</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">🎓</div>
        <div class="stat-label">alumniOf</div>
        <div class="stat-value" data-target="<?= count($pendidikan) ?>">0</div>
        <div class="stat-desc">Entitas Pendidikan</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon purple">🗂️</div>
        <div class="stat-label">CreativeWork</div>
        <div class="stat-value" data-target="<?= count($proyek) ?>">0</div>
        <div class="stat-desc">Entitas Proyek</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange">⚡</div>
        <div class="stat-label">knowsAbout</div>
        <div class="stat-value" data-target="<?= count($skills) ?>">0</div>
        <div class="stat-desc">Entitas Skill</div>
      </div>
    </div>

    <!-- ── Row 2: Properti + JSON-LD Data Utama ── -->
    <div class="grid-2" style="margin-bottom:20px;">

      <!-- Daftar Properti -->
      <div class="card" style="animation:fade-up .5s ease .1s both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:16px;">
            <div class="section-icon">📋</div>
            <div>
              <div class="section-title">Properti yang Digunakan</div>
              <div class="page-subtitle">Berdasarkan Schema.org/Person</div>
            </div>
          </div>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <?php foreach ($properties as $p):
              $c = $colorMap[$p['color']];
            ?>
            <div style="display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:8px;background:<?= $c['bg'] ?>;border:1px solid <?= $c['border'] ?>;">
              <code style="font-size:12px;font-weight:700;color:<?= $c['color'] ?>;min-width:110px;flex-shrink:0;"><?= e($p['prop']) ?></code>
              <span style="font-size:11px;color:<?= $c['color'] ?>;opacity:.8;min-width:90px;flex-shrink:0;"><?= e($p['type']) ?></span>
              <span style="font-size:12px;color:var(--text-secondary);"><?= e($p['desc']) ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- JSON-LD Person section -->
      <div class="card" style="animation:fade-up .5s ease .15s both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:12px;">
            <div class="section-icon">👤</div>
            <div>
              <div class="section-title">Person — Data Utama</div>
              <div class="page-subtitle">Identitas inti dari profil ini</div>
            </div>
          </div>
          <div style="background:#0f172a;border-radius:8px;padding:16px;font-size:12px;line-height:1.9;font-family:monospace;overflow-x:auto;">
<span style="color:#94a3b8;">{</span><br>
&nbsp;&nbsp;<span style="color:#7dd3fc;">"@context"</span>: <span style="color:#86efac;">"https://schema.org"</span>,<br>
&nbsp;&nbsp;<span style="color:#7dd3fc;">"@type"</span>: <span style="color:#86efac;">"Person"</span>,<br>
&nbsp;&nbsp;<span style="color:#7dd3fc;">"name"</span>: <span style="color:#fde68a;">"<?= e($profil['nama'] ?? '') ?>"</span>,<br>
&nbsp;&nbsp;<span style="color:#7dd3fc;">"identifier"</span>: <span style="color:#fde68a;">"<?= e($profil['nim'] ?? '') ?>"</span>,<br>
&nbsp;&nbsp;<span style="color:#7dd3fc;">"email"</span>: <span style="color:#fde68a;">"<?= e($profil['email'] ?? '') ?>"</span>,<br>
&nbsp;&nbsp;<span style="color:#7dd3fc;">"telephone"</span>: <span style="color:#fde68a;">"<?= e($profil['telepon'] ?? '') ?>"</span>,<br>
&nbsp;&nbsp;<span style="color:#7dd3fc;">"address"</span>: {<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"@type"</span>: <span style="color:#86efac;">"PostalAddress"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"addressLocality"</span>: <span style="color:#fde68a;">"<?= e($profil['alamat'] ?? '') ?>"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"addressCountry"</span>: <span style="color:#fde68a;">"ID"</span><br>
&nbsp;&nbsp;},<br>
&nbsp;&nbsp;<span style="color:#7dd3fc;">"sameAs"</span>: [<br>
<?php foreach (array_values(array_filter([$profil['github']??'', $profil['linkedin']??''])) as $i => $sa): ?>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#fde68a;">"<?= e($sa) ?>"</span><?= $i < 1 ? ',' : '' ?><br>
<?php endforeach; ?>
&nbsp;&nbsp;]<br>
<span style="color:#94a3b8;">}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Row 3: alumniOf + knowsAbout ── -->
    <div class="grid-2" style="margin-bottom:20px;">

      <!-- alumniOf -->
      <div class="card" style="animation:fade-up .5s ease .2s both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:12px;">
            <div class="section-icon" style="background:#dcfce7;color:#16a34a;">🎓</div>
            <div>
              <div class="section-title">alumniOf</div>
              <div class="page-subtitle">EducationalOrganization · CollegeOrUniversity</div>
            </div>
          </div>
          <div style="background:#0f172a;border-radius:8px;padding:14px;font-size:11.5px;line-height:1.9;font-family:monospace;overflow-x:auto;">
<span style="color:#7dd3fc;">"alumniOf"</span>: [<br>
<?php foreach ($pendidikan as $i => $d):
  $type   = in_array($d['jenjang'], ['S1','S2','D3']) ? 'CollegeOrUniversity' : 'EducationalOrganization';
  $isLast = $i === count($pendidikan)-1;
?>
&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"@type"</span>: <span style="color:#86efac;">"<?= $type ?>"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"name"</span>: <span style="color:#fde68a;">"<?= e($d['institusi']) ?>"</span><?= !empty($d['jurusan']) ? ',' : '' ?><br>
<?php if (!empty($d['jurusan'])): ?>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"department"</span>: {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"@type"</span>: <span style="color:#86efac;">"Organization"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"name"</span>: <span style="color:#fde68a;">"<?= e($d['jurusan']) ?>"</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<?php endif; ?>
&nbsp;&nbsp;}<?= $isLast ? '' : ',' ?><br>
<?php endforeach; ?>
]
          </div>
        </div>
      </div>

      <!-- knowsAbout -->
      <div class="card" style="animation:fade-up .5s ease .25s both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:12px;">
            <div class="section-icon" style="background:#e0f2fe;color:#0369a1;">⚡</div>
            <div>
              <div class="section-title">knowsAbout</div>
              <div class="page-subtitle">Text / Thing — daftar keahlian</div>
            </div>
          </div>
          <div style="background:#0f172a;border-radius:8px;padding:14px;font-size:11.5px;line-height:1.9;font-family:monospace;overflow-x:auto;">
<span style="color:#7dd3fc;">"knowsAbout"</span>: [<br>
<?php foreach ($skills as $i => $s):
  $isLast = $i === count($skills)-1;
?>
&nbsp;&nbsp;<span style="color:#fde68a;">"<?= e($s['ikon']??'') ?> <?= e($s['nama']) ?>"</span><?= $isLast ? '' : ',' ?><br>
<?php endforeach; ?>
]
          </div>
          <!-- Badge visual -->
          <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:14px;">
            <?php foreach ($skills as $s): ?>
            <span style="font-size:12px;padding:3px 10px;border-radius:99px;background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;">
              <?= e($s['ikon']??'') ?> <?= e($s['nama']) ?>
            </span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Row 4: memberOf + subjectOf (preview) ── -->
    <div class="grid-2" style="margin-bottom:20px;">

      <!-- memberOf -->
      <div class="card" style="animation:fade-up .5s ease .3s both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:12px;">
            <div class="section-icon" style="background:#ffedd5;color:#ea580c;">🏛️</div>
            <div>
              <div class="section-title">memberOf</div>
              <div class="page-subtitle">Organization</div>
            </div>
          </div>
          <div style="background:#0f172a;border-radius:8px;padding:14px;font-size:11.5px;line-height:1.9;font-family:monospace;overflow-x:auto;">
<span style="color:#7dd3fc;">"memberOf"</span>: [<br>
<?php foreach ($organisasi as $i => $o):
  $isLast = $i === count($organisasi)-1;
?>
&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"@type"</span>: <span style="color:#86efac;">"Organization"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"name"</span>: <span style="color:#fde68a;">"<?= e($o['nama']) ?>"</span><br>
&nbsp;&nbsp;}<?= $isLast ? '' : ',' ?><br>
<?php endforeach; ?>
]
          </div>
        </div>
      </div>

      <!-- subjectOf preview -->
      <div class="card" style="animation:fade-up .5s ease .35s both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:12px;">
            <div class="section-icon" style="background:#ede9fe;color:#7c3aed;">🎨</div>
            <div>
              <div class="section-title">subjectOf</div>
              <div class="page-subtitle">CreativeWork — ringkasan proyek</div>
            </div>
          </div>
          <div style="background:#0f172a;border-radius:8px;padding:14px;font-size:11.5px;line-height:1.9;font-family:monospace;overflow-x:auto;">
<span style="color:#7dd3fc;">"subjectOf"</span>: [<br>
<?php foreach ($proyek as $i => $p):
  $tags   = parseTags($p['teknologi'] ?? '');
  $isLast = $i === count($proyek)-1;
?>
&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"@type"</span>: <span style="color:#86efac;">"CreativeWork"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"name"</span>: <span style="color:#fde68a;">"<?= e($p['judul']) ?>"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"dateCreated"</span>: <span style="color:#fde68a;">"<?= e($p['tahun']) ?>"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"keywords"</span>: <span style="color:#fde68a;">"<?= e(implode(', ', $tags)) ?>"</span><br>
&nbsp;&nbsp;}<?= $isLast ? '' : ',' ?><br>
<?php endforeach; ?>
]
          </div>
        </div>
      </div>
    </div>

    <!-- ── Row 5: CreativeWork cards ── -->
    <div class="card" style="animation:fade-up .5s ease .4s both;margin-bottom:20px;">
      <div class="card-body">
        <div class="section-header" style="margin-bottom:16px;">
          <div class="section-icon" style="background:#ede9fe;color:#7c3aed;">🗂️</div>
          <div>
            <div class="section-title">subjectOf → CreativeWork</div>
            <div class="page-subtitle">Setiap proyek direpresentasikan sebagai CreativeWork dengan keywords teknologi</div>
          </div>
        </div>
        <div class="grid-3" style="gap:10px;">
          <?php foreach ($proyek as $p):
            $tags = parseTags($p['teknologi'] ?? '');
          ?>
          <div style="padding:12px;border-radius:8px;background:#ede9fe;border:1px solid #ddd6fe;">
            <div style="font-size:22px;margin-bottom:6px;"><?= e($p['ikon']??'🗂️') ?></div>
            <div style="font-weight:700;font-size:13px;color:#7c3aed;margin-bottom:4px;"><?= e($p['judul']) ?></div>
            <div style="font-size:11px;color:#5b21b6;margin-bottom:6px;">📅 <?= e($p['tahun']) ?></div>
            <div style="display:flex;flex-wrap:wrap;gap:4px;">
              <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
              <span style="font-size:10px;padding:1px 6px;border-radius:99px;background:#ddd6fe;color:#7c3aed;"><?= e($tag) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- ── Row 6: JSON-LD Lengkap (collapsible) ── -->
    <div class="card" style="animation:fade-up .5s ease .45s both;margin-bottom:20px;">
      <div class="card-body">
        <div class="section-header" style="margin-bottom:12px;">
          <div class="section-icon">💾</div>
          <div>
            <div class="section-title">JSON-LD Lengkap</div>
            <div class="page-subtitle">Seluruh markup terstruktur yang tertanam di halaman ini</div>
          </div>
          <button id="toggleBtn" onclick="toggleJson()"
            style="margin-left:auto;font-size:12px;padding:6px 14px;border-radius:6px;border:1px solid var(--border-light);background:transparent;cursor:pointer;color:var(--text-secondary);">
            Tampilkan ▼
          </button>
        </div>
        <div id="jsonFull" style="display:none;">
          <div style="position:relative;">
            <button onclick="copyJson()"
              style="position:absolute;top:10px;right:10px;font-size:11px;padding:4px 10px;border-radius:6px;border:1px solid #334155;background:#1e293b;color:#94a3b8;cursor:pointer;z-index:1;">
              📋 Salin
            </button>
            <pre id="jsonContent" style="background:#0f172a;border-radius:8px;padding:16px;font-size:11.5px;line-height:1.7;font-family:monospace;overflow-x:auto;color:#e2e8f0;margin:0;white-space:pre-wrap;word-break:break-word;"><?= htmlspecialchars($jsonLdPretty) ?></pre>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Row 7: Cara Kerja Schema.org ── -->
    <div class="card" style="animation:fade-up .5s ease .5s both;">
      <div class="card-body">
        <div class="section-header" style="margin-bottom:16px;">
          <div class="section-icon">📖</div>
          <div><div class="section-title">Bagaimana Schema.org Bekerja?</div></div>
        </div>
        <div style="display:flex;gap:16px;flex-wrap:wrap;">

          <div style="flex:1;min-width:200px;padding:14px;border-radius:8px;background:#e8edff;border:1px solid #c5d0ff;text-align:center;">
            <div style="font-size:28px;margin-bottom:8px;">✍️</div>
            <div style="font-weight:700;color:#3b5bdb;margin-bottom:6px;font-size:14px;">1. Markup</div>
            <p style="font-size:12.5px;color:#3730a3;line-height:1.6;margin:0;">Data profil ditandai dengan properti Schema.org menggunakan format JSON-LD yang tertanam di tag &lt;script&gt;.</p>
          </div>

          <div style="flex:1;min-width:200px;padding:14px;border-radius:8px;background:#dcfce7;border:1px solid #bbf7d0;text-align:center;">
            <div style="font-size:28px;margin-bottom:8px;">🤖</div>
            <div style="font-weight:700;color:#16a34a;margin-bottom:6px;font-size:14px;">2. Crawling</div>
            <p style="font-size:12.5px;color:#166534;line-height:1.6;margin:0;">Mesin pencari seperti Google membaca JSON-LD dan memahami makna di balik setiap data yang ada.</p>
          </div>

          <div style="flex:1;min-width:200px;padding:14px;border-radius:8px;background:#ffedd5;border:1px solid #fed7aa;text-align:center;">
            <div style="font-size:28px;margin-bottom:8px;">🔗</div>
            <div style="font-weight:700;color:#ea580c;margin-bottom:6px;font-size:14px;">3. Linking</div>
            <p style="font-size:12.5px;color:#9a3412;line-height:1.6;margin:0;">Data terhubung ke entitas lain dalam web (universitas, organisasi, proyek) membentuk knowledge graph.</p>
          </div>

          <div style="flex:1;min-width:200px;padding:14px;border-radius:8px;background:#ede9fe;border:1px solid #ddd6fe;text-align:center;">
            <div style="font-size:28px;margin-bottom:8px;">🎯</div>
            <div style="font-weight:700;color:#7c3aed;margin-bottom:6px;font-size:14px;">4. Rich Results</div>
            <p style="font-size:12.5px;color:#5b21b6;line-height:1.6;margin:0;">Google menampilkan informasi lebih kaya di hasil pencarian — nama, foto, link sosial, dan keahlian.</p>
          </div>

        </div>
      </div>
    </div>

  </main>
</div><!-- /.main-wrapper -->

<script>
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

// ── Toggle JSON-LD lengkap ────────────────────────────
function toggleJson() {
  const box  = document.getElementById('jsonFull');
  const btn  = document.getElementById('toggleBtn');
  const open = box.style.display === 'none';
  box.style.display = open ? 'block' : 'none';
  btn.textContent   = open ? 'Sembunyikan ▲' : 'Tampilkan ▼';
}

// ── Salin JSON-LD ─────────────────────────────────────
function copyJson() {
  const text = document.getElementById('jsonContent').textContent;
  navigator.clipboard.writeText(text).then(() => {
    const btn = event.target;
    btn.textContent = '✅ Tersalin!';
    setTimeout(() => btn.textContent = '📋 Salin', 2000);
  });
}
</script>

</body>
</html>