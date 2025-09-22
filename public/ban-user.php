<?php

require_once __DIR__ . "/../scripts/init.php";
require_once __DIR__ . "/../scripts/func.php";
require_once __DIR__ . "/../scripts/db-func.php";
require_once __DIR__ . "/../scripts/email-func.php";

// Initialize result
$result = [];

$token = $_GET["token"] ?? null;
if (!$token) {
  header("Location: ./404.php");
  return;
}

// Make database connection
$conn = makeConn();

// Get user
$user = getImposter($conn, "token", $token, "s");
if (!$user) {
  header("Location: ./404.php");
  return;
}

$info = [
  "userID" => $user["user_id"]
];

// Get imposter
$imposter = getImposter($conn, $info);

// Ban imposter
$info["ipAddress"] = $imposter["ip_address"];
addBan($conn, $info, "perma_bans");

// Update user token
$info["token"] = getUUID();
updateToken($conn, "users", $info);

// Email password reset
sendEmail($user, "resetPassword");
$result["status"] = "success";

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./css/defaults/main.css">
  <link rel="stylesheet" href="./css/defaults/nav.css">
  <link rel="stylesheet" href="./css/index.css">
  <title>Document</title>
</head>

<body>

  <?php if ($result["status"] === "success"): ?>
    <h1>
      Imposter successfully banned.<br>
      Check your email, we've sent you a password reset link.
    </h1>
  <?php endif; ?>

</body>

</html>