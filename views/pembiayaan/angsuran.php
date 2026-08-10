<?php
require_once __DIR__ . '/../../models/PembiayaanModel.php';
require_once __DIR__ . '/../../models/DendaModel.php';

$pm       = new PembiayaanModel();
$dm       = new DendaModel();
$id       = (int)($_GET['id'] ?? 0);
$p        = $id ? $pm->find($id) : null;
$berjalan = $pm->all('berjalan');

$pageTitle = 'Pembayaran Angsuran';
$active    = 'pembiayaan-angsuran';
include __DIR__ . '/../layout/header.php';
?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white"><b>Pilih Pembiayaan</b></div>
      <div class="list-group list-group-flush">
        <?php foreach ($berjalan as $b): ?>
        <a href="index.php?mod=pembiayaan&act=angsuran&id=<?= (int)$b['id_pembiayaan'] ?>"
           class="list-group-item list-group-item-action <?= $id===(int)$b['id_pembiayaan']?'active':'' ?>">
          <b><?= e($b['nama']) ?></b> — <?= e($b['no_pembiayaan']) ?><br>
          <small>Sisa: <?= rupiah($b['total_piutang'] - $b['total_bayar']) ?></small>
        </a>
        <?php endforeach; ?>
        <?php if (!$berjalan): ?>
        <div class="list-group-item text-center text-muted py-4">Tidak ada pembiayaan berjalan.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <?php if ($p && $p['status']==='berjalan'):
        $angs  = $pm->angsuran($id);
        $belum = array_filter($angs, function ($a) { return $a['status'] === 'belum'; }); ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white">
        <b>Angsuran Belum Lunas — <?= e($p['no_pembiayaan']) ?> (<?= e($p['nama']) ?>)</b>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr><th>#</th><th>Jatuh Tempo</th><th class="text-end">Tagihan</th><th style="width:340px">Pembayaran</th></tr>
          </thead>
          <tbody>
          <?php foreach ($belum as $a):
              $hariIni = date('Y-m-d');
              $terlambat = strtotime($hariIni) > strtotime($a['tanggal_jatuh_tempo']);
              $potensiDenda = $terlambat ? $dm->hitung($a, $hariIni) : null;
              $sudahDidenda = $dm->byAngsuran((int)$a['id_angsuran']); ?>
            <tr class="<?= $terlambat?'table-danger':'' ?>">
              <td><?= (int)$a['angsuran_ke'] ?></td>
              <td>
                <?= e(date('d/m/Y', strtotime($a['tanggal_jatuh_tempo']))) ?>
                <?php if ($terlambat): ?>
                <div class="small text-danger">
                  <i class="fas fa-exclamation-circle me-1"></i>
                  Terlambat <?= $potensiDenda['hari'] ?> hari
                  <?php if ($sudahDidenda): ?>
                    — denda <?= rupiah($sudahDidenda['jumlah_denda']) ?>
                    <?= $sudahDidenda['status']==='lunas' ? '(lunas)' : '(belum dibayar)' ?>
                  <?php else: ?>
                    — denda <?= rupiah($potensiDenda['jumlah']) ?>
                  <?php endif; ?>
                </div>
                <?php endif; ?>
              </td>
              <td class="text-end"><b><?= rupiah($a['total']) ?></b></td>
              <td>
                <form method="post" action="controllers/proses_pembiayaan.php" class="d-flex flex-column gap-1">
                  <input type="hidden" name="aksi" value="bayar">
                  <input type="hidden" name="id_angsuran" value="<?= (int)$a['id_angsuran'] ?>">
                  <input type="hidden" name="id_pembiayaan" value="<?= $id ?>">
                  <div class="d-flex gap-1">
                    <input type="date" name="tanggal_bayar" class="form-control form-control-sm" value="<?= $hariIni ?>" style="max-width:140px">
                    <input type="text" name="jumlah_bayar" class="form-control form-control-sm input-uang"
                           value="<?= number_format($a['total'] - $a['jumlah_bayar'], 0, ',', '.') ?>" inputmode="numeric">
                    <button class="btn btn-sm btn-success" title="Bayar"><i class="fas fa-check"></i></button>
                  </div>
                  <?php if ($terlambat && !$sudahDidenda): ?>
                  <label class="small text-danger mb-0">
                    <input type="checkbox" name="bayar_denda" value="1" checked>
                    Bayar denda ta'zir sekaligus (<?= rupiah($potensiDenda['jumlah']) ?>)
                  </label>
                  <?php endif; ?>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$belum): ?>
            <tr><td colspan="4" class="text-center text-success py-4"><i class="fas fa-check-circle me-1"></i> Semua angsuran lunas.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info">Pilih salah satu pembiayaan berjalan di sebelah kiri.</div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>