<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login.php'); exit; }

require_once __DIR__ . '/../models/SimpananModel.php';
require_once __DIR__ . '/../models/JurnalModel.php';
$model = new SimpananModel();
$aksi  = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

function flash(string $tipe, string $msg): void
{
    $_SESSION['flash'] = ['type' => $tipe, 'msg' => $msg];
}

try {
    switch ($aksi) {
        case 'simpan':
            $jumlah = (float)str_replace('.', '', $_POST['jumlah'] ?? '0');
            $data = [
                'id_anggota'     => (int)($_POST['id_anggota'] ?? 0),
                'jenis_simpanan' => $_POST['jenis_simpanan'] ?? 'sukarela',
                'tipe'           => $_POST['tipe'] ?? 'setor',
                'tanggal'        => $_POST['tanggal'] ?: date('Y-m-d'),
                'jumlah'         => $jumlah,
                'keterangan'     => trim($_POST['keterangan'] ?? ''),
                'id_user'        => $_SESSION['user']['id_user'],
            ];
            if ($data['id_anggota'] === 0) throw new Exception('Pilih anggota terlebih dahulu.');
            if ($jumlah <= 0)              throw new Exception('Jumlah harus lebih dari 0.');
            if ($data['tipe'] === 'tarik' && $jumlah > $model->saldoAnggota($data['id_anggota'])) {
                throw new Exception('Saldo anggota tidak mencukupi untuk penarikan.');
            }

            $idSimpanan = $model->create($data);

            // Posting jurnal otomatis: Pokok(301)/Wajib(302)=ekuitas, Sukarela(201)=kewajiban
            $mapAkun = ['pokok' => '301', 'wajib' => '302', 'sukarela' => '201'];
            $akunSimpanan = $mapAkun[$data['jenis_simpanan']];
            $jm = new JurnalModel();
            if ($data['tipe'] === 'setor') {
                $jm->otomatis('simpanan', 'SIM-' . $idSimpanan, $data['tanggal'],
                    'Setoran simpanan ' . $data['jenis_simpanan'], [
                    ['kode' => '101',         'posisi' => 'debit',  'jumlah' => $jumlah],
                    ['kode' => $akunSimpanan, 'posisi' => 'kredit', 'jumlah' => $jumlah],
                ]);
            } else {
                $jm->otomatis('simpanan', 'SIM-' . $idSimpanan, $data['tanggal'],
                    'Penarikan simpanan ' . $data['jenis_simpanan'], [
                    ['kode' => $akunSimpanan, 'posisi' => 'debit',  'jumlah' => $jumlah],
                    ['kode' => '101',         'posisi' => 'kredit', 'jumlah' => $jumlah],
                ]);
            }
            flash('success', 'Transaksi simpanan berhasil dicatat.');
            break;

        case 'hapus':
            if ($_SESSION['user']['level'] !== 'admin') {
                throw new Exception('Hanya admin yang boleh menghapus transaksi.');
            }
            $model->delete((int)($_GET['id'] ?? 0));
            flash('success', 'Transaksi simpanan dihapus.');
            break;
    }
} catch (Exception $e) {
    flash('danger', 'Gagal: ' . $e->getMessage());
}

header('Location: ../index.php?mod=simpanan&act=list');
exit;