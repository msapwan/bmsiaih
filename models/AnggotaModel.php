<?php
require_once __DIR__ . '/../config/database.php';

class AnggotaModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(string $cari = ''): array
    {
        $sql = 'SELECT * FROM anggota';
        $params = [];
        if ($cari !== '') {
            $sql .= ' WHERE nama LIKE ? OR kode_anggota LIKE ? OR nik LIKE ? OR no_hp LIKE ?';
            $like = "%$cari%";
            $params = [$like, $like, $like, $like];
        }
        $sql .= ' ORDER BY id_anggota DESC';
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function find(int $id)
    {
        $st = $this->db->prepare('SELECT * FROM anggota WHERE id_anggota = ?');
        $st->execute([$id]);
        return $st->fetch();
    }

    public function opsiAktif(): array
    {
        return $this->db->query(
            "SELECT id_anggota, kode_anggota, nama FROM anggota
             WHERE status_anggota = 'aktif' ORDER BY nama"
        )->fetchAll();
    }

    public function create(array $d): int
    {
        $st = $this->db->prepare(
            'INSERT INTO anggota (kode_anggota, nik, nama, jenis_kelamin, tempat_lahir, tanggal_lahir,
                alamat, no_hp, email, pekerjaan, status_anggota, tanggal_daftar)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $st->execute([
            $this->generateKode(), $d['nik'], $d['nama'], $d['jenis_kelamin'], $d['tempat_lahir'], $d['tanggal_lahir'],
            $d['alamat'], $d['no_hp'], $d['email'], $d['pekerjaan'], $d['status_anggota'], $d['tanggal_daftar'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        $st = $this->db->prepare(
            'UPDATE anggota SET nik=?, nama=?, jenis_kelamin=?, tempat_lahir=?, tanggal_lahir=?,
                alamat=?, no_hp=?, email=?, pekerjaan=?, status_anggota=?, tanggal_daftar=?
             WHERE id_anggota=?'
        );
        $st->execute([
            $d['nik'], $d['nama'], $d['jenis_kelamin'], $d['tempat_lahir'], $d['tanggal_lahir'],
            $d['alamat'], $d['no_hp'], $d['email'], $d['pekerjaan'], $d['status_anggota'], $d['tanggal_daftar'], $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM anggota WHERE id_anggota = ?')->execute([$id]);
    }

    public function saldoSimpanan(int $idAnggota): array
    {
        $st = $this->db->prepare(
            "SELECT jenis_simpanan,
                    COALESCE(SUM(CASE WHEN tipe='setor' THEN jumlah ELSE -jumlah END),0) AS saldo
             FROM simpanan WHERE id_anggota = ? GROUP BY jenis_simpanan"
        );
        $st->execute([$idAnggota]);
        $hasil = ['pokok' => 0, 'wajib' => 0, 'sukarela' => 0];
        foreach ($st->fetchAll() as $r) $hasil[$r['jenis_simpanan']] = (float)$r['saldo'];
        return $hasil;
    }

    public function riwayatSimpanan(int $idAnggota): array
    {
        $st = $this->db->prepare(
            'SELECT s.*, u.nama_lengkap AS petugas
             FROM simpanan s LEFT JOIN users u ON s.id_user = u.id_user
             WHERE s.id_anggota = ? ORDER BY s.tanggal DESC, s.id_simpanan DESC'
        );
        $st->execute([$idAnggota]);
        return $st->fetchAll();
    }

    public function riwayatPembiayaan(int $idAnggota): array
    {
        $st = $this->db->prepare(
            'SELECT p.*, ja.nama_akad
             FROM pembiayaan p JOIN jenis_akad ja ON p.id_akad = ja.id_akad
             WHERE p.id_anggota = ? ORDER BY p.id_pembiayaan DESC'
        );
        $st->execute([$idAnggota]);
        return $st->fetchAll();
    }

    private function generateKode(): string
    {
        $n = (int)$this->db->query('SELECT COALESCE(MAX(id_anggota),0)+1 AS n FROM anggota')->fetch()['n'];
        return 'AGT-' . str_pad($n, 4, '0', STR_PAD_LEFT);
    }
}