<?php
require_once 'config/db.php';

// ── Ambil data dari database ──────────────────────────
$profil     = dbRow($pdo, "SELECT * FROM profil LIMIT 1");
$pendidikan = dbRows($pdo, "SELECT * FROM pendidikan ORDER BY urutan ASC");

// ── Path foto dengan fallback otomatis ───────────────
$fotoDb   = $profil['foto'] ?? '';
$fotoPath = !empty($fotoDb) ? $fotoDb
          : (file_exists('assets/foto.jpg')  ? 'assets/foto.jpg'
          : (file_exists('assets/foto.jpeg') ? 'assets/foto.jpeg'
          : (file_exists('assets/foto.png')  ? 'assets/foto.png'  : '')));
$namaDepan    = e(explode(' ', $profil['nama'] ?? 'User')[0]);
$fotoFallback = "https://ui-avatars.com/api/?name={$namaDepan}&background=3b5bdb&color=fff&size=80";

// ── JSON-LD: alumniOf — tanpa startDate/endDate (tidak valid di Organization) ──
$alumniOf = [];
foreach ($pendidikan as $d) {
    $type  = in_array($d['jenjang'], ['S1','S2','D3']) ? 'CollegeOrUniversity' : 'EducationalOrganization';
    $entry = ['@type' => $type, 'name' => $d['institusi']];
    if (!empty($d['jurusan'])) {
        $entry['department'] = ['@type' => 'Organization', 'name' => $d['jurusan']];
    }
    if ($type === 'CollegeOrUniversity' && !empty($profil['url_kampus'] ?? '')) {
        $entry['url'] = $profil['url_kampus'];
    } elseif ($type === 'CollegeOrUniversity') {
        $entry['url'] = 'https://uho.ac.id';
    }
    $alumniOf[] = $entry;
}

// ── Label jenjang untuk tampilan ─────────────────────
$jenjangLabel = [
    'SD'  => ['label'=>'SD',  'bg'=>'#e8edff','color'=>'#3b5bdb','border'=>'#3b5bdb'],
    'SMP' => ['label'=>'SMP', 'bg'=>'#dcfce7','color'=>'#16a34a','border'=>'#16a34a'],
    'SMA' => ['label'=>'SMA', 'bg'=>'#ffedd5','color'=>'#ea580c','border'=>'#ea580c'],
    'D3'  => ['label'=>'D3',  'bg'=>'#ede9fe','color'=>'#7c3aed','border'=>'#7c3aed'],
    'S1'  => ['label'=>'S1',  'bg'=>'#1e3a5f','color'=>'#fff',   'border'=>'#1e3a5f'],
    'S2'  => ['label'=>'S2',  'bg'=>'#0f172a','color'=>'#fff',   'border'=>'#0f172a'],
];

$jsonLd = [
    '@context'  => 'https://schema.org',
    '@type'     => 'Person',
    'name'      => $profil['nama']      ?? '',
    'identifier'=> $profil['nim']       ?? '',
    'alumniOf'  => $alumniOf,
];
$jsonLdPretty = json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pendidikan — <?= e($profil['nama'] ?? 'Mahasiswa') ?></title>
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
    <a href="pendidikan.php"   class="nav-item active">
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
      <div class="page-eyebrow" style="font-size:12px;color:var(--text-secondary);margin-bottom:4px;">Schema.org · alumniOf · CollegeOrUniversity</div>
      <h1 class="section-title" style="font-size:22px;">🎓 Riwayat Pendidikan</h1>
      <p class="page-subtitle">Jalur pendidikan formal yang direpresentasikan dengan schema <code>alumniOf</code> dan <code>CollegeOrUniversity</code>.</p>
    </div>

    <!-- ── Row 1: Timeline + Tabel ── -->
    <div class="grid-2" style="margin-bottom:20px;">

      <!-- Timeline -->
      <div class="card" style="animation:fade-up .5s ease .05s both;">
        <div class="card-body">
          <div class="section-header">
            <div class="section-icon">📅</div>
            <div><div class="section-title">Timeline Pendidikan</div></div>
          </div>
          <div style="margin-top:16px;position:relative;padding-left:24px;">
            <div style="position:absolute;left:7px;top:0;bottom:0;width:2px;background:var(--border-light);border-radius:2px;"></div>
            <?php foreach ($pendidikan as $i => $d):
              $jl         = $jenjangLabel[$d['jenjang']] ?? ['label'=>$d['jenjang'],'bg'=>'#f1f5f9','color'=>'#475569','border'=>'#cbd5e1'];
              $tahunAkhir = $d['tahun_lulus'] ?? 'Sekarang';
              $isLast     = $i === count($pendidikan) - 1;
            ?>
            <div style="position:relative;margin-bottom:<?= $isLast ? '0' : '20px' ?>;">
              <div style="position:absolute;left:-21px;top:4px;width:10px;height:10px;border-radius:50%;background:<?= $jl['bg'] ?>;border:2px solid <?= $jl['border'] ?>;"></div>
              <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;background:<?= $jl['bg'] ?>;color:<?= $jl['color'] ?>;border:1px solid <?= $jl['border'] ?>;"><?= $jl['label'] ?></span>
                <span style="font-size:12px;color:var(--text-secondary);"><?= e($d['tahun_masuk']) ?> – <?= e($tahunAkhir) ?></span>
              </div>
              <div style="font-weight:600;font-size:14px;color:var(--text-primary);"><?= e($d['institusi']) ?></div>
              <?php if (!empty($d['jurusan'])): ?>
              <div style="font-size:12px;color:var(--text-secondary);margin-top:2px;">Jurusan <?= e($d['jurusan']) ?></div>
              <?php endif; ?>
              <?php if (!empty($d['keterangan'])): ?>
              <div style="font-size:12px;color:var(--text-secondary);margin-top:2px;"><?= e($d['keterangan']) ?></div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Tabel Riwayat -->
      <div class="card" style="animation:fade-up .5s ease .1s both;">
        <div class="card-body">
          <div class="section-header">
            <div class="section-icon">📋</div>
            <div><div class="section-title">Tabel Riwayat</div></div>
          </div>
          <div style="overflow-x:auto;margin-top:12px;">
            <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
              <thead>
                <tr style="border-bottom:2px solid var(--border-light);">
                  <th style="padding:8px 10px;text-align:left;color:var(--text-secondary);font-weight:600;">Jenjang</th>
                  <th style="padding:8px 10px;text-align:left;color:var(--text-secondary);font-weight:600;">Instansi</th>
                  <th style="padding:8px 10px;text-align:left;color:var(--text-secondary);font-weight:600;">Tahun</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pendidikan as $d):
                  $jl         = $jenjangLabel[$d['jenjang']] ?? ['label'=>$d['jenjang'],'bg'=>'#f1f5f9','color'=>'#475569','border'=>'#cbd5e1'];
                  $tahunAkhir = $d['tahun_lulus'] ?? 'Skrg';
                ?>
                <tr style="border-bottom:1px solid var(--border-light);">
                  <td style="padding:10px;">
                    <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;background:<?= $jl['bg'] ?>;color:<?= $jl['color'] ?>;border:1px solid <?= $jl['border'] ?>;"><?= $jl['label'] ?></span>
                  </td>
                  <td style="padding:10px;">
                    <?php if (in_array($d['jenjang'], ['S1','S2','D3'])): ?>
                      <strong><?= e($d['institusi']) ?></strong><br>
                      <span style="font-size:12px;color:var(--text-secondary);"><?= e($d['jurusan'] ?? '') ?></span>
                    <?php else: ?>
                      <?= e($d['institusi']) ?>
                    <?php endif; ?>
                  </td>
                  <td style="padding:10px;white-space:nowrap;"><?= e($d['tahun_masuk']) ?> – <?= e($tahunAkhir) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

    <!-- ── Row 2: JSON-LD ── -->
    <div class="card" style="animation:fade-up .5s ease .15s both;">
      <div class="card-body">
        <div class="section-header">
          <div class="section-icon">💾</div>
          <div>
            <div class="section-title">JSON-LD: alumniOf + CollegeOrUniversity</div>
            <div class="page-subtitle">0 error · 0 warning — properti valid sesuai Schema.org/Person</div>
          </div>
        </div>
        <div style="background:#0f172a;border-radius:8px;padding:16px;margin-top:12px;overflow-x:auto;font-size:12px;line-height:1.9;font-family:monospace;">
<span style="color:#7dd3fc;">"alumniOf"</span>: [<br>
<?php foreach ($pendidikan as $i => $d):
  $type   = in_array($d['jenjang'], ['S1','S2','D3']) ? 'CollegeOrUniversity' : 'EducationalOrganization';
  $isLast = $i === count($pendidikan) - 1;
?>
&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"@type"</span>: <span style="color:#86efac;">"<?= $type ?>"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"name"</span>: <span style="color:#fde68a;">"<?= e($d['institusi']) ?>"</span><?= (!empty($d['jurusan']) || $type === 'CollegeOrUniversity') ? ',' : '' ?><br>
<?php if (!empty($d['jurusan'])): ?>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"department"</span>: {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"@type"</span>: <span style="color:#86efac;">"Organization"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"name"</span>: <span style="color:#fde68a;">"<?= e($d['jurusan']) ?>"</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;}<?= $type === 'CollegeOrUniversity' ? ',' : '' ?><br>
<?php endif; ?>
<?php if ($type === 'CollegeOrUniversity'): ?>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"url"</span>: <span style="color:#fde68a;">"https://uho.ac.id"</span><br>
<?php endif; ?>
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