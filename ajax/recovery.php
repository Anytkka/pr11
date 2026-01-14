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

function PasswordGeneration() {
    // создаём пароль
    $chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
    $max=10;
    $size=StrLen($chars)-1;
    $password="";
    
    while($max--) {
        $password.=$chars[rand(0,$size)];
    }
    
    return $password;
}

// Получаем зашифрованные данные
$login_encrypted = $_POST['login'] ?? '';

$secretKey = "qazxswedcvfrtgbn";

// Дешифруем
$login = decryptAES($login_encrypted, $secretKey);

// Проверяем успешность дешифровки
if ($login === false) {
    echo "-1";
    exit;
}

// Защита от SQL-инъекций
$login = $mysqli->real_escape_string($login);

// ищем пользователя
$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$login."';");
$id = -1;

if($user_read = $query_user->fetch_row()) {
    // создаём новый пароль
    $id = $user_read[0];
    
    if($id > 0) {
        // генерируем новый пароль
        $password = PasswordGeneration();
        
        // проверяем не используется ли пароль 
        $query_password = $mysqli->query("SELECT * FROM `users` WHERE `password`= '".md5($password)."';");
        while($password_read = $query_password->fetch_row()) {
            // создаём новый пароль, если текущий уже используется
            $password = PasswordGeneration();
        }
        
        // обновляем пароль
        $mysqli->query("UPDATE `users` SET `password`='".md5($password)."' WHERE `login` = '".$login."'");
        
        // Отправляем письмо (раскомментировать для реального использования)
        /*
        $to = $login;
        $subject = 'Безопасность web-приложений КГАПОУ "Авиатехникум"';
        $message = "Ваш пароль был изменён. Новый пароль: " . $password;
        $headers = 'From: webmaster@example.com' . "\r\n" .
                   'Reply-To: webmaster@example.com' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        
        mail($to, $subject, $message, $headers);
        */
        
        // Для отладки можно вывести пароль в лог
        error_log("Новый пароль для $login: $password");
    }
}

echo $id;
?>