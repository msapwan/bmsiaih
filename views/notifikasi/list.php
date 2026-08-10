<?php
require_once __DIR__ . '/../../models/NotifikasiModel.php';
require_once __DIR__ . '/../../models/DendaModel.php';

$ntf = new NotifikasiModel();
$dm  = new DendaModel();
$terlambat = $ntf->terlambat();
$akan      = $ntf->akanJatuhTempo();

$pageTitle = 'Notifikasi Jatuh Tempo';
$active    = 'notifikasi';
include __DIR__ . '/../layout/header.php';
?>
<div class="row g-3">
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm border-start border-danger border-4">
      <div class="card-header bg-white"><b class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Sudah Lewat Jatuh Tempo (<?= count($terlambat) ?>)</b></div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Anggota</th><th>Angsuran</th><th>Jatuh Tempo</th><th class="text-end">Tagihan</th><th class="text-end">Estimasi Denda</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($terlambat as $n):
              $h = $dm->hitung($n, date('Y-m-d')); ?>
            <tr>
              <td><?= e($n['nama']) ?><br><small class="text-muted"><?= e($n['no_pembiayaan']) ?></small></td>
              <td>ke-<?= (int)$n['angsuran_ke'] ?></td>
              <td class="text-danger"><?= e(date('d/m/Y', strtotime($n['tanggal_jatuh_tempo']))) ?></td>
              <td class="text-end"><?= rupiah($n['total']) ?></td>
              <td class="text-end text-danger"><?= rupiah($h['jumlah']) ?> (<?= $h['hari'] ?> hari)</td>
              <td><a class="btn btn-sm btn-primary" href="index.php?mod=pembiayaan&act=angsuran&id=<?= (int)$n['id_pembiayaan'] ?>">Bayar</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$terlambat): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada angsuran terlambat. Alhamdulillah!</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card border-0 shadow-sm border-start border-warning border-4">
      <div class="card-header bg-white"><b class="text-warning"><i class="fas fa-clock me-1"></i> Jatuh Tempo Dalam <?= $ntf->hari() ?> Hari (<?= count($akan) ?>)</b></div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Anggota</th><th>Angsuran</th><th>Jatuh Tempo</th><th class="text-end">Tagihan</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($akan as $n):
              $sisaHari = (int)round((strtotime($n['tanggal_jatuh_tempo']) - strtotime('today')) / 86400); ?>
            <tr>
              <td><?= e($n['nama']) ?><br><small class="text-muted"><?= e($n['no_pembiayaan']) ?></small></td>
              <td>ke-<?= (int)$n['angsuran_ke'] ?></td>
              <td><?= e(date('d/m/Y', strtotime($n['tanggal_jatuh_tempo']))) ?>
                  <span class="badge bg-warning text-dark">H-<?= $sisaHari ?></span></td>
              <td class="text-end"><?= rupiah($n['total']) ?></td>
              <td><a class="btn btn-sm btn-outline-primary" href="index.php?mod=pembiayaan&act=angsuran&id=<?= (int)$n['id_pembiayaan'] ?>">Bayar</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$akan): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada angsuran mendekati jatuh tempo.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>