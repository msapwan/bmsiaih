<?php
require_once __DIR__ . '/../../models/PengaturanModel.php';
$rows = (new PengaturanModel())->userSemua();

$pageTitle = 'Akun Pengguna';
$active    = 'pengaturan-user';
include __DIR__ . '/../layout/header.php';
?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <b><i class="fas fa-user-cog me-1"></i> Akun Pengguna</b>
    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalUser"
            onclick="resetFormUser()"><i class="fas fa-plus me-1"></i> Tambah</button>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Username</th><th>Nama Lengkap</th><th>Level</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= e($r['username']) ?></b></td>
          <td><?= e($r['nama_lengkap']) ?></td>
          <td><span class="badge bg-primary"><?= e($r['level']) ?></span></td>
          <td><?= badge_status($r['status']) ?></td>
          <td class="text-center">
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalUser"
              data-id="<?= (int)$r['id_user'] ?>" data-username="<?= e($r['username']) ?>"
              data-nama="<?= e($r['nama_lengkap']) ?>" data-level="<?= e($r['level']) ?>"
              data-status="<?= e($r['status']) ?>" onclick="isiFormUser(this)"><i class="fas fa-edit"></i></button>
            <?php if ($r['id_user'] != $user['id_user']): ?>
            <a class="btn btn-sm btn-outline-danger btn-hapus"
               href="controllers/proses_pengaturan.php?aksi=hapus_user&id=<?= (int)$r['id_user'] ?>">
              <i class="fas fa-trash"></i></a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalUser" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="controllers/proses_pengaturan.php" class="modal-content">
      <input type="hidden" name="aksi" value="simpan_user">
      <input type="hidden" name="id_user" id="us_id" value="0">
      <div class="modal-header"><h5 class="modal-title">Form Akun Pengguna</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body row g-3">
        <div class="col-6"><label class="form-label">Username</label>
          <input type="text" name="username" id="us_username" class="form-control" required></div>
        <div class="col-6"><label class="form-label">Password <small class="text-muted">(kosongkan jika tetap)</small></label>
          <input type="password" name="password" class="form-control" autocomplete="new-password"></div>
        <div class="col-12"><label class="form-label">Nama Lengkap</label>
          <input type="text" name="nama_lengkap" id="us_nama" class="form-control" required></div>
        <div class="col-6"><label class="form-label">Level</label>
          <select name="level" id="us_level" class="form-select">
            <option value="admin">Admin</option><option value="manager">Manager</option><option value="kasir">Kasir</option>
          </select></div>
        <div class="col-6"><label class="form-label">Status</label>
          <select name="status" id="us_status" class="form-select">
            <option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option>
          </select></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-success">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function resetFormUser() {
  document.getElementById('us_id').value = 0;
  ['us_username','us_nama'].forEach(function(i){ document.getElementById(i).value = ''; });
  document.getElementById('us_level').value = 'kasir';
  document.getElementById('us_status').value = 'aktif';
}
function isiFormUser(el) {
  document.getElementById('us_id').value       = el.dataset.id;
  document.getElementById('us_username').value = el.dataset.username;
  document.getElementById('us_nama').value     = el.dataset.nama;
  document.getElementById('us_level').value    = el.dataset.level;
  document.getElementById('us_status').value   = el.dataset.status;
}
</script>
<?php include __DIR__ . '/../layout/footer.php'; ?>