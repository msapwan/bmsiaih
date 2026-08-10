<?php
require_once __DIR__ . '/../../models/JurnalModel.php';

$jm   = new JurnalModel();
$akun = $jm->akunSemua();
$grupAkun = [];
foreach ($akun as $a) $grupAkun[$a['tipe']][] = $a;
$labelTipe = ['aset'=>'Aset','kewajiban'=>'Kewajiban','ekuitas'=>'Ekuitas','pendapatan'=>'Pendapatan','beban'=>'Beban'];

function barisJurnal(array $grupAkun, array $labelTipe): void
{
    ?>
    <tr>
      <td>
        <select name="id_akun[]" class="form-select form-select-sm">
          <option value="">— Pilih Akun —</option>
          <?php foreach ($grupAkun as $tipe => $list): ?>
          <optgroup label="<?= e($labelTipe[$tipe] ?? $tipe) ?>">
            <?php foreach ($list as $a): ?>
            <option value="<?= (int)$a['id_akun'] ?>"><?= e($a['kode_akun'].' — '.$a['nama_akun']) ?></option>
            <?php endforeach; ?>
          </optgroup>
          <?php endforeach; ?>
        </select>
      </td>
      <td>
        <select name="posisi[]" class="form-select form-select-sm">
          <option value="debit">Debit</option>
          <option value="kredit">Kredit</option>
        </select>
      </td>
      <td><input type="text" name="jumlah[]" class="form-control form-control-sm input-uang" inputmode="numeric"></td>
      <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btnHapusBaris"><i class="fas fa-times"></i></button></td>
    </tr>
    <?php
}

$pageTitle = 'Jurnal Manual';
$active    = 'jurnal';
include __DIR__ . '/../layout/header.php';
?>
<div class="card border-0 shadow-sm" style="max-width: 900px">
  <div class="card-header bg-white"><b><i class="fas fa-book me-1"></i> Jurnal Umum Manual</b></div>
  <div class="card-body">
    <form method="post" action="controllers/proses_jurnal.php">
      <input type="hidden" name="aksi" value="simpan">
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Tanggal</label>
          <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-9">
          <label class="form-label">Keterangan</label>
          <input type="text" name="keterangan" class="form-control" placeholder="Uraian jurnal">
        </div>
      </div>

      <table class="table table-bordered align-middle" id="tblJurnal">
        <thead class="table-light">
          <tr><th style="width:45%">Akun</th><th style="width:15%">Posisi</th><th>Jumlah (Rp)</th><th style="width:50px"></th></tr>
        </thead>
        <tbody>
          <?php barisJurnal($grupAkun, $labelTipe); barisJurnal($grupAkun, $labelTipe); ?>
        </tbody>
        <tfoot class="table-light fw-bold">
          <tr>
            <td colspan="2" class="text-end">TOTAL</td>
            <td colspan="2">
              Debit: <span id="totDebit">0</span> &nbsp;|&nbsp; Kredit: <span id="totKredit">0</span>
              <span id="statusSeimbang" class="badge bg-danger ms-2">Belum seimbang</span>
            </td>
          </tr>
        </tfoot>
      </table>

      <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="btnBaris">
        <i class="fas fa-plus me-1"></i> Tambah Baris</button>

      <div class="d-flex gap-2">
        <button class="btn btn-success"><i class="fas fa-save me-1"></i> Simpan Jurnal</button>
        <a href="index.php?mod=jurnal&act=list" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>

<template id="tplBaris">
  <?php barisJurnal($grupAkun, $labelTipe); ?>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var tpl = document.getElementById('tplBaris');
  var tbody = document.querySelector('#tblJurnal tbody');

  document.getElementById('btnBaris').addEventListener('click', function () {
    tbody.appendChild(tpl.content.cloneNode(true));
  });

  document.addEventListener('click', function (e) {
    if (e.target.closest('.btnHapusBaris')) {
      if (tbody.querySelectorAll('tr').length > 2) e.target.closest('tr').remove();
      hitungTotal();
    }
  });

  function hitungTotal() {
    var dr = 0, cr = 0;
    tbody.querySelectorAll('tr').forEach(function (tr) {
      var inp = tr.querySelector('input[name="jumlah[]"]');
      var jumlah = parseFloat((inp ? inp.value : '0').replace(/\./g, '')) || 0;
      var posisi = tr.querySelector('select[name="posisi[]"]');
      if (posisi && posisi.value === 'debit') dr += jumlah; else cr += jumlah;
    });
    document.getElementById('totDebit').textContent  = dr.toLocaleString('id-ID');
    document.getElementById('totKredit').textContent = cr.toLocaleString('id-ID');
    var badge = document.getElementById('statusSeimbang');
    if (dr === cr && dr > 0) { badge.className = 'badge bg-success ms-2'; badge.textContent = 'Seimbang'; }
    else { badge.className = 'badge bg-danger ms-2'; badge.textContent = 'Belum seimbang'; }
  }

  document.addEventListener('input',  function (e) { if (e.target.closest('#tblJurnal')) hitungTotal(); });
  document.addEventListener('change', function (e) { if (e.target.closest('#tblJurnal')) hitungTotal(); });
});
</script>
<?php include __DIR__ . '/../layout/footer.php'; ?>