<?php
require_once __DIR__ . '/../config/database.php';

class NotifikasiKirimModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function catat(int $idAngsuran, string $kanal, string $tujuan, string $status, string $pesan): void
    {
        $this->db->prepare(
            'INSERT INTO notifikasi_kirim (id_angsuran, kanal, tujuan, status, pesan) VALUES (?,?,?,?,?)'
        )->execute([$idAngsuran, $kanal, $tujuan, $status, $pesan]);
    }

    public function log(int $limit = 20): array
    {
        $st = $this->db->prepare(
            'SELECT nk.*, ag.nama AS nama_anggota
             FROM notifikasi_kirim nk
             LEFT JOIN angsuran a   ON nk.id_angsuran = a.id_angsuran
             LEFT JOIN pembiayaan p ON a.id_pembiayaan = p.id_pembiayaan
             LEFT JOIN anggota ag   ON p.id_anggota = ag.id_anggota
             ORDER BY nk.id_kirim DESC LIMIT ?'
        );
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }
}