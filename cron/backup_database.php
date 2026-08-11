<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PengaturanModel.php';

if (PHP_SAPI !== 'cli') header('Content-Type: text/plain; charset=utf-8');

$pm = new PengaturanModel();

$tokenBenar = trim((string)$pm->get('backup_token', ''));
$token      = $_GET['token'] ?? (isset($argv[1]) ? $argv[1] : '');
if ($tokenBenar === '' || !hash_equals($tokenBenar, (string)$token)) {
    http_response_code(403);
    die("Akses ditolak: token salah.");
}

$db     = Database::getInstance();
$profil = $pm->profil();

$sql    = dumpDatabase($db);
$ukuran = strlen($sql);

$namaFile = 'backup_' . DB_NAME . '_' . date('Ymd_His') . '.sql';
$isi      = $sql;
$tipe     = 'text/plain';
if (function_exists('gzencode')) {
    $isi      = gzencode($sql, 9);
    $namaFile .= '.gz';
    $tipe     = 'application/gzip';
}

$dirBackup = __DIR__ . '/../backups';
if (!is_dir($dirBackup)) @mkdir($dirBackup, 0755, true);
@file_put_contents($dirBackup . '/.htaccess', "Order allow,deny\nDeny from all\n");
@file_put_contents($dirBackup . '/' . $namaFile, $isi);
bersihkanBackupLama($dirBackup, 5);

$emailTujuan = trim((string)$pm->get('backup_email', ''));
$status = 'sukses';
$ket    = 'File tersimpan lokal: ' . $namaFile;

if ($emailTujuan !== '') {
    $subjek = 'Backup Database ' . ($profil['nama_koperasi'] ?? 'Koperasi') . ' — ' . date('d/m/Y H:i');
    $pesan  = "Backup database otomatis\n\n"
            . "Koperasi : " . ($profil['nama_koperasi'] ?? '-') . "\n"
            . "Database : " . DB_NAME . "\n"
            . "Waktu    : " . date('d-m-Y H:i:s') . "\n"
            . "File     : " . $namaFile . "\n"
            . "Ukuran   : " . number_format($ukuran / 1024, 1) . " KB (sebelum kompres)\n\n"
            . "Simpan file lampiran di tempat aman. Untuk restore: import via phpMyAdmin.";

    $emailDari = trim((string)$pm->get('email_pengirim', 'backup@koperasi.local'));
    if (kirimEmailLampiran($emailDari, $emailTujuan, $subjek, $pesan, $namaFile, $isi, $tipe)) {
        $ket .= ' | Email terkirim ke ' . $emailTujuan;
    } else {
        $status = 'gagal';
        $ket   .= ' | GAGAL kirim email ke ' . $emailTujuan . ' (file tetap tersimpan lokal)';
    }
}

$db->prepare('INSERT INTO backup_log (tujuan, status, ukuran, keterangan) VALUES (?,?,?,?)')
   ->execute([$emailTujuan ?: '-', $status, $ukuran, $ket]);

echo "BACKUP SELESAI\n";
echo "- File   : $namaFile\n";
echo "- Ukuran : " . number_format($ukuran) . " byte\n";
echo "- Status : $status\n";
echo "- $ket\n";
exit;

function dumpDatabase(PDO $db): string
{
    $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $sql  = "-- =====================================================\n";
    $sql .= "-- BACKUP DATABASE " . DB_NAME . "\n";
    $sql .= "-- Tanggal: " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- =====================================================\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tables as $t) {
        $sql .= "DROP TABLE IF EXISTS `$t`;\n";
        $create = $db->query("SHOW CREATE TABLE `$t`")->fetch(PDO::FETCH_ASSOC);
        $sql .= $create['Create Table'] . ";\n\n";

        $rows = $db->query("SELECT * FROM `$t`")->fetchAll(PDO::FETCH_ASSOC);
        foreach (array_chunk($rows, 100) as $chunk) {
            $sql .= "INSERT INTO `$t` VALUES\n";
            $vals = [];
            foreach ($chunk as $row) {
                $cols = [];
                foreach ($row as $v) {
                    $cols[] = $v === null ? 'NULL' : $db->quote((string)$v);
                }
                $vals[] = '(' . implode(',', $cols) . ')';
            }
            $sql .= implode(",\n", $vals) . ";\n";
        }
        if ($rows) $sql .= "\n";
    }
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    return $sql;
}

function kirimEmailLampiran(string $dari, string $ke, string $subjek, string $pesan,
                            string $namaFile, string $isi, string $tipe): bool
{
    $boundary = '----=_Batas_' . md5(uniqid());
    $headers  = "From: $dari\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    $body  = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $body .= $pesan . "\r\n";

    $body .= "--$boundary\r\n";
    $body .= "Content-Type: $tipe; name=\"$namaFile\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n";
    $body .= "Content-Disposition: attachment; filename=\"$namaFile\"\r\n\r\n";
    $body .= chunk_split(base64_encode($isi)) . "\r\n";
    $body .= "--$boundary--";

    return @mail($ke, $subjek, $body, $headers);
}

function bersihkanBackupLama(string $dir, int $maks = 5): void
{
    $files = glob($dir . '/backup_*');
    if (!$files) return;
    usort($files, function ($a, $b) { return filemtime($b) - filemtime($a); });
    foreach (array_slice($files, $maks) as $f) @unlink($f);
}