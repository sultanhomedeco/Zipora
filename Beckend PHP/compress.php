<?php
// Konfigurasi DB
$host = "localhost";
$user = "root";
$pass = "";
$db = "zipora";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Koneksi gagal: ".$conn->connect_error);

if (isset($_FILES['image'])) {
    $fileTmp = $_FILES['image']['tmp_name'];
    $filenameOriginal = "uploads/" . time() . "_" . basename($_FILES['image']['name']);
    move_uploaded_file($fileTmp, $filenameOriginal);

    $imageInfo = getimagesize($filenameOriginal);
    $mime = $imageInfo['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($filenameOriginal);
            break;
        case 'image/png':
            $image = imagecreatefrompng($filenameOriginal);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($filenameOriginal);
            break;
        default:
            http_response_code(400);
            echo "Format tidak didukung";
            exit;
    }

    // Simpan hasil kompresi
    $filenameCompressed = "compressed/" . time() . "_compressed.jpg";
    imagejpeg($image, $filenameCompressed, 60); // kualitas 60
    imagedestroy($image);

    // Insert ke DB
    $stmt = $conn->prepare(
        "INSERT INTO images (filename_original, filename_compressed) VALUES (?,?)"
    );
    $stmt->bind_param('ss', $filenameOriginal, $filenameCompressed);
    $stmt->execute();

    header('Content-Type: image/jpeg');
    readfile($filenameCompressed);
} else {
    http_response_code(400);
    echo "No image uploaded.";
}
