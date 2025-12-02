<?php
// Простой тест cron
file_put_contents('cron_test.log', date('Y-m-d H:i:s') . " - Cron работает!\n", FILE_APPEND);
echo "Cron тест завершен - проверьте файл cron_test.log";
?>