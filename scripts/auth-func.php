<?php

require_once __DIR__ . "/db-func.php";

// Make database connection
$conn = makeConn();

// Load reserved usernames
$file = file_get_contents(__DIR__ . "/../json/reservedUsernames.json");
$reservedUsernames = json_decode($file);

// Profanity check
function hasProfanity($value) {
  static $censor;
  $censor ??= new \Snipe\BanBuilder\CensorWords;
  $checked = $censor->censorString($value);
  return !empty($checked['matched']);
}

// Weak password check
function weakPassword($password) {
  static $zx;
  $zx ??= new \ZxcvbnPhp\Zxcvbn();
  $checked = $zx->passwordStrength($password);
  return $checked["score"];
}

function passwordProgress($password) {
  static $zx;
  $zx ??= new \ZxcvbnPhp\Zxcvbn();
  $res = $zx->passwordStrength($password);
  $lg  = (float) $res['guesses_log10'];
  $min = 0.0;
  $max = 14.0;
  $clamped = max($min, min($max, $lg));
  return (int) round(($clamped - $min) / ($max - $min) * 100);
}

// Validation for first and last name
function validateName($name) {
  $reg = "/^$|^[a-zA-Z' -]+$/";
  if (strlen($name) > 100) {
    $msg = "Name is too long.";
  } elseif (hasProfanity($name)) {
    $msg = "Profanity found, try again.";
  } elseif (!preg_match($reg, $name)) {
    $msg = "Invalid name, try again.";
  }
  return $msg ?? null;
}

// Validation for usernames
function validateUsername($username) {
  global $reservedUsernames;
  $reg = "/^(?!.*\.\.)(?!\.)(?!.*\.$)[a-zA-Z0-9._]+$/";
  if (!$username) {
    $msg = "Please enter a username.";
  } elseif (strlen($username) < 3) {
    $msg = "Username is too short.";
  } elseif (strlen($username) > 30) {
    $msg = "Username is too long.";
  } elseif (hasProfanity($username)) {
    $msg = "Profanity found, try again.";
  } elseif (!preg_match($reg, $username)) {
    $msg = "Invalid username, try again.";
  }
  return $msg ?? null;
}

// Validation for emails
function validateEmail($email) {
  if (!$email) {
    $msg = "Please enter an email.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $msg = "Invalid email, try again.";
  }
  return $msg ?? null;
}

// Validation for passwords
function validatePassword($password) {
  if (!$password) {
    $msg = "Please enter a password.";
  } elseif (strlen($password) > 72) {
    $msg = "Sorry, your password is too long.";
  } elseif (weakPassword($password) < 3) {
    $msg = "Sorry, your password is too weak.";
  }
  return $msg ?? null;
}

// Validate ban status
function validateBanStatus($user, $ip) {
  // Check if perma banned
  $permaQuery = "
    SELECT *
    FROM perma_bans
    WHERE user_id = ? AND ip_address = ?
    ORDER BY banned_at DESC
    LIMIT 1
  ";
  $params = [
    "ss",
    $user["user_id"],
    $ip
  ];
  $permaBans = sqlQuery($permaQuery, $params);
  if (!empty($permaBans)) return "permaBan";

  // Check if temp banned
  $tempQuery = "
    SELECT *
    FROM temp_bans
    WHERE user_id = ? AND ip_address = ?
    ORDER BY banned_at DESC
    LIMIT 3
  ";
  $tempBans = sqlQuery($tempQuery, $params);
  if (!empty($tempBans)) {
    $lastBanExpiry = new DateTime($tempBans[0]["ban_until"]);
    if (count($tempBans) >= 3) {
      // Perma ban after 3 temp bans
      $addPermaQuery = "
        INSERT INTO perma_bans
        (user_id, ip_address)
        VALUES (?, ?)
      ";
      sqlQuery($addPermaQuery, $params);
      return "permaBan";
    } elseif ($lastBanExpiry > new DateTime()) {
      return "tempBan";
    }
  }

  // Check # of login attempts
  $attemptsQuery = "
    SELECT *
    FROM login_attempts
    WHERE user_id = ? 
      AND ip_address = ? 
      AND attempted_at > NOW() - INTERVAL 60 MINUTE
  ";
  $loginAttempts = sqlQuery($attemptsQuery, $params);
  
  $failedAttempts = [];
  foreach ($loginAttempts as $att) {
    if ((int)$att["success"] === 1) 
      break;
    $failedAttempts[] = $att;
  }
  
  // Temp ban if failed more than 7 times
  $limit = 7;
  if (count($failedAttempts) > $limit) {
    $addTempQuery = "
      INSERT INTO temp_bans
      (user_id, ip_address)
      VALUES (?, ?)
    ";
    sqlQuery($addTempQuery, $params);
    return "tempBan";
  } 

  // Return failed attempt streak
  return $limit - count($failedAttempts);
}