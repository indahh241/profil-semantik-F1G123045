<?php
require_once 'config/db.php';

function getIP(): string {
    return $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['HTTP_CLIENT_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? '0.0.0.0';
}

// ── Handle LIKE (AJAX) ────────────────────────────────────
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
$totalKomen= count($komentar);

$fotoSrc = $profil['foto'] ?? 'assets/foto.jpg';
$fotoFb  = 'https://ui-avatars.com/api/?name=' . urlencode($profil['nama'] ?? 'I') . '&background=3b5bdb&color=fff&size=200';

// ==========================================================
// ✏️  EDIT KONTEN DI SINI
// ==========================================================
$info = [
    'judul_website' => 'Website Profil Berbasis Semantic Web',
    'desc_website'  => 'Website ini merupakan implementasi tugas akhir semester mata kuliah Semantik Web, dibangun untuk mempresentasikan profil mahasiswa secara terstruktur menggunakan teknologi Semantic Web, Schema.org JSON-LD, dan relasi semantik Subject–Predicate–Object.',

    'stack' => [
        ['ikon' => '🐘', 'nama' => 'PHP 8',        'keterangan' => 'Backend & server-side logic'],
        ['ikon' => '🗄️', 'nama' => 'MySQL',         'keterangan' => 'Database relasional'],
        ['ikon' => '🌐', 'nama' => 'HTML5 & CSS3',  'keterangan' => 'Struktur & tampilan'],
        ['ikon' => '⚡', 'nama' => 'JavaScript',     'keterangan' => 'Interaktivitas frontend'],
        ['ikon' => '📋', 'nama' => 'Schema.org',     'keterangan' => 'Markup semantik JSON-LD'],
        ['ikon' => '🔗', 'nama' => 'RDF / Triple',   'keterangan' => 'Relasi semantik SPO'],
    ],

    'github_nama'   => 'GitHub',
    'github_db'     => 'Git',
    'github_url'    => 'https://github.com/indahh241/profil-semantik-F1G123045',
    'github_status' => true,

    'matkul'   => 'Semantik Web',
    'dosen'    => 'Natalis Ransi, S.Si., M.Cs.',
    'semester' => 'Genap 2025/2026',
    'kelas'    => 'Ilmu Komputer',

    'links' => [
        ['ikon' => '📄', 'label' => 'Makalah (PDF)',        'url' => 'https://drive.google.com/file/d/1JM5HvUWwjLvYd844vGHlJvxUuIIdINCF/view?usp=sharing', 'warna' => 'blue'],
        ['ikon' => '🎬', 'label' => 'Video Presentasi',     'url' => 'https://youtu.be/PpbferVwguM?si=tl8eU_3oxXSUxf4S',                                     'warna' => 'purple'],
        ['ikon' => '✅', 'label' => 'Validasi Schema.org',  'url' => 'https://drive.google.com/file/d/1qwIFV980AsBeNkJGMzrbe0D1CHm4XUmB/view?usp=sharing',   'warna' => 'green'],
        ['ikon' => '🔍', 'label' => 'Rich Results Test',    'url' => 'https://drive.google.com/file/d/1qwIFV980AsBeNkJGMzrbe0D1CHm4XUmB/view?usp=sharing',   'warna' => 'orange'],
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
    /* ── Hero banner ── */
    .about-hero {
      background: linear-gradient(135deg, var(--sidebar-bg) 0%, #1a2f5e 60%, #1e1b4b 100%);
      border-radius: var(--radius);
      padding: 36px 40px;
      margin-bottom: 24px;
      position: relative;
      overflow: hidden;
      animation: fade-up .5s ease both;
    }
    .about-hero::before {
      content: '';
      position: absolute; inset: 0;
      background: radial-gradient(ellipse at 80% 50%, rgba(59,91,219,.25) 0%, transparent 70%);
      pointer-events: none;
    }
    .about-hero-inner { position: relative; z-index: 1; display: flex; align-items: center; gap: 28px; flex-wrap: wrap; }
    .about-hero-avatar {
      width: 88px; height: 88px; border-radius: 50%;
      object-fit: cover;
      border: 3px solid rgba(255,255,255,.3);
      box-shadow: 0 0 0 6px rgba(59,91,219,.2);
      flex-shrink: 0;
    }
    .about-hero-nim {
      font-size: 12px; font-weight: 600;
      color: rgba(147,197,253,.9);
      letter-spacing: .08em; text-transform: uppercase;
      margin-bottom: 4px;
    }
    .about-hero-nama {
      font-family: 'Poppins', sans-serif;
      font-size: 26px; font-weight: 800;
      color: #fff; line-height: 1.2;
      margin-bottom: 8px;
    }
    .about-hero-badges { display: flex; gap: 8px; flex-wrap: wrap; }
    .hero-badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 4px 12px; border-radius: 20px;
      font-size: 11.5px; font-weight: 600;
      background: rgba(255,255,255,.1);
      border: 1px solid rgba(255,255,255,.15);
      color: rgba(255,255,255,.85);
    }
    .about-hero-stats {
      margin-left: auto; display: flex; gap: 20px; flex-wrap: wrap;
    }
    .hero-stat { text-align: center; }
    .hero-stat-val {
      font-family: 'Poppins', sans-serif;
      font-size: 28px; font-weight: 800;
      color: #fff; line-height: 1;
    }
    .hero-stat-lbl { font-size: 11px; color: rgba(255,255,255,.5); margin-top: 3px; }

    /* ── Divider ── */
    .section-divider {
      height: 1px; background: var(--border);
      margin: 4px 0 20px; border: none;
    }

    /* ── Info grid (akademik) ── */
    .akademik-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .akademik-item {
      background: var(--bg-page);
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      padding: 14px 16px;
      transition: border-color var(--transition), transform var(--transition);
    }
    .akademik-item:hover { border-color: rgba(59,91,219,.3); transform: translateY(-1px); }
    .akademik-label {
      font-size: 10.5px; color: var(--text-muted);
      font-weight: 600; text-transform: uppercase;
      letter-spacing: .06em; margin-bottom: 5px;
      display: flex; align-items: center; gap: 5px;
    }
    .akademik-value { font-size: 13px; font-weight: 700; color: var(--text-primary); line-height: 1.4; }

    /* ── Stack grid ── */
    .stack-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .stack-item {
      background: var(--bg-page);
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      padding: 16px 14px;
      display: flex; align-items: center; gap: 12px;
      transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition);
    }
    .stack-item:hover { transform: translateY(-3px); box-shadow: var(--shadow-sm); border-color: rgba(59,91,219,.3); }
    .stack-ikon {
      font-size: 24px; flex-shrink: 0;
      width: 44px; height: 44px;
      background: var(--bg-card); border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      box-shadow: var(--shadow-sm);
    }
    .stack-nama { font-size: 13px; font-weight: 700; color: var(--text-primary); margin-bottom: 2px; }
    .stack-ket  { font-size: 11.5px; color: var(--text-muted); }

    /* ── GitHub card ── */
    .github-card {
      background: linear-gradient(135deg, #0f172a 0%, #1a2f5e 100%);
      border-radius: var(--radius); padding: 24px;
      border: 1px solid rgba(255,255,255,.08);
      position: relative; overflow: hidden;
    }
    .github-card::before {
      content: '';
      position: absolute; top: -40px; right: -40px;
      width: 120px; height: 120px; border-radius: 50%;
      background: rgba(59,91,219,.15);
    }
    .github-top { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .github-icon {
      width: 44px; height: 44px; border-radius: 10px;
      background: rgba(255,255,255,.1);
      display: flex; align-items: center; justify-content: center;
      font-size: 22px;
    }
    .github-title { font-family: 'Poppins', sans-serif; font-size: 15px; font-weight: 700; color: #fff; }
    .github-subtitle { font-size: 12px; color: rgba(255,255,255,.5); }
    .github-status {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 4px 12px; border-radius: 20px;
      background: rgba(22,163,74,.2); border: 1px solid rgba(22,163,74,.3);
      font-size: 11.5px; font-weight: 600; color: #86efac;
      margin-bottom: 14px;
    }
    .github-status::before {
      content: ''; width: 7px; height: 7px; border-radius: 50%;
      background: #22c55e; animation: status-pulse 2s ease-in-out infinite;
    }
    .github-url {
      display: flex; align-items: center; gap: 8px;
      padding: 10px 14px; border-radius: var(--radius-sm);
      background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);
      font-size: 12px; color: rgba(255,255,255,.7);
      text-decoration: none; word-break: break-all;
      transition: background .2s;
    }
    .github-url:hover { background: rgba(255,255,255,.12); color: #fff; }
    .github-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 12px; }
    .github-meta-item {
      padding: 8px 12px; border-radius: var(--radius-sm);
      background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.07);
    }
    .github-meta-label { font-size: 10px; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 3px; }
    .github-meta-val   { font-size: 13px; font-weight: 600; color: rgba(255,255,255,.85); }

    /* ── Like section ── */
    .like-wrap {
      display: flex; flex-direction: column; align-items: center;
      justify-content: center; padding: 28px 20px; text-align: center;
    }
    .like-btn {
      display: inline-flex; align-items: center; gap: 10px;
      padding: 14px 32px; border-radius: 50px;
      background: var(--bg-page); border: 2px solid var(--border);
      font-size: 15px; font-weight: 700; color: var(--text-secondary);
      cursor: pointer; transition: all .25s; font-family: inherit;
      margin-bottom: 12px;
    }
    .like-btn:hover { border-color: #ef4444; color: #ef4444; transform: scale(1.04); box-shadow: 0 4px 16px rgba(239,68,68,.15); }
    .like-btn.liked { background: #fee2e2; border-color: #ef4444; color: #ef4444; box-shadow: 0 4px 16px rgba(239,68,68,.2); }
    .like-btn .heart { font-size: 22px; transition: transform .2s; display: inline-block; }
    .like-btn:hover .heart, .like-btn.liked .heart { transform: scale(1.25); }
    .like-count { font-size: 13px; color: var(--text-muted); }
    .like-count strong { color: var(--text-primary); font-weight: 700; }

    /* ── Link pills ── */
    .links-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .link-pill {
      display: flex; align-items: center; gap: 12px;
      padding: 14px 16px; border-radius: var(--radius-sm);
      border: 1px solid var(--border); background: var(--bg-page);
      text-decoration: none; color: var(--text-primary);
      font-size: 13px; font-weight: 600;
      transition: all var(--transition);
    }
    .link-pill:hover { transform: translateY(-2px); box-shadow: var(--shadow-sm); }
    .link-pill.blue:hover   { background: var(--blue-light);   color: var(--blue);   border-color: rgba(59,91,219,.3); }
    .link-pill.purple:hover { background: var(--purple-light); color: var(--purple); border-color: rgba(124,58,237,.3); }
    .link-pill.green:hover  { background: var(--green-light);  color: var(--green);  border-color: rgba(22,163,74,.3); }
    .link-pill.orange:hover { background: var(--orange-light); color: var(--orange); border-color: rgba(234,88,12,.3); }
    .link-pill-icon  { font-size: 20px; flex-shrink: 0; }
    .link-pill-arrow { margin-left: auto; color: var(--text-muted); flex-shrink: 0; transition: transform var(--transition); }
    .link-pill:hover .link-pill-arrow { transform: translateX(4px); }

    /* ── Komentar ── */
    .komentar-list { display: flex; flex-direction: column; gap: 12px; margin-top: 20px; }
    .komentar-item {
      background: var(--bg-page); border: 1px solid var(--border);
      border-radius: var(--radius-sm); padding: 14px 16px;
      display: flex; gap: 14px; animation: fade-up .4s ease both;
    }
    .komentar-avatar {
      width: 40px; height: 40px; flex-shrink: 0; border-radius: 50%;
      background: linear-gradient(135deg, var(--blue), var(--purple));
      display: flex; align-items: center; justify-content: center;
      font-size: 16px; font-weight: 800; color: #fff;
    }
    .komentar-nama  { font-size: 13px; font-weight: 700; color: var(--text-primary); margin-bottom: 2px; }
    .komentar-waktu { font-size: 11px; color: var(--text-muted); margin-bottom: 6px; }
    .komentar-pesan { font-size: 13px; color: var(--text-secondary); line-height: 1.6; }
    .komentar-empty {
      text-align: center; padding: 40px 20px;
      color: var(--text-muted); font-size: 13px;
    }
    .komentar-empty .ei { font-size: 40px; margin-bottom: 10px; }

    /* ── Responsive ── */
    @media(max-width: 1024px) { .stack-grid { grid-template-columns: repeat(2, 1fr); } }
    @media(max-width: 768px) {
      .about-hero { padding: 24px 20px; }
      .about-hero-stats { margin-left: 0; width: 100%; justify-content: space-around; }
      .stack-grid, .links-grid, .akademik-grid { grid-template-columns: 1fr 1fr; }
      .github-meta { grid-template-columns: 1fr; }
    }
    @media(max-width: 480px) {
      .stack-grid, .links-grid, .akademik-grid { grid-template-columns: 1fr; }
      .about-hero-nama { font-size: 20px; }
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
    <button class="topbar-menu-btn" id="menuBtn">
      <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <div class="topbar-greeting">Tentang Website &amp; Penilaian</div>
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

    <!-- ── HERO BANNER ── -->
    <div class="about-hero">
      <div class="about-hero-inner">
        <img
          src="<?= e($fotoSrc) ?>"
          alt="<?= e($profil['nama'] ?? '') ?>"
          class="about-hero-avatar"
          onerror="this.onerror=null;this.src='<?= e($fotoFb) ?>'">
        <div style="flex:1;min-width:200px;">
          <div class="about-hero-nim"><?= e($profil['nim'] ?? '') ?></div>
          <div class="about-hero-nama"><?= e($profil['nama'] ?? '') ?></div>
          <div class="about-hero-badges">
            <span class="hero-badge">&#x1F393; <?= e($profil['prodi'] ?? '') ?></span>
            <span class="hero-badge">&#x1F4DA; <?= e($info['matkul']) ?></span>
            <span class="hero-badge">&#x1F4C5; <?= e($info['semester']) ?></span>
          </div>
        </div>
        <div class="about-hero-stats">
          <div class="hero-stat">
            <div class="hero-stat-val" id="heroLike"><?= $totalLike ?></div>
            <div class="hero-stat-lbl">&#x2764; Like</div>
          </div>
          <div class="hero-stat">
            <div class="hero-stat-val"><?= $totalKomen ?></div>
            <div class="hero-stat-lbl">&#x1F4AC; Komentar</div>
          </div>
          <div class="hero-stat">
            <div class="hero-stat-val"><?= count($info['stack']) ?></div>
            <div class="hero-stat-lbl">&#x1F6E0; Teknologi</div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Row 1: Tentang + Like ── -->
    <div class="grid-2" style="margin-bottom:20px;">

      <!-- Tentang Website -->
      <div class="card" style="animation:fade-up .5s ease .05s both;">
        <div class="card-body">
          <div class="section-header">
            <div class="section-icon">&#x2139;&#xFE0F;</div>
            <div>
              <div class="section-title">Tentang Website</div>
              <div class="section-subtitle"><?= e($info['judul_website']) ?></div>
            </div>
          </div>
          <p style="font-size:13.5px;color:var(--text-secondary);line-height:1.75;margin-bottom:20px;">
            <?= e($info['desc_website']) ?>
          </p>
          <hr class="section-divider">
          <div class="akademik-grid">
            <?php
            $akademik = [
              ['label'=>'Mata Kuliah',    'value'=>$info['matkul'],   'icon'=>'📚'],
              ['label'=>'Dosen Pengampu', 'value'=>$info['dosen'],    'icon'=>'👨‍🏫'],
              ['label'=>'Semester',       'value'=>$info['semester'], 'icon'=>'📅'],
              ['label'=>'Program Studi',  'value'=>$info['kelas'],    'icon'=>'🎓'],
            ];
            foreach ($akademik as $ak): ?>
            <div class="akademik-item">
              <div class="akademik-label"><?= $ak['icon'] ?> <?= e($ak['label']) ?></div>
              <div class="akademik-value"><?= e($ak['value']) ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Like + Link Penting -->
      <div style="display:flex;flex-direction:column;gap:20px;">

        <!-- Like -->
        <div class="card" style="animation:fade-up .5s ease .1s both;">
          <div class="card-body">
            <div class="section-header">
              <div class="section-icon">&#x2764;&#xFE0F;</div>
              <div><div class="section-title">Beri Penilaian</div></div>
            </div>
            <div class="like-wrap">
              <button class="like-btn <?= $sudahLike ? 'liked' : '' ?>" id="likeBtn">
                <span class="heart">&#x2764;&#xFE0F;</span>
                <span id="likeBtnText"><?= $sudahLike ? 'Sudah Disukai' : 'Suka Website Ini' ?></span>
              </button>
              <div class="like-count">
                <strong id="likeCount"><?= $totalLike ?></strong> orang menyukai website ini
              </div>
            </div>
          </div>
        </div>

        <!-- Link Penting -->
        <div class="card" style="animation:fade-up .5s ease .15s both;">
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
    </div>

    <!-- ── Stack Teknologi ── -->
    <div class="card" style="animation:fade-up .5s ease .2s both;margin-bottom:20px;">
      <div class="card-body">
        <div class="section-header">
          <div class="section-icon">&#x1F6E0;&#xFE0F;</div>
          <div>
            <div class="section-title">Stack Teknologi</div>
            <div class="section-subtitle">Tools &amp; bahasa yang digunakan dalam membangun website ini</div>
          </div>
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

    <!-- ── GitHub Repository ── -->
    <div style="margin-bottom:20px;animation:fade-up .5s ease .25s both;">
      <div class="github-card">
        <div class="github-top">
          <div class="github-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="white"><path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844a9.59 9.59 0 012.504.337c1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.02 10.02 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
          </div>
          <div>
            <div class="github-title">GitHub Repository</div>
            <div class="github-subtitle">Source code website ini tersedia secara publik</div>
          </div>
          <a href="<?= e($info['github_url']) ?>" target="_blank"
             style="margin-left:auto;display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);color:#fff;font-size:13px;font-weight:600;text-decoration:none;transition:.2s;"
             onmouseover="this.style.background='rgba(255,255,255,.18)'"
             onmouseout="this.style.background='rgba(255,255,255,.1)'">
            Buka &#x2197;
          </a>
        </div>
        <div class="github-status">
          <?= $info['github_status'] ? 'Public Repository' : 'Private Repository' ?>
        </div>
        <a href="<?= e($info['github_url']) ?>" target="_blank" class="github-url">
          <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
          <?= e($info['github_url']) ?>
        </a>
        <div class="github-meta">
          <div class="github-meta-item">
            <div class="github-meta-label">Platform</div>
            <div class="github-meta-val"><?= e($info['github_nama']) ?></div>
          </div>
          <div class="github-meta-item">
            <div class="github-meta-label">Version Control</div>
            <div class="github-meta-val"><?= e($info['github_db']) ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Komentar ── -->
    <div class="card" id="komentar" style="animation:fade-up .5s ease .3s both;">
      <div class="card-body">
        <div class="section-header">
          <div class="section-icon">&#x1F4AC;</div>
          <div>
            <div class="section-title">Komentar &amp; Kesan</div>
            <div class="section-subtitle">
              <?= $totalKomen ?> komentar &mdash; komentar baru tampil setelah disetujui admin
            </div>
          </div>
        </div>

        <!-- Form -->
        <form method="POST" style="background:var(--bg-page);border:1px solid var(--border);border-radius:var(--radius-sm);padding:20px;margin-bottom:24px;">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
            <div class="form-group" style="margin-bottom:0;">
              <label class="form-label">Nama *</label>
              <input type="text" name="nama" class="form-control" placeholder="Nama Anda" required minlength="2">
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label class="form-label">Komentar *</label>
              <input type="text" name="pesan" class="form-control" placeholder="Kesan atau komentar Anda..." required minlength="5">
            </div>
          </div>
          <input type="hidden" name="aksi" value="komentar">
          <button type="submit" class="btn btn-primary">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Kirim Komentar
          </button>
        </form>

        <!-- List -->
        <?php if (empty($komentar)): ?>
        <div class="komentar-empty">
          <div class="ei">&#x1F4AC;</div>
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

const likeBtn   = document.getElementById('likeBtn');
const likeCount = document.getElementById('likeCount');
const heroLike  = document.getElementById('heroLike');
const likeTxt   = document.getElementById('likeBtnText');

likeBtn.addEventListener('click', async () => {
  likeBtn.disabled = true;
  likeBtn.style.opacity = '.7';
  likeBtn.querySelector('.heart').style.transform = 'scale(1.5)';
  setTimeout(() => likeBtn.querySelector('.heart').style.transform = '', 300);
  try {
    const res  = await fetch('about.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'aksi=like'
    });
    const data = await res.json();
    likeCount.textContent = data.total;
    heroLike.textContent  = data.total;
    if (data.status === 'liked') {
      likeBtn.classList.add('liked');
      likeTxt.textContent = 'Sudah Disukai';
    } else {
      likeBtn.classList.remove('liked');
      likeTxt.textContent = 'Suka Website Ini';
    }
  } catch(e) { console.error(e); }
  likeBtn.disabled = false;
  likeBtn.style.opacity = '1';
});
</script>
</body>
</html>