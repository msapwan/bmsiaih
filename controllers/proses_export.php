<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login.php'); exit; }

require_once __DIR__ . '/../models/JurnalModel.php';
require_once __DIR__ . '/../models/SimpananModel.php';
require_once __DIR__ . '/../models/LaporanModel.php';
require_once __DIR__ . '/../models/AnggotaModel.php';

function rupiah($n) { return 'Rp ' . number_format((float)($n ?: 0), 0, ',', '.'); }

$jenis  = $_GET['jenis'] ?? '';
$dari   = $_GET['dari'] ?? '';
$sampai = $_GET['sampai'] ?? '';

$judul = ''; $head = []; $rows = [];

switch ($jenis) {
    case 'rekap_simpanan':
        $judul = 'Rekapitulasi Simpanan Anggota';
        $head  = ['Kode', 'Nama', 'Pokok', 'Wajib', 'Sukarela', 'Total'];
        foreach ((new SimpananModel())->rekapPerAnggota() as $r) {
            $rows[] = [$r['kode_anggota'], $r['nama'], $r['pokok'], $r['wajib'], $r['sukarela'], $r['total']];
        }
        break;

    case 'transaksi':
        $judul = 'Transaksi Simpanan';
        $head  = ['Tanggal', 'Kode', 'Nama', 'Jenis', 'Tipe', 'Jumlah', 'Keterangan'];
        foreach ((new LaporanModel())->transaksiSimpanan($dari, $sampai) as $r) {
            $rows[] = [$r['tanggal'], $r['kode_anggota'], $r['nama'], $r['jenis_simpanan'], $r['tipe'], $r['jumlah'], $r['keterangan']];
        }
        break;

    case 'pembiayaan':
        $judul = 'Laporan Pembiayaan';
        $head  = ['No. Pembiayaan', 'Anggota', 'Akad', 'Plafon', 'Terbayar', 'Sisa', 'Status'];
        foreach ((new LaporanModel())->rekapPembiayaan($dari, $sampai) as $r) {
            $rows[] = [$r['no_pembiayaan'], $r['nama'], $r['nama_akad'], $r['jumlah_pembiayaan'],
                       $r['total_bayar'], max(0, $r['total_piutang'] - $r['total_bayar']), $r['status']];
        }
        break;

    case 'jurnal':
        $judul = 'Jurnal Umum';
        $head  = ['Tanggal', 'No. Jurnal', 'Keterangan', 'Referensi', 'Sumber', 'Nominal'];
        foreach ((new JurnalModel())->all($dari, $sampai) as $r) {
            $rows[] = [$r['tanggal'], $r['no_jurnal'], $r['keterangan'], $r['referensi'], $r['sumber'], $r['total_debit']];
        }
        break;

    case 'buku_besar':
        $bb = (new JurnalModel())->bukuBesar((int)($_GET['id_akun'] ?? 0), $dari ?: date('Y-01-01'), $sampai ?: date('Y-m-d'));
        $judul = 'Buku Besar — ' . $bb['akun']['kode_akun'] . ' ' . $bb['akun']['nama_akun'];
        $head  = ['Tanggal', 'No. Jurnal', 'Keterangan', 'Debit', 'Kredit', 'Saldo'];
        $rows[] = ['', '', 'Saldo Awal', '', '', $bb['saldo_awal']];
        foreach ($bb['baris'] as $b) {
            $rows[] = [$b['tanggal'], $b['no_jurnal'], $b['keterangan'],
                       $b['posisi'] === 'debit' ? $b['jumlah'] : 0,
                       $b['posisi'] === 'kredit' ? $b['jumlah'] : 0, $b['saldo']];
        }
        break;

    case 'laba_rugi':
        $lr = (new JurnalModel())->labaRugi($dari ?: date('Y-01-01'), $sampai ?: date('Y-m-d'));
        $judul = 'Laporan Laba Rugi';
        $head  = ['Kelompok', 'Akun', 'Jumlah'];
        foreach ($lr['pendapatan'] as $p) $rows[] = ['PENDAPATAN', $p['nama'], $p['jumlah']];
        $rows[] = ['', 'Total Pendapatan', $lr['total_pendapatan']];
        foreach ($lr['beban'] as $b)      $rows[] = ['BEBAN', $b['nama'], $b['jumlah']];
        $rows[] = ['', 'Total Beban', $lr['total_beban']];
        $rows[] = ['', 'LABA (SHU) BERSIH', $lr['laba']];
        break;

    case 'neraca':
        $n = (new JurnalModel())->neraca($_GET['tanggal'] ?? date('Y-m-d'));
        $judul = 'Neraca';
        $head  = ['Kelompok', 'Akun', 'Jumlah'];
        foreach ($n['aset'] as $a)      $rows[] = ['ASET', $a['nama'], $a['jumlah']];
        $rows[] = ['', 'TOTAL ASET', $n['total_aset']];
        foreach ($n['kewajiban'] as $k) $rows[] = ['KEWAJIBAN', $k['nama'], $k['jumlah']];
        foreach ($n['ekuitas'] as $ek)  $rows[] = ['EKUITAS', $ek['nama'], $ek['jumlah']];
        $rows[] = ['EKUITAS', 'SHU Tahun Berjalan', $n['shu_berjalan']];
        $rows[] = ['', 'TOTAL KEWAJIBAN + EKUITAS', $n['total_pasiva']];
        break;

    case 'arus_kas':
        $ak = (new JurnalModel())->arusKas($dari ?: date('Y-01-01'), $sampai ?: date('Y-m-d'));
        $judul = 'Laporan Arus Kas';
        $head  = ['Aktivitas', 'Sumber', 'Kas Masuk', 'Kas Keluar'];
        foreach ($ak['grup'] as $g) {
            foreach ($g['baris'] as $b) $rows[] = [$g['label'], $b['sumber'], $b['masuk'], $b['keluar']];
        }
        $rows[] = ['', 'Saldo Awal Kas', $ak['saldo_awal'], 0];
        $rows[] = ['', 'Saldo Akhir Kas', $ak['saldo_akhir'], 0];
        break;

    case 'rekening_koran':
        $idA = (int)($_GET['id_anggota'] ?? 0);
        $amX = new AnggotaModel();
        $anggotaX = $idA ? $amX->find($idA) : null;
        if (!$anggotaX) die('Anggota tidak ditemukan.');
        $rk = (new SimpananModel())->rekeningKoran($idA, $dari ?: '1970-01-01', $sampai ?: date('Y-m-d'));
        $judul = 'Rekening Koran — ' . $anggotaX['kode_anggota'] . ' ' . $anggotaX['nama'];
        $head  = ['Tanggal', 'Keterangan', 'Jenis', 'Penarikan', 'Setoran', 'Saldo'];
        $rows[] = [$dari ?: '-', 'Saldo Awal', '', '', '', $rk['saldo_awal_total']];
        foreach ($rk['baris'] as $b) {
            $rows[] = [
                $b['tanggal'],
                $b['keterangan'] ?: ucfirst($b['tipe']) . ' simpanan ' . $b['jenis_simpanan'],
                ucfirst($b['jenis_simpanan']),
                $b['tipe'] === 'tarik' ? $b['jumlah'] : 0,
                $b['tipe'] === 'setor' ? $b['jumlah'] : 0,
                $b['saldo'],
            ];
        }
        $rows[] = ['', 'TOTAL', '', $rk['total_keluar'], $rk['total_masuk'], $rk['saldo_akhir']];
        break;

    default:
        die('Jenis export tidak dikenal.');
}

$filename = $jenis . '_' . date('Ymd_His') . '.xls';
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo "\xEF\xBB\xBF";
?>
<h3><?= htmlspecialchars($judul) ?></h3>
<table border="1" cellpadding="4">
  <thead>
    <tr style="background:#0f766e;color:#fff">
      <?php foreach ($head as $h): ?><th><?= htmlspecialchars($h) ?></th><?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <?php foreach ($r as $i => $c): ?>
        <?php if (is_numeric($c) && $i > 0): ?>
          <td style="text-align:right"><?= (float)$c ?></td>
        <?php else: ?>
          <td><?= htmlspecialchars((string)$c) ?></td>
        <?php endif; ?>
      <?php endforeach; ?>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>