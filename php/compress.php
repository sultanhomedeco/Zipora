<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$response = [];

if (isset($_POST['filename']) && !empty($_POST['filename'])) {
    // The filename from frontend is like 'uploads/img_....jpg'
    // The script's location is /php, so we need to go up one level to find the file
    $sourcePath = '../' . $_POST['filename']; 
    
    // Check if the source file actually exists
    if (!file_exists($sourcePath)) {
        $response = ['status' => 'error', 'msg' => 'Original file not found on server. It might have failed to upload.'];
        echo json_encode($response);
        exit;
    }

    $info = pathinfo($sourcePath);
    $ext = strtolower($info['extension']);
    
    $compressedDir = '../compressed/';
    if (!is_dir($compressedDir)) {
        if (!mkdir($compressedDir, 0777, true)) {
            $response = ['status' => 'error', 'msg' => 'Failed to create compressed directory. Please check permissions.'];
            echo json_encode($response);
            exit;
        }
    }

    // Use the same name for the compressed file
    $compressedFileName = $info['basename'];
    $destinationPath = $compressedDir . $compressedFileName;

    // Compression settings
    $quality_jpeg = 40; // 0 (worst) to 100 (best)
    $quality_png = 7;   // 0 (no compression) to 9 (max compression)

    $image = null;
    try {
        // Create image resource from source
        if ($ext == 'jpg' || $ext == 'jpeg') {
            $image = imagecreatefromjpeg($sourcePath);
        } elseif ($ext == 'png') {
            $image = imagecreatefrompng($sourcePath);
        } elseif ($ext == 'gif') {
            $image = imagecreatefromgif($sourcePath);
        }

        // If image resource is created, save it with compression
        if ($image) {
            if ($ext == 'jpg' || $ext == 'jpeg') {
                imagejpeg($image, $destinationPath, $quality_jpeg);
            } elseif ($ext == 'png') {
                // For PNG, we need to preserve transparency
                imagealphablending($image, false);
                imagesavealpha($image, true);
                imagepng($image, $destinationPath, $quality_png);
            } elseif ($ext == 'gif') {
                imagegif($image, $destinationPath);
            }

            imagedestroy($image);

            // Return the web-accessible path to the frontend
            $webPath = 'compressed/' . $compressedFileName;
            $response = ['status' => 'success', 'compressed' => $webPath];
        } else {
             $response = ['status' => 'error', 'msg' => 'Unsupported format for compression.'];
        }

    } catch (Exception $e) {
        $response = ['status' => 'error', 'msg' => 'An error occurred during compression: ' . $e->getMessage()];
    }

} else {
    $response = ['status' => 'error', 'msg' => 'No filename provided for compression.'];
}

echo json_encode($response);
?> 