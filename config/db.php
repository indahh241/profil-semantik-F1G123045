<?php
// config/db.php — Koneksi Database & Konfigurasi Global

// ── Database ──────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'semantic_profile');
define('DB_USER', 'root');
define('DB_PASS', '');         

// ── App ───────────────────────────────────────────────
define('APP_NAME', 'Semantic Profile');
define('APP_URL',  'http://localhost/profil-semantik'); // sesuaikan

// ── Session ───────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Koneksi PDO ───────────────────────────────────────
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        'error' => true,
        'message' => 'Koneksi database gagal: ' . $e->getMessage()
    ]));
}

// ── Helper Functions ──────────────────────────────────

/**
 * Sanitasi output ke HTML
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Ambil satu baris dari tabel
 */
function dbRow(PDO $pdo, string $sql, array $params = []): ?array {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Ambil banyak baris dari tabel
 */
function dbRows(PDO $pdo, string $sql, array $params = []): array {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Hitung jumlah baris
 */
function dbCount(PDO $pdo, string $table, string $where = '1', array $params = []): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE $where");
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

/**
 * Redirect
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Cek apakah admin sudah login
 */
function isAdmin(): bool {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Paksa login jika belum (dipakai di semua halaman admin)
 */
function requireAdmin(): void {
    if (!isAdmin()) {
        redirect(APP_URL . '/admin/login.php');
    }
}

/**
 * Flash message (set)
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Flash message (get & hapus)
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Tampilkan alert flash (HTML)
 */
function showFlash(): void {
    $flash = getFlash();
    if (!$flash) return;
    $color = match($flash['type']) {
        'success' => '#16a34a',
        'error'   => '#dc2626',
        'warning' => '#d97706',
        default   => '#3b5bdb',
    };
    $bg = match($flash['type']) {
        'success' => '#dcfce7',
        'error'   => '#fee2e2',
        'warning' => '#fef3c7',
        default   => '#e8edff',
    };
    echo "<div style='background:{$bg};color:{$color};border:1px solid {$color};
          border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13.5px;font-weight:500;'>
          " . e($flash['message']) . "
          </div>";
}

/**
 * Konversi teknologi string ke array badge
 * "PHP, MySQL, Laravel" → ['PHP', 'MySQL', 'Laravel']
 */
function parseTags(string $str): array {
    return array_filter(array_map('trim', explode(',', $str)));
}