<?php
require_once __DIR__ . '/../../models/AnggotaModel.php';
require_once __DIR__ . '/../../models/PengaturanModel.php';

$am   = new AnggotaModel();
$pm   = new PengaturanModel();
$opsi = $am->opsiAktif();
$pre  = (int)($_GET['id_anggota'] ?? 0);

$pageTitle = 'Transaksi Simpanan Baru';
$active    = 'simpanan';
include __DIR__ . '/../layout/header.php';
?>
<div class="card border-0 shadow-sm" style="max-width: 640px">
  <div class="card-header bg-white"><b><i class="fas fa-piggy-bank me-1"></i> Transaksi Simpanan</b></div>
  <div class="card-body">
    <form method="post" action="controllers/proses_simpanan.php" class="row g-3">
      <input type="hidden" name="aksi" value="simpan">
      <div class="col-12">
        <label class="form-label">Anggota <span class="text-danger">*</span></label>
        <select name="id_anggota" class="form-select" required>
          <option value="">— Pilih Anggota —</option>
          <?php foreach ($opsi as $o): ?>
          <option value="<?= (int)$o['id_anggota'] ?>" <?= $pre===(int)$o['id_anggota']?'selected':'' ?>>
            <?= e($o['kode_anggota'].' - '.$o['nama']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Jenis Simpanan</label>
        <select name="jenis_simpanan" id="jenis_simpanan" class="form-select">
          <option value="pokok"    data-nominal="<?= e($pm->get('simpanan_pokok')) ?>">Pokok</option>
          <option value="wajib"    data-nominal="<?= e($pm->get('simpanan_wajib')) ?>">Wajib</option>
          <option value="sukarela" data-nominal="">Sukarela</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Tipe Transaksi</label>
        <select name="tipe" class="form-select">
          <option value="setor">Setor (masuk)</option>
          <option value="tarik">Tarik (keluar)</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Tanggal</label>
        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Jumlah (Rp) <span class="text-danger">*</span></label>
        <input type="text" name="jumlah" id="jumlah" class="form-control input-uang" required inputmode="numeric">
      </div>
      <div class="col-12">
        <label class="form-label">Keterangan</label>
        <input type="text" name="keterangan" class="form-control" placeholder="Opsional">
      </div>
      <div class="col-12 d-flex gap-2">
        <button class="btn btn-success"><i class="fas fa-save me-1"></i> Simpan Transaksi</button>
        <a href="index.php?mod=simpanan&act=list" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>