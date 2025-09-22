<?php

require_once __DIR__ . "/../scripts/init.php";
require_once __DIR__ . "/../scripts/func.php";
require_once __DIR__ . "/../scripts/db-func.php";
require_once __DIR__ . "/../scripts/auth-func.php";

// Check session
$loggedIn = $_SESSION["userID"] ?? null;
if ($loggedIn) {
  $userQuery = "
    SELECT *
    FROM users
    WHERE user_id = ?
  ";
  $userParams = [
    "s",
    $_SESSION["userID"]
  ];
  [$user] = sqlQuery($userQuery, $userParams);
}

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

  <nav>
    <div id="logo">
      <img src="" alt="">
    </div>

    <div id="nav-items">
      <a href="" class="nav-item">Item name 1</a>
      <a href="" class="nav-item">Item name 2</a>
      <a href="" class="nav-item">Item name 3</a>
    </div>

    <?php if ($loggedIn): ?>
      <a href="./profile.php" id="profile">
        <img src="./img/default-avi.jpg" alt="" id="profile-photo">
        <p><?= htmlspecialchars($user["username"]) ?></p>
      </a>
    <?php else: ?>
      <div id="login-signup">
        <a href="./signup.php" id="signup">Signup</a>
        <a href="./login.php" id="login">Login</a>
      </div>
    <?php endif; ?>
  </nav>

  <section id="section-1"></section>
  <section id="section-2"></section>
  <section id="section-3"></section>

  <footer></footer>

</body>

</html>