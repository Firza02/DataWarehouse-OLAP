📊 DataWarehouseOLAP — Business Intelligence Web Application

Sistem ini merupakan aplikasi Business Intelligence berbasis web yang memanfaatkan Data Warehouse, proses ETL, visualisasi Chart.js, serta OLAP Mondrian untuk menganalisis data AdventureWorks. Aplikasi ini menyajikan informasi seperti tren penjualan, total nilai pembelian vendor, data HR (cuti & gaji), serta pola penjualan per territory, lengkap dengan fitur eksplorasi multidimensi menggunakan drill-down dan analysis slicing melalui Mondrian JPivot.

Login as executive:

username  : Executive

passowrd  : Admin1

/css               → stylesheet dan UI styling

/js                → Chart.js & konfigurasi grafik

/images            → aset gambar untuk tampilan

/fonts/icomoon     → ikon antarmuka

/scss/bootstrap    → styling berbasis Bootstrap

/dw_adventureworks → database

index.php          → halaman utama aplikasi

home.php           → dashboard & menu navigasi

olap.php           → integrasi OLAP Mondrian (JPivot)

koneksi.php        → konfigurasi koneksi MySQL

login.php          → halaman autentikasi

logout.php         → logout session

data1.php          → pengolahan data untuk visualisasi

linechartpenjualan.php → grafik tren penjualan bulanan
