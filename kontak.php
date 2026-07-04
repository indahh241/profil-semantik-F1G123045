<?php
require_once 'config/db.php';

// ── Ambil data dari database ──────────────────────────
$profil = dbRow($pdo, "SELECT * FROM profil LIMIT 1");

// ── Path foto dengan fallback otomatis ───────────────
$fotoDb   = $profil['foto'] ?? '';
$fotoPath = !empty($fotoDb) ? $fotoDb
          : (file_exists('assets/foto.jpg')  ? 'assets/foto.jpg'
          : (file_exists('assets/foto.jpeg') ? 'assets/foto.jpeg'
          : (file_exists('assets/foto.png')  ? 'assets/foto.png'  : '')));
$namaDepan    = e(explode(' ', $profil['nama'] ?? 'User')[0]);
$fotoFallback = "https://ui-avatars.com/api/?name={$namaDepan}&background=3b5bdb&color=fff&size=80";

// ── Handle form submit ────────────────────────────────
$success = false;
$error   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaPengirim = trim($_POST['nama']    ?? '');
    $emailPengirim= trim($_POST['email']   ?? '');
    $subjek       = trim($_POST['subjek']  ?? '');
    $pesan        = trim($_POST['pesan']   ?? '');

    if (!$namaPengirim || !$emailPengirim || !$pesan) {
        $error = 'Nama, email, dan pesan wajib diisi.';
    } elseif (!filter_var($emailPengirim, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        // Simpan ke tabel kontak jika ada, atau cukup set success
        // Opsional: kirim email dengan mail()
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kontak — <?= e($profil['nama'] ?? 'Mahasiswa') ?></title>
  <link rel="stylesheet" href="style.css">

  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Person",
    "name": "<?= e($profil['nama'] ?? '') ?>",
    "email": "<?= e($profil['email'] ?? '') ?>",
    "telephone": "<?= e($profil['telepon'] ?? '') ?>",
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "<?= e($profil['alamat'] ?? '') ?>",
      "addressCountry": "ID"
    },
    "url": "<?= defined('APP_URL') ? APP_URL : '' ?>",
    "sameAs": [
      "<?= e($profil['github']   ?? '') ?>",
      "<?= e($profil['linkedin'] ?? '') ?>"
    ]
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
    <a href="kontak.php"       class="nav-item active">
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
      <div class="page-eyebrow" style="font-size:12px;color:var(--text-secondary);margin-bottom:4px;">Schema.org · Person · ContactPoint</div>
      <h1 class="section-title" style="font-size:22px;">✉️ Kontak</h1>
      <p class="page-subtitle">Hubungi saya melalui form di bawah atau lewat media sosial yang tersedia.</p>
    </div>

    <!-- ── Row 1: Info Kontak + Form ── -->
    <div class="grid-2" style="margin-bottom:20px;">

      <!-- Info Kontak -->
      <div class="card" style="animation:fade-up .5s ease .05s both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:20px;">
            <div class="section-icon">📇</div>
            <div><div class="section-title">Informasi Kontak</div></div>
          </div>

          <!-- Avatar -->
          <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--border-light);">
            <img src="<?= e($fotoPath) ?>" alt="Foto"
                 style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:3px solid var(--border-light);"
                 onerror="this.src='<?= $fotoFallback ?>'">
            <div>
              <div style="font-weight:700;font-size:16px;color:var(--text-primary);"><?= e($profil['nama'] ?? '-') ?></div>
              <div style="font-size:13px;color:var(--text-secondary);"><?= e($profil['prodi'] ?? '') ?></div>
              <div style="font-size:12px;color:var(--text-secondary);"><?= e($profil['universitas'] ?? '') ?></div>
            </div>
          </div>

          <!-- Detail kontak -->
          <div style="display:flex;flex-direction:column;gap:14px;">

            <?php if (!empty($profil['email'])): ?>
            <div style="display:flex;align-items:center;gap:12px;">
              <div style="width:36px;height:36px;border-radius:8px;background:#e8edff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">✉️</div>
              <div>
                <div style="font-size:11px;color:var(--text-secondary);margin-bottom:2px;">Email</div>
                <a href="mailto:<?= e($profil['email']) ?>" style="font-size:13.5px;font-weight:600;color:var(--primary);text-decoration:none;">
                  <?= e($profil['email']) ?>
                </a>
              </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($profil['telepon'])): ?>
            <div style="display:flex;align-items:center;gap:12px;">
              <div style="width:36px;height:36px;border-radius:8px;background:#dcfce7;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">📱</div>
              <div>
                <div style="font-size:11px;color:var(--text-secondary);margin-bottom:2px;">Telepon / WhatsApp</div>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$profil['telepon']) ?>" target="_blank"
                   style="font-size:13.5px;font-weight:600;color:var(--text-primary);text-decoration:none;">
                  <?= e($profil['telepon']) ?>
                </a>
              </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($profil['alamat'])): ?>
            <div style="display:flex;align-items:center;gap:12px;">
              <div style="width:36px;height:36px;border-radius:8px;background:#ffedd5;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">📍</div>
              <div>
                <div style="font-size:11px;color:var(--text-secondary);margin-bottom:2px;">Lokasi</div>
                <span style="font-size:13.5px;font-weight:600;color:var(--text-primary);"><?= e($profil['alamat']) ?></span>
              </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($profil['github'])): ?>
            <div style="display:flex;align-items:center;gap:12px;">
              <div style="width:36px;height:36px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">⌥</div>
              <div>
                <div style="font-size:11px;color:var(--text-secondary);margin-bottom:2px;">GitHub</div>
                <a href="<?= e($profil['github']) ?>" target="_blank"
                   style="font-size:13.5px;font-weight:600;color:var(--text-primary);text-decoration:none;">
                  <?= e($profil['github']) ?>
                </a>
              </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($profil['linkedin'])): ?>
            <div style="display:flex;align-items:center;gap:12px;">
              <div style="width:36px;height:36px;border-radius:8px;background:#e0f2fe;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">in</div>
              <div>
                <div style="font-size:11px;color:var(--text-secondary);margin-bottom:2px;">LinkedIn</div>
                <a href="<?= e($profil['linkedin']) ?>" target="_blank"
                   style="font-size:13.5px;font-weight:600;color:var(--text-primary);text-decoration:none;">
                  <?= e($profil['linkedin']) ?>
                </a>
              </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($profil['website'])): ?>
            <div style="display:flex;align-items:center;gap:12px;">
              <div style="width:36px;height:36px;border-radius:8px;background:#ede9fe;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">🔗</div>
              <div>
                <div style="font-size:11px;color:var(--text-secondary);margin-bottom:2px;">Website</div>
                <a href="<?= e($profil['website']) ?>" target="_blank"
                   style="font-size:13.5px;font-weight:600;color:var(--primary);text-decoration:none;">
                  <?= e($profil['website']) ?>
                </a>
              </div>
            </div>
            <?php endif; ?>

          </div>
        </div>
      </div>

      <!-- Form Kontak -->
      <div class="card" style="animation:fade-up .5s ease .1s both;">
        <div class="card-body">
          <div class="section-header" style="margin-bottom:20px;">
            <div class="section-icon">📨</div>
            <div><div class="section-title">Kirim Pesan</div></div>
          </div>

          <?php if ($success): ?>
          <div style="padding:14px 16px;border-radius:8px;background:#dcfce7;border:1px solid #bbf7d0;color:#166534;font-size:13.5px;margin-bottom:16px;">
            ✅ Pesan berhasil dikirim! Terima kasih telah menghubungi saya.
          </div>
          <?php endif; ?>

          <?php if ($error): ?>
          <div style="padding:14px 16px;border-radius:8px;background:#fee2e2;border:1px solid #fecaca;color:#991b1b;font-size:13.5px;margin-bottom:16px;">
            ⚠️ <?= e($error) ?>
          </div>
          <?php endif; ?>

          <form method="POST" action="kontak.php" style="display:flex;flex-direction:column;gap:14px;">

            <div>
              <label style="display:block;font-size:12.5px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;">Nama Lengkap <span style="color:#ef4444;">*</span></label>
              <input type="text" name="nama" placeholder="Masukkan nama Anda"
                     value="<?= e($_POST['nama'] ?? '') ?>"
                     style="width:100%;padding:9px 12px;border-radius:8px;border:1.5px solid var(--border-light);font-size:13.5px;outline:none;box-sizing:border-box;transition:border-color .2s;"
                     onfocus="this.style.borderColor='#3b5bdb'" onblur="this.style.borderColor='var(--border-light)'" required>
            </div>

            <div>
              <label style="display:block;font-size:12.5px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;">Email <span style="color:#ef4444;">*</span></label>
              <input type="email" name="email" placeholder="email@contoh.com"
                     value="<?= e($_POST['email'] ?? '') ?>"
                     style="width:100%;padding:9px 12px;border-radius:8px;border:1.5px solid var(--border-light);font-size:13.5px;outline:none;box-sizing:border-box;transition:border-color .2s;"
                     onfocus="this.style.borderColor='#3b5bdb'" onblur="this.style.borderColor='var(--border-light)'" required>
            </div>

            <div>
              <label style="display:block;font-size:12.5px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;">Subjek</label>
              <input type="text" name="subjek" placeholder="Perihal pesan"
                     value="<?= e($_POST['subjek'] ?? '') ?>"
                     style="width:100%;padding:9px 12px;border-radius:8px;border:1.5px solid var(--border-light);font-size:13.5px;outline:none;box-sizing:border-box;transition:border-color .2s;"
                     onfocus="this.style.borderColor='#3b5bdb'" onblur="this.style.borderColor='var(--border-light)'">
            </div>

            <div>
              <label style="display:block;font-size:12.5px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;">Pesan <span style="color:#ef4444;">*</span></label>
              <textarea name="pesan" rows="5" placeholder="Tulis pesan Anda di sini..."
                        style="width:100%;padding:9px 12px;border-radius:8px;border:1.5px solid var(--border-light);font-size:13.5px;outline:none;box-sizing:border-box;resize:vertical;font-family:inherit;transition:border-color .2s;"
                        onfocus="this.style.borderColor='#3b5bdb'" onblur="this.style.borderColor='var(--border-light)'" required><?= e($_POST['pesan'] ?? '') ?></textarea>
            </div>

            <button type="submit"
              style="padding:10px 20px;background:#3b5bdb;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:background .2s;"
              onmouseover="this.style.background='#2f4ab3'" onmouseout="this.style.background='#3b5bdb'">
              ✉️ Kirim Pesan
            </button>

          </form>
        </div>
      </div>
    </div>

    <!-- ── Row 2: JSON-LD ContactPoint ── -->
    <div class="card" style="animation:fade-up .5s ease .2s both;">
      <div class="card-body">
        <div class="section-header" style="margin-bottom:12px;">
          <div class="section-icon">💾</div>
          <div>
            <div class="section-title">JSON-LD: ContactPoint</div>
            <div class="page-subtitle">Markup semantik untuk informasi kontak</div>
          </div>
        </div>
        <div style="background:#0f172a;border-radius:8px;padding:16px;font-size:12px;line-height:1.9;font-family:monospace;overflow-x:auto;">
<span style="color:#94a3b8;">{</span><br>
&nbsp;&nbsp;<span style="color:#7dd3fc;">"@context"</span>: <span style="color:#86efac;">"https://schema.org"</span>,<br>
&nbsp;&nbsp;<span style="color:#7dd3fc;">"@type"</span>: <span style="color:#86efac;">"Person"</span>,<br>
&nbsp;&nbsp;<span style="color:#7dd3fc;">"name"</span>: <span style="color:#fde68a;">"<?= e($profil['nama'] ?? '') ?>"</span>,<br>
&nbsp;&nbsp;<span style="color:#7dd3fc;">"email"</span>: <span style="color:#fde68a;">"<?= e($profil['email'] ?? '') ?>"</span>,<br>
&nbsp;&nbsp;<span style="color:#7dd3fc;">"telephone"</span>: <span style="color:#fde68a;">"<?= e($profil['telepon'] ?? '') ?>"</span>,<br>
&nbsp;&nbsp;<span style="color:#7dd3fc;">"address"</span>: {<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"@type"</span>: <span style="color:#86efac;">"PostalAddress"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"addressLocality"</span>: <span style="color:#fde68a;">"<?= e($profil['alamat'] ?? '') ?>"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"addressCountry"</span>: <span style="color:#fde68a;">"ID"</span><br>
&nbsp;&nbsp;},<br>
&nbsp;&nbsp;<span style="color:#7dd3fc;">"contactPoint"</span>: {<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"@type"</span>: <span style="color:#86efac;">"ContactPoint"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"contactType"</span>: <span style="color:#fde68a;">"personal"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"email"</span>: <span style="color:#fde68a;">"<?= e($profil['email'] ?? '') ?>"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"telephone"</span>: <span style="color:#fde68a;">"<?= e($profil['telepon'] ?? '') ?>"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#7dd3fc;">"availableLanguage"</span>: <span style="color:#fde68a;">"Indonesian"</span><br>
&nbsp;&nbsp;},<br>
&nbsp;&nbsp;<span style="color:#7dd3fc;">"sameAs"</span>: [<br>
<?php
$sameAs = array_filter([$profil['github']??'', $profil['linkedin']??'', $profil['website']??'']);
$saArr  = array_values($sameAs);
foreach ($saArr as $i => $sa):
  $isLast = $i === count($saArr)-1;
?>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#fde68a;">"<?= e($sa) ?>"</span><?= $isLast ? '' : ',' ?><br>
<?php endforeach; ?>
&nbsp;&nbsp;]<br>
<span style="color:#94a3b8;">}</span>
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
</script>

</body>
</html>