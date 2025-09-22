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
  <link rel="stylesheet" href="./css/signup.css">
  <title>Document</title>
</head>

<body>

  <span><a href="/">🠄 Back home</a></span>

  <form id="signup-form" novalidate>
    <h1>Sign Up</h1>
    <div class="separator"></div>
    <div id="usernames">
      <label for="username">Username</label>
      <div class="relative">
        <input
          type="text"
          id="username"
          name="username"
          placeholder="Choose a username..."
          oninput="this.value = this.value.toLowerCase().replace(' ', '')">
      </div>
    </div>
    <div id="emails">
      <label for="email">Email</label>
      <div class="relative">
        <input
          type="email"
          id="email"
          name="email"
          placeholder="Your email address..."
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
              placeholder="Choose a password..."
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
    <button type="button" id="signup">Sign Up</button>
    <div id="other">
      <p>Already a member? <a href="./login.php">Login</a> instead</p>
    </div>
  </form>

  <script src="./js/signup.js" type="module"></script>

</body>

</html>