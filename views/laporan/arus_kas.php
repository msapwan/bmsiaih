<?php
require_once __DIR__ . '/../../models/JurnalModel.php';

$dari   = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$ak = (new JurnalModel())->arusKas($dari, $sampai);

$pageTitle = 'Laporan Arus Kas';
$active    = 'laporan';
include __DIR__ . '/../layout/header.php';
?>
<ul class="nav nav-tabs no-print">
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan">Simpanan & Pembiayaan</a></li>
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan&act=laba_rugi">Laba Rugi</a></li>
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan&act=neraca">Neraca</a></li>
  <li class="nav-item"><a class="nav-link active" href="index.php?mod=laporan&act=arus_kas">Arus Kas</a></li>
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan&act=phu">PHU / SHU</a></li>
</ul>

<div class="card border-0 shadow-sm rounded-top-0">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end no-print mb-3">
      <input type="hidden" name="mod" value="laporan">
      <input type="hidden" name="act" value="arus_kas">
      <div class="col-auto"><label class="form-label small">Dari</label>
        <input type="date" name="dari" class="form-control form-control-sm" value="<?= e($dari) ?>"></div>
      <div class="col-auto"><label class="form-label small">Sampai</label>
        <input type="date" name="sampai" class="form-control form-control-sm" value="<?= e($sampai) ?>"></div>
      <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i> Terapkan</button></div>
      <div class="col-auto ms-auto d-flex gap-2">
        <a class="btn btn-sm btn-outline-success"
           href="controllers/proses_export.php?jenis=arus_kas&dari=<?= e($dari) ?>&sampai=<?= e($sampai) ?>">
          <i class="fas fa-file-excel me-1"></i> Excel</a>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
          <i class="fas fa-print me-1"></i> PDF</button>
      </div>
    </form>

    <div class="text-center mb-3">
      <h5 class="mb-0">LAPORAN ARUS KAS</h5>
      <small class="text-muted">Periode <?= e(date('d/m/Y', strtotime($dari))) ?> s.d. <?= e(date('d/m/Y', strtotime($sampai))) ?></small>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-9">
        <div class="alert alert-light border py-2 d-flex justify-content-between">
          <span>Saldo Awal Kas (Kas + Bank)</span><b><?= rupiah($ak['saldo_awal']) ?></b>
        </div>

        <?php foreach ($ak['grup'] as $g):
            $netto = 0; foreach ($g['baris'] as $b) $netto += $b['masuk'] - $b['keluar']; ?>
        <table class="table table-sm table-bordered mb-3">
          <thead class="table-light"><tr><th colspan="3"><b><?= e($g['label']) ?></b></th></tr></thead>
          <?php if (!$g['baris']): ?>
          <tr><td colspan="3" class="text-muted small">Tidak ada mutasi kas.</td></tr>
          <?php endif; ?>
          <?php foreach ($g['baris'] as $b): ?>
          <tr>
            <td>Kas dari: <i><?= e(ucfirst($b['sumber'])) ?></i></td>
            <td class="text-end text-success" style="width:180px">Masuk: <?= rupiah($b['masuk']) ?></td>
            <td class="text-end text-danger" style="width:180px">Keluar: <?= rupiah($b['keluar']) ?></td>
          </tr>
          <?php endforeach; ?>
          <tr class="table-light fw-bold"><td>Arus Kas Netto — <?= e($g['label']) ?></td>
              <td colspan="2" class="text-end"><?= rupiah($netto) ?></td></tr>
        </table>
        <?php endforeach; ?>

        <table class="table table-sm table-bordered">
          <tr><td>Total Kas Masuk</td><td class="text-end text-success"><?= rupiah($ak['masuk']) ?></td></tr>
          <tr><td>Total Kas Keluar</td><td class="text-end text-danger"><?= rupiah($ak['keluar']) ?></td></tr>
          <tr class="table-success fw-bold fs-6"><td>SALDO AKHIR KAS</td><td class="text-end"><?= rupiah($ak['saldo_akhir']) ?></td></tr>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>