<?php

require_once __DIR__ . "/../scripts/init.php";
require_once __DIR__ . "/../scripts/db-func.php";

// Initialize result
$result = [];

// Get token
$token = $_GET["token"] ?? null;
if (!$token) {
  header("Location: /404.php");
  exit;
}

// Get revert token
$dataQuery = "
  SELECT *
  FROM new_email
  WHERE revert_token = ?
";
$dataParams = [
  "s",
  $token
];
[$data] = sqlQuery($dataQuery, $dataParams);

if (!$data) {
  header("Location: /404.php");
  exit;
}

// Revert user
$revertQuery = "
  UPDATE users
  SET email = ?,
    is_verified = 1
  WHERE user_id = ?
";
$revertParams = [
  "ss",
  $data["old_email"],
  $data["user_id"]
];
sqlQuery($revertQuery, $revertParams);

// Set reverted
$revertedQuery = "
  UPDATE new_email
  SET reverted = 0,
    revert_token = NULL
  WHERE revert_token = ?
";
$revertedParams = [
  "s",
  $token
];
sqlQuery($revertedQuery, $revertedParams);

$result["status"] = "success";

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="refresh" content="5;url=/dashboard.php">
  <link rel="stylesheet" href="./css/defaults/main.css">
  <link rel="stylesheet" href="./css/defaults/nav.css">
  <link rel="stylesheet" href="./css/index.css">
  <title>Document</title>
</head>

<body>

  <?php if ($result["status"] === "success"): ?>
    <h1>Email successfully reverted!</h1>
  <?php endif; ?>

</body>

</html>