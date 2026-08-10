<?php
require_once __DIR__ . '/../../models/JurnalModel.php';
require_once __DIR__ . '/../../models/ShuModel.php';
require_once __DIR__ . '/../../models/PengaturanModel.php';

$tahun = (int)($_GET['tahun'] ?? date('Y'));
$jm = new JurnalModel();
$sm = new ShuModel();
$pm = new PengaturanModel();

$lr  = $jm->labaRugi("$tahun-01-01", "$tahun-12-31");
$rec = $sm->byTahun($tahun);
$alokasi = $rec ? $sm->alokasi($rec['id_shu']) : null;

$pageTitle = 'Perhitungan Hasil Usaha (PHU)';
$active    = 'laporan';
include __DIR__ . '/../layout/header.php';
?>
<ul class="nav nav-tabs no-print">
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan">Simpanan & Pembiayaan</a></li>
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan&act=laba_rugi">Laba Rugi</a></li>
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan&act=neraca">Neraca</a></li>
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan&act=arus_kas">Arus Kas</a></li>
  <li class="nav-item"><a class="nav-link active" href="index.php?mod=laporan&act=phu">PHU / SHU</a></li>
</ul>

<div class="card border-0 shadow-sm rounded-top-0">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end no-print mb-3">
      <input type="hidden" name="mod" value="laporan">
      <input type="hidden" name="act" value="phu">
      <div class="col-auto"><label class="form-label small">Tahun</label>
        <select name="tahun" class="form-select form-select-sm">
          <?php for ($t = date('Y'); $t >= date('Y') - 5; $t--): ?>
          <option value="<?= $t ?>" <?= $tahun===$t?'selected':'' ?>><?= $t ?></option>
          <?php endfor; ?>
        </select></div>
      <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i> Terapkan</button></div>
      <div class="col-auto ms-auto">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
          <i class="fas fa-print me-1"></i> PDF</button>
      </div>
    </form>

    <div class="text-center mb-3">
      <h5 class="mb-0">LAPORAN PERHITUNGAN HASIL USAHA (PHU)</h5>
      <small class="text-muted">Tahun Buku <?= $tahun ?></small>
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

          <tr class="table-success fw-bold fs-6"><td>SISA HASIL USAHA (SHU)</td><td class="text-end"><?= rupiah($lr['laba']) ?></td></tr>
        </table>

        <h6 class="mt-4">Distribusi SHU <?= $rec ? '(sudah dihitung — status: '.ucfirst($rec['status']).')' : '(estimasi sesuai parameter)' ?></h6>
        <table class="table table-sm table-bordered">
          <thead class="table-light"><tr><th>Pos Distribusi</th><th class="text-center">%</th><th class="text-end">Jumlah</th></tr></thead>
          <tbody>
          <?php if ($alokasi): ?>
            <?php foreach ($alokasi as $a): ?>
            <tr><td><?= e($a['nama_dana']) ?></td><td class="text-center"><?= e($a['persen']) ?>%</td>
                <td class="text-end"><?= rupiah($a['jumlah']) ?></td></tr>
            <?php endforeach; ?>
          <?php else: ?>
            <?php foreach (ShuModel::$alokasiDefault as $nama => $kunci):
                $persen = (float)$pm->get($kunci, 0); ?>
            <tr><td><?= e($nama) ?></td><td class="text-center"><?= $persen ?>%</td>
                <td class="text-end"><?= rupiah($lr['laba'] * $persen / 100) ?></td></tr>
            <?php endforeach; ?>
            <tr><td colspan="3" class="small text-muted">Belum ada perhitungan resmi — buka menu <b>SHU → Hitung SHU Baru</b>.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>