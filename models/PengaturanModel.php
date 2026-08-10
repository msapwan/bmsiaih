<?php
require_once __DIR__ . '/../config/database.php';

class PengaturanModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function get(string $kunci, $default = null)
    {
        $st = $this->db->prepare('SELECT nilai FROM pengaturan WHERE kunci=?');
        $st->execute([$kunci]);
        $r = $st->fetch();
        return $r ? $r['nilai'] : $default;
    }

    public function semuaParameter(): array
    {
        return $this->db->query('SELECT * FROM pengaturan ORDER BY kunci')->fetchAll();
    }

    public function set(string $kunci, string $nilai): void
    {
        $st = $this->db->prepare(
            'INSERT INTO pengaturan (kunci, nilai) VALUES (?,?)
             ON DUPLICATE KEY UPDATE nilai=VALUES(nilai)'
        );
        $st->execute([$kunci, $nilai]);
    }

    public function profil()
    {
        return $this->db->query('SELECT * FROM profil_koperasi WHERE id=1')->fetch()
            ?: ['id' => 1, 'nama_koperasi' => 'Koperasi Syariah'];
    }

    public function updateProfil(array $d): void
    {
        $this->db->prepare(
            'UPDATE profil_koperasi SET nama_koperasi=?, alamat=?, no_telp=?, email=?, nama_ketua=?, slogan=? WHERE id=1'
        )->execute([
            $d['nama_koperasi'], $d['alamat'], $d['no_telp'],
            $d['email'], $d['nama_ketua'], $d['slogan'],
        ]);
    }

    public function akadSemua(): array
    {
        return $this->db->query('SELECT * FROM jenis_akad ORDER BY id_akad')->fetchAll();
    }

    public function opsiAkad(): array
    {
        return $this->db->query("SELECT * FROM jenis_akad WHERE status='aktif' ORDER BY nama_akad")->fetchAll();
    }

    public function akadSimpan(array $d): void
    {
        if (!empty($d['id_akad'])) {
            $this->db->prepare(
                'UPDATE jenis_akad SET kode_akad=?, nama_akad=?, tipe_akad=?, keterangan=?, status=? WHERE id_akad=?'
            )->execute([$d['kode_akad'], $d['nama_akad'], $d['tipe_akad'], $d['keterangan'], $d['status'], $d['id_akad']]);
        } else {
            $this->db->prepare(
                'INSERT INTO jenis_akad (kode_akad, nama_akad, tipe_akad, keterangan, status) VALUES (?,?,?,?,?)'
            )->execute([$d['kode_akad'], $d['nama_akad'], $d['tipe_akad'], $d['keterangan'], $d['status']]);
        }
    }

    public function akadHapus(int $id): void
    {
        $this->db->prepare('DELETE FROM jenis_akad WHERE id_akad=?')->execute([$id]);
    }

    public function userSemua(): array
    {
        return $this->db->query('SELECT * FROM users ORDER BY id_user')->fetchAll();
    }

    public function userSimpan(array $d): void
    {
        if (!empty($d['id_user'])) {
            $this->db->prepare(
                'UPDATE users SET username=?, nama_lengkap=?, level=?, status=? WHERE id_user=?'
            )->execute([$d['username'], $d['nama_lengkap'], $d['level'], $d['status'], $d['id_user']]);
            if (!empty($d['password'])) $this->userGantiPassword((int)$d['id_user'], $d['password']);
        } else {
            $this->db->prepare(
                'INSERT INTO users (username, password, nama_lengkap, level, status) VALUES (?,?,?,?,?)'
            )->execute([
                $d['username'],
                password_hash($d['password'], PASSWORD_DEFAULT),
                $d['nama_lengkap'], $d['level'], $d['status'],
            ]);
        }
    }

    public function userGantiPassword(int $id, string $plain): void
    {
        $this->db->prepare('UPDATE users SET password=? WHERE id_user=?')
                 ->execute([password_hash($plain, PASSWORD_DEFAULT), $id]);
    }

    public function userHapus(int $id): void
    {
        $this->db->prepare('DELETE FROM users WHERE id_user=? AND id_user<>?')
                 ->execute([$id, $_SESSION['user']['id_user'] ?? 0]);
    }
}