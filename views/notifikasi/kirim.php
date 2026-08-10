<?php
require_once __DIR__ . '/../../models/NotifikasiModel.php';
require_once __DIR__ . '/../../models/NotifikasiKirimModel.php';
require_once __DIR__ . '/../../models/DendaModel.php';
require_once __DIR__ . '/../../models/PengaturanModel.php';

$ntf = new NotifikasiModel();
$dm  = new DendaModel();
$pm  = new PengaturanModel();
$log = (new NotifikasiKirimModel())->log(20);

$terlambat = $ntf->terlambat();
$akan      = $ntf->akanJatuhTempo();
$waKey     = trim($pm->get('wa_api_key', ''));

$pageTitle = 'Kirim Notifikasi (WA / Email)';
$active    = 'notifikasi-kirim';
include __DIR__ . '/../layout/header.php';

$badgeLog = ['terkirim'=>'success','simulasi'=>'warning','gagal'=>'danger'];
?>
<?php if ($waKey === ''): ?>
<div class="alert alert-warning py-2 small">
  <i class="fas fa-exclamation-triangle me-1"></i>
  API key WhatsApp belum diisi — pengiriman WA bersifat <b>simulasi</b> (dicatat di log).
  Atur di <b>Pengaturan → Parameter</b>: <code>wa_gateway</code> & <code>wa_api_key</code>.
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-body d-flex flex-wrap gap-2 align-items-center">
    <b>Kirim massal semua pengingat (<?= count($terlambat) + count($akan) ?> angsuran):</b>
    <a class="btn btn-sm btn-success"
       href="controllers/proses_notifikasi.php?aksi=kirim&kanal=wa&semua=1"
       onclick="return confirm('Kirim pengingat via WhatsApp untuk semua angsuran jatuh tempo?')">
      <i class="fab fa-whatsapp me-1"></i> Kirim WA Semua</a>
    <a class="btn btn-sm btn-primary"
       href="controllers/proses_notifikasi.php?aksi=kirim&kanal=email&semua=1"
       onclick="return confirm('Kirim pengingat via Email untuk semua angsuran jatuh tempo?')">
      <i class="fas fa-envelope me-1"></i> Kirim Email Semua</a>
    <span class="small text-muted ms-auto">Nomor HP & email diambil dari data anggota.</span>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white"><b>Angsuran Terlambat & Akan Jatuh Tempo</b></div>
      <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
          <thead><tr><th>Anggota</th><th>Kontak</th><th>Angsuran</th><th>Jatuh Tempo</th><th class="text-center">Kirim</th></tr></thead>
          <tbody>
          <?php foreach (array_merge($terlambat, $akan) as $n):
              $telat = strtotime($n['tanggal_jatuh_tempo']) < strtotime(date('Y-m-d')); ?>
            <tr>
              <td><?= e($n['nama']) ?><br><small class="text-muted"><?= e($n['no_pembiayaan']) ?></small></td>
              <td class="small">
                <i class="fab fa-whatsapp text-success"></i> <?= e($n['no_hp'] ?: '-') ?><br>
                <i class="fas fa-envelope text-secondary"></i> <?= e($n['email'] ?: '-') ?>
              </td>
              <td>ke-<?= (int)$n['angsuran_ke'] ?><br><small><?= rupiah($n['total']) ?></small></td>
              <td class="<?= $telat?'text-danger':'' ?>">
                <?= e(date('d/m/Y', strtotime($n['tanggal_jatuh_tempo']))) ?>
                <?= $telat ? '<span class="badge bg-danger">Lewat</span>' : '' ?>
              </td>
              <td class="text-center text-nowrap">
                <a class="btn btn-sm btn-outline-success" title="Kirim WA"
                   href="controllers/proses_notifikasi.php?aksi=kirim&kanal=wa&id=<?= (int)$n['id_angsuran'] ?>"
                   onclick="return confirm('Kirim WA ke <?= e($n['nama']) ?>?')"><i class="fab fa-whatsapp"></i></a>
                <a class="btn btn-sm btn-outline-primary" title="Kirim Email"
                   href="controllers/proses_notifikasi.php?aksi=kirim&kanal=email&id=<?= (int)$n['id_angsuran'] ?>"
                   onclick="return confirm('Kirim email ke <?= e($n['nama']) ?>?')"><i class="fas fa-envelope"></i></a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$terlambat && !$akan): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada angsuran yang perlu diingatkan.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white"><b>Log Pengiriman (20 terakhir)</b></div>
      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
          <thead><tr><th>Waktu</th><th>Anggota</th><th>Kanal</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($log as $l): ?>
            <tr>
              <td class="small"><?= e(date('d/m/Y H:i', strtotime($l['tanggal_kirim']))) ?></td>
              <td class="small"><?= e($l['nama_anggota'] ?? '?') ?><br>
                  <span class="text-muted"><?= e($l['tujuan']) ?></span></td>
              <td><?= $l['kanal']==='wa' ? '<i class="fab fa-whatsapp text-success"></i>' : '<i class="fas fa-envelope text-primary"></i>' ?></td>
              <td><span class="badge bg-<?= $badgeLog[$l['status']] ?? 'secondary' ?>"><?= e(ucfirst($l['status'])) ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$log): ?>
            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada pengiriman.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>