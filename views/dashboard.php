<?php
require_once __DIR__ . '/../models/LaporanModel.php';

$lm        = new LaporanModel();
$stat      = $lm->statistik();
$transaksi = $lm->transaksiTerbaru(6);
$pending   = $lm->pengajuanPending(5);

$pageTitle = 'Dashboard';
$active    = 'dashboard';
include __DIR__ . '/layout/header.php';

$npf = ((int)$stat['berjalan'] > 0) ? round((int)$stat['macet'] / (int)$stat['berjalan'] * 100, 1) : 0;
?>
<div class="row g-3 mb-4">
  <div class="col-md-3 col-sm-6">
    <div class="card stat border-0 shadow-sm">
      <div class="icon bg-primary"><i class="fas fa-users"></i></div>
      <div class="body"><div class="label">Anggota Aktif</div>
      <div class="nilai"><?= (int)$stat['anggota_aktif'] ?></div></div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="card stat border-0 shadow-sm">
      <div class="icon bg-success"><i class="fas fa-piggy-bank"></i></div>
      <div class="body"><div class="label">Total Simpanan</div>
      <div class="nilai"><?= rupiah($stat['total_simpanan']) ?></div></div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="card stat border-0 shadow-sm">
      <div class="icon bg-warning"><i class="fas fa-hand-holding-usd"></i></div>
      <div class="body"><div class="label">Pembiayaan Berjalan</div>
      <div class="nilai"><?= rupiah($stat['outstanding']) ?></div></div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="card stat border-0 shadow-sm">
      <div class="icon bg-danger"><i class="fas fa-exclamation-triangle"></i></div>
      <div class="body"><div class="label">Rasio Macet (NPF)</div>
      <div class="nilai"><?= $npf ?>%</div></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <b><i class="fas fa-history me-1"></i> Transaksi Simpanan Terbaru</b>
        <a class="btn btn-sm btn-outline-primary" href="index.php?mod=simpanan&act=list">Lihat semua</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Tanggal</th><th>Anggota</th><th>Jenis</th><th>Tipe</th><th class="text-end">Jumlah</th></tr></thead>
          <tbody>
          <?php foreach ($transaksi as $t): ?>
            <tr>
              <td><?= e(date('d/m/Y', strtotime($t['tanggal']))) ?></td>
              <td><?= e($t['nama']) ?></td>
              <td><span class="badge bg-light text-dark border"><?= e(ucfirst($t['jenis_simpanan'])) ?></span></td>
              <td><span class="badge bg-<?= $t['tipe']==='setor'?'success':'danger' ?>"><?= e(ucfirst($t['tipe'])) ?></span></td>
              <td class="text-end"><?= rupiah($t['jumlah']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$transaksi): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white">
        <b><i class="fas fa-inbox me-1"></i> Pengajuan Pembiayaan (<?= (int)$stat['pengajuan'] ?>)</b>
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($pending as $p): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <b><?= e($p['nama']) ?></b><br>
            <small class="text-muted"><?= e($p['nama_akad']) ?> · <?= rupiah($p['jumlah_pembiayaan']) ?> · <?= (int)$p['jangka_waktu'] ?> bln</small>
          </div>
          <a href="index.php?mod=pembiayaan&act=detail&id=<?= (int)$p['id_pembiayaan'] ?>" class="btn btn-sm btn-primary">Proses</a>
        </li>
        <?php endforeach; ?>
        <?php if (!$pending): ?>
        <li class="list-group-item text-center text-muted py-4">Tidak ada pengajuan menunggu.</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>
<?php include __DIR__ . '/layout/footer.php'; ?>