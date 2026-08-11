<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/PengaturanModel.php';
require_once __DIR__ . '/JurnalModel.php';

class DendaModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function tarifHarian(): float
    {
        return (float)(new PengaturanModel())->get('denda_harian', 2000);
    }

    public function keteranganTarif(): string
    {
        $pm = new PengaturanModel();
        if ($pm->get('denda_jenis', 'nominal') === 'persen') {
            return $pm->get('denda_persen', '0.5') . '% per hari dari tagihan';
        }
        return 'Rp ' . number_format($this->tarifHarian(), 0, ',', '.') . ' per hari';
    }

    public function hitung(array $angsuran, string $tanggalBayar): array
    {
        $hari = (int)floor((strtotime($tanggalBayar) - strtotime($angsuran['tanggal_jatuh_tempo'])) / 86400);
        $hari = max(0, $hari);
        $pm = new PengaturanModel();
        if ($pm->get('denda_jenis', 'nominal') === 'persen') {
            $persen = (float)$pm->get('denda_persen', 0.5);
            $jumlah = $hari * ($angsuran['total'] * $persen / 100);
        } else {
            $jumlah = $hari * $this->tarifHarian();
        }
        return ['hari' => $hari, 'jumlah' => round($jumlah)];
    }

    public function byAngsuran(int $idAngsuran)
    {
        $st = $this->db->prepare('SELECT * FROM denda WHERE id_angsuran = ?');
        $st->execute([$idAngsuran]);
        return $st->fetch();
    }

    public function all(string $status = ''): array
    {
        $sql = 'SELECT d.*, a.angsuran_ke, a.tanggal_jatuh_tempo, p.no_pembiayaan, ag.nama
                FROM denda d
                JOIN angsuran a   ON d.id_angsuran   = a.id_angsuran
                JOIN pembiayaan p ON d.id_pembiayaan = p.id_pembiayaan
                JOIN anggota ag   ON p.id_anggota    = ag.id_anggota';
        $params = [];
        if ($status !== '') { $sql .= ' WHERE d.status = ?'; $params[] = $status; }
        $sql .= ' ORDER BY d.id_denda DESC';
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function create(int $idAngsuran, int $idPembiayaan, string $tanggal, int $hari, float $jumlah, int $idUser): int
    {
        $st = $this->db->prepare(
            'INSERT INTO denda (id_angsuran, id_pembiayaan, tanggal_denda, hari_terlambat, jumlah_denda, status, id_user)
             VALUES (?,?,?,?,?,"belum_bayar",?)'
        );
        $st->execute([$idAngsuran, $idPembiayaan, $tanggal, $hari, $jumlah, $idUser]);
        return (int)$this->db->lastInsertId();
    }

    public function bayar(int $id, string $tanggal): void
    {
        $st = $this->db->prepare('SELECT * FROM denda WHERE id_denda = ?');
        $st->execute([$id]);
        $d = $st->fetch();
        if (!$d) throw new Exception('Data denda tidak ditemukan.');
        if ($d['status'] === 'lunas') return;

        $this->db->prepare('UPDATE denda SET status="lunas", tanggal_bayar=? WHERE id_denda=?')
                 ->execute([$tanggal, $id]);

        (new JurnalModel())->otomatis(
            'denda', 'DENDA-' . $id, $tanggal,
            "Pembayaran denda ta'zir ({$d['hari_terlambat']} hari terlambat)",
            [
                ['kode' => '101', 'posisi' => 'debit',  'jumlah' => $d['jumlah_denda']],
                ['kode' => '305', 'posisi' => 'kredit', 'jumlah' => $d['jumlah_denda']],
            ]
        );
    }

    public function totalTerkumpul(): float
    {
        return (float)$this->db->query(
            "SELECT COALESCE(SUM(jumlah_denda),0) AS t FROM denda WHERE status='lunas'"
        )->fetch()['t'];
    }
}