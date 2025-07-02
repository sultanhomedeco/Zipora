<?php require 'config.php'; $result = $conn->query("SELECT * FROM images ORDER BY uploaded_at DESC"); ?>
<!DOCTYPE html>
<html>
<head><title>Riwayat Gambar</title></head>
<body>
<h1>Riwayat Kompresi Gambar</h1>
<table border="1" cellpadding="10">
  <tr><th>ID</th><th>Asli</th><th>Kompresi</th><th>Waktu</th></tr>
  <?php while ($row = $result->fetch_assoc()): ?>
  <tr>
    <td><?= $row['id'] ?></td>
    <td><a href="<?= $row['filename_original'] ?>" target="_blank">Lihat</a></td>
    <td><a href="<?= $row['filename_compressed'] ?>" target="_blank">Lihat</a></td>
    <td><?= $row['uploaded_at'] ?></td>
  </tr>
  <?php endwhile; ?>
</table>
<a href="index.html">Kembali</a>
</body>
</html>
