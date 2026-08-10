<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login.php'); exit; }

require_once __DIR__ . '/../models/ShuModel.php';
$sm   = new ShuModel();
$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

function flash(string $tipe, string $msg): void
{
    $_SESSION['flash'] = ['type' => $tipe, 'msg' => $msg];
}

$kembali = 'Location: ../index.php?mod=shu&act=list';

try {
    switch ($aksi) {
        case 'simpan':
            $pendapatan = (float)str_replace('.', '', $_POST['total_pendapatan'] ?? '0');
            $beban      = (float)str_replace('.', '', $_POST['total_beban'] ?? '0');
            $id = $sm->simpan((int)($_POST['tahun'] ?? date('Y')), $pendapatan, $beban, $_SESSION['user']['id_user']);
            flash('success', 'Perhitungan SHU berhasil disimpan (status draft).');
            $kembali = 'Location: ../index.php?mod=shu&act=detail&id=' . $id;
            break;

        case 'tetapkan':
            $sm->tetapkan((int)($_GET['id'] ?? 0));
            flash('success', 'SHU ditetapkan.');
            $kembali = 'Location: ../index.php?mod=shu&act=detail&id=' . (int)($_GET['id'] ?? 0);
            break;

        case 'terima':
            $sm->tandaiTerima((int)($_GET['id'] ?? 0));
            flash('success', 'Bagian SHU anggota ditandai diterima.');
            if (isset($_GET['back'])) {
                $kembali = 'Location: ../index.php?mod=shu&act=detail&id=' . (int)$_GET['back'];
            }
            break;

        case 'hapus':
            $sm->hapus((int)($_GET['id'] ?? 0));
            flash('success', 'Data SHU dihapus.');
            break;
    }
} catch (Exception $e) {
    flash('danger', 'Gagal: ' . $e->getMessage());
}

header($kembali);
exit;