<?php
// Folder tempat menyimpan file asli dan terkompresi
$uploadDir = "uploads/";

// Buat folder jika belum ada
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (isset($_FILES["image"])) {
    $file = $_FILES["image"];
    $fileName = basename($file["name"]);
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    // Validasi ekstensi file
    if (!in_array($fileType, $allowedTypes)) {
        echo "Format file tidak didukung. Hanya JPG, PNG, atau GIF.";
        exit();
    }

    // Validasi ukuran file (maks 9MB)
    if ($file["size"] > 9 * 1024 * 1024) {
        echo "Ukuran file terlalu besar. Maksimum 9MB.";
        exit();
    }

    // Nama baru agar unik
    $newName = uniqid("zipora_") . "." . $fileType;
    $originalPath = $uploadDir . $newName;

    // Pindahkan file yang diupload
    if (move_uploaded_file($file["tmp_name"], $originalPath)) {

        // Path hasil kompres
        $compressedName = "compressed_" . $newName;
        $compressedPath = $uploadDir . $compressedName;

        // Kompres gambar
        if (compressImage($originalPath, $compressedPath, $fileType)) {
            echo "<h3>Gambar berhasil dikompres!</h3>";
            echo "<p>Gambar asli: " . round(filesize($originalPath)/1024, 2) . " KB</p>";
            echo "<p>Gambar kompres: " . round(filesize($compressedPath)/1024, 2) . " KB</p>";
            echo "<br><img src='$compressedPath' width='300'><br><br>";
            echo "<a href='../index.php'>← Kembali</a> | ";
            echo "<a href='$compressedPath' download>📥 Download Hasil</a>";
        } else {
            echo "Gagal melakukan kompresi gambar.";
        }

    } else {
        echo "Gagal mengunggah gambar.";
    }
} else {
    echo "Tidak ada file yang dikirim.";
}


// Fungsi untuk kompres gambar
function compressImage($source, $destination, $type) {
    switch ($type) {
        case 'jpeg':
        case 'jpg':
            $image = imagecreatefromjpeg($source);
            $result = imagejpeg($image, $destination, 60); // 60 = kualitas
            break;
        case 'png':
            $image = imagecreatefrompng($source);
            $result = imagepng($image, $destination, 6); // 0-9, 9 = kompres paling kecil
            break;
        case 'gif':
            $image = imagecreatefromgif($source);
            $result = imagegif($image, $destination);
            break;
        default:
            return false;
    }
    imagedestroy($image);
    return $result;
}
?>
