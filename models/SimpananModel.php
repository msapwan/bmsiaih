<?php
require_once __DIR__ . '/../config/database.php';

class SimpananModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(int $idAnggota = 0, string $jenis = '', string $dari = '', string $sampai = ''): array
    {
        $sql = 'SELECT s.*, a.kode_anggota, a.nama, u.nama_lengkap AS petugas
                FROM simpanan s
                JOIN anggota a ON s.id_anggota = a.id_anggota
                LEFT JOIN users u ON s.id_user = u.id_user WHERE 1=1';
        $params = [];
        if ($idAnggota > 0) { $sql .= ' AND s.id_anggota = ?'; $params[] = $idAnggota; }
        if ($jenis !== '')  { $sql .= ' AND s.jenis_simpanan = ?'; $params[] = $jenis; }
        if ($dari !== '')   { $sql .= ' AND s.tanggal >= ?'; $params[] = $dari; }
        if ($sampai !== '') { $sql .= ' AND s.tanggal <= ?'; $params[] = $sampai; }
        $sql .= ' ORDER BY s.tanggal DESC, s.id_simpanan DESC';
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function create(array $d): int
    {
        $st = $this->db->prepare(
            'INSERT INTO simpanan (id_anggota, jenis_simpanan, tipe, tanggal, jumlah, keterangan, id_user)
             VALUES (?,?,?,?,?,?,?)'
        );
        $st->execute([
            $d['id_anggota'], $d['jenis_simpanan'], $d['tipe'], $d['tanggal'],
            $d['jumlah'], $d['keterangan'], $d['id_user'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM simpanan WHERE id_simpanan = ?')->execute([$id]);
    }

    public function saldoAnggota(int $idAnggota): float
    {
        $st = $this->db->prepare(
            "SELECT COALESCE(SUM(CASE WHEN tipe='setor' THEN jumlah ELSE -jumlah END),0) AS saldo
             FROM simpanan WHERE id_anggota = ?"
        );
        $st->execute([$idAnggota]);
        return (float)$st->fetch()['saldo'];
    }

    public function rekapPerAnggota(): array
    {
        return $this->db->query(
            "SELECT a.kode_anggota, a.nama,
                COALESCE(SUM(CASE WHEN s.jenis_simpanan='pokok'
                    THEN IF(s.tipe='setor', s.jumlah, -s.jumlah) END),0) AS pokok,
                COALESCE(SUM(CASE WHEN s.jenis_simpanan='wajib'
                    THEN IF(s.tipe='setor', s.jumlah, -s.jumlah) END),0) AS wajib,
                COALESCE(SUM(CASE WHEN s.jenis_simpanan='sukarela'
                    THEN IF(s.tipe='setor', s.jumlah, -s.jumlah) END),0) AS sukarela,
                COALESCE(SUM(IF(s.tipe='setor', s.jumlah, -s.jumlah)),0) AS total
             FROM anggota a LEFT JOIN simpanan s ON a.id_anggota = s.id_anggota
             WHERE a.status_anggota = 'aktif'
             GROUP BY a.id_anggota ORDER BY a.nama"
        )->fetchAll();
    }

    /** Rekening koran: saldo awal + mutasi + saldo berjalan */
    public function rekeningKoran(int $idAnggota, string $dari, string $sampai): array
    {
        $st = $this->db->prepare(
            "SELECT jenis_simpanan,
                    COALESCE(SUM(IF(tipe='setor', jumlah, -jumlah)),0) AS saldo
             FROM simpanan WHERE id_anggota = ? AND tanggal < ?
             GROUP BY jenis_simpanan"
        );
        $st->execute([$idAnggota, $dari]);
        $saldoAwal = ['pokok' => 0, 'wajib' => 0, 'sukarela' => 0];
        foreach ($st->fetchAll() as $r) $saldoAwal[$r['jenis_simpanan']] = (float)$r['saldo'];
        $saldoAwalTotal = array_sum($saldoAwal);

        $st = $this->db->prepare(
            'SELECT tanggal, jenis_simpanan, tipe, jumlah, keterangan
             FROM simpanan
             WHERE id_anggota = ? AND tanggal BETWEEN ? AND ?
             ORDER BY tanggal, id_simpanan'
        );
        $st->execute([$idAnggota, $dari, $sampai]);

        $baris = [];
        $saldo = $saldoAwalTotal;
        $masuk = $keluar = 0;
        foreach ($st->fetchAll() as $r) {
            if ($r['tipe'] === 'setor') { $masuk += $r['jumlah'];  $saldo += $r['jumlah']; }
            else                        { $keluar += $r['jumlah']; $saldo -= $r['jumlah']; }
            $r['saldo'] = $saldo;
            $baris[] = $r;
        }

        return [
            'saldo_awal'       => $saldoAwal,
            'saldo_awal_total' => $saldoAwalTotal,
            'baris'            => $baris,
            'total_masuk'      => $masuk,
            'total_keluar'     => $keluar,
            'saldo_akhir'      => $saldo,
        ];
    }
}