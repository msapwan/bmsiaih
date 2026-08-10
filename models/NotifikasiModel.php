<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/PengaturanModel.php';

class NotifikasiModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function hari(): int
    {
        return (int)(new PengaturanModel())->get('notif_hari', 7);
    }

    public function akanJatuhTempo(): array
    {
        $st = $this->db->prepare(
            "SELECT a.*, p.no_pembiayaan, ag.nama, ag.no_hp, ag.email
             FROM angsuran a
             JOIN pembiayaan p ON a.id_pembiayaan = p.id_pembiayaan
             JOIN anggota ag   ON p.id_anggota    = ag.id_anggota
             WHERE p.status='berjalan' AND a.status='belum'
               AND a.tanggal_jatuh_tempo BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
             ORDER BY a.tanggal_jatuh_tempo"
        );
        $st->execute([$this->hari()]);
        return $st->fetchAll();
    }

    public function terlambat(): array
    {
        return $this->db->query(
            "SELECT a.*, p.no_pembiayaan, ag.nama, ag.no_hp, ag.email
             FROM angsuran a
             JOIN pembiayaan p ON a.id_pembiayaan = p.id_pembiayaan
             JOIN anggota ag   ON p.id_anggota    = ag.id_anggota
             WHERE p.status='berjalan' AND a.status='belum'
               AND a.tanggal_jatuh_tempo < CURDATE()
             ORDER BY a.tanggal_jatuh_tempo"
        )->fetchAll();
    }

    public function semua(): array
    {
        return array_merge($this->terlambat(), $this->akanJatuhTempo());
    }

    public function total(): int
    {
        return count($this->terlambat()) + count($this->akanJatuhTempo());
    }
}