<?php
// Ganti sesuai kredensial MySQL-mu
$host = "localhost";   // biasanya tetap
$user = "root";        // username default di XAMPP
$pass = "";            // password default di XAMPP (kosong)
$db   = "zipora";      // nama database-mu

// Buat koneksi
$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
} else {
    // echo "Koneksi berhasil!"; // (opsional untuk testing)
}