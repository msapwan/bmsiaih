<?php
require_once __DIR__ . '/../../models/LaporanModel.php';
require_once __DIR__ . '/../../models/SimpananModel.php';

$jenis  = $_GET['jenis'] ?? 'simpanan';
$dari   = $_GET['dari'] ?? '';
$sampai = $_GET['sampai'] ?? '';

$lm = new LaporanModel();

$pageTitle = 'Laporan';
$active    = 'laporan';
include __DIR__ . '/../layout/header.php';

$jenisExport = ['simpanan' => 'rekap_simpanan', 'pembiayaan' => 'pembiayaan', 'transaksi' => 'transaksi'];
?>
<ul class="nav nav-tabs no-print">
  <li class="nav-item"><a class="nav-link <?= $jenis==='simpanan'?'active':'' ?>"
      href="index.php?mod=laporan&jenis=simpanan">Rekap Simpanan</a></li>
  <li class="nav-item"><a class="nav-link <?= $jenis==='pembiayaan'?'active':'' ?>"
      href="index.php?mod=laporan&jenis=pembiayaan">Pembiayaan</a></li>
  <li class="nav-item"><a class="nav-link <?= $jenis==='transaksi'?'active':'' ?>"
      href="index.php?mod=laporan&jenis=transaksi">Transaksi Simpanan</a></li>
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan&act=laba_rugi">Laba Rugi</a></li>
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan&act=neraca">Neraca</a></li>
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan&act=arus_kas">Arus Kas</a></li>
  <li class="nav-item"><a class="nav-link" href="index.php?mod=laporan&act=phu">PHU / SHU</a></li>
</ul>

<div class="card border-0 shadow-sm rounded-top-0">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end no-print mb-3">
      <input type="hidden" name="mod" value="laporan">
      <input type="hidden" name="jenis" value="<?= e($jenis) ?>">
      <?php if ($jenis !== 'simpanan'): ?>
      <div class="col-auto"><label class="form-label small">Dari</label>
        <input type="date" name="dari" class="form-control form-control-sm" value="<?= e($dari) ?>"></div>
      <div class="col-auto"><label class="form-label small">Sampai</label>
        <input type="date" name="sampai" class="form-control form-control-sm" value="<?= e($sampai) ?>"></div>
      <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i> Terapkan</button></div>
      <?php endif; ?>
      <div class="col-auto ms-auto d-flex gap-2">
        <?php if (isset($jenisExport[$jenis])): ?>
        <a class="btn btn-sm btn-outline-success"
           href="controllers/proses_export.php?jenis=<?= $jenisExport[$jenis] ?>&dari=<?= e($dari) ?>&sampai=<?= e($sampai) ?>">
          <i class="fas fa-file-excel me-1"></i> Excel</a>
        <?php endif; ?>
        <?php if ($jenis==='transaksi'): ?>
        <a class="btn btn-sm btn-outline-success"
           href="controllers/proses_laporan.php?aksi=export_transaksi&dari=<?= e($dari) ?>&sampai=<?= e($sampai) ?>">
          <i class="fas fa-file-csv me-1"></i> CSV</a>
        <?php endif; ?>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
          <i class="fas fa-print me-1"></i> PDF</button>
      </div>
    </form>

    <?php if ($jenis === 'simpanan'):
        $rekap = (new SimpananModel())->rekapPerAnggota(); ?>
      <h5 class="mb-3">Rekapitulasi Simpanan Anggota</h5>
      <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle">
          <thead class="table-light">
            <tr><th>Kode</th><th>Nama</th><th class="text-end">Pokok</th><th class="text-end">Wajib</th>
                <th class="text-end">Sukarela</th><th class="text-end">Total</th></tr>
          </thead>
          <tbody>
          <?php $g = ['pokok'=>0,'wajib'=>0,'sukarela'=>0,'total'=>0];
                foreach ($rekap as $r):
                  foreach ($g as $k => $v) $g[$k] += $r[$k]; ?>
            <tr>
              <td><?= e($r['kode_anggota']) ?></td><td><?= e($r['nama']) ?></td>
              <td class="text-end"><?= rupiah($r['pokok']) ?></td>
              <td class="text-end"><?= rupiah($r['wajib']) ?></td>
              <td class="text-end"><?= rupiah($r['sukarela']) ?></td>
              <td class="text-end"><b><?= rupiah($r['total']) ?></b></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot class="table-light fw-bold">
            <tr><td colspan="2">TOTAL</td>
              <td class="text-end"><?= rupiah($g['pokok']) ?></td>
              <td class="text-end"><?= rupiah($g['wajib']) ?></td>
              <td class="text-end"><?= rupiah($g['sukarela']) ?></td>
              <td class="text-end"><?= rupiah($g['total']) ?></td></tr>
          </tfoot>
        </table>
      </div>

    <?php elseif ($jenis === 'pembiayaan'):
        $rows = $lm->rekapPembiayaan($dari, $sampai);
        $tDicairkan = $tDibayar = $tSisa = 0; ?>
      <h5 class="mb-3">Laporan Pembiayaan <?= ($dari||$sampai) ? '(' . e($dari) . ' s.d. ' . e($sampai) . ')' : '' ?></h5>
      <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle">
          <thead class="table-light">
            <tr><th>No.</th><th>Anggota</th><th>Akad</th><th class="text-end">Plafon</th>
                <th class="text-end">Terbayar</th><th class="text-end">Sisa</th><th>Status</th></tr>
          </thead>
          <tbody>
          <?php foreach ($rows as $r):
              $sisa = max(0, $r['total_piutang'] - $r['total_bayar']);
              $tDicairkan += $r['jumlah_pembiayaan']; $tDibayar += $r['total_bayar']; $tSisa += $sisa; ?>
            <tr>
              <td><?= e($r['no_pembiayaan']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['nama_akad']) ?></td>
              <td class="text-end"><?= rupiah($r['jumlah_pembiayaan']) ?></td>
              <td class="text-end"><?= rupiah($r['total_bayar']) ?></td>
              <td class="text-end"><?= rupiah($sisa) ?></td>
              <td><?= badge_status($r['status']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot class="table-light fw-bold">
            <tr><td colspan="3">TOTAL</td>
              <td class="text-end"><?= rupiah($tDicairkan) ?></td>
              <td class="text-end"><?= rupiah($tDibayar) ?></td>
              <td class="text-end"><?= rupiah($tSisa) ?></td><td></td></tr>
          </tfoot>
        </table>
      </div>

    <?php else:
        $rows = $lm->transaksiSimpanan($dari, $sampai);
        $masuk = $keluar = 0; ?>
      <h5 class="mb-3">Transaksi Simpanan <?= ($dari||$sampai) ? '(' . e($dari) . ' s.d. ' . e($sampai) . ')' : '' ?></h5>
      <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle">
          <thead class="table-light">
            <tr><th>Tanggal</th><th>Kode</th><th>Nama</th><th>Jenis</th><th>Tipe</th><th class="text-end">Jumlah</th></tr>
          </thead>
          <tbody>
          <?php foreach ($rows as $r):
              if ($r['tipe']==='setor') $masuk += $r['jumlah']; else $keluar += $r['jumlah']; ?>
            <tr>
              <td><?= e(date('d/m/Y', strtotime($r['tanggal']))) ?></td>
              <td><?= e($r['kode_anggota']) ?></td><td><?= e($r['nama']) ?></td>
              <td><?= e(ucfirst($r['jenis_simpanan'])) ?></td>
              <td><?= e(ucfirst($r['tipe'])) ?></td>
              <td class="text-end"><?= rupiah($r['jumlah']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot class="table-light fw-bold">
            <tr><td colspan="5">Masuk / Keluar</td>
              <td class="text-end"><span class="text-success"><?= rupiah($masuk) ?></span> /
                                   <span class="text-danger"><?= rupiah($keluar) ?></span></td></tr>
          </tfoot>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>