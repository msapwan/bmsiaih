<?php
require_once __DIR__ . '/../../models/SimpananModel.php';
require_once __DIR__ . '/../../models/AnggotaModel.php';
require_once __DIR__ . '/../../models/PengaturanModel.php';

$fAnggota = (int)($_GET['id_anggota'] ?? 0);
$fJenis   = $_GET['jenis'] ?? '';
$fDari    = $_GET['dari'] ?? '';
$fSampai  = $_GET['sampai'] ?? '';

$rows = (new SimpananModel())->all($fAnggota, $fJenis, $fDari, $fSampai);
$opsi = (new AnggotaModel())->opsiAktif();
$pm   = new PengaturanModel();

$totalSetor = $totalTarik = 0;
foreach ($rows as $r) {
    if ($r['tipe'] === 'setor') $totalSetor += $r['jumlah'];
    else                        $totalTarik += $r['jumlah'];
}

$pageTitle = 'Transaksi Simpanan';
$active    = 'simpanan';
include __DIR__ . '/../layout/header.php';
?>
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body">
    <form class="row g-2 align-items-end" method="get">
      <input type="hidden" name="mod" value="simpanan">
      <input type="hidden" name="act" value="list">
      <div class="col-md-3">
        <label class="form-label small">Anggota</label>
        <select name="id_anggota" class="form-select form-select-sm">
          <option value="0">— Semua —</option>
          <?php foreach ($opsi as $o): ?>
          <option value="<?= (int)$o['id_anggota'] ?>" <?= $fAnggota===(int)$o['id_anggota']?'selected':'' ?>>
            <?= e($o['kode_anggota'].' - '.$o['nama']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Jenis</label>
        <select name="jenis" class="form-select form-select-sm">
          <option value="">Semua</option>
          <?php foreach (['pokok','wajib','sukarela'] as $j): ?>
          <option value="<?= $j ?>" <?= $fJenis===$j?'selected':'' ?>><?= ucfirst($j) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2"><label class="form-label small">Dari</label>
        <input type="date" name="dari" class="form-control form-control-sm" value="<?= e($fDari) ?>"></div>
      <div class="col-md-2"><label class="form-label small">Sampai</label>
        <input type="date" name="sampai" class="form-control form-control-sm" value="<?= e($fSampai) ?>"></div>
      <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
        <a href="index.php?mod=simpanan&act=list" class="btn btn-sm btn-outline-secondary">Reset</a>
        <a href="index.php?mod=simpanan&act=form" class="btn btn-sm btn-success ms-auto">
          <i class="fas fa-plus me-1"></i> Transaksi</a>
      </div>
    </form>
  </div>
</div>

<div class="alert alert-info py-2 small">
  Nominal default — Pokok: <b><?= rupiah($pm->get('simpanan_pokok')) ?></b>,
  Wajib: <b><?= rupiah($pm->get('simpanan_wajib')) ?></b> (dapat diubah di menu Parameter).
</div>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr><th>Tanggal</th><th>Anggota</th><th>Jenis</th><th>Tipe</th>
            <th class="text-end">Jumlah</th><th>Keterangan</th><th>Petugas</th>
            <?php if ($user['level']==='admin'): ?><th class="text-center">Aksi</th><?php endif; ?></tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= e(date('d/m/Y', strtotime($r['tanggal']))) ?></td>
          <td><?= e($r['nama']) ?><br><small class="text-muted"><?= e($r['kode_anggota']) ?></small></td>
          <td><?= e(ucfirst($r['jenis_simpanan'])) ?></td>
          <td><span class="badge bg-<?= $r['tipe']==='setor'?'success':'danger' ?>"><?= e(ucfirst($r['tipe'])) ?></span></td>
          <td class="text-end"><?= rupiah($r['jumlah']) ?></td>
          <td class="small"><?= e($r['keterangan']) ?></td>
          <td class="small"><?= e($r['petugas'] ?? '-') ?></td>
          <?php if ($user['level']==='admin'): ?>
          <td class="text-center">
            <a class="btn btn-sm btn-outline-danger btn-hapus"
               href="controllers/proses_simpanan.php?aksi=hapus&id=<?= (int)$r['id_simpanan'] ?>">
              <i class="fas fa-trash"></i></a>
          </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada transaksi.</td></tr>
      <?php endif; ?>
      </tbody>
      <tfoot class="table-light fw-bold">
        <tr>
          <td colspan="4">Total Setoran / Penarikan (hasil filter)</td>
          <td class="text-end">
            <span class="text-success"><?= rupiah($totalSetor) ?></span> /
            <span class="text-danger"><?= rupiah($totalTarik) ?></span>
          </td>
          <td colspan="3"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>