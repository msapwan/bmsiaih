<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['level'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../models/PengaturanModel.php';
$model = new PengaturanModel();
$aksi  = $_POST['aksi'] ?? '';

function flash(string $tipe, string $msg): void
{
    $_SESSION['flash'] = ['type' => $tipe, 'msg' => $msg];
}

$kembali = 'Location: ../index.php?mod=pengaturan&act=profil';

try {
    switch ($aksi) {
        case 'simpan_profil':
            $model->updateProfil([
                'nama_koperasi' => trim($_POST['nama_koperasi'] ?? ''),
                'alamat'        => trim($_POST['alamat'] ?? ''),
                'no_telp'       => trim($_POST['no_telp'] ?? ''),
                'email'         => trim($_POST['email'] ?? ''),
                'nama_ketua'    => trim($_POST['nama_ketua'] ?? ''),
                'slogan'        => trim($_POST['slogan'] ?? ''),
            ]);
            flash('success', 'Profil koperasi berhasil disimpan.');
            break;

        case 'simpan_parameter':
            foreach ($_POST['nilai'] ?? [] as $kunci => $nilai) {
                $model->set($kunci, $nilai);
            }
            flash('success', 'Parameter berhasil disimpan.');
            $kembali = 'Location: ../index.php?mod=pengaturan&act=parameter';
            break;

        case 'simpan_akad':
            $model->akadSimpan([
                'id_akad'    => (int)($_POST['id_akad'] ?? 0),
                'kode_akad'  => strtoupper(trim($_POST['kode_akad'] ?? '')),
                'nama_akad'  => trim($_POST['nama_akad'] ?? ''),
                'tipe_akad'  => $_POST['tipe_akad'] ?? 'margin',
                'keterangan' => trim($_POST['keterangan'] ?? ''),
                'status'     => $_POST['status'] ?? 'aktif',
            ]);
            flash('success', 'Jenis akad berhasil disimpan.');
            $kembali = 'Location: ../index.php?mod=pengaturan&act=jenis_akad';
            break;

        case 'hapus_akad':
            $model->akadHapus((int)($_GET['id'] ?? 0));
            flash('success', 'Jenis akad dihapus.');
            $kembali = 'Location: ../index.php?mod=pengaturan&act=jenis_akad';
            break;

        case 'simpan_user':
            $model->userSimpan([
                'id_user'      => (int)($_POST['id_user'] ?? 0),
                'username'     => trim($_POST['username'] ?? ''),
                'password'     => $_POST['password'] ?? '',
                'nama_lengkap' => trim($_POST['nama_lengkap'] ?? ''),
                'level'        => $_POST['level'] ?? 'kasir',
                'status'       => $_POST['status'] ?? 'aktif',
            ]);
            flash('success', 'Akun pengguna berhasil disimpan.');
            $kembali = 'Location: ../index.php?mod=pengaturan&act=akun_user';
            break;

        case 'hapus_user':
            $model->userHapus((int)($_GET['id'] ?? 0));
            flash('success', 'Akun pengguna dihapus.');
            $kembali = 'Location: ../index.php?mod=pengaturan&act=akun_user';
            break;
    }
} catch (Exception $e) {
    flash('danger', 'Gagal: ' . $e->getMessage());
}

header($kembali);
exit;