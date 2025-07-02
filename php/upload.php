<?php
// Set headers to return JSON and handle CORS (for local development)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// The destination directory for original images, relative to the project root
// We go one level up from /php to the project root, then into /uploads
$uploadDir = '../uploads/'; 
$response = [];

// Create the uploads directory if it doesn't exist
if (!is_dir($uploadDir)) {
    // Attempt to create the directory with full permissions
    if (!mkdir($uploadDir, 0777, true)) {
        $response = ['status' => 'error', 'msg' => 'Failed to create uploads directory. Please check permissions.'];
        echo json_encode($response);
        exit;
    }
}

if (isset($_FILES['image'])) {
    $file = $_FILES['image'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response = ['status' => 'error', 'msg' => 'Error during file upload: code ' . $file['error']];
        echo json_encode($response);
        exit;
    }

    $fileType = strtolower(pathinfo(basename($file["name"]), PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    // Validate file type
    if (!in_array($fileType, $allowedTypes)) {
        $response = ['status' => 'error', 'msg' => 'Invalid file type. Only JPG, PNG, GIF are allowed.'];
        echo json_encode($response);
        exit;
    }

    // Validate file size (max 9MB)
    if ($file["size"] > 9 * 1024 * 1024) {
        $response = ['status' => 'error', 'msg' => 'File size is too large. Maximum is 9MB.'];
        echo json_encode($response);
        exit;
    }

    // Create a new unique name to avoid overwriting files
    $newName = uniqid('img_', true) . '.' . $fileType;
    $destinationPath = $uploadDir . $newName;

    // Move the uploaded file
    if (move_uploaded_file($file["tmp_name"], $destinationPath)) {
        // Return the web-accessible path for the frontend (relative to index.html)
        $webPath = 'uploads/' . $newName;
        $response = ['status' => 'success', 'filename' => $webPath];
    } else {
        $response = ['status' => 'error', 'msg' => 'Failed to move uploaded file. Check server/folder permissions for the "uploads" directory.'];
    }
} else {
    $response = ['status' => 'error', 'msg' => 'No file uploaded. Please ensure the "image" field is sent.'];
}

// Send the JSON response
echo json_encode($response);
?> 