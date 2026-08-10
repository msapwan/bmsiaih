<?php
require_once __DIR__ . '/../../models/JurnalModel.php';

$jm     = new JurnalModel();
$akun   = $jm->akunSemua();
$idAkun = (int)($_GET['id_akun'] ?? 0);
$dari   = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

$bb = null; $error = '';
if ($idAkun > 0) {
    try { $bb = $jm->bukuBesar($idAkun, $dari, $sampai); }
    catch (Exception $ex) { $error = $ex->getMessage(); }
}

$grupAkun = [];
foreach ($akun as $a) $grupAkun[$a['tipe']][] = $a;
$labelTipe = ['aset'=>'Aset','kewajiban'=>'Kewajiban','ekuitas'=>'Ekuitas','pendapatan'=>'Pendapatan','beban'=>'Beban'];

$pageTitle = 'Buku Besar';
$active    = 'jurnal-buku-besar';
include __DIR__ . '/../layout/header.php';
?>
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end">
      <input type="hidden" name="mod" value="jurnal">
      <input type="hidden" name="act" value="buku_besar">
      <div class="col-md-4">
        <label class="form-label small">Akun</label>
        <select name="id_akun" class="form-select form-select-sm">
          <option value="">— Pilih Akun —</option>
          <?php foreach ($grupAkun as $tipe => $list): ?>
          <optgroup label="<?= e($labelTipe[$tipe] ?? $tipe) ?>">
            <?php foreach ($list as $a): ?>
            <option value="<?= (int)$a['id_akun'] ?>" <?= $idAkun===(int)$a['id_akun']?'selected':'' ?>>
              <?= e($a['kode_akun'].' — '.$a['nama_akun']) ?>
            </option>
            <?php endforeach; ?>
          </optgroup>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto"><label class="form-label small">Dari</label>
        <input type="date" name="dari" class="form-control form-control-sm" value="<?= e($dari) ?>"></div>
      <div class="col-auto"><label class="form-label small">Sampai</label>
        <input type="date" name="sampai" class="form-control form-control-sm" value="<?= e($sampai) ?>"></div>
      <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="fas fa-eye me-1"></i> Tampilkan</button></div>
      <?php if ($bb): ?>
      <div class="col-auto ms-auto d-flex gap-2">
        <a class="btn btn-sm btn-outline-success"
           href="controllers/proses_export.php?jenis=buku_besar&id_akun=<?= $idAkun ?>&dari=<?= e($dari) ?>&sampai=<?= e($sampai) ?>">
          <i class="fas fa-file-excel me-1"></i> Excel</a>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
          <i class="fas fa-print me-1"></i> PDF</button>
      </div>
      <?php endif; ?>
    </form>
  </div>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

<?php if ($bb): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white">
    <b>Buku Besar: <?= e($bb['akun']['kode_akun'].' — '.$bb['akun']['nama_akun']) ?></b>
    <span class="badge bg-light text-dark border ms-1">Saldo normal: <?= e(ucfirst($bb['akun']['saldo_normal'])) ?></span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover table-sm align-middle mb-0">
      <thead>
        <tr><th>Tanggal</th><th>No. Jurnal</th><th>Keterangan</th>
            <th class="text-end">Debit</th><th class="text-end">Kredit</th><th class="text-end">Saldo</th></tr>
      </thead>
      <tbody>
        <tr class="table-light">
          <td colspan="3"><i>Saldo Awal (sebelum <?= e(date('d/m/Y', strtotime($dari))) ?>)</i></td>
          <td></td><td></td><td class="text-end"><b><?= rupiah($bb['saldo_awal']) ?></b></td>
        </tr>
        <?php foreach ($bb['baris'] as $b): ?>
        <tr>
          <td><?= e(date('d/m/Y', strtotime($b['tanggal']))) ?></td>
          <td><?= e($b['no_jurnal']) ?></td>
          <td><?= e($b['keterangan']) ?></td>
          <td class="text-end"><?= $b['posisi']==='debit' ? rupiah($b['jumlah']) : '' ?></td>
          <td class="text-end"><?= $b['posisi']==='kredit' ? rupiah($b['jumlah']) : '' ?></td>
          <td class="text-end"><?= rupiah($b['saldo']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light fw-bold">
        <tr>
          <td colspan="3">Mutasi Periode</td>
          <td class="text-end"><?= rupiah($bb['mutasi_debit']) ?></td>
          <td class="text-end"><?= rupiah($bb['mutasi_kredit']) ?></td>
          <td class="text-end"><?= rupiah($bb['saldo_akhir']) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
<?php elseif (!$error): ?>
<div class="alert alert-info">Pilih akun untuk menampilkan buku besar.</div>
<?php endif; ?>
<?php include __DIR__ . '/../layout/footer.php'; ?>