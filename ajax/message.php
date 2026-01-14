<?php
session_start();
include("../settings/connect_datebase.php");

function decryptAES($encryptedData, $key){
    if (empty($encryptedData)) {
        return false;
    }
    
    $data = base64_decode($encryptedData);

    if ($data === false || strlen($data) < 17){
        return false;
    }

    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    
    $keyHash = md5($key);
    $keyBytes = hex2bin($keyHash);

    $decrypted = openssl_decrypt(
        $encrypted,
        'aes-128-cbc',
        $keyBytes,
        OPENSSL_RAW_DATA,
        $iv
    );

    return $decrypted;
}


if (!isset($_SESSION['user'])) {
    echo "error";
    exit;
}

$IdUser = $_SESSION['user'];

$encryptedMessage = $_POST["Message"] ?? '';
$IdPost = $_POST["IdPost"] ?? 0;

$secretKey = "qazxswedcvfrtgbn";

$Message = decryptAES($encryptedMessage, $secretKey);


if ($Message === false) {
    echo "error_decrypt";
    exit;
}


$IdUser = intval($IdUser);
$IdPost = intval($IdPost);
$Message = $mysqli->real_escape_string($Message);


$mysqli->query("INSERT INTO `comments`(`IdUser`, `IdPost`, `Messages`) VALUES ({$IdUser}, {$IdPost}, '{$Message}');");

echo "success";
?>