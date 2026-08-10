# Aplikasi Koperasi Syariah

Sistem informasi koperasi syariah berbasis **PHP native (PDO) + MySQL + Bootstrap 5**.

## Fitur Lengkap
- **Dashboard** — statistik anggota, simpanan, outstanding pembiayaan, NPF.
- **Anggota** — CRUD, pencarian, detail + riwayat.
- **Simpanan** — pokok/wajib/sukarela, setor/tarik, validasi saldo, jurnal otomatis.
- **Pembiayaan Multi-Akad** — Murabahah, Mudharabah, Musyarakah, Ijarah, Qardh.
- **Alur Pembiayaan** — pengajuan → persetujuan → jadwal angsuran otomatis → pembayaran → lunas, dengan jurnal otomatis.
- **Denda Ta'zir** — nominal (Rp/hari) atau persen (%/hari), dicatat sebagai Dana Sosial (bukan pendapatan).
- **Akuntansi** — Chart of Accounts, jurnal otomatis & manual, saldo awal, buku besar.
- **Laporan Keuangan** — Laba Rugi, Neraca, Arus Kas, PHU/SHU + export Excel & cetak PDF.
- **SHU** — perhitungan tahunan, alokasi dana, pembagian ke anggota (jasa modal & jasa usaha).
- **Notifikasi** — pengingat jatuh tempo (lonceng), kirim via WhatsApp gateway / Email.
- **Pengaturan** — profil koperasi, parameter, jenis akad, akun user (khusus admin).

## Instalasi (XAMPP)
1. Letakkan folder di `C:\xampp\htdocs\Koperasi`.
2. Import `database_full.sql` via phpMyAdmin (menghapus & membuat ulang database).
3. Sesuaikan `config/database.php` (default XAMPP: root, password kosong).
4. Akses `http://localhost/Koperasi/` → login **admin / admin123**.
5. Ganti password default di Pengaturan → Akun User.

## Konfigurasi Opsional
- **Denda persen**: Pengaturan → Parameter → `denda_jenis` = `persen`.
- **WhatsApp**: isi `wa_gateway` & `wa_api_key` (contoh: Fonnte). Tanpa API key = mode simulasi.
- **Email**: server harus mendukung fungsi `mail()` (XAMPP lokal biasanya tidak — gunakan hosting/PHPMailer).
- **PDF asli**: tambahkan DomPDF/TCPDF via Composer ke folder `libs/`.
- **Excel .xlsx asli**: tambahkan PhpSpreadsheet ke folder `libs/`.

## Catatan Penting
- Laporan keuangan terisi dari **jurnal**; transaksi lama sebelum fitur akuntansi tidak tercatat — gunakan **Saldo Awal** untuk posisi awal.
- Simpanan Pokok & Wajib = **Ekuitas**; Simpanan Sukarela = **Kewajiban**.
- Denda ta'zir = **Dana Sosial (305)**, sesuai prinsip syariah.