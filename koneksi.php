<?php
$servername = "localhost:3306";
$username = "root";
$password = "";
$database = "dw_adventureworks"; // Nama database yang benar

// Membuat koneksi
$conn = mysqli_connect($servername, $username, $password, $database);

// Mengecek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
