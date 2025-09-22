<?php

require_once __DIR__ . "/../scripts/init.php";
require_once __DIR__ . "/../scripts/func.php";
require_once __DIR__ . "/../scripts/db-func.php";

// Check session and redirect
$loggedIn = $_SESSION["userID"] ?? null;
if (!$loggedIn) {
  header("Location: ./login.php");
  exit;
}

$userQuery = "
  SELECT user_id, name, username, email, location, bio
  FROM users
  WHERE user_id = ?
";
$userParams = [
  "s",
  $_SESSION["userID"]
];
[$user] = sqlQuery($userQuery, $userParams);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./css/defaults/main.css">
  <link rel="stylesheet" href="./css/defaults/nav.css">
  <link rel="stylesheet" href="./css/profile.css">
  <title>Document</title>
</head>

<body>

  <div class="wrapper">
    <div id="profile"
    data-user="<?= htmlspecialchars(json_encode($user)) ?>">
      <div id="avi-container">
        <img src="./img/default-avi.jpg" alt="profile photo">
      </div>
      <div id="user-info">
          <h1 id="username-display"><?= htmlspecialchars($user["username"]) ?></h1>
        <?php if ($user["name"]): ?>
          <h3 id="name-display"><?= htmlspecialchars($user["name"]) ?></h3>
        <?php endif; ?>
        <?php if ($user["location"]): ?>
          <h5 id="location-display"><?= htmlspecialchars($user["location"]) ?></h5>
        <?php endif; ?>
        <?php if ($user["bio"]): ?>
          <p id="bio-display"><?= htmlspecialchars($user["bio"]) ?></p>
        <?php endif; ?>
        <button id="edit-profile">
          <img src="./img/edit.svg" alt="Edit Profile Icon">
          <span>Edit Profile</span>
        </button>
        <a id="change-password">Change Password</a>
      </div>
    </div>
  </div>

  <script src="./js/profile.js" type="module"></script>

</body>

</html>