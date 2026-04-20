<?php
ini_set("session.gc_maxlifetime", 86400 * 30);
session_set_cookie_params([
    "lifetime" => 86400 * 30,
    "path" => "/",
    "secure" => false,
    "httponly" => true,
    "samesite" => "Lax"
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
setcookie(session_name(), session_id(), [
    "expires" => time() + (86400 * 30),
    "path" => "/",
    "secure" => false,
    "httponly" => true,
    "samesite" => "Lax"
]);
?>
