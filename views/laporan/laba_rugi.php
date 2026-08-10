<?php
require_once __DIR__ . '/../../models/JurnalModel.php';

$dari   = $_GET['dari'] ?? date('Y-01-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$lr = (new JurnalModel())->labaRugi($dari, $sampai);

$pageTitle = 'Laporan Laba Rugi';
$active    = 'laporan';
include __DIR__ . '/../layout/header.php';
?>
<ul class="nav nav-tabs no-print">
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan">Simpanan & Pembiayaan</a></li>
  <li class="nav-item"><a class="nav-link active" href="index.php?mod=laporan&act=laba_rugi">Laba Rugi</a></li>
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan&act=neraca">Neraca</a></li>
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan&act=arus_kas">Arus Kas</a></li>
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan&act=phu">PHU / SHU</a></li>
</ul>

<div class="card border-0 shadow-sm rounded-top-0">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end no-print mb-3">
      <input type="hidden" name="mod" value="laporan">
      <input type="hidden" name="act" value="laba_rugi">
      <div class="col-auto"><label class="form-label small">Dari</label>
        <input type="date" name="dari" class="form-control form-control-sm" value="<?= e($dari) ?>"></div>
      <div class="col-auto"><label class="form-label small">Sampai</label>
        <input type="date" name="sampai" class="form-control form-control-sm" value="<?= e($sampai) ?>"></div>
      <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i> Terapkan</button></div>
      <div class="col-auto ms-auto d-flex gap-2">
        <a class="btn btn-sm btn-outline-success"
           href="controllers/proses_export.php?jenis=laba_rugi&dari=<?= e($dari) ?>&sampai=<?= e($sampai) ?>">
          <i class="fas fa-file-excel me-1"></i> Excel</a>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
          <i class="fas fa-print me-1"></i> PDF</button>
      </div>
    </form>

    <div class="text-center mb-3">
      <h5 class="mb-0">LAPORAN LABA RUGI</h5>
      <small class="text-muted">Periode <?= e(date('d/m/Y', strtotime($dari))) ?> s.d. <?= e(date('d/m/Y', strtotime($sampai))) ?></small>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-8">
        <table class="table table-sm table-bordered">
          <thead class="table-light"><tr><th colspan="2"><b>PENDAPATAN</b></th></tr></thead>
          <?php foreach ($lr['pendapatan'] as $p): ?>
          <tr><td class="ps-4"><?= e($p['nama']) ?></td><td class="text-end" style="width:200px"><?= rupiah($p['jumlah']) ?></td></tr>
          <?php endforeach; ?>
          <tr class="table-light fw-bold"><td>Total Pendapatan</td><td class="text-end"><?= rupiah($lr['total_pendapatan']) ?></td></tr>

          <thead class="table-light"><tr><th colspan="2"><b>BEBAN</b></th></tr></thead>
          <?php foreach ($lr['beban'] as $b): ?>
          <tr><td class="ps-4"><?= e($b['nama']) ?></td><td class="text-end"><?= rupiah($b['jumlah']) ?></td></tr>
          <?php endforeach; ?>
          <tr class="table-light fw-bold"><td>Total Beban</td><td class="text-end"><?= rupiah($lr['total_beban']) ?></td></tr>

          <tr class="<?= $lr['laba']>=0?'table-success':'table-danger' ?> fw-bold fs-6">
            <td>LABA (SHU) BERSIH</td><td class="text-end"><?= rupiah($lr['laba']) ?></td>
          </tr>
        </table>
        <?php if (!$lr['pendapatan'] && !$lr['beban']): ?>
        <div class="alert alert-info small">Belum ada jurnal pada periode ini. Jurnal terbentuk otomatis dari transaksi atau buat manual di menu Jurnal.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>