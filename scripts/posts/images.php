<?php

require_once __DIR__ . "/../scripts/init.php";
require_once __DIR__ . "/../scripts/db-func.php";

// Initialize result
$result = [];

// Check if logged in
$userID = $_SESSION["userID"];
if (!$userID) {
  $result["status"] = "fail";
  exit(json_encode($result));
}

// Initialize directory
$uploadDir = __DIR__ . "/../../storage/images/";
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0755, true);
}

$file = $_FILES["image"] ?? NULL;
if ($file) {
  // Check for upload errors
  if ($file["error"] !== UPLOAD_ERR_OK) {
    die("Upload failed with error code " . $file["error"]);
  }

  // Validate file type
  $allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
  if (!in_array($file["type"], $allowedTypes)) {
    die("Only JPG, PNG, GIF, and WebP images are allowed.");
  }

  // Generate safe filename
  $ext        = pathinfo($file["name"], PATHINFO_EXTENSION);
  $newName    = uniqid("img_", true) . "." . $ext;
  $targetPath = $uploadDir . $newName;

  // Move file to uploads folder
  if (move_uploaded_file($file["tmp_name"], $targetPath)) {
    $imgQuery = "
      INSERT INTO images
      (user_id, path, mime_type)
      VALUES (?, ?, ?)
    ";
    $imgParams = [
      "sss",
      $userID,
      $targetPath,
      $file["type"]
    ];
    sqlQuery($imgQuery, $imgParams);

    // Return success
    $result["status"] = "success";
    exit(json_encode($result));
  } else {
    echo "Error saving the uploaded file.";
  }
}