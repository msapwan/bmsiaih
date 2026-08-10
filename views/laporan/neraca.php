<?php
require_once __DIR__ . '/../../models/JurnalModel.php';

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$n = (new JurnalModel())->neraca($tanggal);

$pageTitle = 'Neraca';
$active    = 'laporan';
include __DIR__ . '/../layout/header.php';
?>
<ul class="nav nav-tabs no-print">
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan">Simpanan & Pembiayaan</a></li>
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan&act=laba_rugi">Laba Rugi</a></li>
  <li class="nav-item"><a class="nav-link active" href="index.php?mod=laporan&act=neraca">Neraca</a></li>
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan&act=arus_kas">Arus Kas</a></li>
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan&act=phu">PHU / SHU</a></li>
</ul>

<div class="card border-0 shadow-sm rounded-top-0">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end no-print mb-3">
      <input type="hidden" name="mod" value="laporan">
      <input type="hidden" name="act" value="neraca">
      <div class="col-auto"><label class="form-label small">Per Tanggal</label>
        <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= e($tanggal) ?>"></div>
      <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i> Terapkan</button></div>
      <div class="col-auto ms-auto d-flex gap-2">
        <a class="btn btn-sm btn-outline-success"
           href="controllers/proses_export.php?jenis=neraca&tanggal=<?= e($tanggal) ?>">
          <i class="fas fa-file-excel me-1"></i> Excel</a>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
          <i class="fas fa-print me-1"></i> PDF</button>
      </div>
    </form>

    <div class="text-center mb-3">
      <h5 class="mb-0">NERACA</h5>
      <small class="text-muted">Per <?= e(date('d/m/Y', strtotime($tanggal))) ?></small>
    </div>

    <div class="alert <?= $n['balance']?'alert-success':'alert-danger' ?> py-2 small">
      <i class="fas fa-<?= $n['balance']?'check-circle':'exclamation-triangle' ?> me-1"></i>
      <?= $n['balance']
          ? 'Neraca SEIMBANG (balance).'
          : 'Neraca TIDAK SEIMBANG — selisih ' . rupiah(abs($n['total_aset'] - $n['total_pasiva'])) . '. Periksa kembali jurnal.' ?>
    </div>

    <div class="row">
      <div class="col-md-6">
        <table class="table table-sm table-bordered">
          <thead class="table-light"><tr><th colspan="2"><b>ASET</b></th></tr></thead>
          <?php foreach ($n['aset'] as $a): ?>
          <tr><td><?= e($a['kode'].' — '.$a['nama']) ?></td><td class="text-end" style="width:170px"><?= rupiah($a['jumlah']) ?></td></tr>
          <?php endforeach; ?>
          <tr class="table-light fw-bold"><td>TOTAL ASET</td><td class="text-end"><?= rupiah($n['total_aset']) ?></td></tr>
        </table>
      </div>
      <div class="col-md-6">
        <table class="table table-sm table-bordered">
          <thead class="table-light"><tr><th colspan="2"><b>KEWAJIBAN</b></th></tr></thead>
          <?php foreach ($n['kewajiban'] as $k): ?>
          <tr><td><?= e($k['kode'].' — '.$k['nama']) ?></td><td class="text-end" style="width:170px"><?= rupiah($k['jumlah']) ?></td></tr>
          <?php endforeach; ?>
          <tr class="table-light fw-bold"><td>Total Kewajiban</td><td class="text-end"><?= rupiah($n['total_kewajiban']) ?></td></tr>

          <thead class="table-light"><tr><th colspan="2"><b>EKUITAS</b></th></tr></thead>
          <?php foreach ($n['ekuitas'] as $ek): ?>
          <tr><td><?= e($ek['kode'].' — '.$ek['nama']) ?></td><td class="text-end"><?= rupiah($ek['jumlah']) ?></td></tr>
          <?php endforeach; ?>
          <tr><td>SHU Tahun Berjalan</td><td class="text-end"><?= rupiah($n['shu_berjalan']) ?></td></tr>
          <tr class="table-light fw-bold"><td>Total Ekuitas</td><td class="text-end"><?= rupiah($n['total_ekuitas'] + $n['shu_berjalan']) ?></td></tr>

          <tr class="table-light fw-bold"><td>TOTAL KEWAJIBAN + EKUITAS</td><td class="text-end"><?= rupiah($n['total_pasiva']) ?></td></tr>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>