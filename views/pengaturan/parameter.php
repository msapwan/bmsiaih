<?php
require_once __DIR__ . '/../../models/PengaturanModel.php';
$rows = (new PengaturanModel())->semuaParameter();

$pageTitle = 'Parameter Koperasi';
$active    = 'pengaturan-parameter';
include __DIR__ . '/../layout/header.php';
?>
<div class="card border-0 shadow-sm" style="max-width: 760px">
  <div class="card-header bg-white"><b><i class="fas fa-sliders-h me-1"></i> Parameter Sistem</b></div>
  <div class="card-body">
    <form method="post" action="controllers/proses_pengaturan.php">
      <input type="hidden" name="aksi" value="simpan_parameter">
      <table class="table align-middle">
        <thead><tr><th style="width:35%">Parameter</th><th>Nilai</th><th style="width:40%">Keterangan</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><code><?= e($r['kunci']) ?></code></td>
            <td><input type="text" name="nilai[<?= e($r['kunci']) ?>]" class="form-control form-control-sm"
                       value="<?= e($r['nilai']) ?>"></td>
            <td class="small text-muted"><?= e($r['keterangan']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <button class="btn btn-success"><i class="fas fa-save me-1"></i> Simpan Parameter</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>