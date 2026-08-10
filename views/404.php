<?php
$pageTitle = '404 — Halaman Tidak Ditemukan';
include __DIR__ . '/layout/header.php';
?>
<div class="text-center py-5">
  <div style="font-size:6rem; font-weight:800; color:#0f766e">404</div>
  <h4>Halaman tidak ditemukan</h4>
  <p class="text-muted">Alamat yang Anda tuju tidak tersedia atau telah dipindahkan.</p>
  <a href="index.php?mod=dashboard" class="btn btn-success mt-2">
    <i class="fas fa-home me-1"></i> Kembali ke Dashboard</a>
</div>
<?php include __DIR__ . '/layout/footer.php'; ?>