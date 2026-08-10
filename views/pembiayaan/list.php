<?php
require_once __DIR__ . '/../../models/PembiayaanModel.php';

$fStatus = $_GET['status'] ?? '';
$rows = (new PembiayaanModel())->all($fStatus);

$pageTitle = 'Data Pembiayaan';
$active    = 'pembiayaan';
include __DIR__ . '/../layout/header.php';
?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
    <form method="get" class="d-flex gap-2">
      <input type="hidden" name="mod" value="pembiayaan">
      <input type="hidden" name="act" value="list">
      <select name="status" class="form-select form-select-sm" style="width:170px">
        <option value="">Semua Status</option>
        <?php foreach (['pengajuan','berjalan','lunas','ditolak'] as $s): ?>
        <option value="<?= $s ?>" <?= $fStatus===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-filter"></i></button>
    </form>
    <a href="index.php?mod=pembiayaan&act=pengajuan" class="btn btn-sm btn-success">
      <i class="fas fa-plus me-1"></i> Pengajuan Baru</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr><th>No. Pembiayaan</th><th>Anggota</th><th>Akad</th>
            <th class="text-end">Plafon</th><th class="text-end">Sisa Piutang</th>
            <th>Jangka</th><th>Status</th><th class="text-center">Aksi</th></tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r):
          $sisa = $r['status']==='berjalan' ? ($r['total_piutang'] - $r['total_bayar']) : 0; ?>
        <tr>
          <td><b><?= e($r['no_pembiayaan']) ?></b></td>
          <td><?= e($r['nama']) ?><br><small class="text-muted"><?= e($r['kode_anggota']) ?></small></td>
          <td><?= e($r['nama_akad']) ?></td>
          <td class="text-end"><?= rupiah($r['jumlah_pembiayaan']) ?></td>
          <td class="text-end"><?= $sisa > 0 ? rupiah($sisa) : '—' ?></td>
          <td><?= (int)$r['jangka_waktu'] ?> bln</td>
          <td><?= badge_status($r['status']) ?></td>
          <td class="text-center">
            <a class="btn btn-sm btn-outline-info"
               href="index.php?mod=pembiayaan&act=detail&id=<?= (int)$r['id_pembiayaan'] ?>"><i class="fas fa-eye"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>