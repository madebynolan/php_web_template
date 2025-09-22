<?php

// Generate UUID
function genUUID() 
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// Generate 6 digit #
function gen6Digit() {
  return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Stay logged in
function stayLoggedIn($days = 365) {
    $lifetime = $days * 24 * 60 * 60;
    setcookie("PHPSESSID", session_id(), [
        'expires'  => time() + $lifetime,
    ]);
}