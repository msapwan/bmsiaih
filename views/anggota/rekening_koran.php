<?php
require_once __DIR__ . '/../../models/AnggotaModel.php';
require_once __DIR__ . '/../../models/SimpananModel.php';

$am = new AnggotaModel();
$sm = new SimpananModel();

$idAnggota = (int)($_GET['id_anggota'] ?? $_GET['id'] ?? 0);
$dari      = $_GET['dari'] ?? date('Y-m-01');
$sampai    = $_GET['sampai'] ?? date('Y-m-d');

$semuaAnggota = $am->all();
$anggota      = $idAnggota ? $am->find($idAnggota) : null;
$rk           = $anggota ? $sm->rekeningKoran($idAnggota, $dari, $sampai) : null;

$pageTitle = 'Rekening Koran Anggota';
$active    = 'anggota';
include __DIR__ . '/../layout/header.php';
?>

<!-- FILTER & TOMBOL (tidak ikut tercetak) -->
<div class="card border-0 shadow-sm mb-3 no-print">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end">
      <input type="hidden" name="mod" value="anggota">
      <input type="hidden" name="act" value="rekening_koran">
      <div class="col-md-4">
        <label class="form-label small">Anggota</label>
        <select name="id_anggota" class="form-select form-select-sm">
          <option value="">— Pilih Anggota —</option>
          <?php foreach ($semuaAnggota as $o): ?>
          <option value="<?= (int)$o['id_anggota'] ?>" <?= $idAnggota===(int)$o['id_anggota']?'selected':'' ?>>
            <?= e($o['kode_anggota'].' — '.$o['nama']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto"><label class="form-label small">Dari</label>
        <input type="date" name="dari" class="form-control form-control-sm" value="<?= e($dari) ?>"></div>
      <div class="col-auto"><label class="form-label small">Sampai</label>
        <input type="date" name="sampai" class="form-control form-control-sm" value="<?= e($sampai) ?>"></div>
      <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="fas fa-eye me-1"></i> Tampilkan</button></div>
      <?php if ($rk): ?>
      <div class="col-auto ms-auto d-flex gap-2">
        <a class="btn btn-sm btn-outline-success"
           href="controllers/proses_export.php?jenis=rekening_koran&id_anggota=<?= $idAnggota ?>&dari=<?= e($dari) ?>&sampai=<?= e($sampai) ?>">
          <i class="fas fa-file-excel me-1"></i> Excel</a>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
          <i class="fas fa-print me-1"></i> Cetak / PDF</button>
      </div>
      <?php endif; ?>
    </form>
  </div>
</div>

<?php if (!$rk): ?>
<div class="alert alert-info">Pilih anggota untuk menampilkan rekening koran.</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
  <div class="card-body">

    <!-- KOP SURAT -->
    <div class="rk-kop">
      <div class="d-flex align-items-center justify-content-center gap-3">
        <?php if ($logoAda): ?>
        <img src="assets/img/<?= e($logo) ?>" alt="Logo" style="height:64px; object-fit:contain">
        <?php endif; ?>
        <div class="text-center">
          <h4 class="mb-0"><?= e($profil['nama_koperasi']) ?></h4>
          <div class="small"><?= e($profil['alamat']) ?></div>
          <div class="small">Telp: <?= e($profil['no_telp']) ?> &nbsp;|&nbsp; Email: <?= e($profil['email']) ?></div>
        </div>
      </div>
      <hr class="border-dark border-2 mb-0"><hr class="border-dark mt-1 mb-3">
      <div class="text-center mb-3">
        <h5 class="mb-0"><u>REKENING KORAN SIMPANAN ANGGOTA</u></h5>
        <div class="small text-muted">
          Periode <?= e(date('d/m/Y', strtotime($dari))) ?> s.d. <?= e(date('d/m/Y', strtotime($sampai))) ?>
        </div>
      </div>
    </div>

    <!-- DATA ANGGOTA -->
    <div class="row mb-3 small">
      <div class="col-md-6">
        <table class="table table-sm table-borderless mb-0">
          <tr><th width="120">Kode Anggota</th><td>: <b><?= e($anggota['kode_anggota']) ?></b></td></tr>
          <tr><th>Nama</th><td>: <?= e($anggota['nama']) ?></td></tr>
          <tr><th>Alamat</th><td>: <?= e($anggota['alamat'] ?: '-') ?></td></tr>
        </table>
      </div>
      <div class="col-md-6">
        <table class="table table-sm table-borderless mb-0">
          <tr><th width="120">No. HP</th><td>: <?= e($anggota['no_hp'] ?: '-') ?></td></tr>
          <tr><th>Terdaftar</th><td>: <?= e(date('d/m/Y', strtotime($anggota['tanggal_daftar']))) ?></td></tr>
          <tr><th>Status</th><td>: <?= badge_status($anggota['status_anggota']) ?></td></tr>
        </table>
      </div>
    </div>

    <!-- TABEL MUTASI -->
    <table class="table table-bordered table-sm align-middle">
      <thead class="table-light">
        <tr>
          <th>Tanggal</th><th>Keterangan</th><th>Jenis</th>
          <th class="text-end">Penarikan</th><th class="text-end">Setoran</th><th class="text-end">Saldo</th>
        </tr>
      </thead>
      <tbody>
        <tr class="table-light">
          <td><?= e(date('d/m/Y', strtotime($dari))) ?></td>
          <td><i>Saldo Awal</i></td>
          <td></td><td></td><td></td>
          <td class="text-end"><b><?= rupiah($rk['saldo_awal_total']) ?></b></td>
        </tr>
        <?php foreach ($rk['baris'] as $b): ?>
        <tr>
          <td><?= e(date('d/m/Y', strtotime($b['tanggal']))) ?></td>
          <td><?= e($b['keterangan'] ?: ucfirst($b['tipe']) . ' simpanan ' . $b['jenis_simpanan']) ?></td>
          <td><?= e(ucfirst($b['jenis_simpanan'])) ?></td>
          <td class="text-end text-danger"><?= $b['tipe']==='tarik' ? rupiah($b['jumlah']) : '' ?></td>
          <td class="text-end text-success"><?= $b['tipe']==='setor' ? rupiah($b['jumlah']) : '' ?></td>
          <td class="text-end"><?= rupiah($b['saldo']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rk['baris']): ?>
        <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada transaksi pada periode ini.</td></tr>
        <?php endif; ?>
      </tbody>
      <tfoot class="table-light fw-bold">
        <tr>
          <td colspan="3">TOTAL</td>
          <td class="text-end"><?= rupiah($rk['total_keluar']) ?></td>
          <td class="text-end"><?= rupiah($rk['total_masuk']) ?></td>
          <td class="text-end"><?= rupiah($rk['saldo_akhir']) ?></td>
        </tr>
      </tfoot>
    </table>

    <div class="small text-muted mb-4">
      Rincian saldo awal — Pokok: <b><?= rupiah($rk['saldo_awal']['pokok']) ?></b>,
      Wajib: <b><?= rupiah($rk['saldo_awal']['wajib']) ?></b>,
      Sukarela: <b><?= rupiah($rk['saldo_awal']['sukarela']) ?></b>
    </div>

    <!-- TANDA TANGAN (ikut tercetak) -->
    <div class="row mt-5 text-center small">
      <div class="col-6">
        <div>Mengetahui,</div>
        <div class="mb-5 pb-3">Ketua Koperasi</div>
        <div><b><u><?= e($profil['nama_ketua'] ?: '________________________') ?></u></b></div>
      </div>
      <div class="col-6">
        <div>&nbsp;</div>
        <div class="mb-5 pb-3">Anggota</div>
        <div><b><u><?= e($anggota['nama']) ?></u></b></div>
      </div>
    </div>

  </div>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layout/footer.php'; ?>