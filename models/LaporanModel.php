<?php
require_once __DIR__ . '/../config/database.php';

class LaporanModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function statistik(): array
    {
        $sql = "SELECT
            (SELECT COUNT(*) FROM anggota WHERE status_anggota='aktif')                AS anggota_aktif,
            (SELECT COALESCE(SUM(IF(tipe='setor', jumlah, -jumlah)),0) FROM simpanan) AS total_simpanan,
            (SELECT COALESCE(SUM(p.total_piutang - COALESCE(b.bayar,0)),0)
               FROM pembiayaan p
               LEFT JOIN (SELECT id_pembiayaan, SUM(jumlah_bayar) bayar
                          FROM angsuran GROUP BY id_pembiayaan) b
                      ON b.id_pembiayaan = p.id_pembiayaan
              WHERE p.status='berjalan')                                              AS outstanding,
            (SELECT COUNT(*) FROM pembiayaan WHERE status='pengajuan')                AS pengajuan,
            (SELECT COUNT(*) FROM pembiayaan WHERE status='berjalan')                 AS berjalan,
            (SELECT COUNT(DISTINCT a.id_pembiayaan)
               FROM angsuran a
               JOIN pembiayaan p ON a.id_pembiayaan = p.id_pembiayaan AND p.status='berjalan'
              WHERE a.status='belum' AND a.tanggal_jatuh_tempo < CURDATE())           AS macet";
        return $this->db->query($sql)->fetch();
    }

    public function transaksiTerbaru(int $limit = 6): array
    {
        $st = $this->db->prepare(
            'SELECT s.*, a.nama FROM simpanan s JOIN anggota a ON s.id_anggota=a.id_anggota
             ORDER BY s.id_simpanan DESC LIMIT ?'
        );
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public function pengajuanPending(int $limit = 5): array
    {
        $st = $this->db->prepare(
            "SELECT p.*, a.nama, ja.nama_akad
             FROM pembiayaan p
             JOIN anggota a ON p.id_anggota=a.id_anggota
             JOIN jenis_akad ja ON p.id_akad=ja.id_akad
             WHERE p.status='pengajuan' ORDER BY p.id_pembiayaan DESC LIMIT ?"
        );
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public function rekapPembiayaan(string $dari = '', string $sampai = ''): array
    {
        $sql = 'SELECT p.*, a.nama, a.kode_anggota, ja.nama_akad,
                       COALESCE(b.bayar,0) AS total_bayar
                FROM pembiayaan p
                JOIN anggota a ON p.id_anggota=a.id_anggota
                JOIN jenis_akad ja ON p.id_akad=ja.id_akad
                LEFT JOIN (SELECT id_pembiayaan, SUM(jumlah_bayar) bayar
                           FROM angsuran GROUP BY id_pembiayaan) b
                       ON b.id_pembiayaan=p.id_pembiayaan
                WHERE p.status <> "pengajuan"';
        $params = [];
        if ($dari !== '')   { $sql .= ' AND p.tanggal_akad >= ?'; $params[] = $dari; }
        if ($sampai !== '') { $sql .= ' AND p.tanggal_akad <= ?'; $params[] = $sampai; }
        $sql .= ' ORDER BY p.id_pembiayaan DESC';
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function transaksiSimpanan(string $dari = '', string $sampai = ''): array
    {
        $sql = 'SELECT s.*, a.kode_anggota, a.nama FROM simpanan s
                JOIN anggota a ON s.id_anggota=a.id_anggota WHERE 1=1';
        $params = [];
        if ($dari !== '')   { $sql .= ' AND s.tanggal >= ?'; $params[] = $dari; }
        if ($sampai !== '') { $sql .= ' AND s.tanggal <= ?'; $params[] = $sampai; }
        $sql .= ' ORDER BY s.tanggal DESC, s.id_simpanan DESC';
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
}