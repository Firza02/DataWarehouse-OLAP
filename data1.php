<?php
require 'koneksi.php';

// Contoh: ambil semua data tren penjualan bulanan dari staging
$sql1 = "SELECT Tahun, Bulan, TotalSales
         FROM stg_sales_monthly
         ORDER BY Tahun, Bulan";

$result1 = mysqli_query($conn, $sql1);

$hasil = [];

while ($row = mysqli_fetch_assoc($result1)) {
    $hasil[] = [
        "Tahun"      => (int)$row['Tahun'],
        "Bulan"      => (int)$row['Bulan'],
        "TotalSales" => (float)$row['TotalSales']
    ];
}

// Response JSON
header('Content-Type: application/json');
echo json_encode($hasil);
?>
