<?php
$host = "localhost";
$username = "root";
$password = ""; // Ganti dengan password database Anda yang sebenarnya
$database = "db_hotel";

$conn = new mysqli($host, $username, $password, $database);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Set karakter set ke UTF-8 untuk menghindari masalah encoding
$conn->set_charset("utf8");
?>