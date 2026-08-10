<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/PengaturanModel.php';

class ShuModel
{
    private $db;

    public static $alokasiDefault = [
        'Dana Cadangan'                 => 'shu_cadangan',
        'Jasa Modal Anggota'            => 'shu_jasa_modal',
        'Jasa Usaha Anggota'            => 'shu_jasa_usaha',
        'Jasa Pengurus & Pengelola'     => 'shu_pengurus',
        'Dana Sosial'                   => 'shu_sosial',
        'Dana Pembangunan & Lingkungan' => 'shu_pembangunan',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM shu ORDER BY tahun DESC')->fetchAll();
    }

    public function find(int $id)
    {
        $st = $this->db->prepare('SELECT * FROM shu WHERE id_shu = ?');
        $st->execute([$id]);
        return $st->fetch();
    }

    public function byTahun(int $tahun)
    {
        $st = $this->db->prepare('SELECT * FROM shu WHERE tahun = ?');
        $st->execute([$tahun]);
        return $st->fetch();
    }

    public function alokasi(int $idShu): array
    {
        $st = $this->db->prepare('SELECT * FROM shu_alokasi WHERE id_shu = ? ORDER BY id_alokasi');
        $st->execute([$idShu]);
        return $st->fetchAll();
    }

    public function anggota(int $idShu): array
    {
        $st = $this->db->prepare(
            'SELECT sa.*, a.kode_anggota, a.nama
             FROM shu_anggota sa JOIN anggota a ON sa.id_anggota = a.id_anggota
             WHERE sa.id_shu = ? ORDER BY sa.total DESC'
        );
        $st->execute([$idShu]);
        return $st->fetchAll();
    }

    public function simpan(int $tahun, float $pendapatan, float $beban, int $idUser): int
    {
        $shu = $pendapatan - $beban;
        if ($shu < 0) throw new Exception('SHU negatif — periksa kembali pendapatan & beban.');

        $this->db->prepare('DELETE FROM shu WHERE tahun = ?')->execute([$tahun]);

        $this->db->prepare(
            'INSERT INTO shu (tahun, total_pendapatan, total_beban, total_shu, status, id_user)
             VALUES (?,?,?,?, "draft", ?)'
        )->execute([$tahun, $pendapatan, $beban, $shu, $idUser]);
        $idShu = (int)$this->db->lastInsertId();

        $pm = new PengaturanModel();

        $insAlokasi = $this->db->prepare(
            'INSERT INTO shu_alokasi (id_shu, nama_dana, persen, jumlah) VALUES (?,?,?,?)'
        );
        foreach (self::$alokasiDefault as $nama => $kunci) {
            $persen = (float)$pm->get($kunci, 0);
            $insAlokasi->execute([$idShu, $nama, $persen, round($shu * $persen / 100)]);
        }

        $poolModal = $shu * (float)$pm->get('shu_jasa_modal', 0) / 100;
        $poolUsaha = $shu * (float)$pm->get('shu_jasa_usaha', 0) / 100;

        $saldo = $this->db->query(
            "SELECT a.id_anggota,
                    COALESCE(SUM(IF(s.tipe='setor', s.jumlah, -s.jumlah)),0) AS saldo
             FROM anggota a LEFT JOIN simpanan s ON s.id_anggota = a.id_anggota
             WHERE a.status_anggota = 'aktif' GROUP BY a.id_anggota"
        )->fetchAll(PDO::FETCH_KEY_PAIR);
        $totalSaldo = array_sum($saldo);

        $st = $this->db->prepare(
            'SELECT p.id_anggota, COALESCE(SUM(a.margin),0) AS jasa
             FROM angsuran a JOIN pembiayaan p ON a.id_pembiayaan = p.id_pembiayaan
             WHERE a.jumlah_bayar > 0 AND YEAR(a.tanggal_bayar) = ?
             GROUP BY p.id_anggota'
        );
        $st->execute([$tahun]);
        $usaha = $st->fetchAll(PDO::FETCH_KEY_PAIR);
        $totalUsaha = array_sum($usaha);

        $insAnggota = $this->db->prepare(
            'INSERT INTO shu_anggota (id_shu, id_anggota, jasa_modal, jasa_usaha, total) VALUES (?,?,?,?,?)'
        );
        foreach (array_keys($saldo) as $idAnggota) {
            $jm = $totalSaldo > 0 ? $saldo[$idAnggota] / $totalSaldo * $poolModal : 0;
            $ju = ($totalUsaha > 0 && isset($usaha[$idAnggota]))
                ? $usaha[$idAnggota] / $totalUsaha * $poolUsaha : 0;
            $insAnggota->execute([$idShu, $idAnggota, round($jm), round($ju), round($jm + $ju)]);
        }
        return $idShu;
    }

    public function tetapkan(int $id): void
    {
        $this->db->prepare('UPDATE shu SET status="ditetapkan", tanggal_penetapan=? WHERE id_shu=?')
                 ->execute([date('Y-m-d'), $id]);
    }

    public function hapus(int $id): void
    {
        $this->db->prepare('DELETE FROM shu WHERE id_shu = ?')->execute([$id]);
    }

    public function tandaiTerima(int $idShuAnggota): void
    {
        $this->db->prepare('UPDATE shu_anggota SET status="diterima", tanggal_terima=? WHERE id_shu_anggota=?')
                 ->execute([date('Y-m-d'), $idShuAnggota]);
        $st = $this->db->prepare('SELECT id_shu FROM shu_anggota WHERE id_shu_anggota = ?');
        $st->execute([$idShuAnggota]);
        $idShu = (int)$st->fetch()['id_shu'];
        $cek = $this->db->prepare('SELECT COUNT(*) AS c FROM shu_anggota WHERE id_shu=? AND status="belum"');
        $cek->execute([$idShu]);
        if ((int)$cek->fetch()['c'] === 0) {
            $this->db->prepare('UPDATE shu SET status="dibagikan" WHERE id_shu=?')->execute([$idShu]);
        }
    }
}