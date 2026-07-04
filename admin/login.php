<?php
require_once '../config/db.php';

// ── Hardcoded credentials (tanpa database) ────────────
define('ADMIN_USERNAME', 'INDAH');
define('ADMIN_PASSWORD', 'F1G123045');

// ── Redirect jika sudah login ─────────────────────────
if (isAdmin()) {
    redirect(APP_URL . '/admin/index.php');
}

// ── Handle login ──────────────────────────────────────
$error = '';
$shakeForm = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username']  = $username;
        redirect(APP_URL . '/admin/index.php');
    } else {
        $error     = 'Username atau password salah. Silakan coba lagi.';
        $shakeForm = true;
    }
}

// ── Ambil nama dari profil untuk greeting ─────────────
$profil    = dbRow($pdo, "SELECT nama, foto FROM profil LIMIT 1");
$namaDepan = e(explode(' ', $profil['nama'] ?? 'Admin')[0]);
$fotoDb    = $profil['foto'] ?? '';
$fotoPath  = !empty($fotoDb) ? '../' . $fotoDb
           : (file_exists('../assets/foto.jpg')  ? '../assets/foto.jpg'
           : (file_exists('../assets/foto.jpeg') ? '../assets/foto.jpeg'
           : (file_exists('../assets/foto.png')  ? '../assets/foto.png' : '')));
$fotoFallback = "https://ui-avatars.com/api/?name={$namaDepan}&background=3b5bdb&color=fff&size=80";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin — Semantic Profile</title>
  <link rel="stylesheet" href="../style.css">
  <style>
    /* ── Full-page layout ── */
    body {
      display: flex;
      min-height: 100vh;
      background: var(--bg-page);
      overflow: hidden;
    }

    /* ── Left panel (sidebar visual) ── */
    .login-left {
      width: 420px;
      flex-shrink: 0;
      background: var(--sidebar-bg);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 48px 40px;
      position: relative;
      overflow: hidden;
    }

    /* Decorative blobs */
    .login-left::before {
      content: '';
      position: absolute;
      width: 320px; height: 320px;
      background: rgba(59,91,219,.18);
      border-radius: 50%;
      top: -80px; left: -80px;
      pointer-events: none;
    }
    .login-left::after {
      content: '';
      position: absolute;
      width: 240px; height: 240px;
      background: rgba(124,58,237,.15);
      border-radius: 50%;
      bottom: -60px; right: -60px;
      pointer-events: none;
    }

    .login-brand {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 48px;
      position: relative;
      z-index: 1;
    }

    .login-brand-icon {
      width: 52px; height: 52px;
      background: var(--sidebar-active);
      border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      font-size: 26px;
      box-shadow: 0 0 0 8px rgba(59,91,219,.18);
      animation: pulse-brand 3s ease-in-out infinite;
    }

    .login-brand-text h2 {
      font-family: 'Poppins', sans-serif;
      font-size: 17px; font-weight: 700;
      color: #fff; line-height: 1.2;
    }
    .login-brand-text span { font-size: 12px; color: var(--sidebar-text); }

    /* Avatar profil di panel kiri */
    .login-avatar-wrap {
      position: relative;
      margin-bottom: 24px;
      z-index: 1;
    }
    .login-avatar {
      width: 110px; height: 110px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid var(--sidebar-active);
      box-shadow: 0 0 0 8px rgba(59,91,219,.2), 0 8px 32px rgba(0,0,0,.3);
      transition: transform .3s ease;
    }
    .login-avatar:hover { transform: scale(1.04); }
    .login-avatar-status {
      position: absolute;
      bottom: 6px; right: 6px;
      width: 18px; height: 18px;
      background: #22c55e;
      border-radius: 50%;
      border: 3px solid var(--sidebar-bg);
    }

    .login-welcome {
      text-align: center;
      z-index: 1;
      position: relative;
    }
    .login-welcome h3 {
      font-family: 'Poppins', sans-serif;
      font-size: 20px; font-weight: 700;
      color: #fff; margin-bottom: 6px;
    }
    .login-welcome p {
      font-size: 13px;
      color: var(--sidebar-text);
      line-height: 1.6;
      max-width: 280px;
      margin: 0 auto 32px;
    }

    /* Info pills */
    .login-pills {
      display: flex; flex-direction: column; gap: 10px;
      width: 100%; z-index: 1; position: relative;
    }
    .login-pill {
      display: flex; align-items: center; gap: 12px;
      background: rgba(255,255,255,.06);
      border: 1px solid rgba(255,255,255,.08);
      border-radius: 10px;
      padding: 12px 16px;
      transition: background .2s;
    }
    .login-pill:hover { background: rgba(255,255,255,.1); }
    .login-pill-icon { font-size: 18px; flex-shrink: 0; }
    .login-pill-label { font-size: 10px; color: var(--sidebar-text); font-weight: 600; text-transform: uppercase; letter-spacing: .06em; }
    .login-pill-value { font-size: 13px; color: #fff; font-weight: 600; }

    /* Quote footer */
    .login-quote {
      position: absolute;
      bottom: 24px; left: 40px; right: 40px;
      text-align: center;
      z-index: 1;
      font-size: 11.5px;
      font-style: italic;
      color: var(--sidebar-text);
      opacity: .7;
      line-height: 1.6;
    }

    /* ── Right panel (form) ── */
    .login-right {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 32px;
    }

    .login-box {
      width: 100%;
      max-width: 420px;
      animation: fade-up .5s ease both;
    }

    .login-box-header {
      margin-bottom: 32px;
    }
    .login-box-eyebrow {
      font-size: 12px;
      font-weight: 600;
      color: var(--blue);
      text-transform: uppercase;
      letter-spacing: .08em;
      margin-bottom: 6px;
    }
    .login-box-title {
      font-family: 'Poppins', sans-serif;
      font-size: 28px; font-weight: 800;
      color: var(--text-primary);
      line-height: 1.2;
      margin-bottom: 8px;
    }
    .login-box-subtitle {
      font-size: 13.5px;
      color: var(--text-muted);
    }

    /* Form */
    .login-form-card {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 32px;
      box-shadow: var(--shadow-md);
    }

    /* Input dengan icon */
    .input-group {
      position: relative;
      margin-bottom: 18px;
    }
    .input-group-icon {
      position: absolute;
      left: 14px;
      top: 50%; transform: translateY(-50%);
      color: var(--text-muted);
      pointer-events: none;
      transition: color .2s;
    }
    .input-group input {
      width: 100%;
      padding: 12px 14px 12px 42px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-sm);
      font-size: 13.5px;
      font-family: inherit;
      color: var(--text-primary);
      background: var(--bg-page);
      outline: none;
      transition: border-color .2s, box-shadow .2s, background .2s;
    }
    .input-group input:focus {
      border-color: var(--blue);
      background: #fff;
      box-shadow: 0 0 0 3px rgba(59,91,219,.1);
    }
    .input-group input:focus + .input-group-icon,
    .input-group:focus-within .input-group-icon { color: var(--blue); }
    .input-group input::placeholder { color: var(--text-muted); }

    /* Toggle password */
    .toggle-pw {
      position: absolute;
      right: 13px;
      top: 50%; transform: translateY(-50%);
      background: none; border: none;
      cursor: pointer;
      color: var(--text-muted);
      padding: 4px;
      border-radius: 4px;
      transition: color .2s;
      display: flex; align-items: center;
    }
    .toggle-pw:hover { color: var(--blue); }

    /* Error alert */
    .login-error {
      display: flex;
      align-items: center;
      gap: 10px;
      background: #fee2e2;
      border: 1px solid #fca5a5;
      color: #dc2626;
      border-radius: var(--radius-sm);
      padding: 12px 14px;
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 18px;
      animation: shake .4s ease;
    }

    @keyframes shake {
      0%,100% { transform: translateX(0); }
      20%      { transform: translateX(-8px); }
      40%      { transform: translateX(8px); }
      60%      { transform: translateX(-5px); }
      80%      { transform: translateX(5px); }
    }

    /* Login button */
    .btn-login {
      width: 100%;
      padding: 13px;
      background: var(--blue);
      color: #fff;
      border: none;
      border-radius: var(--radius-sm);
      font-size: 14px;
      font-weight: 700;
      font-family: inherit;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 4px 14px rgba(59,91,219,.35);
      transition: all .25s ease;
      position: relative;
      overflow: hidden;
    }
    .btn-login::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255,255,255,.15) 0%, transparent 60%);
      pointer-events: none;
    }
    .btn-login:hover {
      background: #2f4ac8;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(59,91,219,.45);
    }
    .btn-login:active { transform: translateY(0); }

    /* Divider */
    .login-divider {
      display: flex; align-items: center; gap: 12px;
      margin: 20px 0;
      font-size: 12px; color: var(--text-muted);
    }
    .login-divider::before, .login-divider::after {
      content: ''; flex: 1; height: 1px; background: var(--border);
    }

    /* Back link */
    .login-back {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      font-size: 13px;
      color: var(--text-muted);
      text-decoration: none;
      margin-top: 20px;
      transition: color .2s;
    }
    .login-back:hover { color: var(--blue); }

    /* Floating dots decoration */
    .login-dots {
      position: absolute;
      inset: 0;
      pointer-events: none;
      overflow: hidden;
      z-index: 0;
    }
    .dot {
      position: absolute;
      border-radius: 50%;
      opacity: .07;
      animation: float-dot 6s ease-in-out infinite;
    }
    @keyframes float-dot {
      0%,100% { transform: translateY(0) scale(1); }
      50%      { transform: translateY(-20px) scale(1.1); }
    }

    @media (max-width: 768px) {
      .login-left { display: none; }
      body { overflow: auto; }
    }
  </style>
</head>
<body>

  <!-- ===== LEFT PANEL ===== -->
  <div class="login-left">

    <!-- Decorative dots -->
    <div class="login-dots">
      <div class="dot" style="width:120px;height:120px;background:#3b5bdb;top:15%;left:10%;animation-delay:0s;"></div>
      <div class="dot" style="width:80px;height:80px;background:#7c3aed;top:50%;left:60%;animation-delay:1.5s;"></div>
      <div class="dot" style="width:60px;height:60px;background:#0ea5e9;top:75%;left:20%;animation-delay:3s;"></div>
      <div class="dot" style="width:40px;height:40px;background:#16a34a;top:30%;left:75%;animation-delay:2s;"></div>
    </div>

    <!-- Brand -->
    <div class="login-brand">
      <div class="login-brand-icon">🎓</div>
      <div class="login-brand-text">
        <h2>Semantic Profile</h2>
        <span>Panel Admin</span>
      </div>
    </div>

    <!-- Avatar -->
    <div class="login-avatar-wrap">
      <img
        src="<?= e($fotoPath) ?>"
        alt="Foto Profil"
        class="login-avatar"
        onerror="this.src='<?= $fotoFallback ?>'">
      <div class="login-avatar-status"></div>
    </div>

    <!-- Welcome text -->
    <div class="login-welcome">
      <h3>Selamat Datang, <?= $namaDepan ?>! 👋</h3>
      <p>Masuk ke panel admin untuk mengelola data profil semantik Anda.</p>
    </div>

    <!-- Info pills -->
    <div class="login-pills">
      <div class="login-pill">
        <span class="login-pill-icon">👤</span>
        <div>
          <div class="login-pill-label">Nama</div>
          <div class="login-pill-value"><?= e($profil['nama'] ?? 'Admin') ?></div>
        </div>
      </div>
      <div class="login-pill">
        <span class="login-pill-icon">🔗</span>
        <div>
          <div class="login-pill-label">Website</div>
          <div class="login-pill-value">Semantic Profile</div>
        </div>
      </div>
      <div class="login-pill">
        <span class="login-pill-icon">🔒</span>
        <div>
          <div class="login-pill-label">Akses</div>
          <div class="login-pill-value">Admin Only</div>
        </div>
      </div>
    </div>

    <div class="login-quote">
      "Knowledge Connects Everything" — Semantic Web
    </div>
  </div>

  <!-- ===== RIGHT PANEL (FORM) ===== -->
  <div class="login-right">
    <div class="login-box">

      <div class="login-box-header">
        <div class="login-box-eyebrow">🔐 Panel Admin</div>
        <h1 class="login-box-title">Masuk ke<br>Dashboard</h1>
        <p class="login-box-subtitle">Gunakan kredensial admin untuk mengakses panel pengelolaan.</p>
      </div>

      <div class="login-form-card">

        <?php if ($error): ?>
        <div class="login-error">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" flex-shrink="0">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">

          <!-- Username -->
          <div style="margin-bottom:6px;">
            <label class="form-label" for="username">Username</label>
          </div>
          <div class="input-group">
            <svg class="input-group-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
            </svg>
            <input
              type="text"
              id="username"
              name="username"
              placeholder="Masukkan username"
              value="<?= isset($_POST['username']) ? e($_POST['username']) : '' ?>"
              autocomplete="username"
              required
              autofocus>
          </div>

          <!-- Password -->
          <div style="margin-bottom:6px;">
            <label class="form-label" for="password">Password</label>
          </div>
          <div class="input-group" style="margin-bottom:24px;">
            <svg class="input-group-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Masukkan password"
              autocomplete="current-password"
              required>
            <button type="button" class="toggle-pw" id="togglePw" title="Tampilkan/sembunyikan password">
              <svg id="eyeIcon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>

          <button type="submit" class="btn-login">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
              <polyline points="10 17 15 12 10 7"/>
              <line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
            Masuk ke Panel Admin
          </button>

          <div class="login-divider">atau</div>

          <a href="../index.php" class="btn btn-outline" style="width:100%;justify-content:center;text-decoration:none;">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Lihat Website Publik
          </a>

        </form>
      </div>

      <a href="../index.php" class="login-back">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
        Kembali ke halaman utama
      </a>

    </div>
  </div>

<script>
// ── Toggle show/hide password ─────────────────────────
const togglePw  = document.getElementById('togglePw');
const pwInput   = document.getElementById('password');
const eyeIcon   = document.getElementById('eyeIcon');

const eyeOpen   = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
const eyeClosed = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`;

let showing = false;
togglePw.addEventListener('click', () => {
  showing = !showing;
  pwInput.type     = showing ? 'text' : 'password';
  eyeIcon.innerHTML = showing ? eyeClosed : eyeOpen;
  pwInput.focus();
});

// ── Input focus animation ──────────────────────────────
document.querySelectorAll('.input-group input').forEach(inp => {
  const wrap = inp.closest('.input-group');
  inp.addEventListener('focus',  () => wrap.style.transform = 'translateY(-1px)');
  inp.addEventListener('blur',   () => wrap.style.transform = '');
});
</script>
</body>
</html>