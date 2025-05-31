# Sistem Manajemen Toko Sembako Supri

## Deskripsi
Sistem Manajemen Toko Sembako Supri adalah aplikasi web yang dirancang untuk membantu pengelolaan toko sembako secara digital. Sistem ini mencakup manajemen penjualan, pembelian, stok, piutang, dan laporan keuangan.

## Fitur Utama

### 1. Dashboard
- Tampilan ringkas penjualan hari ini
- Grafik penjualan dan pembelian
- Statistik stok produk
- Informasi piutang

### 2. Manajemen Produk
- Daftar produk dengan detail lengkap
- Manajemen stok otomatis
- Pencatatan harga beli dan jual
- Kategori produk
- **Kategori produk otomatis**: Sistem akan mengisi kategori produk secara otomatis berdasarkan nama produk menggunakan kata kunci umum toko kelontong/sembako. Contoh mapping otomatis:
  - **Minuman**: aqua, minum, galon, mineral, teh, kopi, susu, fanta, sprite, coca, pocari, mizone, ultramilk, yakult, sariwangi, goodday, marimas, nutrisari
  - **Sembako**: beras, gula, tepung, mie, telur, kecap, minyak, garam, sagu, jagung, roti, margarin, mentega, sarden, abon, saos, sambal
  - **Makanan Ringan**: snack, ciki, chiki, wafer, biskuit, keripik, kacang, coklat, permen, astor, oreo, tango, roma, nabati, bengbeng, taro, lays, doritos
  - **Kebutuhan Rumah Tangga**: sabun, shampo, shampoo, deterjen, sunlight, pel, sapu, ember, gayung, sikat, pewangi, pelicin, pembersih, wipol, so klin, rinso, molto, baygon, kapur barus
  - **Perawatan Tubuh**: pasta, gigi, odol, sikat gigi, deodorant, lotion, minyak angin, balsem, minyak kayu putih, minyak telon, bedak, tissue, cotton bud, kapas
  - **Rokok**: rokok, sampoerna, gudang garam, djarum, surya, filter, mild, marlboro, LA, magnum
  - **Gas & Energi**: gas, elpiji, lpg, minyak tanah, solar, bensin
  - **Alat Tulis**: pensil, pulpen, buku, penghapus, spidol, penggaris, tip-ex, lem, kertas, map, amplop
  - **Elektronik Kecil**: baterai, lampu, charger, kabel, colokan, stop kontak
  - **Lainnya**: fallback jika tidak cocok dengan kategori di atas

### 3. Penjualan
- Transaksi penjualan cepat
- Pencetakan struk
- Riwayat penjualan
- Detail transaksi

### 4. Pembelian
- Pencatatan pembelian dari supplier
- Manajemen supplier
- Riwayat pembelian
- Update stok otomatis

### 5. Piutang
- Pencatatan piutang pelanggan
- Status pembayaran
- Jatuh tempo
- Pencetakan bukti piutang

### 6. Akuntan
- **Jurnal Umum**
  - Pencatatan otomatis setiap transaksi (penjualan, pembelian, piutang)
  - Deskripsi jurnal menampilkan nama supplier/customer
  - Format tanggal dan waktu sesuai timezone Asia/Jakarta
  - Pencatatan debit dan kredit otomatis
  - Riwayat transaksi lengkap

- **Buku Besar**
  - Pencatatan otomatis dari jurnal umum
  - Saldo per akun
  - Riwayat mutasi per akun
  - Format tanggal dan waktu sesuai timezone Asia/Jakarta
  - Pencatatan debit dan kredit otomatis

### 7. Laporan
- Laporan penjualan
- Laporan pembelian
- Laporan stok
- Laporan laba rugi
- Laporan arus kas

## Perhitungan Keuangan

### Laba Kotor
Sistem menghitung laba kotor dengan rumus:
```
Laba Kotor = Total Penjualan - Harga Pokok Penjualan (HPP)

Dimana:
HPP = (Persediaan Awal + Pembelian) - Persediaan Akhir
```

Komponen perhitungan:
1. **Total Penjualan**
   - Jumlah semua penjualan tunai
   - Jumlah semua penjualan kredit (piutang)
   - Tidak termasuk PPN

2. **Harga Pokok Penjualan (HPP)**
   - **Persediaan Awal**: Stok awal periode
   - **Pembelian**: Total pembelian dari supplier
   - **Persediaan Akhir**: Stok akhir periode

3. **Laba Kotor**
   - Menunjukkan keuntungan sebelum biaya operasional
   - Digunakan untuk menutup biaya operasional
   - Sisa setelah biaya operasional adalah laba bersih

### Arus Kas
Perhitungan arus kas meliputi:

1. **Pemasukan (Cash In)**
   - Penjualan tunai
   - Pembayaran piutang

2. **Pengeluaran (Cash Out)**
   - Pembelian barang

3. **Saldo Kas**
   - Saldo awal periode
   - Total pemasukan
   - Total pengeluaran
   - Saldo akhir periode

## Teknologi yang Digunakan
- PHP 7.4+
- MySQL 5.7+
- Bootstrap 5
- jQuery
- Chart.js untuk grafik
- DataTables untuk tabel interaktif

## Instalasi

### Persyaratan Sistem
- Web server (Apache/Nginx)
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Ekstensi PHP yang diperlukan:
  * PDO
  * MySQLi
  * JSON
  * GD

### Langkah Instalasi
1. Clone repository ke server
2. Import database dari file `database/db-supri.sql`
3. Konfigurasi koneksi database di `config/Database.php`
4. Pastikan folder memiliki permission yang tepat
5. Akses aplikasi melalui browser

## Penggunaan

### Login
- Username: kasir
- Password: kasir123

### Panduan Penggunaan
1. **Dashboard**
   - Lihat ringkasan bisnis
   - Monitor penjualan harian
   - Cek status stok

2. **Penjualan**
   - Pilih produk
   - Masukkan jumlah
   - Proses pembayaran
   - Cetak struk

3. **Pembelian**
   - Pilih supplier
   - Input produk dan jumlah
   - Konfirmasi pembelian
   - Update stok otomatis

4. **Piutang**
   - Catat piutang baru
   - Update status pembayaran
   - Cetak bukti piutang
   - Monitor jatuh tempo

5. **Akuntan**
   - Lihat jurnal umum
   - Lihat buku besar

6. **Laporan**
   - Pilih periode laporan
   - Export ke PDF/Excel
   - Analisis data penjualan
   - Evaluasi kinerja

## Keamanan
- Login dengan autentikasi
- Manajemen hak akses
- Enkripsi password
- Validasi input
- Proteksi SQL injection

## Backup & Pemulihan
- Backup database otomatis
- Export data manual
- Pemulihan sistem

## Lisensi
Hak Cipta © 2024 Toko Sembako Supri 