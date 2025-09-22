<?php

require_once __DIR__ . "/../scripts/init.php";
require_once __DIR__ . "/../scripts/func.php";

// Check session and redirect
$loggedIn = $_SESSION["userID"] ?? null;
if ($loggedIn) {
  header("Location: ./dashboard.php");
  exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./css/defaults/main.css">
  <link rel="stylesheet" href="./css/login.css">
  <title>Document</title>
</head>

<body>

  <span><a href="/">🠄 Back home</a></span>

  <form id="login-form" novalidate>
    <h1>Login</h1>
    <div class="separator"></div>
    <div id="emailUsernames">
      <label for="emailUsername">Email or Username</label>
      <div class="relative">
        <input
          type="text"
          id="emailUsername"
          name="emailUsername"
          placeholder="Enter your email or username"
          oninput="this.value = this.value.toLowerCase().replace(' ', '')">
      </div>
    </div>
    <div id="passwords">
      <label for="password">Password</label>
      <div id="password-wrapper">
        <div id="password-inputs">
          <div class="relative">
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Enter your password...">
          </div>
          <button type="button" id="show-password">
            <img src="./img/show-password.svg" alt="Show Password">
          </button>
        </div>
      </div>
    </div>
    <button type="button" id="login">Login</button>
    <div id="other">
      <p>Not a member yet? <a href="./signup.php">Signup</a> instead</p>
      <p><a id="forgot-password">Forgot password?</a></p>
    </div>
  </form>

<script src="./js/login.js" type="module"></script>

</body>

</html>