<?php
require_once __DIR__ . '/../../models/AnggotaModel.php';
require_once __DIR__ . '/../../models/PengaturanModel.php';

$am          = new AnggotaModel();
$pm          = new PengaturanModel();
$opsiAnggota = $am->opsiAktif();
$opsiAkad    = $pm->opsiAkad();

$pageTitle = 'Pengajuan Pembiayaan';
$active    = 'pembiayaan-pengajuan';
include __DIR__ . '/../layout/header.php';
?>
<div class="card border-0 shadow-sm" style="max-width: 760px">
  <div class="card-header bg-white"><b><i class="fas fa-file-signature me-1"></i> Form Pengajuan Pembiayaan</b></div>
  <div class="card-body">
    <form method="post" action="controllers/proses_pembiayaan.php" class="row g-3">
      <input type="hidden" name="aksi" value="ajukan">
      <div class="col-md-7">
        <label class="form-label">Anggota <span class="text-danger">*</span></label>
        <select name="id_anggota" class="form-select" required>
          <option value="">— Pilih Anggota —</option>
          <?php foreach ($opsiAnggota as $o): ?>
          <option value="<?= (int)$o['id_anggota'] ?>"><?= e($o['kode_anggota'].' - '.$o['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-5">
        <label class="form-label">Tanggal Pengajuan</label>
        <input type="date" name="tanggal_pengajuan" class="form-control" value="<?= date('Y-m-d') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Akad <span class="text-danger">*</span></label>
        <select name="id_akad" id="id_akad" class="form-select" required>
          <?php foreach ($opsiAkad as $k): ?>
          <option value="<?= (int)$k['id_akad'] ?>" data-tipe="<?= e($k['tipe_akad']) ?>"
                  title="<?= e($k['keterangan']) ?>"><?= e($k['nama_akad']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Jangka Waktu (bulan) <span class="text-danger">*</span></label>
        <input type="number" name="jangka_waktu" class="form-control" min="1" max="60" value="12" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Jumlah Pengajuan (Rp) <span class="text-danger">*</span></label>
        <input type="text" name="jumlah_pembiayaan" class="form-control input-uang" required inputmode="numeric">
      </div>
      <div class="col-md-3" id="grupMargin">
        <label class="form-label">Margin (%/thn)</label>
        <input type="number" step="0.5" name="margin_persen" class="form-control"
               value="<?= e($pm->get('margin_default', 12)) ?>">
      </div>
      <div class="col-md-3 d-none" id="grupNisbah">
        <label class="form-label">Nisbah Koperasi (%)</label>
        <input type="number" step="1" name="nisbah_koperasi" class="form-control"
               value="<?= e($pm->get('nisbah_default', 60)) ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Catatan / Tujuan Pembiayaan</label>
        <textarea name="catatan" class="form-control" rows="2"></textarea>
      </div>
      <div class="col-12">
        <div class="alert alert-light border small mb-0">
          <i class="fas fa-info-circle me-1"></i>
          Akad <b>Murabahah/Ijarah</b>: margin dihitung saat persetujuan.
          Akad <b>Mudharabah/Musyarakah</b>: nisbah bagi hasil. Akad <b>Qardh</b>: tanpa margin.
        </div>
      </div>
      <div class="col-12 d-flex gap-2">
        <button class="btn btn-success"><i class="fas fa-paper-plane me-1"></i> Ajukan</button>
        <a href="index.php?mod=pembiayaan&act=list" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>