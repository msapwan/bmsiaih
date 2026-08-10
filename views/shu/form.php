<?php
require_once __DIR__ . '/../../models/JurnalModel.php';
require_once __DIR__ . '/../../models/PengaturanModel.php';
require_once __DIR__ . '/../../models/ShuModel.php';

$tahun = (int)($_GET['tahun'] ?? date('Y'));
$jm = new JurnalModel();
$pm = new PengaturanModel();
$lr = $jm->labaRugi("$tahun-01-01", "$tahun-12-31");

$pageTitle = 'Hitung SHU';
$active    = 'shu';
include __DIR__ . '/../layout/header.php';
?>
<div class="row g-3">
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white"><b>1. Pilih Tahun & Cek Data</b></div>
      <div class="card-body">
        <form method="get" class="d-flex gap-2 mb-3">
          <input type="hidden" name="mod" value="shu">
          <input type="hidden" name="act" value="form">
          <select name="tahun" class="form-select">
            <?php for ($t = date('Y'); $t >= date('Y') - 5; $t--): ?>
            <option value="<?= $t ?>" <?= $tahun===$t?'selected':'' ?>><?= $t ?></option>
            <?php endfor; ?>
          </select>
          <button class="btn btn-outline-primary">Tampilkan</button>
        </form>
        <h6>Ringkasan <?= $tahun ?> (dari jurnal):</h6>
        <table class="table table-sm">
          <tr><td>Total Pendapatan</td><td class="text-end"><?= rupiah($lr['total_pendapatan']) ?></td></tr>
          <tr><td>Total Beban</td><td class="text-end"><?= rupiah($lr['total_beban']) ?></td></tr>
          <tr class="fw-bold"><td>SHU Tahun Berjalan</td><td class="text-end text-success"><?= rupiah($lr['laba']) ?></td></tr>
        </table>
        <div class="alert alert-light border small mb-0">
          <i class="fas fa-info-circle me-1"></i>
          Nilai di form dapat disesuaikan (mis. ada pendapatan/beban di luar sistem).
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white"><b>2. Simpan Perhitungan SHU</b></div>
      <div class="card-body">
        <form method="post" action="controllers/proses_shu.php">
          <input type="hidden" name="aksi" value="simpan">
          <input type="hidden" name="tahun" value="<?= $tahun ?>">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Total Pendapatan (Rp)</label>
              <input type="text" name="total_pendapatan" class="form-control input-uang"
                     value="<?= number_format($lr['total_pendapatan'], 0, ',', '.') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Total Beban (Rp)</label>
              <input type="text" name="total_beban" class="form-control input-uang"
                     value="<?= number_format($lr['total_beban'], 0, ',', '.') ?>">
            </div>
          </div>
          <h6>Rencana Alokasi (sesuai Parameter):</h6>
          <table class="table table-sm table-bordered small">
            <thead class="table-light"><tr><th>Dana</th><th class="text-center">%</th><th class="text-end">Estimasi</th></tr></thead>
            <tbody>
            <?php foreach (ShuModel::$alokasiDefault as $nama => $kunci):
                $persen = (float)$pm->get($kunci, 0); ?>
              <tr>
                <td><?= e($nama) ?></td>
                <td class="text-center"><?= $persen ?>%</td>
                <td class="text-end"><?= rupiah($lr['laba'] * $persen / 100) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          <button class="btn btn-success"><i class="fas fa-save me-1"></i> Hitung & Simpan SHU</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>