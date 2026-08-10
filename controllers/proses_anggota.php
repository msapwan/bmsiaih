<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login.php'); exit; }

require_once __DIR__ . '/../models/AnggotaModel.php';
$model = new AnggotaModel();
$aksi  = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

function flash(string $tipe, string $msg): void
{
    $_SESSION['flash'] = ['type' => $tipe, 'msg' => $msg];
}

try {
    switch ($aksi) {
        case 'simpan':
            $id   = (int)($_POST['id_anggota'] ?? 0);
            $data = [
                'nik'            => trim($_POST['nik'] ?? ''),
                'nama'           => trim($_POST['nama'] ?? ''),
                'jenis_kelamin'  => $_POST['jenis_kelamin'] ?? 'L',
                'tempat_lahir'   => trim($_POST['tempat_lahir'] ?? '') ?: null,
                'tanggal_lahir'  => $_POST['tanggal_lahir'] ?: null,
                'alamat'         => trim($_POST['alamat'] ?? ''),
                'no_hp'          => trim($_POST['no_hp'] ?? ''),
                'email'          => trim($_POST['email'] ?? ''),
                'pekerjaan'      => trim($_POST['pekerjaan'] ?? ''),
                'status_anggota' => $_POST['status_anggota'] ?? 'aktif',
                'tanggal_daftar' => $_POST['tanggal_daftar'] ?: date('Y-m-d'),
            ];
            if ($data['nama'] === '') throw new Exception('Nama anggota wajib diisi.');
            if ($id > 0) { $model->update($id, $data); flash('success', 'Data anggota berhasil diperbarui.'); }
            else         { $model->create($data);      flash('success', 'Anggota baru berhasil disimpan.'); }
            break;

        case 'hapus':
            $model->delete((int)($_GET['id'] ?? 0));
            flash('success', 'Anggota berhasil dihapus beserta seluruh datanya.');
            break;
    }
} catch (Exception $e) {
    flash('danger', 'Gagal: ' . $e->getMessage());
}

header('Location: ../index.php?mod=anggota&act=list');
exit;