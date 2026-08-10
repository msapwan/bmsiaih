<?php
require_once __DIR__ . '/../../models/PembiayaanModel.php';

$id = (int)($_GET['id'] ?? 0);
$pm = new PembiayaanModel();
$p  = $pm->find($id);
if (!$p) { header('Location: index.php?mod=pembiayaan&act=list'); exit; }

$angs     = $pm->angsuran($id);
$terbayar = array_sum(array_column($angs, 'jumlah_bayar'));
$sisa     = max(0, $p['total_piutang'] - $terbayar);

$pageTitle = 'Detail Pembiayaan';
$active    = 'pembiayaan';
include __DIR__ . '/../layout/header.php';
?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white"><b>Informasi Pembiayaan</b></div>
      <div class="card-body">
        <table class="table table-sm table-borderless small mb-0">
          <tr><th width="42%">No. Pembiayaan</th><td>: <b><?= e($p['no_pembiayaan']) ?></b></td></tr>
          <tr><th>Anggota</th><td>: <?= e($p['nama']) ?> (<?= e($p['kode_anggota']) ?>)</td></tr>
          <tr><th>Akad</th><td>: <?= e($p['nama_akad']) ?></td></tr>
          <tr><th>Tgl. Pengajuan</th><td>: <?= e(date('d/m/Y', strtotime($p['tanggal_pengajuan']))) ?></td></tr>
          <tr><th>Tgl. Akad</th><td>: <?= $p['tanggal_akad'] ? e(date('d/m/Y', strtotime($p['tanggal_akad']))) : '—' ?></td></tr>
          <tr><th>Jatuh Tempo</th><td>: <?= $p['tanggal_jatuh_tempo'] ? e(date('d/m/Y', strtotime($p['tanggal_jatuh_tempo']))) : '—' ?></td></tr>
          <tr><th>Plafon</th><td>: <?= rupiah($p['jumlah_pembiayaan']) ?></td></tr>
          <?php if ($p['tipe_akad']==='margin'): ?>
          <tr><th>Margin</th><td>: <?= e($p['margin_persen']) ?>% = <?= rupiah($p['margin_nominal']) ?></td></tr>
          <tr><th>Total Piutang</th><td>: <b><?= rupiah($p['total_piutang']) ?></b></td></tr>
          <?php else: ?>
          <tr><th>Nisbah Koperasi</th><td>: <?= e($p['nisbah_koperasi']) ?>%</td></tr>
          <?php endif; ?>
          <tr><th>Jangka Waktu</th><td>: <?= (int)$p['jangka_waktu'] ?> bulan</td></tr>
          <tr><th>Status</th><td>: <?= badge_status($p['status']) ?></td></tr>
          <?php if ($p['status']==='berjalan'): ?>
          <tr><th>Terbayar</th><td>: <?= rupiah($terbayar) ?></td></tr>
          <tr><th>Sisa</th><td>: <b class="text-danger"><?= rupiah($sisa) ?></b></td></tr>
          <?php endif; ?>
          <?php if ($p['catatan']): ?>
          <tr><th>Catatan</th><td>: <?= e($p['catatan']) ?></td></tr>
          <?php endif; ?>
        </table>
      </div>

      <?php if ($p['status']==='pengajuan'): ?>
      <div class="card-footer bg-white">
        <form method="post" action="controllers/proses_pembiayaan.php" class="mb-2">
          <input type="hidden" name="aksi" value="setujui">
          <input type="hidden" name="id_pembiayaan" value="<?= $id ?>">
          <label class="form-label small">Tanggal Akad / Pencairan</label>
          <input type="date" name="tanggal_akad" class="form-control form-control-sm mb-2" value="<?= date('Y-m-d') ?>">
          <button class="btn btn-sm btn-success w-100"><i class="fas fa-check me-1"></i> Setujui & Buat Jadwal</button>
        </form>
        <form method="post" action="controllers/proses_pembiayaan.php"
              onsubmit="return confirm('Tolak pengajuan ini?')">
          <input type="hidden" name="aksi" value="tolak">
          <input type="hidden" name="id_pembiayaan" value="<?= $id ?>">
          <input type="text" name="catatan" class="form-control form-control-sm mb-2" placeholder="Alasan penolakan">
          <button class="btn btn-sm btn-danger w-100"><i class="fas fa-times me-1"></i> Tolak</button>
        </form>
      </div>
      <?php elseif ($p['status']==='berjalan'): ?>
      <div class="card-footer bg-white">
        <a href="index.php?mod=pembiayaan&act=angsuran&id=<?= $id ?>" class="btn btn-sm btn-primary w-100">
          <i class="fas fa-money-bill-wave me-1"></i> Bayar Angsuran</a>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white"><b>Jadwal & Realisasi Angsuran</b></div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr><th>#</th><th>Jatuh Tempo</th><th class="text-end">Pokok</th>
                <th class="text-end">Margin</th><th class="text-end">Total</th>
                <th>Tgl. Bayar</th><th>Status</th></tr>
          </thead>
          <tbody>
          <?php foreach ($angs as $a):
              $telat = $a['status']==='belum' && strtotime($a['tanggal_jatuh_tempo']) < time(); ?>
            <tr class="<?= $telat?'table-danger':'' ?>">
              <td><?= (int)$a['angsuran_ke'] ?></td>
              <td><?= e(date('d/m/Y', strtotime($a['tanggal_jatuh_tempo']))) ?>
                  <?= $telat?'<i class="fas fa-exclamation-circle text-danger" title="Terlambat"></i>':'' ?></td>
              <td class="text-end"><?= rupiah($a['pokok']) ?></td>
              <td class="text-end"><?= rupiah($a['margin']) ?></td>
              <td class="text-end"><b><?= rupiah($a['total']) ?></b></td>
              <td><?= $a['tanggal_bayar'] ? e(date('d/m/Y', strtotime($a['tanggal_bayar']))) : '—' ?>
                  <?php if ($a['jumlah_bayar']>0 && $a['status']==='belum'): ?>
                  <br><small class="text-muted">dibayar: <?= rupiah($a['jumlah_bayar']) ?></small><?php endif; ?></td>
              <td><?= badge_status($a['status']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$angs): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">Jadwal dibuat setelah pembiayaan disetujui.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>