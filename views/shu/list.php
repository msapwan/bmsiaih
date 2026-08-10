<?php
require_once __DIR__ . '/../../models/ShuModel.php';

$rows = (new ShuModel())->all();

$pageTitle = 'SHU (Sisa Hasil Usaha)';
$active    = 'shu';
include __DIR__ . '/../layout/header.php';

$badgeShu = ['draft'=>'secondary','ditetapkan'=>'primary','dibagikan'=>'success'];
?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <b><i class="fas fa-chart-pie me-1"></i> Perhitungan SHU Per Tahun</b>
    <a href="index.php?mod=shu&act=form" class="btn btn-sm btn-success">
      <i class="fas fa-calculator me-1"></i> Hitung SHU Baru</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr><th>Tahun</th><th class="text-end">Pendapatan</th><th class="text-end">Beban</th>
            <th class="text-end">SHU</th><th>Status</th><th class="text-center">Aksi</th></tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= (int)$r['tahun'] ?></b></td>
          <td class="text-end"><?= rupiah($r['total_pendapatan']) ?></td>
          <td class="text-end"><?= rupiah($r['total_beban']) ?></td>
          <td class="text-end"><b class="text-success"><?= rupiah($r['total_shu']) ?></b></td>
          <td><span class="badge bg-<?= $badgeShu[$r['status']] ?? 'secondary' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
          <td class="text-center">
            <a class="btn btn-sm btn-outline-info" href="index.php?mod=shu&act=detail&id=<?= (int)$r['id_shu'] ?>">
              <i class="fas fa-eye"></i></a>
            <a class="btn btn-sm btn-outline-danger btn-hapus"
               href="controllers/proses_shu.php?aksi=hapus&id=<?= (int)$r['id_shu'] ?>">
              <i class="fas fa-trash"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada perhitungan SHU.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>