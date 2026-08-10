<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login.php'); exit; }

require_once __DIR__ . '/../models/LaporanModel.php';
$aksi = $_GET['aksi'] ?? '';

if ($aksi === 'export_transaksi') {
    $rows = (new LaporanModel())->transaksiSimpanan($_GET['dari'] ?? '', $_GET['sampai'] ?? '');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=transaksi_simpanan_' . date('Ymd_His') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Tanggal', 'Kode', 'Nama Anggota', 'Jenis', 'Tipe', 'Jumlah', 'Keterangan']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['tanggal'], $r['kode_anggota'], $r['nama'],
            $r['jenis_simpanan'], $r['tipe'], $r['jumlah'], $r['keterangan'],
        ]);
    }
    fclose($out);
    exit;
}

header('Location: ../index.php?mod=laporan');
exit;