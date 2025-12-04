<?php
/**
 * SMTP отправка писем через PHPMailer для Яндекс Почты
 * Улучшенная версия с обработкой ошибок
 */

// Подключаем PHPMailer
require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

function send_smtp_confirmation_email($email, $username, $confirmation_link) {
    $mail = new PHPMailer(true);
    
    // Детальное логирование
    $log_message = "[" . date('Y-m-d H:i:s') . "] SMTP Attempt to: {$email}\n";
    file_put_contents(__DIR__ . '/email_log.txt', $log_message, FILE_APPEND);
    
    try {
        // ВАРИАНТ 1: Основные настройки (SSL)
        $mail->isSMTP();
        $mail->Host = 'smtp.yandex.ru';
        $mail->SMTPAuth = true;
        $mail->Username = 'market.support@yandex.ru';
        $mail->Password = 'zjhaelxipsybpwlt'; // Замените на реальный пароль
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
        $mail->Port = 465;
        
        // Важные настройки для хостинга
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->Timeout = 30; // Увеличиваем таймаут
        $mail->CharSet = 'UTF-8';
        
        // Отправитель
        $mail->setFrom('market.support@yandex.ru', 'ПлюкСудоку');
        $mail->addAddress($email, $username);
        $mail->addReplyTo('market.support@yandex.ru', 'ПлюкСудоку');
        
        // Содержание письма
        $mail->isHTML(true);
        $mail->Subject = 'Подтверждение регистрации в ПлюкСудоку';
        $mail->Body = create_email_template($username, $confirmation_link);
        $mail->AltBody = "Подтвердите регистрацию: {$confirmation_link}";
        
        $mail->send();
        
        file_put_contents(__DIR__ . '/email_log.txt', 
            "[" . date('Y-m-d H:i:s') . "] SMTP SUCCESS: Email sent via Yandex SSL\n", 
            FILE_APPEND
        );
        return true;
        
    } catch (Exception $e) {
        $error1 = $e->getMessage();
        file_put_contents(__DIR__ . '/email_log.txt', 
            "[" . date('Y-m-d H:i:s') . "] SMTP SSL Failed: {$error1}\n", 
            FILE_APPEND
        );
        
        // Пробуем ВАРИАНТ 2: TLS
        try {
            $mail2 = new PHPMailer(true);
            $mail2->isSMTP();
            $mail2->Host = 'smtp.yandex.ru';
            $mail2->SMTPAuth = true;
            $mail2->Username = 'market.support@yandex.ru';
            $mail2->Password = 'zjhaelxipsybpwlt';
            $mail2->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
            $mail2->Port = 587;
            
            $mail2->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            $mail2->Timeout = 30;
            $mail2->CharSet = 'UTF-8';
            $mail2->setFrom('market.support@yandex.ru', 'ПлюкСудоку');
            $mail2->addAddress($email, $username);
            $mail2->isHTML(true);
            $mail2->Subject = 'Подтверждение регистрации в ПлюкСудоку';
            $mail2->Body = create_email_template($username, $confirmation_link);
            $mail2->AltBody = "Подтвердите регистрацию: {$confirmation_link}";
            
            $mail2->send();
            
            file_put_contents(__DIR__ . '/email_log.txt', 
                "[" . date('Y-m-d H:i:s') . "] SMTP SUCCESS: Email sent via Yandex TLS\n", 
                FILE_APPEND
            );
            return true;
            
        } catch (Exception $e) {
            $error2 = $e->getMessage();
            file_put_contents(__DIR__ . '/email_log.txt', 
                "[" . date('Y-m-d H:i:s') . "] SMTP TLS Failed: {$error2}\n", 
                FILE_APPEND
            );
            
            // Пробуем ВАРИАНТ 3: Без шифрования (не рекомендуется, но для теста)
            try {
                $mail3 = new PHPMailer(true);
                $mail3->isSMTP();
                $mail3->Host = 'smtp.yandex.ru';
                $mail3->SMTPAuth = true;
                $mail3->Username = 'market.support@yandex.ru';
                $mail3->Password = 'zjhaelxipsybpwlt';
                $mail3->SMTPSecure = false; // Без шифрования
                $mail3->Port = 25;
                
                $mail3->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
                
                $mail3->Timeout = 30;
                $mail3->CharSet = 'UTF-8';
                $mail3->setFrom('market.support@yandex.ru', 'ПлюкСудоку');
                $mail3->addAddress($email, $username);
                $mail3->isHTML(true);
                $mail3->Subject = 'Подтверждение регистрации в ПлюкСудоку';
                $mail3->Body = create_email_template($username, $confirmation_link);
                $mail3->AltBody = "Подтвердите регистрацию: {$confirmation_link}";
                
                $mail3->send();
                
                file_put_contents(__DIR__ . '/email_log.txt', 
                    "[" . date('Y-m-d H:i:s') . "] SMTP SUCCESS: Email sent via Yandex No-Encryption\n", 
                    FILE_APPEND
                );
                return true;
                
            } catch (Exception $e) {
                $error3 = $e->getMessage();
                file_put_contents(__DIR__ . '/email_log.txt', 
                    "[" . date('Y-m-d H:i:s') . "] SMTP All Methods Failed:\n- SSL: {$error1}\n- TLS: {$error2}\n- No-Encrypt: {$error3}\n", 
                    FILE_APPEND
                );
                return false;
            }
        }
    }
}

function create_email_template($username, $confirmation_link) {
    return "
    <html>
    <body>
        <h2>Добро пожаловать, {$username}!</h2>
        <p>Для подтверждения регистрации перейдите по ссылке:</p>
        <a href='{$confirmation_link}'>{$confirmation_link}</a>
        <p>Ссылка действительна 24 часа.</p>
    </body>
    </html>
    ";
}
?>