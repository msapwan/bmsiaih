<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PengaturanModel.php';

$pm    = new PengaturanModel();
$token = $pm->get('backup_token', '');
$email = $pm->get('backup_email', '');

$log = Database::getInstance()
    ->query('SELECT * FROM backup_log ORDER BY id_backup DESC LIMIT 20')
    ->fetchAll();

$pageTitle = 'Backup Database';
$active    = 'pengaturan-backup';
include __DIR__ . '/../layout/header.php';

$urlBackup = 'cron/backup_database.php?token=' . urlencode($token);
?>
<?php if (trim($email) === ''): ?>
<div class="alert alert-warning py-2 small">
  <i class="fas fa-exclamation-triangle me-1"></i>
  <code>backup_email</code> belum diisi — backup hanya disimpan ke folder <code>backups/</code>.
  Isi di <b>Pengaturan → Parameter</b> agar dikirim ke email.
</div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white"><b><i class="fas fa-play me-1"></i> Backup Manual & Jadwal</b></div>
      <div class="card-body">
        <p class="small mb-2">
          Email tujuan: <b><?= e($email ?: '(belum diatur)') ?></b> —
          Token: <code><?= e($token) ?></code><br>
          Ubah keduanya di <b>Pengaturan → Parameter</b> (<code>backup_email</code> & <code>backup_token</code>).
        </p>
        <a href="<?= e($urlBackup) ?>" target="_blank" class="btn btn-success btn-sm mb-3"
           onclick="return confirm('Jalankan backup sekarang?')">
          <i class="fas fa-database me-1"></i> Backup Sekarang</a>

        <h6 class="small text-muted">Jadwal otomatis (Cron Job di hosting):</h6>
        <code class="d-block bg-light border rounded p-2 small mb-1" style="word-break:break-all">
          wget -q -O /dev/null "https://DOMAIN-ANDA/cron/backup_database.php?token=<?= e($token) ?>"
        </code>
        <small class="text-muted">
          Harian: pilih setiap hari (mis. 01:00). Mingguan: pilih hari Minggu.<br>
          Di XAMPP gunakan Windows Task Scheduler.
        </small>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white"><b><i class="fas fa-history me-1"></i> Riwayat Backup (20 terakhir)</b></div>
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead><tr><th>Waktu</th><th>Tujuan</th><th>Status</th><th class="text-end">Ukuran</th><th>Keterangan</th></tr></thead>
          <tbody>
          <?php foreach ($log as $l): ?>
            <tr>
              <td class="small"><?= e(date('d/m/Y H:i', strtotime($l['tanggal']))) ?></td>
              <td class="small"><?= e($l['tujuan']) ?></td>
              <td><?= $l['status']==='sukses'
                      ? '<span class="badge bg-success">Sukses</span>'
                      : '<span class="badge bg-danger">Gagal</span>' ?></td>
              <td class="text-end small"><?= number_format($l['ukuran'] / 1024, 1) ?> KB</td>
              <td class="small text-muted"><?= e($l['keterangan']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$log): ?>
            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada backup.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>