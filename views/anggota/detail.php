<?php
require_once __DIR__ . '/../../models/AnggotaModel.php';

$id = (int)($_GET['id'] ?? 0);
$m  = new AnggotaModel();
$a  = $m->find($id);
if (!$a) { header('Location: index.php?mod=anggota&act=list'); exit; }

$saldo = $m->saldoSimpanan($id);
$rs    = $m->riwayatSimpanan($id);
$rp    = $m->riwayatPembiayaan($id);
$total = array_sum($saldo);

$pageTitle = 'Detail Anggota';
$active    = 'anggota';
include __DIR__ . '/../layout/header.php';
?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body text-center">
        <div class="avatar mx-auto mb-2"><i class="fas fa-user"></i></div>
        <h5 class="mb-0"><?= e($a['nama']) ?></h5>
        <div class="text-muted"><?= e($a['kode_anggota']) ?> · <?= badge_status($a['status_anggota']) ?></div>
        <hr>
        <table class="table table-sm table-borderless small text-start mb-0">
          <tr><th width="35%">NIK</th><td>: <?= e($a['nik'] ?: '-') ?></td></tr>
          <tr><th>JK</th><td>: <?= $a['jenis_kelamin']==='L'?'Laki-laki':'Perempuan' ?></td></tr>
          <tr><th>TTL</th><td>: <?= e(trim($a['tempat_lahir'].' '.($a['tanggal_lahir']?date('d/m/Y',strtotime($a['tanggal_lahir'])):''))) ?: '-' ?></td></tr>
          <tr><th>HP</th><td>: <?= e($a['no_hp'] ?: '-') ?></td></tr>
          <tr><th>Email</th><td>: <?= e($a['email'] ?: '-') ?></td></tr>
          <tr><th>Pekerjaan</th><td>: <?= e($a['pekerjaan'] ?: '-') ?></td></tr>
          <tr><th>Alamat</th><td>: <?= e($a['alamat'] ?: '-') ?></td></tr>
        </table>
      </div>
      <div class="card-footer bg-white d-flex gap-2">
        <a href="index.php?mod=anggota&act=form&id=<?= $id ?>" class="btn btn-sm btn-primary flex-fill">
          <i class="fas fa-edit me-1"></i> Edit</a>
        <a href="index.php?mod=simpanan&act=form&id_anggota=<?= $id ?>" class="btn btn-sm btn-success flex-fill">
          <i class="fas fa-plus me-1"></i> Simpanan</a>
        <a href="index.php?mod=anggota&act=rekening_koran&id=<?= $id ?>" class="btn btn-sm btn-secondary flex-fill">
          <i class="fas fa-file-invoice-dollar me-1"></i> Rek. Koran</a>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="row g-3 mb-3">
      <div class="col-sm-3"><div class="card border-0 shadow-sm p-3 text-center">
        <small class="text-muted">Pokok</small><b><?= rupiah($saldo['pokok']) ?></b></div></div>
      <div class="col-sm-3"><div class="card border-0 shadow-sm p-3 text-center">
        <small class="text-muted">Wajib</small><b><?= rupiah($saldo['wajib']) ?></b></div></div>
      <div class="col-sm-3"><div class="card border-0 shadow-sm p-3 text-center">
        <small class="text-muted">Sukarela</small><b><?= rupiah($saldo['sukarela']) ?></b></div></div>
      <div class="col-sm-3"><div class="card border-0 shadow-sm p-3 text-center bg-success text-white">
        <small>Total Simpanan</small><b><?= rupiah($total) ?></b></div></div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white"><b>Riwayat Simpanan</b></div>
      <div class="table-responsive" style="max-height: 300px">
        <table class="table table-sm table-hover mb-0">
          <thead><tr><th>Tanggal</th><th>Jenis</th><th>Tipe</th><th class="text-end">Jumlah</th><th>Ket.</th></tr></thead>
          <tbody>
          <?php foreach ($rs as $t): ?>
            <tr>
              <td><?= e(date('d/m/Y', strtotime($t['tanggal']))) ?></td>
              <td><?= e(ucfirst($t['jenis_simpanan'])) ?></td>
              <td><span class="badge bg-<?= $t['tipe']==='setor'?'success':'danger' ?>"><?= e(ucfirst($t['tipe'])) ?></span></td>
              <td class="text-end"><?= rupiah($t['jumlah']) ?></td>
              <td class="small text-muted"><?= e($t['keterangan']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rs): ?><tr><td colspan="5" class="text-center text-muted py-3">Belum ada.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white"><b>Riwayat Pembiayaan</b></div>
      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
          <thead><tr><th>No.</th><th>Akad</th><th class="text-end">Jumlah</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($rp as $f): ?>
            <tr>
              <td><?= e($f['no_pembiayaan']) ?></td>
              <td><?= e($f['nama_akad']) ?></td>
              <td class="text-end"><?= rupiah($f['jumlah_pembiayaan']) ?></td>
              <td><?= badge_status($f['status']) ?></td>
              <td><a class="btn btn-sm btn-outline-primary"
                     href="index.php?mod=pembiayaan&act=detail&id=<?= (int)$f['id_pembiayaan'] ?>">Detail</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rp): ?><tr><td colspan="5" class="text-center text-muted py-3">Belum ada.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>