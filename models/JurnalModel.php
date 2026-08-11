<?php
require_once __DIR__ . '/../config/database.php';

class JurnalModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function akunSemua(): array
    {
        return $this->db->query('SELECT * FROM akun ORDER BY kode_akun')->fetchAll();
    }

    public function idAkun(string $kode)
    {
        $st = $this->db->prepare('SELECT id_akun FROM akun WHERE kode_akun = ?');
        $st->execute([$kode]);
        $r = $st->fetch();
        return $r ? (int)$r['id_akun'] : null;
    }

    public function all(string $dari = '', string $sampai = ''): array
    {
        $sql = 'SELECT j.*, COALESCE(SUM(IF(jd.posisi="debit", jd.jumlah, 0)),0) AS total_debit
                FROM jurnal j
                LEFT JOIN jurnal_detail jd ON jd.id_jurnal = j.id_jurnal
                WHERE 1=1';
        $params = [];
        if ($dari !== '')   { $sql .= ' AND j.tanggal >= ?'; $params[] = $dari; }
        if ($sampai !== '') { $sql .= ' AND j.tanggal <= ?'; $params[] = $sampai; }
        $sql .= ' GROUP BY j.id_jurnal ORDER BY j.tanggal DESC, j.id_jurnal DESC';
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function detailsUntuk(array $ids): array
    {
        if (!$ids) return [];
        $in = implode(',', array_map('intval', $ids));
        $rows = $this->db->query(
            "SELECT jd.*, a.kode_akun, a.nama_akun
             FROM jurnal_detail jd JOIN akun a ON jd.id_akun = a.id_akun
             WHERE jd.id_jurnal IN ($in) ORDER BY jd.id_detail"
        )->fetchAll();
        $grup = [];
        foreach ($rows as $r) $grup[$r['id_jurnal']][] = $r;
        return $grup;
    }

    public function buat(string $tanggal, string $keterangan, string $referensi, string $sumber, array $details): int
    {
        $dr = $cr = 0;
        foreach ($details as $d) {
            if ($d['posisi'] === 'debit') $dr += $d['jumlah'];
            else                          $cr += $d['jumlah'];
        }
        if (abs($dr - $cr) > 0.01) {
            throw new Exception('Jurnal tidak seimbang (debit ' . number_format($dr) . ' vs kredit ' . number_format($cr) . ').');
        }
        $st = $this->db->prepare(
            'INSERT INTO jurnal (no_jurnal, tanggal, keterangan, referensi, sumber, id_user) VALUES (?,?,?,?,?,?)'
        );
        $st->execute([
            $this->generateNomor(), $tanggal, $keterangan, $referensi, $sumber,
            $_SESSION['user']['id_user'] ?? null,
        ]);
        $id = (int)$this->db->lastInsertId();

        $ins = $this->db->prepare('INSERT INTO jurnal_detail (id_jurnal, id_akun, posisi, jumlah) VALUES (?,?,?,?)');
        foreach ($details as $d) {
            if ($d['jumlah'] > 0) $ins->execute([$id, $d['id_akun'], $d['posisi'], $d['jumlah']]);
        }
        return $id;
    }

    public function otomatis(string $sumber, string $referensi, string $tanggal, string $keterangan, array $lines): void
    {
        try {
            $details = [];
            foreach ($lines as $l) {
                if ($l['jumlah'] <= 0) continue;
                $id = $this->idAkun($l['kode']);
                if (!$id) continue;
                $details[] = ['id_akun' => $id, 'posisi' => $l['posisi'], 'jumlah' => $l['jumlah']];
            }
            if ($details) $this->buat($tanggal, $keterangan, $referensi, $sumber, $details);
        } catch (Exception $e) {
            // diabaikan agar transaksi utama tetap jalan
        }
    }

    public function hapus(int $id): void
    {
        $this->db->prepare('DELETE FROM jurnal_detail WHERE id_jurnal = ?')->execute([$id]);
        $this->db->prepare('DELETE FROM jurnal WHERE id_jurnal = ?')->execute([$id]);
    }

    private function generateNomor(): string
    {
        $n = (int)$this->db->query('SELECT COUNT(*)+1 AS n FROM jurnal')->fetch()['n'];
        return 'JU-' . date('Ym') . '-' . str_pad($n, 4, '0', STR_PAD_LEFT);
    }

    public function saldoAwalAda(): array
    {
        $j = $this->db->query(
            "SELECT id_jurnal, tanggal FROM jurnal WHERE sumber='saldo_awal' ORDER BY id_jurnal DESC LIMIT 1"
        )->fetch();
        if (!$j) return [];
        $st = $this->db->prepare(
            'SELECT a.kode_akun, a.nama_akun, jd.posisi, jd.jumlah
             FROM jurnal_detail jd JOIN akun a ON jd.id_akun = a.id_akun
             WHERE jd.id_jurnal = ? ORDER BY a.kode_akun'
        );
        $st->execute([$j['id_jurnal']]);
        return ['tanggal' => $j['tanggal'], 'baris' => $st->fetchAll()];
    }

    public function simpanSaldoAwal(string $tanggal, array $nilaiPerKode): void
    {
        $this->db->prepare("DELETE FROM jurnal WHERE sumber='saldo_awal'")->execute();
        $details = [];
        foreach ($nilaiPerKode as $kode => $jumlah) {
            $st = $this->db->prepare('SELECT id_akun, saldo_normal FROM akun WHERE kode_akun = ?');
            $st->execute([$kode]);
            $a = $st->fetch();
            if (!$a || $jumlah <= 0) continue;
            $details[] = ['id_akun' => $a['id_akun'], 'posisi' => $a['saldo_normal'], 'jumlah' => $jumlah];
        }
        if (!$details) throw new Exception('Tidak ada nominal saldo awal yang diisi.');
        $this->buat($tanggal, 'Saldo Awal', '', 'saldo_awal', $details);
    }

    public function saldoPerAkun(string $sampai = ''): array
    {
        $sql = "SELECT a.id_akun, a.kode_akun, a.nama_akun, a.tipe, a.saldo_normal,
                  COALESCE(SUM(CASE WHEN jd.posisi='debit'  THEN jd.jumlah END),0) AS debit,
                  COALESCE(SUM(CASE WHEN jd.posisi='kredit' THEN jd.jumlah END),0) AS kredit
                FROM akun a
                LEFT JOIN jurnal_detail jd ON jd.id_akun = a.id_akun
                LEFT JOIN jurnal j ON j.id_jurnal = jd.id_jurnal"
             . ($sampai !== '' ? ' AND j.tanggal <= ?' : '')
             . ' GROUP BY a.id_akun ORDER BY a.kode_akun';
        $st = $this->db->prepare($sql);
        $st->execute($sampai !== '' ? [$sampai] : []);
        $out = [];
        foreach ($st->fetchAll() as $r) {
            $r['saldo'] = $r['saldo_normal'] === 'debit' ? $r['debit'] - $r['kredit'] : $r['kredit'] - $r['debit'];
            $out[] = $r;
        }
        return $out;
    }

    public function mutasiPerAkun(string $dari, string $sampai): array
    {
        $sql = "SELECT a.id_akun, a.kode_akun, a.nama_akun, a.tipe, a.saldo_normal,
                  COALESCE(SUM(CASE WHEN jd.posisi='debit'  THEN jd.jumlah END),0) AS debit,
                  COALESCE(SUM(CASE WHEN jd.posisi='kredit' THEN jd.jumlah END),0) AS kredit
                FROM akun a
                LEFT JOIN jurnal_detail jd ON jd.id_akun = a.id_akun
                LEFT JOIN jurnal j ON j.id_jurnal = jd.id_jurnal AND j.tanggal BETWEEN ? AND ?
                GROUP BY a.id_akun ORDER BY a.kode_akun";
        $st = $this->db->prepare($sql);
        $st->execute([$dari, $sampai]);
        $out = [];
        foreach ($st->fetchAll() as $r) {
            $r['saldo'] = $r['saldo_normal'] === 'debit' ? $r['debit'] - $r['kredit'] : $r['kredit'] - $r['debit'];
            $out[] = $r;
        }
        return $out;
    }

    public function labaRugi(string $dari, string $sampai): array
    {
        $rows = $this->mutasiPerAkun($dari, $sampai);
        $pendapatan = $beban = [];
        $tp = $tb = 0;
        foreach ($rows as $r) {
            if ($r['tipe'] === 'pendapatan' && $r['saldo'] != 0) {
                $pendapatan[] = ['kode' => $r['kode_akun'], 'nama' => $r['nama_akun'], 'jumlah' => $r['saldo']];
                $tp += $r['saldo'];
            }
            if ($r['tipe'] === 'beban' && $r['saldo'] != 0) {
                $beban[] = ['kode' => $r['kode_akun'], 'nama' => $r['nama_akun'], 'jumlah' => $r['saldo']];
                $tb += $r['saldo'];
            }
        }
        return [
            'pendapatan' => $pendapatan, 'beban' => $beban,
            'total_pendapatan' => $tp, 'total_beban' => $tb, 'laba' => $tp - $tb,
        ];
    }

    public function neraca(string $tanggal): array
    {
        $rows = $this->saldoPerAkun($tanggal);
        $aset = $kewajiban = $ekuitas = [];
        $ta = $tk = $te = 0;
        foreach ($rows as $r) {
            if ($r['saldo'] == 0 || $r['tipe'] === 'pendapatan' || $r['tipe'] === 'beban') continue;
            $item = ['kode' => $r['kode_akun'], 'nama' => $r['nama_akun'], 'jumlah' => $r['saldo']];
            if ($r['tipe'] === 'aset')      { $aset[] = $item;      $ta += $r['saldo']; }
            if ($r['tipe'] === 'kewajiban') { $kewajiban[] = $item; $tk += $r['saldo']; }
            if ($r['tipe'] === 'ekuitas')   { $ekuitas[] = $item;   $te += $r['saldo']; }
        }
        $tahun = substr($tanggal, 0, 4);
        $shuBerjalan = $this->labaRugi("$tahun-01-01", $tanggal)['laba'];
        $pasiva = $tk + $te + $shuBerjalan;
        return [
            'aset' => $aset, 'kewajiban' => $kewajiban, 'ekuitas' => $ekuitas,
            'shu_berjalan' => $shuBerjalan,
            'total_aset' => $ta, 'total_kewajiban' => $tk, 'total_ekuitas' => $te,
            'total_pasiva' => $pasiva, 'balance' => abs($ta - $pasiva) < 1,
        ];
    }

    public function bukuBesar(int $idAkun, string $dari, string $sampai): array
    {
        $st = $this->db->prepare('SELECT * FROM akun WHERE id_akun = ?');
        $st->execute([$idAkun]);
        $akun = $st->fetch();
        if (!$akun) throw new Exception('Akun tidak ditemukan.');

        $st = $this->db->prepare(
            "SELECT COALESCE(SUM(CASE WHEN jd.posisi='debit' THEN jd.jumlah ELSE -jd.jumlah END),0) AS net
             FROM jurnal_detail jd JOIN jurnal j ON j.id_jurnal = jd.id_jurnal
             WHERE jd.id_akun = ? AND j.tanggal < ?"
        );
        $st->execute([$idAkun, $dari]);
        $net = (float)$st->fetch()['net'];
        $saldoAwal = $akun['saldo_normal'] === 'debit' ? $net : -$net;

        $st = $this->db->prepare(
            "SELECT j.tanggal, j.no_jurnal, j.keterangan, jd.posisi, jd.jumlah
             FROM jurnal_detail jd JOIN jurnal j ON j.id_jurnal = jd.id_jurnal
             WHERE jd.id_akun = ? AND j.tanggal BETWEEN ? AND ?
             ORDER BY j.tanggal, j.id_jurnal, jd.id_detail"
        );
        $st->execute([$idAkun, $dari, $sampai]);

        $baris = [];
        $saldo = $saldoAwal;
        $td = $tk = 0;
        foreach ($st->fetchAll() as $r) {
            $dr = $r['posisi'] === 'debit' ? $r['jumlah'] : 0;
            $cr = $r['posisi'] === 'kredit' ? $r['jumlah'] : 0;
            $td += $dr; $tk += $cr;
            $saldo += $akun['saldo_normal'] === 'debit' ? ($dr - $cr) : ($cr - $dr);
            $r['saldo'] = $saldo;
            $baris[] = $r;
        }
        return [
            'akun' => $akun, 'saldo_awal' => $saldoAwal, 'baris' => $baris,
            'mutasi_debit' => $td, 'mutasi_kredit' => $tk, 'saldo_akhir' => $saldo,
        ];
    }

    public function arusKas(string $dari, string $sampai): array
    {
        $kas = $this->db->query("SELECT id_akun FROM akun WHERE kode_akun IN ('101','102')")
                        ->fetchAll(PDO::FETCH_COLUMN);
        if (!$kas) return ['saldo_awal' => 0, 'grup' => [], 'masuk' => 0, 'keluar' => 0, 'saldo_akhir' => 0];
        $in = implode(',', array_map('intval', $kas));

        $st = $this->db->prepare(
            "SELECT COALESCE(SUM(CASE WHEN jd.posisi='debit' THEN jd.jumlah ELSE -jd.jumlah END),0) AS s
             FROM jurnal_detail jd JOIN jurnal j ON j.id_jurnal = jd.id_jurnal
             WHERE jd.id_akun IN ($in) AND j.tanggal < ?"
        );
        $st->execute([$dari]);
        $saldoAwal = (float)$st->fetch()['s'];

        $st = $this->db->prepare(
            "SELECT j.sumber,
                    COALESCE(SUM(CASE WHEN jd.posisi='debit'  THEN jd.jumlah END),0) AS masuk,
                    COALESCE(SUM(CASE WHEN jd.posisi='kredit' THEN jd.jumlah END),0) AS keluar
             FROM jurnal_detail jd JOIN jurnal j ON j.id_jurnal = jd.id_jurnal
             WHERE jd.id_akun IN ($in) AND j.tanggal BETWEEN ? AND ?
             GROUP BY j.sumber"
        );
        $st->execute([$dari, $sampai]);

        $mapAktivitas = [
            'simpanan' => 'operasional', 'angsuran' => 'operasional',
            'denda' => 'operasional', 'shu' => 'operasional',
            'pembiayaan' => 'investasi',
            'manual' => 'lainnya', 'saldo_awal' => 'lainnya',
        ];
        $grup = [
            'operasional' => ['label' => 'Aktivitas Operasional', 'baris' => []],
            'investasi'   => ['label' => 'Aktivitas Investasi (Pencairan Pembiayaan)', 'baris' => []],
            'lainnya'     => ['label' => 'Aktivitas Lainnya / Manual', 'baris' => []],
        ];
        $masuk = $keluar = 0;
        foreach ($st->fetchAll() as $r) {
            $g = $mapAktivitas[$r['sumber']] ?? 'lainnya';
            $grup[$g]['baris'][] = $r;
            $masuk += $r['masuk']; $keluar += $r['keluar'];
        }
        return [
            'saldo_awal' => $saldoAwal, 'grup' => $grup,
            'masuk' => $masuk, 'keluar' => $keluar,
            'saldo_akhir' => $saldoAwal + $masuk - $keluar,
        ];
    }
}