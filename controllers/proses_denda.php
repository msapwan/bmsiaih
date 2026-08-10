<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login.php'); exit; }

require_once __DIR__ . '/../models/DendaModel.php';
$dm   = new DendaModel();
$aksi = $_GET['aksi'] ?? '';

function flash(string $tipe, string $msg): void
{
    $_SESSION['flash'] = ['type' => $tipe, 'msg' => $msg];
}

try {
    if ($aksi === 'bayar') {
        $dm->bayar((int)($_GET['id'] ?? 0), date('Y-m-d'));
        flash('success', 'Denda dibayar & dicatat sebagai Dana Sosial.');
    }
} catch (Exception $e) {
    flash('danger', 'Gagal: ' . $e->getMessage());
}

header('Location: ../index.php?mod=denda&act=list');
exit;