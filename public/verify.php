<?php

require_once __DIR__ . "/../scripts/init.php";
require_once __DIR__ . "/../scripts/func.php";
require_once __DIR__ . "/../scripts/db-func.php";
require_once __DIR__ . "/../scripts/email-func.php";

// Initialize result
$result = [];

// Get token
$token = $_GET["token"] ?? null;
if (!$token) {
  header("Location: /404.php");
  exit;
}

// Get user
$userQuery = "
  SELECT *
  FROM users
  WHERE token = ?
";
$userParams = [
  "s",
  $token
];
[$user] = sqlQuery($userQuery, $userParams);

if (!$user) {
  header("Location: /404.php");
  exit;
}

// Check if token expired
if (new DateTime() > new DateTime($user['token_expires_at'])) {
  $result["status"] = "expired";
  $newToken = genUUID();
  $updateTokenQuery = "
    UPDATE users
    SET token = ?, token_expires_at = NOW() + INTERVAL 60 MINUTE
    WHERE user_id = ?
  ";
  $updateTokenParams = [
    "ss",
    $newToken,
    $user["user_id"]
  ];
  sqlQuery($updateTokenQuery, $updateTokenParams);
  $user["token"] = $newToken;
  sendEmail($user, "verification");
} else {
  // Update user
  $updateVerifiedQuery = "
    UPDATE users
    SET is_verified = ?, token = NULL, token_expires_at = NULL
    WHERE user_id = ?
  ";
  $updateVerifiedParams = [
    "ss",
    1,
    $user["user_id"]
  ];
  sqlQuery($updateVerifiedQuery, $updateVerifiedParams);
  $result["status"] = "success";
}

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

  <?php if ($result["status"] === "expired"): ?>
    <h1>Sorry, this link has expired. We've emailed you a new one!</h1>
  <?php elseif ($result["status"] === "success"): ?>
    <h1>Email successfully verified!</h1>
  <?php endif; ?>

</body>

</html>