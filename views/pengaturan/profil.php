<?php
require_once __DIR__ . '/../../models/PengaturanModel.php';
$p = (new PengaturanModel())->profil();

$pageTitle = 'Profil Koperasi';
$active    = 'pengaturan-profil';
include __DIR__ . '/../layout/header.php';
?>
<div class="card border-0 shadow-sm" style="max-width: 720px">
  <div class="card-header bg-white"><b><i class="fas fa-building me-1"></i> Profil Koperasi</b></div>
  <div class="card-body">
    <form method="post" action="controllers/proses_pengaturan.php" class="row g-3">
      <input type="hidden" name="aksi" value="simpan_profil">
      <div class="col-md-8">
        <label class="form-label">Nama Koperasi</label>
        <input type="text" name="nama_koperasi" class="form-control" required value="<?= e($p['nama_koperasi']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">No. Telepon</label>
        <input type="text" name="no_telp" class="form-control" value="<?= e($p['no_telp']) ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Alamat</label>
        <textarea name="alamat" class="form-control" rows="2"><?= e($p['alamat']) ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= e($p['email']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Nama Ketua</label>
        <input type="text" name="nama_ketua" class="form-control" value="<?= e($p['nama_ketua']) ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Slogan</label>
        <input type="text" name="slogan" class="form-control" value="<?= e($p['slogan']) ?>">
      </div>
      <div class="col-12">
        <button class="btn btn-success"><i class="fas fa-save me-1"></i> Simpan Profil</button>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>