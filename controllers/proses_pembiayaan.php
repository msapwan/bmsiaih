<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login.php'); exit; }

require_once __DIR__ . '/../models/PembiayaanModel.php';
require_once __DIR__ . '/../models/JurnalModel.php';
require_once __DIR__ . '/../models/DendaModel.php';

$model = new PembiayaanModel();
$aksi  = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

function flash(string $tipe, string $msg): void
{
    $_SESSION['flash'] = ['type' => $tipe, 'msg' => $msg];
}
function rupiah_flash($n): string
{
    return 'Rp ' . number_format((float)$n, 0, ',', '.');
}

$kembali = 'Location: ../index.php?mod=pembiayaan&act=list';
$mapPiutang    = ['margin' => '110', 'sosial' => '111', 'bagihasil' => '112'];
$mapPendapatan = ['MRB' => '401', 'IJR' => '403', 'MDR' => '402', 'MSY' => '402'];

try {
    switch ($aksi) {
        case 'ajukan':
            $jumlah = (float)str_replace('.', '', $_POST['jumlah_pembiayaan'] ?? '0');
            if ($jumlah <= 0) throw new Exception('Jumlah pembiayaan harus lebih dari 0.');
            $id = $model->create([
                'id_anggota'        => (int)($_POST['id_anggota'] ?? 0),
                'id_akad'           => (int)($_POST['id_akad'] ?? 0),
                'tanggal_pengajuan' => $_POST['tanggal_pengajuan'] ?: date('Y-m-d'),
                'jumlah_pembiayaan' => $jumlah,
                'margin_persen'     => (float)($_POST['margin_persen'] ?? 0),
                'nisbah_koperasi'   => (float)($_POST['nisbah_koperasi'] ?? 0),
                'jangka_waktu'      => (int)($_POST['jangka_waktu'] ?? 1),
                'catatan'           => trim($_POST['catatan'] ?? ''),
                'id_user'           => $_SESSION['user']['id_user'],
            ]);
            flash('success', 'Pengajuan pembiayaan berhasil dibuat (status: pengajuan).');
            $kembali = 'Location: ../index.php?mod=pembiayaan&act=detail&id=' . $id;
            break;

        case 'setujui':
            $tanggalAkad  = $_POST['tanggal_akad'] ?: date('Y-m-d');
            $idPembiayaan = (int)$_POST['id_pembiayaan'];
            $model->setujui($idPembiayaan, $tanggalAkad);

            // Jurnal pencairan: Dr Piutang, Cr Kas
            $p = $model->find($idPembiayaan);
            $akunPiutang = $mapPiutang[$p['tipe_akad']] ?? '112';
            (new JurnalModel())->otomatis(
                'pembiayaan', $p['no_pembiayaan'], $tanggalAkad,
                'Pencairan pembiayaan ' . $p['no_pembiayaan'] . ' (' . $p['nama_akad'] . ')', [
                ['kode' => $akunPiutang, 'posisi' => 'debit',  'jumlah' => $p['jumlah_pembiayaan']],
                ['kode' => '101',        'posisi' => 'kredit', 'jumlah' => $p['jumlah_pembiayaan']],
            ]);
            flash('success', 'Pembiayaan disetujui & jadwal angsuran dibuat otomatis.');
            $kembali = 'Location: ../index.php?mod=pembiayaan&act=detail&id=' . $idPembiayaan;
            break;

        case 'tolak':
            $model->tolak((int)$_POST['id_pembiayaan'], trim($_POST['catatan'] ?? ''));
            flash('warning', 'Pengajuan pembiayaan ditolak.');
            break;

        case 'hapus':
            $model->delete((int)($_GET['id'] ?? 0));
            flash('success', 'Pengajuan pembiayaan dihapus.');
            break;

        case 'bayar':
            $jumlahBayar = (float)str_replace('.', '', $_POST['jumlah_bayar'] ?? '0');
            if ($jumlahBayar <= 0) throw new Exception('Jumlah pembayaran harus lebih dari 0.');

            $idAngsuran = (int)$_POST['id_angsuran'];
            $tanggal    = $_POST['tanggal_bayar'] ?: date('Y-m-d');

            $a = $model->findAngsuran($idAngsuran);
            if (!$a) throw new Exception('Angsuran tidak ditemukan.');
            $p = $model->find((int)$a['id_pembiayaan']);
            $lunasSebelumnya = $a['status'] === 'lunas';

            $model->bayarAngsuran($idAngsuran, $jumlahBayar, $tanggal);
            $a2 = $model->findAngsuran($idAngsuran);

            // Jurnal pembayaran angsuran
            $jm = new JurnalModel();
            $akunPiutang    = $mapPiutang[$p['tipe_akad']] ?? '112';
            $akunPendapatan = $mapPendapatan[$p['kode_akad']] ?? '402';

            if ($a2['status'] === 'lunas' && !$lunasSebelumnya) {
                $jm->otomatis('angsuran', $p['no_pembiayaan'] . '#' . $a['angsuran_ke'], $tanggal,
                    'Angsuran ke-' . $a['angsuran_ke'] . ' ' . $p['no_pembiayaan'], [
                    ['kode' => '101',           'posisi' => 'debit',  'jumlah' => $a['total']],
                    ['kode' => $akunPiutang,    'posisi' => 'kredit', 'jumlah' => $a['pokok']],
                    ['kode' => $akunPendapatan, 'posisi' => 'kredit', 'jumlah' => $a['margin']],
                ]);
            } else {
                $jm->otomatis('angsuran', $p['no_pembiayaan'] . '#' . $a['angsuran_ke'], $tanggal,
                    'Pembayaran sebagian angsuran ke-' . $a['angsuran_ke'] . ' ' . $p['no_pembiayaan'], [
                    ['kode' => '101',        'posisi' => 'debit',  'jumlah' => $jumlahBayar],
                    ['kode' => $akunPiutang, 'posisi' => 'kredit', 'jumlah' => $jumlahBayar],
                ]);
            }

            // Denda ta'zir (jika terlambat & belum pernah didenda)
            $dm = new DendaModel();
            if ($a['status'] === 'belum'
                && strtotime($tanggal) > strtotime($a['tanggal_jatuh_tempo'])
                && !$dm->byAngsuran($idAngsuran)) {
                $h = $dm->hitung($a, $tanggal);
                if ($h['jumlah'] > 0) {
                    $idDenda = $dm->create(
                        $idAngsuran, (int)$a['id_pembiayaan'], $tanggal,
                        $h['hari'], $h['jumlah'], $_SESSION['user']['id_user']
                    );
                    if (isset($_POST['bayar_denda']) && $_POST['bayar_denda'] == '1') {
                        $dm->bayar($idDenda, $tanggal);
                        flash('success', 'Angsuran & denda ta\'zir (' . $h['hari'] . ' hari) berhasil dibayar.');
                    } else {
                        flash('warning', 'Angsuran dibayar. Denda ' . rupiah_flash($h['jumlah']) . ' belum dibayar — cek menu Denda.');
                    }
                    $kembali = 'Location: ../index.php?mod=pembiayaan&act=angsuran&id=' . (int)$_POST['id_pembiayaan'];
                    break;
                }
            }
            flash('success', 'Pembayaran angsuran berhasil dicatat.');
            $kembali = 'Location: ../index.php?mod=pembiayaan&act=angsuran&id=' . (int)$_POST['id_pembiayaan'];
            break;
    }
} catch (Exception $e) {
    flash('danger', 'Gagal: ' . $e->getMessage());
}

header($kembali);
exit;