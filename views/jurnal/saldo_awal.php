<?php
require_once __DIR__ . '/../../models/JurnalModel.php';

$jm    = new JurnalModel();
$akun  = $jm->akunSemua();
$ada   = $jm->saldoAwalAda();
$nilaiLama = [];
if ($ada) foreach ($ada['baris'] as $b) $nilaiLama[$b['kode_akun']] = $b['jumlah'];

$pageTitle = 'Jurnal Saldo Awal';
$active    = 'jurnal-saldo-awal';
include __DIR__ . '/../layout/header.php';

$grup = ['ASET'=>'aset','KEWAJIBAN'=>'kewajiban','EKUITAS'=>'ekuitas','PENDAPATAN'=>'pendapatan','BEBAN'=>'beban'];
?>
<div class="card border-0 shadow-sm" style="max-width: 860px">
  <div class="card-header bg-white"><b><i class="fas fa-flag-checkered me-1"></i> Input Saldo Awal</b></div>
  <div class="card-body">
    <?php if ($ada): ?>
    <div class="alert alert-info py-2 small">
      Saldo awal sudah ada (tanggal <?= e(date('d/m/Y', strtotime($ada['tanggal']))) ?>).
      Menyimpan kembali akan <b>mengganti</b> jurnal saldo awal sebelumnya.
    </div>
    <?php endif; ?>
    <div class="alert alert-light border small">
      <i class="fas fa-info-circle me-1"></i>
      Total <b>Debit harus = Kredit</b> (contoh: Kas 50 juta di debit → Simpanan Pokok/Wajib/Sukarela total 50 juta di kredit).
    </div>

    <form method="post" action="controllers/proses_jurnal.php">
      <input type="hidden" name="aksi" value="simpan_saldo_awal">
      <div class="row g-2 mb-3">
        <div class="col-auto">
          <label class="form-label small">Tanggal Saldo Awal</label>
          <input type="date" name="tanggal" class="form-control form-control-sm"
                 value="<?= e($ada['tanggal'] ?? date('Y-m-d')) ?>">
        </div>
      </div>

      <?php foreach ($grup as $label => $tipe): ?>
      <h6 class="mt-3 text-muted"><?= $label ?></h6>
      <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
          <tr><th>Kode</th><th>Nama Akun</th><th class="text-center">Normal</th><th style="width:220px">Nominal (Rp)</th></tr>
        </thead>
        <tbody>
        <?php foreach ($akun as $a) if ($a['tipe']===$tipe): ?>
          <tr>
            <td><code><?= e($a['kode_akun']) ?></code></td>
            <td><?= e($a['nama_akun']) ?></td>
            <td class="text-center"><span class="badge bg-light text-dark border"><?= e(ucfirst($a['saldo_normal'])) ?></span></td>
            <td><input type="text" name="nilai[<?= e($a['kode_akun']) ?>]" class="form-control form-control-sm input-uang saldo-input"
                       data-normal="<?= e($a['saldo_normal']) ?>" inputmode="numeric"
                       value="<?= isset($nilaiLama[$a['kode_akun']]) ? number_format($nilaiLama[$a['kode_akun']],0,',','.') : '' ?>"></td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
      <?php endforeach; ?>

      <div class="d-flex align-items-center gap-2 mb-3">
        <b>Total Debit:</b> <span id="totDr">0</span> &nbsp;|&nbsp;
        <b>Total Kredit:</b> <span id="totCr">0</span>
        <span id="stBalance" class="badge bg-danger">Belum seimbang</span>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-success"><i class="fas fa-save me-1"></i> Simpan Saldo Awal</button>
        <a href="index.php?mod=jurnal&act=list" class="btn btn-outline-secondary">Kembali</a>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('input', function (e) {
  if (!e.target.matches('.saldo-input')) return;
  var dr = 0, cr = 0;
  document.querySelectorAll('.saldo-input').forEach(function (inp) {
    var v = parseFloat(inp.value.replace(/\./g, '')) || 0;
    if (inp.dataset.normal === 'debit') dr += v; else cr += v;
  });
  document.getElementById('totDr').textContent = dr.toLocaleString('id-ID');
  document.getElementById('totCr').textContent = cr.toLocaleString('id-ID');
  var b = document.getElementById('stBalance');
  if (dr === cr && dr > 0) { b.className = 'badge bg-success'; b.textContent = 'Seimbang'; }
  else { b.className = 'badge bg-danger'; b.textContent = 'Belum seimbang'; }
});
</script>
<?php include __DIR__ . '/../layout/footer.php'; ?>