# Aplikasi Koperasi Syariah (Versi Final)

Sistem informasi koperasi syariah berbasis **PHP native (PDO) + MySQL + Bootstrap 5**.

## Fitur Lengkap
- **Dashboard** — statistik anggota, simpanan, outstanding pembiayaan, NPF.
- **Anggota** — CRUD, pencarian, detail + riwayat, **Rekening Koran** (cetak/PDF & Excel).
- **Simpanan** — pokok/wajib/sukarela, setor/tarik, validasi saldo, jurnal otomatis.
- **Pembiayaan Multi-Akad** — Murabahah, Mudharabah, Musyarakah, Ijarah, Qardh.
- **Denda Ta'zir** — nominal (Rp/hari) atau persen (%/hari), dicatat sebagai Dana Sosial.
- **Akuntansi** — Chart of Accounts, jurnal otomatis & manual, saldo awal, buku besar.
- **Laporan Keuangan** — Laba Rugi, Neraca, Arus Kas, PHU/SHU + export Excel & PDF.
- **SHU** — perhitungan tahunan, alokasi dana, pembagian ke anggota (jasa modal & jasa usaha).
- **Notifikasi** — pengingat jatuh tempo (lonceng), kirim via WhatsApp gateway / Email.
- **Logo Koperasi** — upload dari Pengaturan → Profil (sidebar, login, favicon, kop laporan).
- **Backup Otomatis** — export database + kirim lampiran ke email (cron/task scheduler), riwayat di tabel backup_log.
- **Pengaturan** — profil, parameter, jenis akad, akun user (khusus admin).

## Instalasi (XAMPP)
1. Salin folder ke `C:\xampp\htdocs\Koperasi`.
2. Import `database_full.sql` via phpMyAdmin (menghapus & membuat ulang database).
   - Untuk ByetHost: hapus baris DROP/CREATE/USE, lalu import ke database hosting Anda.
3. Sesuaikan `config/database.php`.
4. Akses `http://localhost/Koperasi/` → login **admin / admin123** → ganti password.

## Konfigurasi Penting (Pengaturan → Parameter)
| Parameter | Fungsi |
|---|---|
| `denda_jenis` / `denda_harian` / `denda_persen` | Mode & tarif denda ta'zir |
| `notif_hari` | Notifikasi H-sekian sebelum jatuh tempo |
| `shu_*` | Persentase alokasi SHU |
| `wa_gateway` / `wa_api_key` | Gateway WhatsApp (Fonnte/Wablas) |
| `email_pengirim` | Alamat email pengirim notifikasi & backup |
| `logo` | Nama file logo (otomatis saat upload) |
| `backup_email` / `backup_token` | Tujuan & keamanan backup otomatis |

## Backup Otomatis
- Uji manual: buka `cron/backup_database.php?token=TOKEN-ANDA` atau tombol di **Pengaturan → Backup Database**.
- Jadwal ByetHost (Cron Job): `wget -q -O /dev/null "https://DOMAIN/cron/backup_database.php?token=TOKEN"`
- Jadwal XAMPP (Task Scheduler): program `C:\xampp\php\php.exe`, argumen `"C:\xampp\htdocs\Koperasi\cron\backup_database.php" TOKEN`
- Salinan juga disimpan di folder `backups/` (5 file terakhir, terproteksi .htaccess).

## Catatan
- Laporan keuangan terisi dari jurnal; gunakan **Saldo Awal** untuk posisi awal.
- Simpanan Pokok & Wajib = Ekuitas; Sukarela = Kewajiban; Denda = Dana Sosial (305).
- Untuk email yang lebih andal (SMTP Gmail), dapat menambahkan PHPMailer ke folder `libs/`.