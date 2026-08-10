-- ============================================================
-- DATABASE KOPERASI SYARIAH — VERSI LENGKAP (FINAL)
-- PERHATIAN: skrip ini MENGHAPUS database lama beserta isinya!
-- ============================================================

-- ---------------- USERS ----------------
CREATE TABLE users (
    id_user       INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    nama_lengkap  VARCHAR(100) NOT NULL,
    level         ENUM('admin','manager','kasir') NOT NULL DEFAULT 'kasir',
    status        ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Login default => admin / admin123 (segera ganti!)
INSERT INTO users (username, password, nama_lengkap, level) VALUES
('admin', 'admin123', 'Administrator Koperasi', 'admin');

-- ---------------- ANGGOTA ----------------
CREATE TABLE anggota (
    id_anggota     INT AUTO_INCREMENT PRIMARY KEY,
    kode_anggota   VARCHAR(20) NOT NULL UNIQUE,
    nik            VARCHAR(20) DEFAULT NULL,
    nama           VARCHAR(100) NOT NULL,
    jenis_kelamin  ENUM('L','P') NOT NULL DEFAULT 'L',
    tempat_lahir   VARCHAR(50) DEFAULT NULL,
    tanggal_lahir  DATE DEFAULT NULL,
    alamat         TEXT,
    no_hp          VARCHAR(20) DEFAULT NULL,
    email          VARCHAR(100) DEFAULT NULL,
    pekerjaan      VARCHAR(100) DEFAULT NULL,
    status_anggota ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    tanggal_daftar DATE NOT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------- SIMPANAN ----------------
CREATE TABLE simpanan (
    id_simpanan      INT AUTO_INCREMENT PRIMARY KEY,
    id_anggota       INT NOT NULL,
    jenis_simpanan   ENUM('pokok','wajib','sukarela') NOT NULL,
    tipe             ENUM('setor','tarik') NOT NULL DEFAULT 'setor',
    tanggal          DATE NOT NULL,
    jumlah           DECIMAL(15,2) NOT NULL,
    keterangan       VARCHAR(255) DEFAULT NULL,
    id_user          INT DEFAULT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_simpanan_anggota FOREIGN KEY (id_anggota)
        REFERENCES anggota(id_anggota) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------- JENIS AKAD ----------------
CREATE TABLE jenis_akad (
    id_akad    INT AUTO_INCREMENT PRIMARY KEY,
    kode_akad  VARCHAR(10) NOT NULL UNIQUE,
    nama_akad  VARCHAR(100) NOT NULL,
    tipe_akad  ENUM('margin','bagihasil','sosial') NOT NULL DEFAULT 'margin',
    keterangan VARCHAR(255) DEFAULT NULL,
    status     ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB;

INSERT INTO jenis_akad (kode_akad, nama_akad, tipe_akad, keterangan) VALUES
('MRB', 'Murabahah',   'margin',     'Jual beli dengan margin keuntungan yang disepakati'),
('MDR', 'Mudharabah',  'bagihasil',  'Bagi hasil antara pemilik dana dan pengelola'),
('MSY', 'Musyarakah',  'bagihasil',  'Kerja sama usaha dengan kontribusi modal bersama'),
('IJR', 'Ijarah',      'margin',     'Sewa menyewa dengan ujrah (upah) yang disepakati'),
('QRD', 'Qardh',       'sosial',     'Pinjaman kebajikan tanpa imbalan');

-- ---------------- PENGATURAN ----------------
CREATE TABLE pengaturan (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    kunci      VARCHAR(50) NOT NULL UNIQUE,
    nilai      TEXT,
    keterangan VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

INSERT INTO pengaturan (kunci, nilai, keterangan) VALUES
('simpanan_pokok',  '100000', 'Nominal simpanan pokok (sekali saat mendaftar)'),
('simpanan_wajib',  '25000',  'Nominal simpanan wajib per bulan'),
('margin_default',  '12',     'Margin default per tahun (%) untuk akad murabahah/ijarah'),
('nisbah_default',  '60',     'Nisbah koperasi default (%) untuk akad bagi hasil'),
('denda_harian',    '2000',   'Denda ta''zir per hari keterlambatan (Rp) — mode nominal'),
('denda_jenis',     'nominal','Jenis denda: nominal (Rp/hari) atau persen (%/hari dari tagihan)'),
('denda_persen',    '0.5',    'Denda persen per hari dari tagihan angsuran (%)'),
('notif_hari',      '7',      'Notifikasi angsuran H-sekian sebelum jatuh tempo'),
('shu_cadangan',    '25',     'Alokasi SHU: Dana Cadangan (%)'),
('shu_jasa_modal',  '20',     'Alokasi SHU: Jasa Modal Anggota (%)'),
('shu_jasa_usaha',  '20',     'Alokasi SHU: Jasa Usaha Anggota (%)'),
('shu_pengurus',    '20',     'Alokasi SHU: Jasa Pengurus & Pengelola (%)'),
('shu_sosial',      '10',     'Alokasi SHU: Dana Sosial (%)'),
('shu_pembangunan', '5',      'Alokasi SHU: Dana Pembangunan & Lingkungan (%)'),
('wa_gateway',      'https://api.fonnte.com/send', 'URL gateway WhatsApp (Fonnte/Wablas/dll)'),
('wa_api_key',      '',       'API key / token gateway WhatsApp'),
('email_pengirim',  'koperasi@example.com', 'Alamat email pengirim notifikasi');

-- ---------------- PROFIL KOPERASI ----------------
CREATE TABLE profil_koperasi (
    id            INT PRIMARY KEY DEFAULT 1,
    nama_koperasi VARCHAR(150) NOT NULL,
    alamat        TEXT,
    no_telp       VARCHAR(30),
    email         VARCHAR(100),
    nama_ketua    VARCHAR(100),
    slogan        VARCHAR(150)
) ENGINE=InnoDB;

INSERT INTO profil_koperasi VALUES
(1, 'Koperasi Syariah Amanah Ummah', 'Jl. Merdeka No. 17, Bandung', '022-1234567',
 'info@ksu-amanah.co.id', 'H. Ahmad Fauzi', 'Berkah Bersama, Tumbuh Bersama');

-- ---------------- PEMBIAYAAN ----------------
CREATE TABLE pembiayaan (
    id_pembiayaan       INT AUTO_INCREMENT PRIMARY KEY,
    no_pembiayaan       VARCHAR(30) NOT NULL UNIQUE,
    id_anggota          INT NOT NULL,
    id_akad             INT NOT NULL,
    tanggal_pengajuan   DATE NOT NULL,
    tanggal_akad        DATE DEFAULT NULL,
    jumlah_pembiayaan   DECIMAL(15,2) NOT NULL,
    margin_persen       DECIMAL(6,2) NOT NULL DEFAULT 0,
    nisbah_koperasi     DECIMAL(5,2) NOT NULL DEFAULT 0,
    margin_nominal      DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_piutang       DECIMAL(15,2) NOT NULL DEFAULT 0,
    jangka_waktu        SMALLINT NOT NULL,
    tanggal_jatuh_tempo DATE DEFAULT NULL,
    status              ENUM('pengajuan','berjalan','ditolak','lunas') NOT NULL DEFAULT 'pengajuan',
    catatan             TEXT,
    id_user             INT DEFAULT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pembiayaan_anggota FOREIGN KEY (id_anggota)
        REFERENCES anggota(id_anggota) ON DELETE CASCADE,
    CONSTRAINT fk_pembiayaan_akad FOREIGN KEY (id_akad)
        REFERENCES jenis_akad(id_akad)
) ENGINE=InnoDB;

-- ---------------- ANGSURAN ----------------
CREATE TABLE angsuran (
    id_angsuran         INT AUTO_INCREMENT PRIMARY KEY,
    id_pembiayaan       INT NOT NULL,
    angsuran_ke         SMALLINT NOT NULL,
    tanggal_jatuh_tempo DATE NOT NULL,
    pokok               DECIMAL(15,2) NOT NULL,
    margin              DECIMAL(15,2) NOT NULL DEFAULT 0,
    total               DECIMAL(15,2) NOT NULL,
    tanggal_bayar       DATE DEFAULT NULL,
    jumlah_bayar        DECIMAL(15,2) NOT NULL DEFAULT 0,
    status              ENUM('belum','lunas') NOT NULL DEFAULT 'belum',
    CONSTRAINT fk_angsuran_pembiayaan FOREIGN KEY (id_pembiayaan)
        REFERENCES pembiayaan(id_pembiayaan) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------- AKUN (CHART OF ACCOUNTS) ----------------
CREATE TABLE akun (
    id_akun      INT AUTO_INCREMENT PRIMARY KEY,
    kode_akun    VARCHAR(10) NOT NULL UNIQUE,
    nama_akun    VARCHAR(100) NOT NULL,
    tipe         ENUM('aset','kewajiban','ekuitas','pendapatan','beban') NOT NULL,
    saldo_normal ENUM('debit','kredit') NOT NULL
) ENGINE=InnoDB;

INSERT INTO akun (kode_akun, nama_akun, tipe, saldo_normal) VALUES
('101','Kas','aset','debit'),
('102','Bank','aset','debit'),
('110','Piutang Pembiayaan Murabahah/Ijarah','aset','debit'),
('111','Piutang Pembiayaan Qardh','aset','debit'),
('112','Piutang Pembiayaan Bagi Hasil','aset','debit'),
('120','Aset Tetap','aset','debit'),
('201','Simpanan Sukarela Anggota','kewajiban','kredit'),
('210','Hutang Lainnya','kewajiban','kredit'),
('301','Simpanan Pokok','ekuitas','kredit'),
('302','Simpanan Wajib','ekuitas','kredit'),
('303','SHU Tahun Berjalan','ekuitas','kredit'),
('304','Dana Cadangan','ekuitas','kredit'),
('305','Dana Sosial / Kebajikan','ekuitas','kredit'),
('401','Pendapatan Margin Murabahah','pendapatan','kredit'),
('402','Pendapatan Bagi Hasil','pendapatan','kredit'),
('403','Pendapatan Ujrah (Ijarah)','pendapatan','kredit'),
('410','Pendapatan Lainnya','pendapatan','kredit'),
('501','Beban Bagi Hasil Simpanan','beban','debit'),
('502','Beban Operasional','beban','debit'),
('503','Beban Administrasi','beban','debit'),
('509','Beban Lainnya','beban','debit');

-- ---------------- JURNAL ----------------
CREATE TABLE jurnal (
    id_jurnal   INT AUTO_INCREMENT PRIMARY KEY,
    no_jurnal   VARCHAR(30) NOT NULL UNIQUE,
    tanggal     DATE NOT NULL,
    keterangan  VARCHAR(255) DEFAULT NULL,
    referensi   VARCHAR(50) DEFAULT NULL,
    sumber      ENUM('manual','simpanan','pembiayaan','angsuran','denda','shu','saldo_awal')
                NOT NULL DEFAULT 'manual',
    id_user     INT DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE jurnal_detail (
    id_detail   INT AUTO_INCREMENT PRIMARY KEY,
    id_jurnal   INT NOT NULL,
    id_akun     INT NOT NULL,
    posisi      ENUM('debit','kredit') NOT NULL,
    jumlah      DECIMAL(15,2) NOT NULL,
    CONSTRAINT fk_jd_jurnal FOREIGN KEY (id_jurnal)
        REFERENCES jurnal(id_jurnal) ON DELETE CASCADE,
    CONSTRAINT fk_jd_akun FOREIGN KEY (id_akun)
        REFERENCES akun(id_akun)
) ENGINE=InnoDB;

-- ---------------- DENDA TA'ZIR ----------------
CREATE TABLE denda (
    id_denda       INT AUTO_INCREMENT PRIMARY KEY,
    id_angsuran    INT NOT NULL,
    id_pembiayaan  INT NOT NULL,
    tanggal_denda  DATE NOT NULL,
    hari_terlambat INT NOT NULL,
    jumlah_denda   DECIMAL(15,2) NOT NULL,
    status         ENUM('belum_bayar','lunas') NOT NULL DEFAULT 'belum_bayar',
    tanggal_bayar  DATE DEFAULT NULL,
    id_user        INT DEFAULT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_denda_angsuran FOREIGN KEY (id_angsuran)
        REFERENCES angsuran(id_angsuran) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------- SHU ----------------
CREATE TABLE shu (
    id_shu            INT AUTO_INCREMENT PRIMARY KEY,
    tahun             SMALLINT NOT NULL UNIQUE,
    total_pendapatan  DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_beban       DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_shu         DECIMAL(15,2) NOT NULL DEFAULT 0,
    status            ENUM('draft','ditetapkan','dibagikan') NOT NULL DEFAULT 'draft',
    tanggal_penetapan DATE DEFAULT NULL,
    catatan           TEXT,
    id_user           INT DEFAULT NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE shu_alokasi (
    id_alokasi INT AUTO_INCREMENT PRIMARY KEY,
    id_shu     INT NOT NULL,
    nama_dana  VARCHAR(100) NOT NULL,
    persen     DECIMAL(5,2) NOT NULL DEFAULT 0,
    jumlah     DECIMAL(15,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_sha_shu FOREIGN KEY (id_shu)
        REFERENCES shu(id_shu) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE shu_anggota (
    id_shu_anggota INT AUTO_INCREMENT PRIMARY KEY,
    id_shu         INT NOT NULL,
    id_anggota     INT NOT NULL,
    jasa_modal     DECIMAL(15,2) NOT NULL DEFAULT 0,
    jasa_usaha     DECIMAL(15,2) NOT NULL DEFAULT 0,
    total          DECIMAL(15,2) NOT NULL DEFAULT 0,
    status         ENUM('belum','diterima') NOT NULL DEFAULT 'belum',
    tanggal_terima DATE DEFAULT NULL,
    CONSTRAINT fk_shm_shu FOREIGN KEY (id_shu)
        REFERENCES shu(id_shu) ON DELETE CASCADE,
    CONSTRAINT fk_shm_anggota FOREIGN KEY (id_anggota)
        REFERENCES anggota(id_anggota) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------- LOG NOTIFIKASI ----------------
CREATE TABLE notifikasi_kirim (
    id_kirim      INT AUTO_INCREMENT PRIMARY KEY,
    id_angsuran   INT NOT NULL,
    kanal         ENUM('wa','email') NOT NULL,
    tujuan        VARCHAR(100) DEFAULT NULL,
    status        ENUM('terkirim','simulasi','gagal') NOT NULL DEFAULT 'simulasi',
    pesan         TEXT,
    tanggal_kirim DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------- DATA CONTOH ----------------
INSERT INTO anggota (kode_anggota, nik, nama, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, no_hp, email, pekerjaan, tanggal_daftar) VALUES
('AGT-0001', '3204010101900001', 'Budi Santoso', 'L', 'Bandung', '1990-01-01', 'Jl. Kenanga No. 3, Bandung', '081234567890', 'budi@example.com', 'Wiraswasta', '2025-01-10'),
('AGT-0002', '3204010202920002', 'Siti Aminah',  'P', 'Garut',   '1992-02-02', 'Jl. Melati No. 8, Bandung', '081298765432', 'siti@example.com', 'Guru', '2025-02-15');

INSERT INTO simpanan (id_anggota, jenis_simpanan, tipe, tanggal, jumlah, keterangan, id_user) VALUES
(1, 'pokok',   'setor', '2025-01-10', 100000, 'Simpanan pokok saat mendaftar', 1),
(1, 'wajib',   'setor', '2025-01-10', 25000,  'Simpanan wajib Januari', 1),
(2, 'pokok',   'setor', '2025-02-15', 100000, 'Simpanan pokok saat mendaftar', 1),
(2, 'sukarela','setor', '2025-03-01', 500000, 'Setoran sukarela', 1);