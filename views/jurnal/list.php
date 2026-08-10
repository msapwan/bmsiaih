<?php
require_once __DIR__ . '/../../models/JurnalModel.php';

$dari   = $_GET['dari'] ?? '';
$sampai = $_GET['sampai'] ?? '';

$jm   = new JurnalModel();
$rows = $jm->all($dari, $sampai);
$grup = $jm->detailsUntuk(array_column($rows, 'id_jurnal'));

$pageTitle = 'Jurnal Umum';
$active    = 'jurnal';
include __DIR__ . '/../layout/header.php';

$badgeSumber = ['manual'=>'secondary','simpanan'=>'success','pembiayaan'=>'primary',
                'angsuran'=>'info','denda'=>'danger','shu'=>'warning','saldo_awal'=>'dark'];
?>
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end">
      <input type="hidden" name="mod" value="jurnal">
      <input type="hidden" name="act" value="list">
      <div class="col-auto"><label class="form-label small">Dari</label>
        <input type="date" name="dari" class="form-control form-control-sm" value="<?= e($dari) ?>"></div>
      <div class="col-auto"><label class="form-label small">Sampai</label>
        <input type="date" name="sampai" class="form-control form-control-sm" value="<?= e($sampai) ?>"></div>
      <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i> Filter</button></div>
      <div class="col-auto ms-auto d-flex gap-2">
        <a href="controllers/proses_export.php?jenis=jurnal&dari=<?= e($dari) ?>&sampai=<?= e($sampai) ?>"
           class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel me-1"></i> Excel</a>
        <a href="index.php?mod=jurnal&act=form" class="btn btn-sm btn-success">
          <i class="fas fa-plus me-1"></i> Jurnal Manual</a>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr><th>Tanggal</th><th>No. Jurnal</th><th>Keterangan</th><th>Referensi</th>
            <th>Sumber</th><th class="text-end">Nominal</th><th class="text-center">Aksi</th></tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $j): ?>
        <tr style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#jd<?= (int)$j['id_jurnal'] ?>">
          <td><?= e(date('d/m/Y', strtotime($j['tanggal']))) ?></td>
          <td><b><?= e($j['no_jurnal']) ?></b></td>
          <td><?= e($j['keterangan']) ?></td>
          <td class="small text-muted"><?= e($j['referensi'] ?: '-') ?></td>
          <td><span class="badge bg-<?= $badgeSumber[$j['sumber']] ?? 'secondary' ?>"><?= e(ucfirst($j['sumber'])) ?></span></td>
          <td class="text-end"><?= rupiah($j['total_debit']) ?></td>
          <td class="text-center">
            <i class="fas fa-chevron-down small text-muted"></i>
            <?php if ($j['sumber']==='manual'): ?>
            <a class="btn btn-sm btn-outline-danger btn-hapus" onclick="event.stopPropagation()"
               href="controllers/proses_jurnal.php?aksi=hapus&id=<?= (int)$j['id_jurnal'] ?>"><i class="fas fa-trash"></i></a>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <td colspan="7" class="p-0 border-0">
            <div class="collapse" id="jd<?= (int)$j['id_jurnal'] ?>">
              <table class="table table-sm mb-0 bg-light small">
                <?php foreach ($grup[$j['id_jurnal']] ?? [] as $d): ?>
                <tr>
                  <td style="width:120px"><?= e($d['kode_akun']) ?></td>
                  <td><?= $d['posisi']==='kredit' ? '<span class="ms-4">'.e($d['nama_akun']).'</span>' : e($d['nama_akun']) ?></td>
                  <td class="text-end" style="width:160px"><?= $d['posisi']==='debit' ? rupiah($d['jumlah']) : '' ?></td>
                  <td class="text-end" style="width:160px"><?= $d['posisi']==='kredit' ? rupiah($d['jumlah']) : '' ?></td>
                </tr>
                <?php endforeach; ?>
              </table>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada jurnal. Jurnal terbentuk otomatis dari transaksi, atau buat manual.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>