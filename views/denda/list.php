<?php
require_once __DIR__ . '/../../models/DendaModel.php';

$fStatus = $_GET['status'] ?? '';
$dm   = new DendaModel();
$rows = $dm->all($fStatus);

$pageTitle = 'Denda Ta\'zir';
$active    = 'denda';
include __DIR__ . '/../layout/header.php';
?>
<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card stat border-0 shadow-sm">
      <div class="icon bg-danger"><i class="fas fa-gavel"></i></div>
      <div class="body">
        <div class="label">Tarif Denda Aktif</div>
        <div class="nilai"><?= e($dm->keteranganTarif()) ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat border-0 shadow-sm">
      <div class="icon bg-success"><i class="fas fa-hand-holding-heart"></i></div>
      <div class="body">
        <div class="label">Dana Sosial Terkumpul (dari denda)</div>
        <div class="nilai"><?= rupiah($dm->totalTerkumpul()) ?></div>
      </div>
    </div>
  </div>
</div>

<div class="alert alert-light border small">
  <i class="fas fa-info-circle me-1"></i>
  Sesuai prinsip syariah, denda ta'zir <b>bukan pendapatan koperasi</b> — seluruhnya dicatat sebagai
  <b>Dana Sosial / Kebajikan (akun 305)</b>. Mode denda (nominal/persen) diatur di <b>Pengaturan → Parameter</b>.
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <b>Daftar Denda</b>
    <form method="get" class="d-flex gap-2">
      <input type="hidden" name="mod" value="denda">
      <input type="hidden" name="act" value="list">
      <select name="status" class="form-select form-select-sm" style="width:160px">
        <option value="">Semua</option>
        <option value="belum_bayar" <?= $fStatus==='belum_bayar'?'selected':'' ?>>Belum Bayar</option>
        <option value="lunas" <?= $fStatus==='lunas'?'selected':'' ?>>Lunas</option>
      </select>
      <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-filter"></i></button>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr><th>Tanggal</th><th>Anggota</th><th>No. Pembiayaan</th><th>Angsuran</th>
            <th class="text-center">Terlambat</th><th class="text-end">Denda</th><th>Status</th><th class="text-center">Aksi</th></tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= e(date('d/m/Y', strtotime($r['tanggal_denda']))) ?></td>
          <td><?= e($r['nama']) ?></td>
          <td><?= e($r['no_pembiayaan']) ?></td>
          <td>ke-<?= (int)$r['angsuran_ke'] ?></td>
          <td class="text-center"><span class="badge bg-danger"><?= (int)$r['hari_terlambat'] ?> hari</span></td>
          <td class="text-end"><b><?= rupiah($r['jumlah_denda']) ?></b></td>
          <td><?= $r['status']==='lunas'
                  ? '<span class="badge bg-success">Lunas</span>'
                  : '<span class="badge bg-warning text-dark">Belum Bayar</span>' ?></td>
          <td class="text-center">
            <?php if ($r['status'] !== 'lunas'): ?>
            <a class="btn btn-sm btn-success"
               href="controllers/proses_denda.php?aksi=bayar&id=<?= (int)$r['id_denda'] ?>"
               onclick="return confirm('Terima pembayaran denda <?= rupiah($r['jumlah_denda']) ?>?')">
              <i class="fas fa-check me-1"></i> Bayar</a>
            <?php else: ?>
            <small class="text-muted">dibayar <?= e(date('d/m/Y', strtotime($r['tanggal_bayar']))) ?></small>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data denda. Alhamdulillah!</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>