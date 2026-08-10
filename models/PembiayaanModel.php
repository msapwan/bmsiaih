<?php
require_once __DIR__ . '/../config/database.php';

class PembiayaanModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(string $status = ''): array
    {
        $sql = 'SELECT p.*, a.nama, a.kode_anggota, ja.nama_akad,
                       COALESCE(b.bayar,0) AS total_bayar
                FROM pembiayaan p
                JOIN anggota a     ON p.id_anggota = a.id_anggota
                JOIN jenis_akad ja ON p.id_akad    = ja.id_akad
                LEFT JOIN (SELECT id_pembiayaan, SUM(jumlah_bayar) AS bayar
                           FROM angsuran GROUP BY id_pembiayaan) b
                       ON b.id_pembiayaan = p.id_pembiayaan';
        $params = [];
        if ($status !== '') { $sql .= ' WHERE p.status = ?'; $params[] = $status; }
        $sql .= ' ORDER BY p.id_pembiayaan DESC';
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function find(int $id)
    {
        $st = $this->db->prepare(
            'SELECT p.*, a.nama, a.kode_anggota, ja.nama_akad, ja.tipe_akad, ja.kode_akad
             FROM pembiayaan p
             JOIN anggota a     ON p.id_anggota = a.id_anggota
             JOIN jenis_akad ja ON p.id_akad    = ja.id_akad
             WHERE p.id_pembiayaan = ?'
        );
        $st->execute([$id]);
        return $st->fetch();
    }

    public function create(array $d): int
    {
        $st = $this->db->prepare(
            'INSERT INTO pembiayaan (no_pembiayaan, id_anggota, id_akad, tanggal_pengajuan,
                jumlah_pembiayaan, margin_persen, nisbah_koperasi, jangka_waktu, status, catatan, id_user)
             VALUES (?,?,?,?,?,?,?,?, "pengajuan", ?,?)'
        );
        $st->execute([
            $this->generateNomor(), $d['id_anggota'], $d['id_akad'], $d['tanggal_pengajuan'],
            $d['jumlah_pembiayaan'], $d['margin_persen'], $d['nisbah_koperasi'],
            $d['jangka_waktu'], $d['catatan'], $d['id_user'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function setujui(int $id, string $tanggalAkad): void
    {
        $p = $this->find($id);
        if (!$p || $p['status'] !== 'pengajuan') {
            throw new Exception('Pembiayaan tidak valid untuk disetujui.');
        }
        $marginNominal = 0;
        if ($p['tipe_akad'] === 'margin') {
            $marginNominal = (int)round(
                $p['jumlah_pembiayaan'] * $p['margin_persen'] / 100 * ($p['jangka_waktu'] / 12)
            );
        }
        $totalPiutang = $p['jumlah_pembiayaan'] + $marginNominal;
        $tglAkad    = new DateTime($tanggalAkad);
        $jatuhTempo = (clone $tglAkad)->modify('+' . (int)$p['jangka_waktu'] . ' month');

        $this->db->prepare(
            'UPDATE pembiayaan
             SET status="berjalan", tanggal_akad=?, margin_nominal=?, total_piutang=?, tanggal_jatuh_tempo=?
             WHERE id_pembiayaan=?'
        )->execute([$tanggalAkad, $marginNominal, $totalPiutang, $jatuhTempo->format('Y-m-d'), $id]);

        $this->generateAngsuran($id);
    }

    public function tolak(int $id, string $catatan): void
    {
        $this->db->prepare(
            'UPDATE pembiayaan SET status="ditolak", catatan=? WHERE id_pembiayaan=? AND status="pengajuan"'
        )->execute([$catatan, $id]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM pembiayaan WHERE id_pembiayaan=? AND status="pengajuan"')->execute([$id]);
    }

    private function generateAngsuran(int $id): void
    {
        $p  = $this->find($id);
        $jw = (int)$p['jangka_waktu'];
        if ($jw < 1) return;

        $pokokBulan  = (int)floor($p['jumlah_pembiayaan'] / $jw);
        $marginBulan = (int)floor($p['margin_nominal'] / $jw);
        $sisaPokok   = $p['jumlah_pembiayaan'] - ($pokokBulan * $jw);
        $sisaMargin  = $p['margin_nominal']    - ($marginBulan * $jw);

        $tgl = new DateTime($p['tanggal_akad']);
        $ins = $this->db->prepare(
            'INSERT INTO angsuran (id_pembiayaan, angsuran_ke, tanggal_jatuh_tempo, pokok, margin, total)
             VALUES (?,?,?,?,?,?)'
        );
        for ($i = 1; $i <= $jw; $i++) {
            $jatuh = (clone $tgl)->modify("+{$i} month")->format('Y-m-d');
            $pokok  = $pokokBulan + ($i === $jw ? $sisaPokok : 0);
            $margin = $marginBulan + ($i === $jw ? $sisaMargin : 0);
            $ins->execute([$id, $i, $jatuh, $pokok, $margin, $pokok + $margin]);
        }
    }

    public function angsuran(int $idPembiayaan): array
    {
        $st = $this->db->prepare('SELECT * FROM angsuran WHERE id_pembiayaan=? ORDER BY angsuran_ke');
        $st->execute([$idPembiayaan]);
        return $st->fetchAll();
    }

    public function findAngsuran(int $idAngsuran)
    {
        $st = $this->db->prepare('SELECT * FROM angsuran WHERE id_angsuran=?');
        $st->execute([$idAngsuran]);
        return $st->fetch();
    }

    public function bayarAngsuran(int $idAngsuran, float $jumlah, string $tanggal): void
    {
        $a = $this->findAngsuran($idAngsuran);
        if (!$a) throw new Exception('Angsuran tidak ditemukan.');

        $baru   = (float)$a['jumlah_bayar'] + $jumlah;
        $status = ($baru >= $a['total']) ? 'lunas' : 'belum';

        $this->db->prepare(
            'UPDATE angsuran SET tanggal_bayar=?, jumlah_bayar=?, status=? WHERE id_angsuran=?'
        )->execute([$tanggal, $baru, $status, $idAngsuran]);

        $cek = $this->db->prepare('SELECT COUNT(*) AS sisa FROM angsuran WHERE id_pembiayaan=? AND status="belum"');
        $cek->execute([$a['id_pembiayaan']]);
        if ((int)$cek->fetch()['sisa'] === 0) {
            $this->db->prepare('UPDATE pembiayaan SET status="lunas" WHERE id_pembiayaan=?')
                     ->execute([$a['id_pembiayaan']]);
        }
    }

    private function generateNomor(): string
    {
        $n = (int)$this->db->query('SELECT COUNT(*)+1 AS n FROM pembiayaan')->fetch()['n'];
        return 'PJM-' . date('Ym') . '-' . str_pad($n, 4, '0', STR_PAD_LEFT);
    }
}