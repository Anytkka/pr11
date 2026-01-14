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


$login_encrypted = $_POST['login'] ?? '';
$password_encrypted = $_POST['password'] ?? '';

$secretKey = "qazxswedcvfrtgbn";

$login = decryptAES($login_encrypted, $secretKey);
$password = decryptAES($password_encrypted, $secretKey);


if ($login === false || $password === false) {
    echo "-1";
    exit;
}


$login = $mysqli->real_escape_string($login);
$password = $mysqli->real_escape_string($password);


$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$login."'");
$id = -1;

if($query_user->num_rows > 0) {
    echo "-1";
} else {
    
    $mysqli->query("INSERT INTO `users`(`login`, `password`, `roll`) VALUES ('".$login."', '".$password."', 0)");
    
    
    $query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$login."' AND `password`= '".$password."';");
    
    if($user_new = $query_user->fetch_row()) {
        $id = $user_new[0];
        $_SESSION['user'] = $id; 
    }
    echo $id;
}
?>