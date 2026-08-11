<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$mod = $_GET['mod'] ?? 'dashboard';
$act = $_GET['act'] ?? 'index';

$routes = [
    'dashboard'  => ['index' => __DIR__ . '/views/dashboard.php'],
    'anggota'    => [
        'list'           => __DIR__ . '/views/anggota/list.php',
        'form'           => __DIR__ . '/views/anggota/form.php',
        'detail'         => __DIR__ . '/views/anggota/detail.php',
        'rekening_koran' => __DIR__ . '/views/anggota/rekening_koran.php',
    ],
    'simpanan'   => [
        'list' => __DIR__ . '/views/simpanan/list.php',
        'form' => __DIR__ . '/views/simpanan/form.php',
    ],
    'pembiayaan' => [
        'list'      => __DIR__ . '/views/pembiayaan/list.php',
        'pengajuan' => __DIR__ . '/views/pembiayaan/pengajuan.php',
        'detail'    => __DIR__ . '/views/pembiayaan/detail.php',
        'angsuran'  => __DIR__ . '/views/pembiayaan/angsuran.php',
    ],
    'denda'      => ['list' => __DIR__ . '/views/denda/list.php'],
    'jurnal'     => [
        'list'       => __DIR__ . '/views/jurnal/list.php',
        'form'       => __DIR__ . '/views/jurnal/form.php',
        'saldo_awal' => __DIR__ . '/views/jurnal/saldo_awal.php',
        'buku_besar' => __DIR__ . '/views/jurnal/buku_besar.php',
    ],
    'shu'        => [
        'list'   => __DIR__ . '/views/shu/list.php',
        'form'   => __DIR__ . '/views/shu/form.php',
        'detail' => __DIR__ . '/views/shu/detail.php',
    ],
    'notifikasi' => [
        'list'  => __DIR__ . '/views/notifikasi/list.php',
        'kirim' => __DIR__ . '/views/notifikasi/kirim.php',
    ],
    'laporan'    => [
        'index'     => __DIR__ . '/views/laporan/index.php',
        'laba_rugi' => __DIR__ . '/views/laporan/laba_rugi.php',
        'neraca'    => __DIR__ . '/views/laporan/neraca.php',
        'arus_kas'  => __DIR__ . '/views/laporan/arus_kas.php',
        'phu'       => __DIR__ . '/views/laporan/phu.php',
    ],
    'pengaturan' => [
        'profil'     => __DIR__ . '/views/pengaturan/profil.php',
        'parameter'  => __DIR__ . '/views/pengaturan/parameter.php',
        'jenis_akad' => __DIR__ . '/views/pengaturan/jenis_akad.php',
        'akun_user'  => __DIR__ . '/views/pengaturan/akun_user.php',
        'backup'     => __DIR__ . '/views/pengaturan/backup.php',
    ],
];

if ($mod === 'pengaturan' && $_SESSION['user']['level'] !== 'admin') {
    $mod = '_404';
}

if (isset($routes[$mod][$act]) && file_exists($routes[$mod][$act])) {
    require $routes[$mod][$act];
} else {
    http_response_code(404);
    require __DIR__ . '/views/404.php';
}