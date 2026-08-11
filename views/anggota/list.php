<?php
require_once __DIR__ . '/../../models/AnggotaModel.php';

$cari = trim($_GET['cari'] ?? '');
$rows = (new AnggotaModel())->all($cari);

$pageTitle = 'Data Anggota';
$active    = 'anggota';
include __DIR__ . '/../layout/header.php';
?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
    <form class="d-flex" method="get">
      <input type="hidden" name="mod" value="anggota">
      <input type="hidden" name="act" value="list">
      <input type="text" name="cari" class="form-control form-control-sm me-2"
             placeholder="Cari nama / kode / NIK / HP..." value="<?= e($cari) ?>">
      <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-search"></i></button>
    </form>
    <a href="index.php?mod=anggota&act=form" class="btn btn-sm btn-success">
      <i class="fas fa-plus me-1"></i> Anggota Baru</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr><th>Kode</th><th>Nama</th><th>JK</th><th>No. HP</th>
            <th>Pekerjaan</th><th>Status</th><th>Daftar</th><th class="text-center">Aksi</th></tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= e($r['kode_anggota']) ?></b></td>
          <td><?= e($r['nama']) ?><br><small class="text-muted"><?= e($r['nik']) ?></small></td>
          <td><?= $r['jenis_kelamin']==='L'?'Laki-laki':'Perempuan' ?></td>
          <td><?= e($r['no_hp']) ?></td>
          <td><?= e($r['pekerjaan']) ?></td>
          <td><?= badge_status($r['status_anggota']) ?></td>
          <td><?= e(date('d/m/Y', strtotime($r['tanggal_daftar']))) ?></td>
          <td class="text-center text-nowrap">
            <a class="btn btn-sm btn-outline-info" title="Detail"
               href="index.php?mod=anggota&act=detail&id=<?= (int)$r['id_anggota'] ?>"><i class="fas fa-eye"></i></a>
            <a class="btn btn-sm btn-outline-secondary" title="Rekening Koran"
               href="index.php?mod=anggota&act=rekening_koran&id=<?= (int)$r['id_anggota'] ?>"><i class="fas fa-file-invoice-dollar"></i></a>
            <a class="btn btn-sm btn-outline-primary" title="Edit"
               href="index.php?mod=anggota&act=form&id=<?= (int)$r['id_anggota'] ?>"><i class="fas fa-edit"></i></a>
            <a class="btn btn-sm btn-outline-danger btn-hapus" title="Hapus"
               href="controllers/proses_anggota.php?aksi=hapus&id=<?= (int)$r['id_anggota'] ?>"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">Data tidak ditemukan.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>