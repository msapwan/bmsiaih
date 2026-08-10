<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login.php'); exit; }

require_once __DIR__ . '/../models/NotifikasiModel.php';
require_once __DIR__ . '/../models/NotifikasiKirimModel.php';
require_once __DIR__ . '/../models/DendaModel.php';
require_once __DIR__ . '/../models/PengaturanModel.php';

function flash(string $tipe, string $msg): void
{
    $_SESSION['flash'] = ['type' => $tipe, 'msg' => $msg];
}
function rupiah($n) { return 'Rp ' . number_format((float)($n ?: 0), 0, ',', '.'); }

function normalisasiWA(?string $hp): string
{
    $hp = preg_replace('/[^0-9]/', '', (string)$hp);
    if ($hp === '') return '';
    if (substr($hp, 0, 1) === '0') $hp = '62' . substr($hp, 1);
    if (substr($hp, 0, 2) === '8') $hp = '62' . $hp;
    return $hp;
}

function kirimWA(string $gateway, string $key, string $tujuan, string $pesan): bool
{
    if ($gateway === '' || $key === '') return false;
    $ch = curl_init($gateway);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['Authorization: ' . $key],
        CURLOPT_POSTFIELDS     => http_build_query(['target' => $tujuan, 'message' => $pesan]),
    ]);
    $res = curl_exec($ch);
    $ok  = curl_errno($ch) === 0 && $res !== false;
    curl_close($ch);
    return $ok;
}

function buatPesan(array $n, array $profil, DendaModel $dm): string
{
    $tgl = date('d/m/Y', strtotime($n['tanggal_jatuh_tempo']));
    $pesan = "Assalamu'alaikum " . $n['nama'] . ",\n\n"
           . "Mengingatkan angsuran ke-" . $n['angsuran_ke'] . " pembiayaan " . $n['no_pembiayaan']
           . " sebesar " . rupiah($n['total']) . " jatuh tempo pada " . $tgl . ".";
    if (strtotime(date('Y-m-d')) > strtotime($n['tanggal_jatuh_tempo'])) {
        $h = $dm->hitung($n, date('Y-m-d'));
        $pesan .= "\n\nTelah melewati jatuh tempo " . $h['hari'] . " hari."
                . " Estimasi denda ta'zir: " . rupiah($h['jumlah']) . ".";
    }
    return $pesan . "\n\nMohon segera melakukan pembayaran. Terima kasih.\n- " . ($profil['nama_koperasi'] ?? 'Koperasi Syariah');
}

$aksi = $_GET['aksi'] ?? '';
if ($aksi !== 'kirim') { header('Location: ../index.php?mod=notifikasi&act=kirim'); exit; }

$kanal  = $_GET['kanal'] ?? 'wa';
$ntf    = new NotifikasiModel();
$log    = new NotifikasiKirimModel();
$dm     = new DendaModel();
$pm     = new PengaturanModel();
$profil = $pm->profil();

$items = $ntf->semua();
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $items = array_filter($items, function ($n) use ($id) { return (int)$n['id_angsuran'] === $id; });
}

$gateway   = trim($pm->get('wa_gateway', ''));
$waKey     = trim($pm->get('wa_api_key', ''));
$emailDari = trim($pm->get('email_pengirim', 'koperasi@example.com'));

$terkirim = $simulasi = $gagal = $lewati = 0;

foreach ($items as $n) {
    $tujuan = $kanal === 'wa' ? normalisasiWA($n['no_hp'] ?? '') : trim($n['email'] ?? '');
    if ($tujuan === '') { $lewati++; continue; }

    $pesan  = buatPesan($n, $profil, $dm);
    $status = 'gagal';

    if ($kanal === 'wa') {
        if ($waKey === '') $status = 'simulasi';
        else               $status = kirimWA($gateway, $waKey, $tujuan, $pesan) ? 'terkirim' : 'gagal';
    } else {
        $subjek  = 'Pengingat Angsuran ' . $n['no_pembiayaan'] . ' — ' . ($profil['nama_koperasi'] ?? 'Koperasi');
        $headers = 'From: ' . $emailDari . "\r\nContent-Type: text/plain; charset=UTF-8";
        $status  = @mail($tujuan, $subjek, $pesan, $headers) ? 'terkirim' : 'gagal';
    }

    $log->catat((int)$n['id_angsuran'], $kanal, $tujuan, $status, $pesan);
    if ($status === 'terkirim') $terkirim++;
    elseif ($status === 'simulasi') $simulasi++;
    else $gagal++;
}

$ringkas = "Terkirim: $terkirim | Simulasi: $simulasi | Gagal: $gagal | Dilewati (tanpa kontak): $lewati";
if ($kanal === 'wa' && $waKey === '' && $simulasi > 0) {
    flash('warning', 'API key WhatsApp belum diatur (Pengaturan → Parameter) — pesan disimulasikan & dicatat di log. ' . $ringkas);
} else {
    flash($gagal > 0 ? 'warning' : 'success', 'Notifikasi selesai. ' . $ringkas);
}

header('Location: ../index.php?mod=notifikasi&act=kirim');
exit;