<?php
require_once '../config/db.php';
requireAdmin();

// ============================================================
// HANDLE SEMUA AKSI POST (CRUD)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi  = $_POST['aksi']  ?? '';
    $tabel = $_POST['tabel'] ?? '';

    // ── PROFIL ───────────────────────────────────────────────
    if ($tabel === 'profil' && $aksi === 'update') {
        $fields = ['nama','nim','prodi','fakultas','universitas','email','telepon','alamat','bio','linkedin','github','website'];
        $set    = implode(', ', array_map(fn($f) => "$f = ?", $fields));
        $vals   = array_map(fn($f) => trim($_POST[$f] ?? ''), $fields);
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $filename = 'foto.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], '../assets/' . $filename);
                $set   .= ', foto = ?';
                $vals[] = 'assets/' . $filename;
            }
        }
        $vals[] = 1;
        $pdo->prepare("UPDATE profil SET $set WHERE id = ?")->execute($vals);
        setFlash('success', 'Data profil berhasil diperbarui.');
        redirect(APP_URL . '/admin/index.php?tab=profil');
    }

    // ── GENERIC CRUD ─────────────────────────────────────────
    $allowed_tables = ['skill','pendidikan','organisasi','proyek'];
    if (!in_array($tabel, $allowed_tables)) { setFlash('error','Tabel tidak valid.'); redirect(APP_URL.'/admin/index.php'); }

    if ($aksi === 'hapus') {
        $pdo->prepare("DELETE FROM `$tabel` WHERE id = ?")->execute([(int)$_POST['id']]);
        setFlash('success', 'Data berhasil dihapus.');
        redirect(APP_URL . '/admin/index.php?tab=' . $tabel);
    }

    if ($aksi === 'simpan') {
        if ($tabel === 'skill') {
            $pdo->prepare("INSERT INTO skill (nama,kategori,level,ikon,urutan) VALUES (?,?,?,?,?)")
                ->execute([trim($_POST['nama']), trim($_POST['kategori']??'Umum'), (int)($_POST['level']??70), trim($_POST['ikon']??'💡'), (int)($_POST['urutan']??0)]);
        } elseif ($tabel === 'pendidikan') {
            $pdo->prepare("INSERT INTO pendidikan (jenjang,institusi,jurusan,tahun_masuk,tahun_lulus,keterangan,urutan) VALUES (?,?,?,?,?,?,?)")
                ->execute([trim($_POST['jenjang']), trim($_POST['institusi']), trim($_POST['jurusan']??''), (int)$_POST['tahun_masuk'], !empty($_POST['tahun_lulus'])?(int)$_POST['tahun_lulus']:null, trim($_POST['keterangan']??''), (int)($_POST['urutan']??0)]);
        } elseif ($tabel === 'organisasi') {
            $pdo->prepare("INSERT INTO organisasi (nama,jabatan,tahun_masuk,tahun_keluar,deskripsi,ikon) VALUES (?,?,?,?,?,?)")
                ->execute([trim($_POST['nama']), trim($_POST['jabatan']), (int)$_POST['tahun_masuk'], !empty($_POST['tahun_keluar'])?(int)$_POST['tahun_keluar']:null, trim($_POST['deskripsi']??''), trim($_POST['ikon']??'👥')]);
        } elseif ($tabel === 'proyek') {
            $pdo->prepare("INSERT INTO proyek (judul,deskripsi,teknologi,link_demo,link_github,tahun,ikon,featured) VALUES (?,?,?,?,?,?,?,?)")
                ->execute([trim($_POST['judul']), trim($_POST['deskripsi']??''), trim($_POST['teknologi']??''), trim($_POST['link_demo']??''), trim($_POST['link_github']??''), (int)$_POST['tahun'], trim($_POST['ikon']??'🗂️'), isset($_POST['featured'])?1:0]);
        }
        setFlash('success', 'Data berhasil ditambahkan.');
        redirect(APP_URL . '/admin/index.php?tab=' . $tabel);
    }

    if ($aksi === 'update') {
        $id = (int)$_POST['id'];
        if ($tabel === 'skill') {
            $pdo->prepare("UPDATE skill SET nama=?,kategori=?,level=?,ikon=?,urutan=? WHERE id=?")
                ->execute([trim($_POST['nama']), trim($_POST['kategori']), (int)$_POST['level'], trim($_POST['ikon']), (int)$_POST['urutan'], $id]);
        } elseif ($tabel === 'pendidikan') {
            $pdo->prepare("UPDATE pendidikan SET jenjang=?,institusi=?,jurusan=?,tahun_masuk=?,tahun_lulus=?,keterangan=?,urutan=? WHERE id=?")
                ->execute([trim($_POST['jenjang']), trim($_POST['institusi']), trim($_POST['jurusan']), (int)$_POST['tahun_masuk'], !empty($_POST['tahun_lulus'])?(int)$_POST['tahun_lulus']:null, trim($_POST['keterangan']), (int)$_POST['urutan'], $id]);
        } elseif ($tabel === 'organisasi') {
            $pdo->prepare("UPDATE organisasi SET nama=?,jabatan=?,tahun_masuk=?,tahun_keluar=?,deskripsi=?,ikon=? WHERE id=?")
                ->execute([trim($_POST['nama']), trim($_POST['jabatan']), (int)$_POST['tahun_masuk'], !empty($_POST['tahun_keluar'])?(int)$_POST['tahun_keluar']:null, trim($_POST['deskripsi']), trim($_POST['ikon']), $id]);
        } elseif ($tabel === 'proyek') {
            $pdo->prepare("UPDATE proyek SET judul=?,deskripsi=?,teknologi=?,link_demo=?,link_github=?,tahun=?,ikon=?,featured=? WHERE id=?")
                ->execute([trim($_POST['judul']), trim($_POST['deskripsi']), trim($_POST['teknologi']), trim($_POST['link_demo']), trim($_POST['link_github']), (int)$_POST['tahun'], trim($_POST['ikon']), isset($_POST['featured'])?1:0, $id]);
        }
        setFlash('success', 'Data berhasil diperbarui.');
        redirect(APP_URL . '/admin/index.php?tab=' . $tabel);
    }
}

// ── Ambil data ────────────────────────────────────────────
$tab        = $_GET['tab'] ?? 'profil';
$editId     = (int)($_GET['edit'] ?? 0);
$profil     = dbRow($pdo,  "SELECT * FROM profil LIMIT 1");
$skills     = dbRows($pdo, "SELECT * FROM skill ORDER BY urutan ASC");
$pendidikan = dbRows($pdo, "SELECT * FROM pendidikan ORDER BY urutan ASC");
$organisasi = dbRows($pdo, "SELECT * FROM organisasi ORDER BY tahun_masuk DESC");
$proyek     = dbRows($pdo, "SELECT * FROM proyek ORDER BY tahun DESC");

$editRow = null;
if ($editId > 0 && in_array($tab, ['skill','pendidikan','organisasi','proyek']))
    $editRow = dbRow($pdo, "SELECT * FROM `$tab` WHERE id = ?", [$editId]);

// Resolve foto
$fotoSrc = '../' . ($profil['foto'] ?? 'assets/foto.jpg');
$fotoFallback = 'https://ui-avatars.com/api/?name=' . urlencode($profil['nama'] ?? 'Indah') . '&background=3b5bdb&color=fff&size=80';

$navItems = [
    'profil'     => ['icon' => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>', 'label' => 'Profil'],
    'skill'      => ['icon' => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>', 'label' => 'Skill'],
    'pendidikan' => ['icon' => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/></svg>', 'label' => 'Pendidikan'],
    'organisasi' => ['icon' => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>', 'label' => 'Organisasi'],
    'proyek'     => ['icon' => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>', 'label' => 'Proyek'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Panel Admin &mdash; Semantic Profile</title>
  <link rel="stylesheet" href="../style.css">
  <style>
    /* ── Override body untuk admin layout ── */
    body { display: flex; }

    /* ── Tab navigation ── */
    .admin-tabs {
      display: flex; gap: 4px; flex-wrap: wrap;
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 6px;
      margin-bottom: 24px;
      box-shadow: var(--shadow-sm);
    }
    .admin-tab-btn {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 9px 18px; border-radius: var(--radius-sm);
      font-size: 13px; font-weight: 600;
      color: var(--text-secondary);
      background: transparent; border: none; cursor: pointer;
      transition: background var(--transition), color var(--transition);
      text-decoration: none;
    }
    .admin-tab-btn:hover  { background: var(--bg-page); color: var(--text-primary); }
    .admin-tab-btn.active { background: var(--blue); color: #fff; box-shadow: 0 4px 12px rgba(59,91,219,.3); }
    .admin-tab-btn svg { opacity: .8; }
    .admin-tab-btn.active svg { opacity: 1; }

    /* ── Section card ── */
    .admin-section {
      background: var(--bg-card);
      border-radius: var(--radius);
      border: 1px solid var(--border);
      box-shadow: var(--shadow-sm);
      margin-bottom: 24px;
      overflow: hidden;
      animation: fade-up .4s ease both;
    }
    .admin-section.edit-active { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(59,91,219,.12); }

    .admin-section-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 16px 24px;
      border-bottom: 1px solid var(--border);
      background: var(--bg-page);
    }
    .admin-section-header h3 {
      font-family: 'Poppins', sans-serif;
      font-size: 14px; font-weight: 700;
      color: var(--text-primary);
      display: flex; align-items: center; gap: 8px;
    }
    .admin-section-body { padding: 24px; }

    /* ── Form grid ── */
    .form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
    .col-full   { grid-column: 1 / -1; }

    /* ── Select styling ── */
    select.form-control { cursor: pointer; }

    /* ── Range level ── */
    .range-wrap { display: flex; align-items: center; gap: 12px; }
    .range-wrap input[type=range] { flex: 1; accent-color: var(--blue); height: 6px; }
    .range-num {
      min-width: 44px; text-align: center;
      font-family: 'Poppins', sans-serif;
      font-size: 14px; font-weight: 700;
      color: var(--blue);
      background: var(--blue-light);
      padding: 3px 8px; border-radius: 6px;
    }

    /* ── Admin table ── */
    .admin-table-wrap { overflow-x: auto; }
    .admin-table {
      width: 100%; border-collapse: collapse; font-size: 13px;
    }
    .admin-table thead tr { background: var(--bg-page); }
    .admin-table thead th {
      padding: 11px 16px; text-align: left;
      font-size: 11px; font-weight: 600;
      text-transform: uppercase; letter-spacing: .06em;
      color: var(--text-muted);
      border-bottom: 2px solid var(--border);
      white-space: nowrap;
    }
    .admin-table tbody tr {
      border-bottom: 1px solid var(--border);
      transition: background var(--transition);
    }
    .admin-table tbody tr:last-child { border-bottom: none; }
    .admin-table tbody tr:hover { background: #f8faff; }
    .admin-table td { padding: 12px 16px; color: var(--text-secondary); vertical-align: middle; }

    /* ── Action buttons ── */
    .btn-act-edit {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;
      background: var(--blue-light); color: var(--blue);
      border: 1px solid rgba(59,91,219,.2);
      text-decoration: none; cursor: pointer;
      transition: background var(--transition), transform var(--transition);
    }
    .btn-act-edit:hover { background: var(--blue); color: #fff; transform: translateY(-1px); }

    .btn-act-del {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;
      background: #fee2e2; color: #dc2626;
      border: 1px solid #fca5a5;
      cursor: pointer;
      transition: background var(--transition), transform var(--transition);
    }
    .btn-act-del:hover { background: #dc2626; color: #fff; transform: translateY(-1px); }

    /* ── Level bar mini ── */
    .mini-bar {
      display: inline-flex; align-items: center; gap: 6px;
      vertical-align: middle;
    }
    .mini-bar-track {
      width: 72px; height: 6px;
      background: var(--bg-page); border-radius: 3px;
      border: 1px solid var(--border); overflow: hidden;
    }
    .mini-bar-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--blue), var(--purple));
      border-radius: 3px;
    }

    /* ── Foto preview ── */
    .foto-circle {
      width: 52px; height: 52px;
      border-radius: 50%; object-fit: cover;
      border: 2px solid var(--blue);
      box-shadow: 0 0 0 4px rgba(59,91,219,.12);
    }

    /* ── Checkbox styled ── */
    .check-label {
      display: inline-flex; align-items: center; gap: 8px;
      cursor: pointer; font-size: 13px; color: var(--text-primary);
    }
    .check-label input[type=checkbox] {
      width: 16px; height: 16px; accent-color: var(--blue); cursor: pointer;
    }

    /* ── Empty state ── */
    .empty-state {
      text-align: center; padding: 40px 20px;
      color: var(--text-muted); font-size: 13px;
    }
    .empty-state .empty-icon { font-size: 36px; margin-bottom: 10px; }

    /* ── Stats mini di topbar ── */
    .admin-stat-pill {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 5px 12px; border-radius: 20px;
      font-size: 12px; font-weight: 600;
      background: var(--blue-light); color: var(--blue);
      border: 1px solid rgba(59,91,219,.15);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .form-row, .form-row-3 { grid-template-columns: 1fr; }
      .admin-tabs { gap: 2px; }
      .admin-tab-btn { padding: 8px 12px; font-size: 12px; }
      .admin-tab-btn span.tab-label { display: none; }
    }
  </style>
</head>
<body>

<!-- ===== SIDEBAR (sama persis dengan halaman publik) ===== -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">&#x1F393;</div>
    <div class="brand-text">
      <h2>Semantic Profile</h2>
      <span>Panel Admin</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-label">Kelola Data</div>
    <?php foreach ($navItems as $key => $nav): ?>
    <a href="?tab=<?= $key ?>" class="nav-item <?= $tab === $key ? 'active' : '' ?>">
      <?= $nav['icon'] ?>
      <?= $nav['label'] ?>
    </a>
    <?php endforeach; ?>

    <div class="nav-label" style="margin-top:8px;">Lainnya</div>
    <a href="../index.php" class="nav-item" target="_blank">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
      Lihat Website
    </a>
    <a href="logout.php" class="nav-item" style="color:#f87171;">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Logout
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-footer-card">
      <div class="sidebar-footer-icon">&#x1F4BB;</div>
      <p>Login sebagai<br><strong style="color:#fff;"><?= e($_SESSION['admin_username'] ?? 'admin') ?></strong></p>
    </div>
  </div>
</aside>

<!-- ===== MAIN WRAPPER ===== -->
<div class="main-wrapper">

  <!-- TOPBAR -->
  <header class="topbar">
    <button class="topbar-menu-btn" id="menuBtn" aria-label="Toggle sidebar">
      <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </button>

    <div class="topbar-greeting">
      Panel Admin &mdash; <span><?= $navItems[$tab]['label'] ?? 'Dashboard' ?></span>
    </div>

    <div class="topbar-actions">
      <!-- Mini stats -->
      <span class="admin-stat-pill">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
        <?= dbCount($pdo,'skill') ?> Skill
      </span>
      <span class="admin-stat-pill" style="background:var(--green-light);color:var(--green);border-color:rgba(22,163,74,.15);">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
        <?= dbCount($pdo,'proyek') ?> Proyek
      </span>

      <div class="topbar-user">
        <img src="<?= e($fotoSrc) ?>" alt="Foto"
             class="topbar-avatar"
             onerror="this.onerror=null;this.src='<?= e($fotoFallback) ?>'">
        <span class="topbar-user-name"><?= e(explode(' ', $profil['nama'] ?? 'Admin')[0]) ?></span>
      </div>
    </div>
  </header>

  <!-- PAGE CONTENT -->
  <main class="page-content">
    <?php showFlash(); ?>

    <!-- TAB NAVIGATION -->
    <div class="admin-tabs">
      <?php foreach ($navItems as $key => $nav): ?>
      <a href="?tab=<?= $key ?>" class="admin-tab-btn <?= $tab === $key ? 'active' : '' ?>">
        <?= $nav['icon'] ?>
        <span class="tab-label"><?= $nav['label'] ?></span>
      </a>
      <?php endforeach; ?>
    </div>


    <?php /* ═══════════════════════════════════════════
           ║  TAB: PROFIL
           ═══════════════════════════════════════════ */ ?>
    <?php if ($tab === 'profil'): ?>

    <div class="admin-section" style="animation-delay:.05s">
      <div class="admin-section-header">
        <h3>
          <div class="section-icon" style="width:30px;height:30px;font-size:14px;">&#x1F464;</div>
          Edit Biodata Profil
        </h3>
        <?php if (!empty($profil['foto'])): ?>
        <img src="<?= e($fotoSrc) ?>" class="foto-circle"
             onerror="this.onerror=null;this.src='<?= e($fotoFallback) ?>'">
        <?php endif; ?>
      </div>
      <div class="admin-section-body">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="aksi"  value="update">
          <input type="hidden" name="tabel" value="profil">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Nama Lengkap *</label>
              <input type="text" name="nama" class="form-control" required value="<?= e($profil['nama']??'') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">NIM *</label>
              <input type="text" name="nim" class="form-control" required value="<?= e($profil['nim']??'') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Program Studi *</label>
              <input type="text" name="prodi" class="form-control" required value="<?= e($profil['prodi']??'') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Fakultas</label>
              <input type="text" name="fakultas" class="form-control" value="<?= e($profil['fakultas']??'') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Universitas *</label>
              <input type="text" name="universitas" class="form-control" required value="<?= e($profil['universitas']??'') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Email *</label>
              <input type="email" name="email" class="form-control" required value="<?= e($profil['email']??'') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Telepon</label>
              <input type="text" name="telepon" class="form-control" value="<?= e($profil['telepon']??'') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Alamat</label>
              <input type="text" name="alamat" class="form-control" value="<?= e($profil['alamat']??'') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">LinkedIn URL</label>
              <input type="url" name="linkedin" class="form-control" value="<?= e($profil['linkedin']??'') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">GitHub URL</label>
              <input type="url" name="github" class="form-control" value="<?= e($profil['github']??'') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Website URL</label>
              <input type="url" name="website" class="form-control" value="<?= e($profil['website']??'') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Ganti Foto
                <span style="font-weight:400;color:var(--text-muted);"> (jpg/jpeg/png/webp)</span>
              </label>
              <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png,.webp">
              <?php if(!empty($profil['foto'])): ?>
              <small style="color:var(--text-muted);font-size:11.5px;margin-top:4px;display:block;">
                File saat ini: <?= e($profil['foto']) ?>
              </small>
              <?php endif; ?>
            </div>
            <div class="form-group col-full">
              <label class="form-label">Bio / Deskripsi Diri</label>
              <textarea name="bio" class="form-control" rows="3"><?= e($profil['bio']??'') ?></textarea>
            </div>
          </div>
          <div style="margin-top:8px;">
            <button type="submit" class="btn btn-primary">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>


    <?php /* ═══════════════════════════════════════════
           ║  TAB: SKILL
           ═══════════════════════════════════════════ */ ?>
    <?php elseif ($tab === 'skill'): ?>

    <!-- Form Tambah/Edit Skill -->
    <div class="admin-section <?= $editRow ? 'edit-active' : '' ?>" style="animation-delay:.05s">
      <div class="admin-section-header">
        <h3>
          <div class="section-icon" style="width:30px;height:30px;font-size:14px;">&#x2B50;</div>
          <?= $editRow ? 'Edit Skill' : 'Tambah Skill Baru' ?>
        </h3>
        <?php if ($editRow): ?>
        <a href="?tab=skill" class="btn btn-outline" style="font-size:12px;padding:6px 14px;">Batal Edit</a>
        <?php endif; ?>
      </div>
      <div class="admin-section-body">
        <form method="POST">
          <input type="hidden" name="aksi"  value="<?= $editRow ? 'update' : 'simpan' ?>">
          <input type="hidden" name="tabel" value="skill">
          <?php if ($editRow): ?><input type="hidden" name="id" value="<?= $editRow['id'] ?>"><?php endif; ?>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Nama Skill *</label>
              <input type="text" name="nama" class="form-control" required
                     value="<?= e($editRow['nama']??'') ?>" placeholder="cth: PHP, Laravel, Python">
            </div>
            <div class="form-group">
              <label class="form-label">Kategori</label>
              <select name="kategori" class="form-control">
                <?php foreach(['Frontend','Backend','Database','Tools','Lainnya'] as $k): ?>
                <option value="<?= $k ?>" <?= ($editRow['kategori']??'')===$k?'selected':'' ?>><?= $k ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Ikon (emoji)</label>
              <input type="text" name="ikon" class="form-control"
                     value="<?= e($editRow['ikon']??'💡') ?>" placeholder="💡">
            </div>
            <div class="form-group">
              <label class="form-label">Urutan Tampil</label>
              <input type="number" name="urutan" class="form-control"
                     value="<?= e($editRow['urutan']??0) ?>" min="0">
            </div>
            <div class="form-group col-full">
              <label class="form-label">
                Level Kemampuan &nbsp;
                <span class="range-num" id="levelDisplay"><?= $editRow['level']??70 ?></span>%
              </label>
              <div class="range-wrap">
                <input type="range" name="level" id="levelRange" min="0" max="100"
                       value="<?= $editRow['level']??70 ?>">
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary" style="margin-top:8px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <?= $editRow ? 'Update Skill' : 'Tambah Skill' ?>
          </button>
        </form>
      </div>
    </div>

    <!-- Tabel Skill -->
    <div class="admin-section" style="animation-delay:.1s">
      <div class="admin-section-header">
        <h3>
          <div class="section-icon" style="width:30px;height:30px;font-size:14px;">&#x1F4CB;</div>
          Daftar Skill
          <span class="badge badge-blue"><?= count($skills) ?></span>
        </h3>
      </div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr><th>#</th><th>Ikon</th><th>Nama</th><th>Kategori</th><th>Level</th><th>Urutan</th><th>Aksi</th></tr>
          </thead>
          <tbody>
          <?php if(empty($skills)): ?>
            <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">&#x2B50;</div>Belum ada data skill.</div></td></tr>
          <?php else: ?>
          <?php foreach($skills as $row): ?>
          <tr>
            <td style="color:var(--text-muted);"><?= $row['id'] ?></td>
            <td style="font-size:22px;line-height:1;"><?= e($row['ikon']) ?></td>
            <td><strong style="color:var(--text-primary);"><?= e($row['nama']) ?></strong></td>
            <td><span class="badge badge-blue"><?= e($row['kategori']) ?></span></td>
            <td>
              <div class="mini-bar">
                <span style="font-size:12px;font-weight:600;color:var(--text-primary);min-width:30px;"><?= $row['level'] ?>%</span>
                <div class="mini-bar-track"><div class="mini-bar-fill" style="width:<?= $row['level'] ?>%"></div></div>
              </div>
            </td>
            <td><?= $row['urutan'] ?></td>
            <td style="white-space:nowrap;">
              <a href="?tab=skill&edit=<?= $row['id'] ?>" class="btn-act-edit">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
              </a>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus skill ini?')">
                <input type="hidden" name="aksi" value="hapus">
                <input type="hidden" name="tabel" value="skill">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" class="btn-act-del">
                  <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                  Hapus
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>


    <?php /* ═══════════════════════════════════════════
           ║  TAB: PENDIDIKAN
           ═══════════════════════════════════════════ */ ?>
    <?php elseif ($tab === 'pendidikan'): ?>

    <div class="admin-section <?= $editRow ? 'edit-active' : '' ?>" style="animation-delay:.05s">
      <div class="admin-section-header">
        <h3>
          <div class="section-icon" style="width:30px;height:30px;font-size:14px;">&#x1F393;</div>
          <?= $editRow ? 'Edit Riwayat Pendidikan' : 'Tambah Riwayat Pendidikan' ?>
        </h3>
        <?php if ($editRow): ?>
        <a href="?tab=pendidikan" class="btn btn-outline" style="font-size:12px;padding:6px 14px;">Batal Edit</a>
        <?php endif; ?>
      </div>
      <div class="admin-section-body">
        <form method="POST">
          <input type="hidden" name="aksi"  value="<?= $editRow ? 'update' : 'simpan' ?>">
          <input type="hidden" name="tabel" value="pendidikan">
          <?php if ($editRow): ?><input type="hidden" name="id" value="<?= $editRow['id'] ?>"><?php endif; ?>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Jenjang *</label>
              <select name="jenjang" class="form-control">
                <?php foreach(['SD','SMP','SMA','SMK','D3','S1','S2','S3'] as $j): ?>
                <option value="<?= $j ?>" <?= ($editRow['jenjang']??'')===$j?'selected':'' ?>><?= $j ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Nama Institusi *</label>
              <input type="text" name="institusi" class="form-control" required
                     value="<?= e($editRow['institusi']??'') ?>" placeholder="cth: Universitas Halu Oleo">
            </div>
            <div class="form-group">
              <label class="form-label">Jurusan / Prodi</label>
              <input type="text" name="jurusan" class="form-control"
                     value="<?= e($editRow['jurusan']??'') ?>" placeholder="Kosongkan jika tidak ada">
            </div>
            <div class="form-group">
              <label class="form-label">Urutan Tampil</label>
              <input type="number" name="urutan" class="form-control" value="<?= e($editRow['urutan']??0) ?>" min="0">
            </div>
            <div class="form-group">
              <label class="form-label">Tahun Masuk *</label>
              <input type="number" name="tahun_masuk" class="form-control" required
                     value="<?= e($editRow['tahun_masuk']??date('Y')) ?>" min="1990" max="2099">
            </div>
            <div class="form-group">
              <label class="form-label">Tahun Lulus <span style="font-weight:400;color:var(--text-muted);">(kosong = masih aktif)</span></label>
              <input type="number" name="tahun_lulus" class="form-control"
                     value="<?= e($editRow['tahun_lulus']??'') ?>" min="1990" max="2099" placeholder="Kosongkan jika masih aktif">
            </div>
            <div class="form-group col-full">
              <label class="form-label">Keterangan</label>
              <input type="text" name="keterangan" class="form-control"
                     value="<?= e($editRow['keterangan']??'') ?>" placeholder="Opsional">
            </div>
          </div>
          <button type="submit" class="btn btn-primary" style="margin-top:8px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <?= $editRow ? 'Update Pendidikan' : 'Tambah Pendidikan' ?>
          </button>
        </form>
      </div>
    </div>

    <div class="admin-section" style="animation-delay:.1s">
      <div class="admin-section-header">
        <h3>
          <div class="section-icon" style="width:30px;height:30px;font-size:14px;">&#x1F4CB;</div>
          Riwayat Pendidikan
          <span class="badge badge-blue"><?= count($pendidikan) ?></span>
        </h3>
      </div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr><th>#</th><th>Jenjang</th><th>Institusi</th><th>Jurusan</th><th>Tahun</th><th>Aksi</th></tr>
          </thead>
          <tbody>
          <?php if(empty($pendidikan)): ?>
            <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">&#x1F393;</div>Belum ada data pendidikan.</div></td></tr>
          <?php else: ?>
          <?php foreach($pendidikan as $row): ?>
          <tr>
            <td style="color:var(--text-muted);"><?= $row['id'] ?></td>
            <td><span class="badge badge-orange"><?= e($row['jenjang']) ?></span></td>
            <td><strong style="color:var(--text-primary);"><?= e($row['institusi']) ?></strong></td>
            <td><?= e($row['jurusan'] ?: '-') ?></td>
            <td style="white-space:nowrap;"><?= $row['tahun_masuk'] ?> &ndash; <?= $row['tahun_lulus'] ?? '<span class="badge badge-green">Aktif</span>' ?></td>
            <td style="white-space:nowrap;">
              <a href="?tab=pendidikan&edit=<?= $row['id'] ?>" class="btn-act-edit">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
              </a>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus data ini?')">
                <input type="hidden" name="aksi" value="hapus">
                <input type="hidden" name="tabel" value="pendidikan">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" class="btn-act-del">
                  <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                  Hapus
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>


    <?php /* ═══════════════════════════════════════════
           ║  TAB: ORGANISASI
           ═══════════════════════════════════════════ */ ?>
    <?php elseif ($tab === 'organisasi'): ?>

    <div class="admin-section <?= $editRow ? 'edit-active' : '' ?>" style="animation-delay:.05s">
      <div class="admin-section-header">
        <h3>
          <div class="section-icon" style="width:30px;height:30px;font-size:14px;">&#x1F465;</div>
          <?= $editRow ? 'Edit Organisasi' : 'Tambah Organisasi' ?>
        </h3>
        <?php if ($editRow): ?>
        <a href="?tab=organisasi" class="btn btn-outline" style="font-size:12px;padding:6px 14px;">Batal Edit</a>
        <?php endif; ?>
      </div>
      <div class="admin-section-body">
        <form method="POST">
          <input type="hidden" name="aksi"  value="<?= $editRow ? 'update' : 'simpan' ?>">
          <input type="hidden" name="tabel" value="organisasi">
          <?php if ($editRow): ?><input type="hidden" name="id" value="<?= $editRow['id'] ?>"><?php endif; ?>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Nama Organisasi *</label>
              <input type="text" name="nama" class="form-control" required
                     value="<?= e($editRow['nama']??'') ?>" placeholder="cth: HIMAKOM UHO">
            </div>
            <div class="form-group">
              <label class="form-label">Jabatan *</label>
              <input type="text" name="jabatan" class="form-control" required
                     value="<?= e($editRow['jabatan']??'') ?>" placeholder="cth: Anggota, Ketua">
            </div>
            <div class="form-group">
              <label class="form-label">Tahun Masuk *</label>
              <input type="number" name="tahun_masuk" class="form-control" required
                     value="<?= e($editRow['tahun_masuk']??date('Y')) ?>" min="2000" max="2099">
            </div>
            <div class="form-group">
              <label class="form-label">Tahun Keluar <span style="font-weight:400;color:var(--text-muted);">(kosong = masih aktif)</span></label>
              <input type="number" name="tahun_keluar" class="form-control"
                     value="<?= e($editRow['tahun_keluar']??'') ?>" min="2000" max="2099" placeholder="Kosongkan jika masih aktif">
            </div>
            <div class="form-group">
              <label class="form-label">Ikon (emoji)</label>
              <input type="text" name="ikon" class="form-control" value="<?= e($editRow['ikon']??'👥') ?>">
            </div>
            <div class="form-group col-full">
              <label class="form-label">Deskripsi</label>
              <textarea name="deskripsi" class="form-control" rows="2"
                        placeholder="Deskripsi singkat kegiatan/peran"><?= e($editRow['deskripsi']??'') ?></textarea>
            </div>
          </div>
          <button type="submit" class="btn btn-primary" style="margin-top:8px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <?= $editRow ? 'Update Organisasi' : 'Tambah Organisasi' ?>
          </button>
        </form>
      </div>
    </div>

    <div class="admin-section" style="animation-delay:.1s">
      <div class="admin-section-header">
        <h3>
          <div class="section-icon" style="width:30px;height:30px;font-size:14px;">&#x1F4CB;</div>
          Daftar Organisasi
          <span class="badge badge-blue"><?= count($organisasi) ?></span>
        </h3>
      </div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr><th>#</th><th>Ikon</th><th>Nama Organisasi</th><th>Jabatan</th><th>Tahun</th><th>Aksi</th></tr>
          </thead>
          <tbody>
          <?php if(empty($organisasi)): ?>
            <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">&#x1F465;</div>Belum ada data organisasi.</div></td></tr>
          <?php else: ?>
          <?php foreach($organisasi as $row): ?>
          <tr>
            <td style="color:var(--text-muted);"><?= $row['id'] ?></td>
            <td style="font-size:22px;line-height:1;"><?= e($row['ikon']) ?></td>
            <td><strong style="color:var(--text-primary);"><?= e($row['nama']) ?></strong></td>
            <td><?= e($row['jabatan']) ?></td>
            <td style="white-space:nowrap;"><?= $row['tahun_masuk'] ?> &ndash; <?= $row['tahun_keluar'] ?? '<span class="badge badge-green">Aktif</span>' ?></td>
            <td style="white-space:nowrap;">
              <a href="?tab=organisasi&edit=<?= $row['id'] ?>" class="btn-act-edit">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
              </a>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus data ini?')">
                <input type="hidden" name="aksi" value="hapus">
                <input type="hidden" name="tabel" value="organisasi">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" class="btn-act-del">
                  <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                  Hapus
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>


    <?php /* ═══════════════════════════════════════════
           ║  TAB: PROYEK
           ═══════════════════════════════════════════ */ ?>
    <?php elseif ($tab === 'proyek'): ?>

    <div class="admin-section <?= $editRow ? 'edit-active' : '' ?>" style="animation-delay:.05s">
      <div class="admin-section-header">
        <h3>
          <div class="section-icon" style="width:30px;height:30px;font-size:14px;">&#x1F5A5;</div>
          <?= $editRow ? 'Edit Proyek' : 'Tambah Proyek Baru' ?>
        </h3>
        <?php if ($editRow): ?>
        <a href="?tab=proyek" class="btn btn-outline" style="font-size:12px;padding:6px 14px;">Batal Edit</a>
        <?php endif; ?>
      </div>
      <div class="admin-section-body">
        <form method="POST">
          <input type="hidden" name="aksi"  value="<?= $editRow ? 'update' : 'simpan' ?>">
          <input type="hidden" name="tabel" value="proyek">
          <?php if ($editRow): ?><input type="hidden" name="id" value="<?= $editRow['id'] ?>"><?php endif; ?>
          <div class="form-row">
            <div class="form-group col-full">
              <label class="form-label">Judul Proyek *</label>
              <input type="text" name="judul" class="form-control" required
                     value="<?= e($editRow['judul']??'') ?>" placeholder="cth: Website Profil Semantik">
            </div>
            <div class="form-group">
              <label class="form-label">Tahun *</label>
              <input type="number" name="tahun" class="form-control" required
                     value="<?= e($editRow['tahun']??date('Y')) ?>" min="2000" max="2099">
            </div>
            <div class="form-group">
              <label class="form-label">Ikon (emoji)</label>
              <input type="text" name="ikon" class="form-control" value="<?= e($editRow['ikon']??'🗂️') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Link Demo</label>
              <input type="url" name="link_demo" class="form-control"
                     value="<?= e($editRow['link_demo']??'') ?>" placeholder="https://...">
            </div>
            <div class="form-group">
              <label class="form-label">Link GitHub</label>
              <input type="url" name="link_github" class="form-control"
                     value="<?= e($editRow['link_github']??'') ?>" placeholder="https://github.com/...">
            </div>
            <div class="form-group col-full">
              <label class="form-label">Teknologi <span style="font-weight:400;color:var(--text-muted);">(pisahkan dengan koma)</span></label>
              <input type="text" name="teknologi" class="form-control"
                     value="<?= e($editRow['teknologi']??'') ?>" placeholder="PHP, MySQL, Laravel, HTML">
            </div>
            <div class="form-group col-full">
              <label class="form-label">Deskripsi</label>
              <textarea name="deskripsi" class="form-control" rows="3"><?= e($editRow['deskripsi']??'') ?></textarea>
            </div>
            <div class="form-group col-full">
              <label class="check-label">
                <input type="checkbox" name="featured" value="1" <?= ($editRow['featured']??0)?'checked':'' ?>>
                Tampilkan di Dashboard (Featured)
              </label>
            </div>
          </div>
          <button type="submit" class="btn btn-primary" style="margin-top:8px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <?= $editRow ? 'Update Proyek' : 'Tambah Proyek' ?>
          </button>
        </form>
      </div>
    </div>

    <div class="admin-section" style="animation-delay:.1s">
      <div class="admin-section-header">
        <h3>
          <div class="section-icon" style="width:30px;height:30px;font-size:14px;">&#x1F4CB;</div>
          Daftar Proyek
          <span class="badge badge-blue"><?= count($proyek) ?></span>
        </h3>
      </div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr><th>#</th><th>Ikon</th><th>Judul</th><th>Teknologi</th><th>Tahun</th><th>Featured</th><th>Aksi</th></tr>
          </thead>
          <tbody>
          <?php if(empty($proyek)): ?>
            <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">&#x1F5A5;</div>Belum ada data proyek.</div></td></tr>
          <?php else: ?>
          <?php foreach($proyek as $row): ?>
          <tr>
            <td style="color:var(--text-muted);"><?= $row['id'] ?></td>
            <td style="font-size:22px;line-height:1;"><?= e($row['ikon']) ?></td>
            <td><strong style="color:var(--text-primary);"><?= e($row['judul']) ?></strong></td>
            <td>
              <?php foreach(array_slice(parseTags($row['teknologi']??''),0,3) as $tag): ?>
              <span class="badge badge-blue" style="font-size:10.5px;margin-bottom:2px;"><?= e($tag) ?></span>
              <?php endforeach; ?>
            </td>
            <td><?= $row['tahun'] ?></td>
            <td>
              <?= $row['featured']
                ? '<span class="badge badge-green">&#x2B50; Ya</span>'
                : '<span style="color:var(--text-muted);font-size:12px;">Tidak</span>' ?>
            </td>
            <td style="white-space:nowrap;">
              <a href="?tab=proyek&edit=<?= $row['id'] ?>" class="btn-act-edit">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
              </a>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus proyek ini?')">
                <input type="hidden" name="aksi" value="hapus">
                <input type="hidden" name="tabel" value="proyek">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" class="btn-act-del">
                  <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                  Hapus
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php endif; ?>

  </main>
</div>

<!-- Sidebar overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
// Sidebar mobile toggle
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');
const menuBtn = document.getElementById('menuBtn');
if (menuBtn) {
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
}

// Level range slider
const levelRange = document.getElementById('levelRange');
const levelDisplay = document.getElementById('levelDisplay');
if (levelRange && levelDisplay) {
  levelRange.addEventListener('input', () => {
    levelDisplay.textContent = levelRange.value;
  });
}
</script>
</body>
</html>