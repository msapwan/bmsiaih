<?php
require_once __DIR__ . '/../../models/ShuModel.php';

$id  = (int)($_GET['id'] ?? 0);
$sm  = new ShuModel();
$shu = $sm->find($id);
if (!$shu) { header('Location: index.php?mod=shu&act=list'); exit; }

$alokasi = $sm->alokasi($id);
$anggota = $sm->anggota($id);

$pageTitle = 'Detail SHU ' . $shu['tahun'];
$active    = 'shu';
include __DIR__ . '/../layout/header.php';

$badgeShu = ['draft'=>'secondary','ditetapkan'=>'primary','dibagikan'=>'success'];
?>
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <h5 class="mb-1">SHU Tahun <?= (int)$shu['tahun'] ?>
        <span class="badge bg-<?= $badgeShu[$shu['status']] ?? 'secondary' ?>"><?= e(ucfirst($shu['status'])) ?></span>
      </h5>
      <small class="text-muted">
        Pendapatan: <b><?= rupiah($shu['total_pendapatan']) ?></b> —
        Beban: <b><?= rupiah($shu['total_beban']) ?></b> —
        SHU: <b class="text-success"><?= rupiah($shu['total_shu']) ?></b>
      </small>
    </div>
    <div class="d-flex gap-2 no-print">
      <?php if ($shu['status']==='draft'): ?>
      <a href="controllers/proses_shu.php?aksi=tetapkan&id=<?= $id ?>" class="btn btn-sm btn-primary"
         onclick="return confirm('Tetapkan SHU tahun <?= (int)$shu['tahun'] ?>?')">
        <i class="fas fa-gavel me-1"></i> Tetapkan</a>
      <?php endif; ?>
      <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
        <i class="fas fa-print me-1"></i> Cetak</button>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white"><b>Alokasi / Pembagian SHU</b></div>
      <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Dana</th><th class="text-center">%</th><th class="text-end">Jumlah</th></tr></thead>
        <tbody>
        <?php foreach ($alokasi as $a): ?>
          <tr>
            <td><?= e($a['nama_dana']) ?></td>
            <td class="text-center"><?= e($a['persen']) ?>%</td>
            <td class="text-end"><?= rupiah($a['jumlah']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot class="table-light fw-bold">
          <tr><td colspan="2">TOTAL</td>
              <td class="text-end"><?= rupiah(array_sum(array_column($alokasi,'jumlah'))) ?></td></tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white"><b>Pembagian ke Anggota (Jasa Modal + Jasa Usaha)</b></div>
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead><tr><th>Anggota</th><th class="text-end">Jasa Modal</th><th class="text-end">Jasa Usaha</th>
              <th class="text-end">Total</th><th>Status</th><th class="text-center no-print">Aksi</th></tr></thead>
          <tbody>
          <?php foreach ($anggota as $r): ?>
            <tr>
              <td><?= e($r['nama']) ?><br><small class="text-muted"><?= e($r['kode_anggota']) ?></small></td>
              <td class="text-end"><?= rupiah($r['jasa_modal']) ?></td>
              <td class="text-end"><?= rupiah($r['jasa_usaha']) ?></td>
              <td class="text-end"><b><?= rupiah($r['total']) ?></b></td>
              <td><?= $r['status']==='diterima'
                      ? '<span class="badge bg-success">Diterima</span>'
                      : '<span class="badge bg-secondary">Belum</span>' ?></td>
              <td class="text-center no-print">
                <?php if ($r['status']!=='diterima'): ?>
                <a class="btn btn-sm btn-outline-success"
                   href="controllers/proses_shu.php?aksi=terima&id=<?= (int)$r['id_shu_anggota'] ?>&back=<?= $id ?>"
                   onclick="return confirm('Tandai SHU <?= e($r['nama']) ?> sudah diterima?')">
                  <i class="fas fa-check"></i></a>
                <?php else: ?>
                <small class="text-muted"><?= $r['tanggal_terima'] ? e(date('d/m/Y', strtotime($r['tanggal_terima']))) : '' ?></small>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$anggota): ?>
            <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada anggota aktif.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>