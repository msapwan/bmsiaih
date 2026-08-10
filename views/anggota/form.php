<?php
require_once __DIR__ . '/../../models/AnggotaModel.php';

$id = (int)($_GET['id'] ?? 0);
$a  = $id ? (new AnggotaModel())->find($id) : null;
if ($id && !$a) { header('Location: index.php?mod=anggota&act=list'); exit; }

$pageTitle = $id ? 'Edit Anggota' : 'Anggota Baru';
$active    = 'anggota';
include __DIR__ . '/../layout/header.php';
?>
<div class="card border-0 shadow-sm" style="max-width: 860px">
  <div class="card-header bg-white"><b><i class="fas fa-user-plus me-1"></i> <?= e($pageTitle) ?></b></div>
  <div class="card-body">
    <form method="post" action="controllers/proses_anggota.php" class="row g-3">
      <input type="hidden" name="aksi" value="simpan">
      <input type="hidden" name="id_anggota" value="<?= $id ?>">
      <div class="col-md-6">
        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
        <input type="text" name="nama" class="form-control" required value="<?= e($a['nama'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">NIK</label>
        <input type="text" name="nik" class="form-control" maxlength="20" value="<?= e($a['nik'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Jenis Kelamin</label>
        <select name="jenis_kelamin" class="form-select">
          <option value="L" <?= ($a['jenis_kelamin'] ?? '')==='L'?'selected':'' ?>>Laki-laki</option>
          <option value="P" <?= ($a['jenis_kelamin'] ?? '')==='P'?'selected':'' ?>>Perempuan</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Tempat Lahir</label>
        <input type="text" name="tempat_lahir" class="form-control" value="<?= e($a['tempat_lahir'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" class="form-control" value="<?= e($a['tanggal_lahir'] ?? '') ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Alamat</label>
        <textarea name="alamat" class="form-control" rows="2"><?= e($a['alamat'] ?? '') ?></textarea>
      </div>
      <div class="col-md-4">
        <label class="form-label">No. HP</label>
        <input type="text" name="no_hp" class="form-control" value="<?= e($a['no_hp'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= e($a['email'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Pekerjaan</label>
        <input type="text" name="pekerjaan" class="form-control" value="<?= e($a['pekerjaan'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Tanggal Daftar</label>
        <input type="date" name="tanggal_daftar" class="form-control" value="<?= e($a['tanggal_daftar'] ?? date('Y-m-d')) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status_anggota" class="form-select">
          <option value="aktif" <?= ($a['status_anggota'] ?? '')==='aktif'?'selected':'' ?>>Aktif</option>
          <option value="nonaktif" <?= ($a['status_anggota'] ?? '')==='nonaktif'?'selected':'' ?>>Nonaktif</option>
        </select>
      </div>
      <div class="col-12 d-flex gap-2">
        <button class="btn btn-success"><i class="fas fa-save me-1"></i> Simpan</button>
        <a href="index.php?mod=anggota&act=list" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>