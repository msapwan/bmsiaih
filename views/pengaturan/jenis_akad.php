<?php
require_once __DIR__ . '/../../models/PengaturanModel.php';
$rows = (new PengaturanModel())->akadSemua();

$pageTitle = 'Jenis Akad';
$active    = 'pengaturan-akad';
include __DIR__ . '/../layout/header.php';
?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <b><i class="fas fa-handshake me-1"></i> Jenis Akad Pembiayaan</b>
    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalAkad"
            onclick="resetFormAkad()"><i class="fas fa-plus me-1"></i> Tambah</button>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Kode</th><th>Nama Akad</th><th>Tipe</th><th>Keterangan</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= e($r['kode_akad']) ?></b></td>
          <td><?= e($r['nama_akad']) ?></td>
          <td><span class="badge bg-light text-dark border"><?= e(ucfirst($r['tipe_akad'])) ?></span></td>
          <td class="small"><?= e($r['keterangan']) ?></td>
          <td><?= badge_status($r['status']) ?></td>
          <td class="text-center">
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAkad"
              data-id="<?= (int)$r['id_akad'] ?>" data-kode="<?= e($r['kode_akad']) ?>"
              data-nama="<?= e($r['nama_akad']) ?>" data-tipe="<?= e($r['tipe_akad']) ?>"
              data-ket="<?= e($r['keterangan']) ?>" data-status="<?= e($r['status']) ?>"
              onclick="isiFormAkad(this)"><i class="fas fa-edit"></i></button>
            <a class="btn btn-sm btn-outline-danger btn-hapus"
               href="controllers/proses_pengaturan.php?aksi=hapus_akad&id=<?= (int)$r['id_akad'] ?>">
              <i class="fas fa-trash"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalAkad" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="controllers/proses_pengaturan.php" class="modal-content">
      <input type="hidden" name="aksi" value="simpan_akad">
      <input type="hidden" name="id_akad" id="ak_id" value="0">
      <div class="modal-header"><h5 class="modal-title">Form Jenis Akad</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body row g-3">
        <div class="col-4"><label class="form-label">Kode</label>
          <input type="text" name="kode_akad" id="ak_kode" class="form-control" maxlength="10" required></div>
        <div class="col-8"><label class="form-label">Nama Akad</label>
          <input type="text" name="nama_akad" id="ak_nama" class="form-control" required></div>
        <div class="col-6"><label class="form-label">Tipe</label>
          <select name="tipe_akad" id="ak_tipe" class="form-select">
            <option value="margin">Margin (Murabahah/Ijarah)</option>
            <option value="bagihasil">Bagi Hasil (Mudharabah/Musyarakah)</option>
            <option value="sosial">Sosial (Qardh)</option>
          </select></div>
        <div class="col-6"><label class="form-label">Status</label>
          <select name="status" id="ak_status" class="form-select">
            <option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option>
          </select></div>
        <div class="col-12"><label class="form-label">Keterangan</label>
          <textarea name="keterangan" id="ak_ket" class="form-control" rows="2"></textarea></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-success">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function resetFormAkad() {
  document.getElementById('ak_id').value = 0;
  ['ak_kode','ak_nama','ak_ket'].forEach(function(i){ document.getElementById(i).value = ''; });
  document.getElementById('ak_tipe').value = 'margin';
  document.getElementById('ak_status').value = 'aktif';
}
function isiFormAkad(el) {
  document.getElementById('ak_id').value    = el.dataset.id;
  document.getElementById('ak_kode').value  = el.dataset.kode;
  document.getElementById('ak_nama').value  = el.dataset.nama;
  document.getElementById('ak_tipe').value  = el.dataset.tipe;
  document.getElementById('ak_status').value= el.dataset.status;
  document.getElementById('ak_ket').value   = el.dataset.ket;
}
</script>
<?php include __DIR__ . '/../layout/footer.php'; ?>