<?php

require_once __DIR__ . "/../scripts/init.php";
require_once __DIR__ . "/../scripts/func.php";
require_once __DIR__ . "/../scripts/db-func.php";

// Initialize result
$result = [];

// Check if token exists
$token = $_GET["token"];
if (!$token) {
  header("Location: ./404.php");
  exit;
}

// Get request data
$reqQuery = "
  SELECT user_id, token_expires_at
  FROM new_password
  WHERE token = ?
";
$reqParams = [
  "s",
  $token
];
[$req] = sqlQuery($reqQuery, $reqParams);

if (!$req) {
  header("Location: ./404.php");
  exit;
}

// Check if token expired
if (new DateTime() > new DateTime($req["token_expires_at"])) {
  $result["status"] = "expired";
} else {
  $_SESSION["tempUserID"] = $req["user_id"];
  $result["status"] = "success";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./css/defaults/main.css">
  <link rel="stylesheet" href="./css/defaults/nav.css">
  <link rel="stylesheet" href="./css/change-password.css">
  <title>Document</title>
</head>

<body>

  <?php if ($result["status"] === "success"): ?>
    <form id="change-password-form">
      <h1>Change Password</h1>
      <div class="separator"></div>
      <div id="passwords">
        <label for="password">New Password</label>
        <div id="password-wrapper">
          <div id="password-inputs">
            <div class="relative">
              <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your new password..."
                oninput="this.value = this.value.replace(' ', '')">
            </div>
            <button type="button" id="show-password">
              <img src="./img/show-password.svg" alt="Show Password">
            </button>
          </div>
        </div>
        <div class="separator">
          <div id="progress"></div>
        </div>
      </div>
      <div id="confirm-passwords">
        <label for="confirm-password">Confirm New Password</label>
        <div id="password-wrapper">
          <div id="password-inputs">
            <div class="relative">
              <input
                type="password"
                id="confirm-password"
                name="confirm-password"
                placeholder="Retype your new password..."
                oninput="this.value = this.value.replace(' ', '')">
            </div>
            <button type="button" id="show-password">
              <img src="./img/show-password.svg" alt="Show Password">
            </button>
          </div>
        </div>
      </div>
      <button type="button" id="change-password">Change Password</button>
    </form>
  <?php elseif ($result["status"] === "expired"): ?>
    <span><a href="<?= $_SESSION["userID"] ? "/profile.php" : "/" ?>">🠄 Back home</a></span>
    <h1>
      Sorry, that token has expired.<br>
      Please make another request.
    </h1>
  <?php endif; ?>

  <script src="./js/change-password.js" type="module"></script>

</body>

</html>