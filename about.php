<?php
require_once 'config/db.php';

// ── Ambil IP pengunjung ───────────────────────────────────
function getIP(): string {
    return $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['HTTP_CLIENT_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? '0.0.0.0';
}

// ── Handle LIKE (AJAX) ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'like') {
    header('Content-Type: application/json');
    $ip = getIP();
    $sudahLike = dbRow($pdo, "SELECT id FROM likes WHERE ip_address = ?", [$ip]);
    if ($sudahLike) {
        $pdo->prepare("DELETE FROM likes WHERE ip_address = ?")->execute([$ip]);
        $status = 'unliked';
    } else {
        $pdo->prepare("INSERT IGNORE INTO likes (ip_address) VALUES (?)")->execute([$ip]);
        $status = 'liked';
    }
    $total = (int)$pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn();
    echo json_encode(['status' => $status, 'total' => $total]);
    exit;
}

// ── Handle KOMENTAR ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'komentar') {
    $nama  = trim($_POST['nama']  ?? '');
    $pesan = trim($_POST['pesan'] ?? '');
    if (strlen($nama) >= 2 && strlen($pesan) >= 5) {
        $pdo->prepare("INSERT INTO komentar (nama, pesan, approved) VALUES (?, ?, 0)")
            ->execute([$nama, $pesan]);
        setFlash('success', 'Komentar berhasil dikirim! Akan tampil setelah disetujui.');
    } else {
        setFlash('error', 'Nama minimal 2 karakter dan pesan minimal 5 karakter.');
    }
    redirect(APP_URL . '/about.php#komentar');
}

// ── Data ──────────────────────────────────────────────────
$profil    = dbRow($pdo, "SELECT * FROM profil LIMIT 1");
$totalLike = (int)$pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn();
$ip        = getIP();
$sudahLike = (bool)dbRow($pdo, "SELECT id FROM likes WHERE ip_address = ?", [$ip]);
$komentar  = dbRows($pdo, "SELECT * FROM komentar WHERE approved = 1 ORDER BY created_at DESC");

// ── Resolve foto ──────────────────────────────────────────
$fotoSrc = $profil['foto'] ?? 'assets/foto.jpg';
$fotoFb  = 'https://ui-avatars.com/api/?name=' . urlencode($profil['nama'] ?? 'I') . '&background=3b5bdb&color=fff&size=200';

// ==========================================================
// ✏️  EDIT KONTEN DI SINI
// ==========================================================
$info = [

    // ── Bagian 1: Tentang Website ─────────────────────────
    'judul_website' => 'Website Profil Berbasis Semantic Web',
    'desc_website'  => 'Website ini merupakan implementasi tugas akhir semester mata kuliah Semantik Web, dibangun untuk mempresentasikan profil mahasiswa secara terstruktur menggunakan teknologi Semantic Web, Schema.org JSON-LD, dan relasi semantik Subject–Predicate–Object.',

    // ── Bagian 2: Stack Teknologi ─────────────────────────
    'stack' => [
        ['ikon' => '🐘', 'nama' => 'PHP 8',       'keterangan' => 'Backend & server-side logic'],
        ['ikon' => '🗄️', 'nama' => 'MySQL',        'keterangan' => 'Database relasional'],
        ['ikon' => '🌐', 'nama' => 'HTML5 & CSS3', 'keterangan' => 'Struktur & tampilan'],
        ['ikon' => '⚡', 'nama' => 'JavaScript',    'keterangan' => 'Interaktivitas frontend'],
        ['ikon' => '📋', 'nama' => 'Schema.org',    'keterangan' => 'Markup semantik JSON-LD'],
        ['ikon' => '🔗', 'nama' => 'RDF / Triple',  'keterangan' => 'Relasi semantik SPO'],
    ],

    // ── Bagian 3: Hosting & Deployment ───────────────────
    'hosting_nama'   => 'Shared Hosting (cPanel)',
    'hosting_db'     => 'MySQL',
    'hosting_url'    => 'https://projectvaulthub.my.id',
    'hosting_status' => true,

    // ── Bagian 4: Info Akademik ───────────────────────────
    'matkul'   => 'Semantik Web',
    'dosen'    => 'Natalis Ransi, S.Si., M.Cs.',
    'semester' => 'Genap 2025/2026',
    'kelas'    => 'Ilmu Komputer',

    // ── Bagian 5: Link Penting ────────────────────────────
    'links' => [
        ['ikon' => '📄', 'label' => 'Makalah (PDF)',       'url' => 'https://drive.google.com/file/d/1JM5HvUWwjLvYd844vGHlJvxUuIIdINCF/view?usp=sharing',                                          'warna' => 'blue'],
        ['ikon' => '🎬', 'label' => 'Video Presentasi',    'url' => 'https://youtu.be/PpbferVwguM?si=tl8eU_3oxXSUxf4S',                                          'warna' => 'purple'],
        ['ikon' => '✅', 'label' => 'Validasi Schema.org', 'url' => 'https://drive.google.com/file/d/1qwIFV980AsBeNkJGMzrbe0D1CHm4XUmB/view?usp=sharing',               'warna' => 'green'],
        ['ikon' => '🔍', 'label' => 'Rich Results Test',   'url' => 'https://drive.google.com/file/d/1qwIFV980AsBeNkJGMzrbe0D1CHm4XUmB/view?usp=sharing','warna' => 'orange'],
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tentang Website &mdash; <?= e($profil['nama'] ?? '') ?></title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* ── Stack grid ── */
    .stack-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px;
    }
    .stack-item {
      background: var(--bg-page);
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      padding: 16px;
      display: flex; align-items: center; gap: 14px;
      transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition);
    }
    .stack-item:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-sm);
      border-color: rgba(59,91,219,.3);
    }
    .stack-ikon {
      font-size: 26px; flex-shrink: 0;
      width: 48px; height: 48px;
      background: var(--bg-card);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      box-shadow: var(--shadow-sm);
    }
    .stack-nama { font-size: 13px; font-weight: 700; color: var(--text-primary); margin-bottom: 2px; }
    .stack-ket  { font-size: 11.5px; color: var(--text-muted); }

    /* ── Hosting card ── */
    .hosting-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .hosting-row {
      display: flex; align-items: center; justify-content: space-between;
      padding: 11px 16px;
      background: var(--bg-page);
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      font-size: 13px;
    }
    .hosting-row-label { color: var(--text-muted); font-size: 11.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; }
    .hosting-row-val   { font-weight: 600; color: var(--text-primary); }
    .status-dot {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: 12px; font-weight: 600;
    }
    .status-dot::before {
      content: '';
      width: 8px; height: 8px; border-radius: 50%;
      background: var(--green);
      box-shadow: 0 0 0 3px rgba(22,163,74,.2);
      animation: status-pulse 2s ease-in-out infinite;
    }
    .status-dot.offline::before { background: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.2); }

    /* ── Like button ── */
    .like-section {
      display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      padding: 32px;
      text-align: center;
    }
    .like-btn {
      display: inline-flex; align-items: center; gap: 10px;
      padding: 14px 32px; border-radius: 50px;
      background: var(--bg-page);
      border: 2px solid var(--border);
      font-size: 15px; font-weight: 700;
      color: var(--text-secondary);
      cursor: pointer;
      transition: all .25s;
      font-family: inherit;
      margin-bottom: 12px;
    }
    .like-btn:hover { border-color: #ef4444; color: #ef4444; transform: scale(1.04); }
    .like-btn.liked {
      background: #fee2e2; border-color: #ef4444;
      color: #ef4444;
      box-shadow: 0 4px 16px rgba(239,68,68,.2);
    }
    .like-btn .heart { font-size: 22px; transition: transform .2s; }
    .like-btn:hover .heart, .like-btn.liked .heart { transform: scale(1.2); }
    .like-count { font-size: 13px; color: var(--text-muted); }
    .like-count span { font-weight: 700; color: var(--text-primary); }

    /* ── Komentar ── */
    .komentar-list { display: flex; flex-direction: column; gap: 14px; margin-top: 20px; }
    .komentar-item {
      background: var(--bg-page);
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      padding: 14px 16px;
      display: flex; gap: 14px;
      animation: fade-up .4s ease both;
    }
    .komentar-avatar {
      width: 38px; height: 38px; flex-shrink: 0;
      border-radius: 50%;
      background: var(--blue);
      display: flex; align-items: center; justify-content: center;
      font-size: 15px; font-weight: 700; color: #fff;
    }
    .komentar-nama  { font-size: 13px; font-weight: 700; color: var(--text-primary); margin-bottom: 3px; }
    .komentar-waktu { font-size: 11px; color: var(--text-muted); margin-bottom: 6px; }
    .komentar-pesan { font-size: 13px; color: var(--text-secondary); line-height: 1.6; }
    .komentar-empty {
      text-align: center; padding: 32px;
      color: var(--text-muted); font-size: 13px;
    }
    .komentar-empty .empty-icon { font-size: 36px; margin-bottom: 8px; }

    /* ── Link pills ── */
    .links-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .link-pill {
      display: flex; align-items: center; gap: 12px;
      padding: 14px 18px;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border);
      background: var(--bg-page);
      text-decoration: none;
      color: var(--text-primary);
      font-size: 13.5px; font-weight: 600;
      transition: all var(--transition);
    }
    .link-pill:hover { transform: translateY(-2px); box-shadow: var(--shadow-sm); }
    .link-pill.blue   { border-color: rgba(59,91,219,.3);  } .link-pill.blue:hover   { background: var(--blue-light);   color: var(--blue); }
    .link-pill.purple { border-color: rgba(124,58,237,.3); } .link-pill.purple:hover { background: var(--purple-light); color: var(--purple); }
    .link-pill.green  { border-color: rgba(22,163,74,.3);  } .link-pill.green:hover  { background: var(--green-light);  color: var(--green); }
    .link-pill.orange { border-color: rgba(234,88,12,.3);  } .link-pill.orange:hover { background: var(--orange-light); color: var(--orange); }
    .link-pill-icon  { font-size: 20px; }
    .link-pill-arrow { margin-left: auto; color: var(--text-muted); transition: transform var(--transition); }
    .link-pill:hover .link-pill-arrow { transform: translateX(4px); }

    @media(max-width:768px){
      .stack-grid   { grid-template-columns: 1fr 1fr; }
      .hosting-grid { grid-template-columns: 1fr; }
      .links-grid   { grid-template-columns: 1fr; }
    }
    @media(max-width:480px){
      .stack-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">&#x1F393;</div>
    <div class="brand-text"><h2>Semantic Profile</h2><span>Mahasiswa</span></div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-label">Menu</div>
    <a href="index.php"      class="nav-item"><svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>Dashboard</a>
    <a href="profil.php"     class="nav-item"><svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>Profil</a>
    <a href="pendidikan.php" class="nav-item"><svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/></svg>Riwayat Pendidikan</a>
    <a href="skill.php"      class="nav-item"><svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>Skill</a>
    <a href="organisasi.php" class="nav-item"><svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>Organisasi</a>
    <a href="project.php"    class="nav-item"><svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>Proyek</a>
    <a href="semantic.php"   class="nav-item"><svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/></svg>Relasi Semantik</a>
    <a href="schema.php"     class="nav-item"><svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>Schema.org</a>
    <a href="kontak.php"     class="nav-item"><svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>Kontak</a>
    <a href="about.php"      class="nav-item active"><svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>Tentang Website</a>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-footer-card">
      <div class="sidebar-footer-icon">&#x1F393;</div>
      <p>&ldquo;Knowledge<br>Connects Everything&rdquo;</p>
    </div>
  </div>
</aside>

<!-- ===== MAIN ===== -->
<div class="main-wrapper">

  <header class="topbar">
    <button class="topbar-menu-btn" id="menuBtn" aria-label="Toggle sidebar">
      <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <div class="topbar-greeting">Tentang Website</div>
    <div class="topbar-actions">
      <div class="topbar-user">
        <img src="<?= e($fotoSrc) ?>" alt="Foto" class="topbar-avatar"
             onerror="this.onerror=null;this.src='<?= e($fotoFb) ?>'">
        <span class="topbar-user-name"><?= e(explode(' ', $profil['nama'] ?? 'User')[0]) ?></span>
      </div>
    </div>
  </header>

  <main class="page-content">
    <?php showFlash(); ?>

    <!-- ── Row 1: Tentang + Like ── -->
    <div class="grid-2" style="margin-bottom:20px;">

      <!-- Tentang Website -->
      <div class="card stagger" style="animation-delay:.05s">
        <div class="card-body">
          <div class="section-header">
            <div class="section-icon">&#x2139;&#xFE0F;</div>
            <div><div class="section-title">Tentang Website</div></div>
          </div>
          <p style="font-size:13.5px;color:var(--text-secondary);line-height:1.75;margin-bottom:20px;">
            <?= e($info['judul_website']) ?> &mdash; <?= e($info['desc_website']) ?>
          </p>

          <!-- Info Akademik -->
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <?php
            $akademik = [
              ['label' => 'Mata Kuliah',    'value' => $info['matkul'],   'icon' => '📚'],
              ['label' => 'Dosen Pengampu', 'value' => $info['dosen'],    'icon' => '👨‍🏫'],
              ['label' => 'Semester',       'value' => $info['semester'], 'icon' => '📅'],
              ['label' => 'Program Studi',  'value' => $info['kelas'],    'icon' => '🎓'],
            ];
            foreach ($akademik as $ak): ?>
            <div style="background:var(--bg-page);border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px 14px;">
              <div style="font-size:10.5px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">
                <?= $ak['icon'] ?> <?= e($ak['label']) ?>
              </div>
              <div style="font-size:13px;font-weight:700;color:var(--text-primary);"><?= e($ak['value']) ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Like Card -->
      <div class="card" style="animation:fade-up .5s ease .1s both;">
        <div class="card-body">
          <div class="section-header">
            <div class="section-icon">&#x2764;&#xFE0F;</div>
            <div><div class="section-title">Beri Penilaian</div></div>
          </div>
          <div class="like-section">
            <button class="like-btn <?= $sudahLike ? 'liked' : '' ?>" id="likeBtn">
              <span class="heart">&#x2764;&#xFE0F;</span>
              <span id="likeBtnText"><?= $sudahLike ? 'Sudah Disukai' : 'Suka Website Ini' ?></span>
            </button>
            <div class="like-count">
              <span id="likeCount"><?= $totalLike ?></span> orang menyukai website ini
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- ── Row 2: Stack Teknologi ── -->
    <div class="card" style="animation:fade-up .5s ease .15s both;margin-bottom:20px;">
      <div class="card-body">
        <div class="section-header">
          <div class="section-icon">&#x1F6E0;&#xFE0F;</div>
          <div><div class="section-title">Stack Teknologi</div></div>
        </div>
        <div class="stack-grid">
          <?php foreach ($info['stack'] as $s): ?>
          <div class="stack-item">
            <div class="stack-ikon"><?= $s['ikon'] ?></div>
            <div>
              <div class="stack-nama"><?= e($s['nama']) ?></div>
              <div class="stack-ket"><?= e($s['keterangan']) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- ── Row 3: Hosting + Link Penting ── -->
    <div class="grid-2" style="margin-bottom:20px;">

      <!-- Hosting -->
      <div class="card" style="animation:fade-up .5s ease .2s both;">
        <div class="card-body">
          <div class="section-header">
            <div class="section-icon">&#x2601;&#xFE0F;</div>
            <div><div class="section-title">Hosting &amp; Deployment</div></div>
          </div>
          <div class="hosting-grid">
            <div class="hosting-row" style="grid-column:1/-1;">
              <div>
                <div class="hosting-row-label">Status Server</div>
                <div class="hosting-row-val">
                  <span class="status-dot <?= $info['hosting_status'] ? '' : 'offline' ?>">
                    <?= $info['hosting_status'] ? 'Online' : 'Offline' ?>
                  </span>
                </div>
              </div>
              <a href="<?= e($info['hosting_url']) ?>" target="_blank"
                 style="font-size:12px;color:var(--blue);font-weight:600;text-decoration:none;">
                projectvaulthub.my.id &#x2197;
              </a>
            </div>
            <div class="hosting-row">
              <div class="hosting-row-label">Platform Hosting</div>
              <div class="hosting-row-val"><?= e($info['hosting_nama']) ?></div>
            </div>
            <div class="hosting-row">
              <div class="hosting-row-label">Primary Domain</div>
              <div class="hosting-row-val">projectvaulthub.my.id</div>
            </div>
            <div class="hosting-row" style="grid-column:1/-1;">
              <div class="hosting-row-label">Database</div>
              <div class="hosting-row-val"><?= e($info['hosting_db']) ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Link Penting -->
      <div class="card" style="animation:fade-up .5s ease .25s both;">
        <div class="card-body">
          <div class="section-header">
            <div class="section-icon">&#x1F517;</div>
            <div><div class="section-title">Link Penting</div></div>
          </div>
          <div class="links-grid">
            <?php foreach ($info['links'] as $lnk): ?>
            <a href="<?= e($lnk['url']) ?>" target="_blank" class="link-pill <?= $lnk['warna'] ?>">
              <span class="link-pill-icon"><?= $lnk['ikon'] ?></span>
              <?= e($lnk['label']) ?>
              <svg class="link-pill-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

    </div>

    <!-- ── Row 4: Komentar ── -->
    <div class="card" id="komentar" style="animation:fade-up .5s ease .3s both;margin-bottom:20px;">
      <div class="card-body">
        <div class="section-header">
          <div class="section-icon">&#x1F4AC;</div>
          <div>
            <div class="section-title">Komentar &amp; Kesan</div>
            <div class="section-subtitle">Komentar akan tampil setelah disetujui admin</div>
          </div>
        </div>

        <!-- Form Komentar -->
        <form method="POST" style="margin-bottom:24px;">
          <input type="hidden" name="aksi" value="komentar">
          <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
            <div class="form-group" style="margin-bottom:0;">
              <label class="form-label">Nama *</label>
              <input type="text" name="nama" class="form-control" placeholder="Nama Anda" required minlength="2">
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label class="form-label">Komentar *</label>
              <input type="text" name="pesan" class="form-control" placeholder="Kesan atau komentar Anda..." required minlength="5">
            </div>
          </div>
          <button type="submit" class="btn btn-primary">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Kirim Komentar
          </button>
        </form>

        <!-- Daftar Komentar -->
        <?php if (empty($komentar)): ?>
        <div class="komentar-empty">
          <div class="empty-icon">&#x1F4AC;</div>
          Belum ada komentar. Jadilah yang pertama!
        </div>
        <?php else: ?>
        <div class="komentar-list">
          <?php foreach ($komentar as $i => $k): ?>
          <div class="komentar-item" style="animation-delay:<?= $i * .06 ?>s;">
            <div class="komentar-avatar"><?= mb_strtoupper(mb_substr($k['nama'], 0, 1)) ?></div>
            <div style="flex:1;">
              <div class="komentar-nama"><?= e($k['nama']) ?></div>
              <div class="komentar-waktu"><?= date('d M Y, H:i', strtotime($k['created_at'])) ?></div>
              <div class="komentar-pesan"><?= e($k['pesan']) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

      </div>
    </div>

  </main>
</div>

<script>
// Sidebar mobile
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

// Like button AJAX
const likeBtn  = document.getElementById('likeBtn');
const likeCount = document.getElementById('likeCount');
const likeTxt  = document.getElementById('likeBtnText');

likeBtn.addEventListener('click', async () => {
  likeBtn.disabled = true;
  likeBtn.style.opacity = '.7';

  likeBtn.querySelector('.heart').style.transform = 'scale(1.4)';
  setTimeout(() => likeBtn.querySelector('.heart').style.transform = '', 300);

  try {
    const res  = await fetch('about.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'aksi=like'
    });
    const data = await res.json();
    likeCount.textContent = data.total;
    if (data.status === 'liked') {
      likeBtn.classList.add('liked');
      likeTxt.textContent = 'Sudah Disukai';
    } else {
      likeBtn.classList.remove('liked');
      likeTxt.textContent = 'Suka Website Ini';
    }
  } catch(err) { console.error(err); }

  likeBtn.disabled = false;
  likeBtn.style.opacity = '1';
});
</script>

</body>
</html>