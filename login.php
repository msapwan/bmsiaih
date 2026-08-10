<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (isset($_SESSION['user'])) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $st = Database::getInstance()->prepare(
        "SELECT * FROM users WHERE username = ? AND status = 'aktif'"
    );
    $st->execute([$username]);
    $u = $st->fetch();

    if ($u && (password_verify($password, $u['password']) || $password === $u['password'])) {
        $_SESSION['user'] = [
            'id_user'      => $u['id_user'],
            'username'     => $u['username'],
            'nama_lengkap' => $u['nama_lengkap'],
            'level'        => $u['level'],
        ];
        header('Location: index.php');
        exit;
    }
    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login | Koperasi Syariah</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
<div class="login-card">
  <div class="text-center mb-4">
    <div class="login-logo"><i class="fas fa-mosque"></i></div>
    <h4 class="mt-2 mb-0">Koperasi Syariah</h4>
    <small class="text-muted">Sistem Informasi Koperasi Syariah</small>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" autocomplete="off">
    <div class="mb-3">
      <label class="form-label">Username</label>
      <div class="input-group">
        <span class="input-group-text"><i class="fas fa-user"></i></span>
        <input type="text" name="username" class="form-control" required autofocus>
      </div>
    </div>
    <div class="mb-4">
      <label class="form-label">Password</label>
      <div class="input-group">
        <span class="input-group-text"><i class="fas fa-lock"></i></span>
        <input type="password" name="password" class="form-control" required>
      </div>
    </div>
    <button class="btn btn-success w-100"><i class="fas fa-sign-in-alt me-1"></i> Masuk</button>
  </form>
  <div class="text-center small text-muted mt-3">Default: <b>admin</b> / <b>admin123</b></div>
</div>
</body>
</html>