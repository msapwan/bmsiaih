<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) { header('Location: ../../login.php'); exit; }

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PengaturanModel.php';
require_once __DIR__ . '/../../models/NotifikasiModel.php';

$user      = $_SESSION['user'];
$pageTitle = $pageTitle ?? 'Dashboard';
$active    = $active ?? 'dashboard';
$profil    = (new PengaturanModel())->profil();

if (!function_exists('rupiah')) {
    function rupiah($n) { return 'Rp ' . number_format((float)($n ?: 0), 0, ',', '.'); }
}
if (!function_exists('e')) {
    function e($s) { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('badge_status')) {
    function badge_status(string $s): string {
        $map = ['pengajuan'=>'warning','berjalan'=>'primary','lunas'=>'success','ditolak'=>'danger',
                'aktif'=>'success','nonaktif'=>'secondary','belum'=>'secondary'];
        $c = $map[$s] ?? 'secondary';
        return '<span class="badge bg-' . $c . '">' . ucfirst($s) . '</span>';
    }
}

$ntf          = new NotifikasiModel();
$ntfTerlambat = $ntf->terlambat();
$ntfAkan      = $ntf->akanJatuhTempo();
$ntfTotal     = count($ntfTerlambat) + count($ntfAkan);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | <?= e($profil['nama_koperasi'] ?? 'Koperasi Syariah') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="wrapper">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <i class="fas fa-mosque"></i>
      <span><?= e($profil['nama_koperasi'] ?? 'KSU Syariah') ?></span>
    </div>
    <nav>
      <a href="index.php?mod=dashboard" class="<?= $active==='dashboard'?'on':'' ?>">
        <i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <a href="index.php?mod=anggota&act=list" class="<?= $active==='anggota'?'on':'' ?>">
        <i class="fas fa-users"></i> Anggota</a>
      <a href="index.php?mod=simpanan&act=list" class="<?= $active==='simpanan'?'on':'' ?>">
        <i class="fas fa-piggy-bank"></i> Simpanan</a>

      <div class="menu-label">Pembiayaan</div>
      <a href="index.php?mod=pembiayaan&act=list" class="<?= $active==='pembiayaan'?'on':'' ?>">
        <i class="fas fa-hand-holding-usd"></i> Data Pembiayaan</a>
      <a href="index.php?mod=pembiayaan&act=pengajuan" class="<?= $active==='pembiayaan-pengajuan'?'on':'' ?>">
        <i class="fas fa-file-signature"></i> Pengajuan Baru</a>
      <a href="index.php?mod=pembiayaan&act=angsuran" class="<?= $active==='pembiayaan-angsuran'?'on':'' ?>">
        <i class="fas fa-money-bill-wave"></i> Bayar Angsuran</a>
      <a href="index.php?mod=denda&act=list" class="<?= $active==='denda'?'on':'' ?>">
        <i class="fas fa-gavel"></i> Denda Ta'zir</a>

      <div class="menu-label">Akuntansi</div>
      <a href="index.php?mod=jurnal&act=list" class="<?= $active==='jurnal'?'on':'' ?>">
        <i class="fas fa-book"></i> Jurnal Umum</a>
      <a href="index.php?mod=jurnal&act=saldo_awal" class="<?= $active==='jurnal-saldo-awal'?'on':'' ?>">
        <i class="fas fa-flag-checkered"></i> Saldo Awal</a>
      <a href="index.php?mod=jurnal&act=buku_besar" class="<?= $active==='jurnal-buku-besar'?'on':'' ?>">
        <i class="fas fa-list-ol"></i> Buku Besar</a>
      <a href="index.php?mod=shu&act=list" class="<?= $active==='shu'?'on':'' ?>">
        <i class="fas fa-chart-pie"></i> SHU</a>

      <div class="menu-label">Lainnya</div>
      <a href="index.php?mod=laporan" class="<?= $active==='laporan'?'on':'' ?>">
        <i class="fas fa-chart-line"></i> Laporan</a>
      <a href="index.php?mod=notifikasi&act=kirim" class="<?= $active==='notifikasi-kirim'?'on':'' ?>">
        <i class="fab fa-whatsapp"></i> Kirim Notifikasi</a>

      <?php if ($user['level'] === 'admin'): ?>
      <div class="menu-label">Pengaturan</div>
      <a href="index.php?mod=pengaturan&act=profil" class="<?= $active==='pengaturan-profil'?'on':'' ?>">
        <i class="fas fa-building"></i> Profil Koperasi</a>
      <a href="index.php?mod=pengaturan&act=parameter" class="<?= $active==='pengaturan-parameter'?'on':'' ?>">
        <i class="fas fa-sliders-h"></i> Parameter</a>
      <a href="index.php?mod=pengaturan&act=jenis_akad" class="<?= $active==='pengaturan-akad'?'on':'' ?>">
        <i class="fas fa-handshake"></i> Jenis Akad</a>
      <a href="index.php?mod=pengaturan&act=akun_user" class="<?= $active==='pengaturan-user'?'on':'' ?>">
        <i class="fas fa-user-cog"></i> Akun User</a>
      <?php endif; ?>
    </nav>
  </aside>

  <!-- MAIN -->
  <div class="main">
    <nav class="topbar">
      <button class="btn btn-link text-white d-md-none" id="btnSidebar"><i class="fas fa-bars"></i></button>
      <div class="topbar-title"><?= e($pageTitle) ?></div>

      <!-- LONCENG NOTIFIKASI -->
      <div class="dropdown ms-auto me-2">
        <button class="btn btn-link text-white position-relative" data-bs-toggle="dropdown">
          <i class="fas fa-bell"></i>
          <?php if ($ntfTotal > 0): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $ntfTotal ?></span>
          <?php endif; ?>
        </button>
        <div class="dropdown-menu dropdown-menu-end notif-dropdown">
          <h6 class="dropdown-header">Notifikasi Jatuh Tempo</h6>
          <?php foreach (array_slice($ntfTerlambat, 0, 4) as $n): ?>
          <a class="dropdown-item small text-wrap" href="index.php?mod=pembiayaan&act=angsuran&id=<?= (int)$n['id_pembiayaan'] ?>">
            <span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>TERLAMBAT</span>
            — <?= e($n['nama']) ?>, angsuran #<?= (int)$n['angsuran_ke'] ?>
            (<?= e(date('d/m/Y', strtotime($n['tanggal_jatuh_tempo']))) ?>) <?= rupiah($n['total']) ?>
          </a>
          <?php endforeach; ?>
          <?php foreach (array_slice($ntfAkan, 0, 4) as $n):
              $sisaHari = max(0, (int)round((strtotime($n['tanggal_jatuh_tempo']) - strtotime('today')) / 86400)); ?>
          <a class="dropdown-item small text-wrap" href="index.php?mod=pembiayaan&act=angsuran&id=<?= (int)$n['id_pembiayaan'] ?>">
            <span class="text-warning"><i class="fas fa-clock me-1"></i>H-<?= $sisaHari ?></span>
            — <?= e($n['nama']) ?>, angsuran #<?= (int)$n['angsuran_ke'] ?> <?= rupiah($n['total']) ?>
          </a>
          <?php endforeach; ?>
          <?php if ($ntfTotal === 0): ?>
          <span class="dropdown-item-text small text-muted">Tidak ada notifikasi.</span>
          <?php endif; ?>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item small text-center" href="index.php?mod=notifikasi&act=kirim">Kirim notifikasi WA/Email</a>
        </div>
      </div>

      <div class="dropdown">
        <button class="btn btn-link text-white dropdown-toggle text-decoration-none" data-bs-toggle="dropdown">
          <i class="fas fa-user-circle me-1"></i> <?= e($user['nama_lengkap']) ?>
          <span class="badge bg-light text-dark ms-1"><?= e($user['level']) ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><span class="dropdown-item-text small text-muted">Login sebagai<br><b><?= e($user['username']) ?></b></span></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Keluar</a></li>
        </ul>
      </div>
    </nav>

    <main class="content">
      <?php if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>