<?php
require_once __DIR__ . '/../../models/PengaturanModel.php';
$pmProfil = new PengaturanModel();
$p        = $pmProfil->profil();
$logoFile = trim((string)$pmProfil->get('logo', ''));
$logoAda2 = $logoFile !== '' && file_exists(__DIR__ . '/../../assets/img/' . $logoFile);

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

    <hr class="my-4">
    <h6 class="mb-3"><i class="fas fa-image me-1"></i> Logo Koperasi</h6>

    <div class="d-flex align-items-center gap-3 mb-3">
      <?php if ($logoAda2): ?>
        <img src="assets/img/<?= e($logoFile) ?>" alt="Logo saat ini"
             style="height:64px; object-fit:contain; border:1px solid #e2e8f0; border-radius:8px; padding:4px">
        <span class="small text-muted">Logo saat ini: <code><?= e($logoFile) ?></code></span>
      <?php else: ?>
        <span class="small text-muted">Belum ada logo (menggunakan ikon default).</span>
      <?php endif; ?>
    </div>

    <form method="post" action="controllers/proses_pengaturan.php" enctype="multipart/form-data" class="row g-3">
      <input type="hidden" name="aksi" value="simpan_logo">
      <div class="col-md-6">
        <label class="form-label">Upload Logo Baru (PNG/JPG/SVG/WEBP, maks 512 KB)</label>
        <input type="file" name="logo" class="form-control" accept="image/*" required>
      </div>
      <div class="col-12">
        <button class="btn btn-success"><i class="fas fa-upload me-1"></i> Upload & Ganti Logo</button>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>