<?php

require_once __DIR__ . "/init.php";
require_once __DIR__ . "/db-func.php";

// Load mailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Send email function
function sendEmail($user, $emailTemplate) {
  // Setup mailer
  $mail = new PHPMailer(true);

  try {
    $mail->isSMTP();
    $mail->Host       = $_ENV["SMTP_HOST"];
    $mail->Username   = $_ENV["SMTP_USER"];
    $mail->Password   = $_ENV["SMTP_PASS"];
    $mail->Port = (int) $_ENV['SMTP_PORT'];
    $mail->SMTPSecure = $mail->Port === 465
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->SMTPAuth   = true;
    $mail->CharSet    = "UTF-8";
    $mail->Timeout = 10;

    $mail->setFrom($_ENV["FROM_EMAIL"], $_ENV["FROM_NAME"]);
    $mail->addAddress($user["email"], $user["username"]);

    // Content
    $mail->isHTML(true);
    switch ($emailTemplate) {
      case "verification":
        $tpl = verificationEmail($user);
        $mail->Subject  = "Verify your email";
        $mail->Body     = $tpl["body"];
        $mail->AltBody  = $tpl["altBody"];
        break;
      case "2fa":
        $tpl = twoFAEmail($user);
        $mail->Subject  = "Your 2FA Code";
        $mail->Body     = $tpl["body"];
        $mail->AltBody  = $tpl["altBody"];
        break;
      case "deletedUser":
        $tpl = deletedUserEmail($user);
        $mail->Subject  = "Deleted User";
        $mail->Body     = $tpl["body"];
        $mail->AltBody  = $tpl["altBody"];
        break;
      case "emailWarning":
        $tpl = emailWarning($user);
        $mail->Subject  = "Your Email was Changed";
        $mail->Body     = $tpl["body"];
        $mail->AltBody  = $tpl["altBody"];
        break;
      case "newEmail":
        $tpl = newEmail($user);
        $mail->Subject  = "Verify Your New Email";
        $mail->Body     = $tpl["body"];
        $mail->AltBody  = $tpl["altBody"];
        break;
      case "changePassword":
        $tpl = changePassword($user);
        $mail->Subject  = "Change Your Password";
        $mail->Body     = $tpl["body"];
        $mail->AltBody  = $tpl["altBody"];
        break;
    }

    // Send email
    $mail->send();
  } catch (Exception $e) {
    return false;
  }
}

// Verification email template
function verificationEmail($user) {
  $template = file_get_contents(__DIR__ . "/../emails/verification.html");
  $token = $user["token"];
  $replace = [
    "{{name}}" => $user["username"],
    "{{link}}" => "http://localhost/verify.php?token=$token"
  ];
  $body = strtr($template, $replace);
  $altBody = "Hi {$user['username']},\n\nVerify:\nhttp://localhost/verify.php?token=$token";
  return ["body" => $body, "altBody" => $altBody];
}

// 2fa code email template
function twoFAEmail($user) {
  $template = file_get_contents(__DIR__ . "/../emails/2fa.html");
  $code = $user["code"];
  $token = $user["token"];
  $replace = [
    "{{code}}" => $code,
    "{{link}}" => "http://localhost/ban-user.php?token=$token"
  ];
  $body = strtr($template, $replace);
  $altBody = "Hi {$user['username']},\n\nHere's your code: $code\n\nIf this wasn't you, click this link: http://localhost/ban-user.php?token=$token";
  return ["body" => $body, "altBody" => $altBody];
}

// Email warning template
function emailWarning($user) {
  $template = file_get_contents(__DIR__ . "/../emails/email-warning.html");
  $token = $user["token"];
  $replace = [
    "{{link}}" => "http://localhost/revert.php?token=$token"
  ];
  $body = strtr($template, $replace);
  $altBody = "Your email was changed,\n\nIf this was not done by you, click this link: http://localhost/revert.php?token=$token";
  return ["body" => $body, "altBody" => $altBody];
}

// New email template
function newEmail($user) {
  $template = file_get_contents(__DIR__ . "/../emails/new-email.html");
  $token = $user["token"];
  $replace = [
    "{{link}}" => "http://localhost/verify.php?token=$token"
  ];
  $body = strtr($template, $replace);
  $altBody = "Your email was changed,\n\nClick here to verify your new email: http://localhost/verify.php?token=$token";
  return ["body" => $body, "altBody" => $altBody];
}

// Reset password email template
function changePassword($user) {
  $template = file_get_contents(__DIR__ . "/../emails/change-password.html");
  $token = $user["token"];
  $replace = [
    "{{link}}" => "http://localhost/change-password.php?token=$token"
  ];
  $body = strtr($template, $replace);
  $altBody = "Hi {$user['username']},\n\nClick here to change your password: http://localhost/change-password.php?token=$token";
  return ["body" => $body, "altBody" => $altBody];
}