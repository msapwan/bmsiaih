<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login.php'); exit; }

require_once __DIR__ . '/../models/JurnalModel.php';
$jm   = new JurnalModel();
$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

function flash(string $tipe, string $msg): void
{
    $_SESSION['flash'] = ['type' => $tipe, 'msg' => $msg];
}

$kembali = 'Location: ../index.php?mod=jurnal&act=list';

try {
    switch ($aksi) {
        case 'simpan':
            $details = [];
            foreach ($_POST['id_akun'] ?? [] as $i => $idAkun) {
                $idAkun = (int)$idAkun;
                $jumlah = (float)str_replace('.', '', $_POST['jumlah'][$i] ?? '0');
                $posisi = $_POST['posisi'][$i] ?? 'debit';
                if ($idAkun > 0 && $jumlah > 0) {
                    $details[] = ['id_akun' => $idAkun, 'posisi' => $posisi, 'jumlah' => $jumlah];
                }
            }
            if (count($details) < 2) throw new Exception('Jurnal minimal terdiri dari 2 baris.');
            $jm->buat($_POST['tanggal'] ?: date('Y-m-d'), trim($_POST['keterangan'] ?? ''), '', 'manual', $details);
            flash('success', 'Jurnal manual berhasil dicatat.');
            break;

        case 'simpan_saldo_awal':
            $nilai = [];
            foreach ($_POST['nilai'] ?? [] as $kode => $v) {
                $v = (float)str_replace('.', '', $v);
                if ($v > 0) $nilai[$kode] = $v;
            }
            $jm->simpanSaldoAwal($_POST['tanggal'] ?: date('Y-m-d'), $nilai);
            flash('success', 'Saldo awal berhasil disimpan (jurnal lama diganti).');
            $kembali = 'Location: ../index.php?mod=jurnal&act=saldo_awal';
            break;

        case 'hapus':
            $jm->hapus((int)($_GET['id'] ?? 0));
            flash('success', 'Jurnal dihapus.');
            break;
    }
} catch (Exception $e) {
    flash('danger', 'Gagal: ' . $e->getMessage());
}

header($kembali);
exit;