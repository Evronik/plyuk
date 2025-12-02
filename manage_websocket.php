<?php
// manage_websocket.php - управление демо-сервером
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Турнирная система - ПлюкСудоку</title>
    <meta charset="utf-8">
    <style>
        body { 
            font-family: 'Inter', Arial, sans-serif; 
            margin: 40px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .status-online {
            background: #4CAF50;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .info-box {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎯 Турнирная система ПлюкСудоку</h1>
        
        <div class="status-online">
            <h2>✅ Система работает в HTTP-режиме</h2>
            <p>Используется long-polling вместо WebSocket</p>
        </div>
        
        <div class="info-box">
            <h3>📊 Статус:</h3>
            <p>• ✅ Турниры доступны через HTTP API</p>
            <p>• ✅ Регистрация в турнирах работает</p>
            <p>• ✅ Авто-обновление каждые 30 секунд</p>
            <p>• 🔄 Реальный WebSocket недоступен на shared хостинге</p>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <button onclick="window.location.href='https://plyuk.site/game.php'" style="padding: 12px 24px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px;">
                🎮 Перейти к игре
            </button>
        </div>
    </div>
</body>
</html>
<?php
    exit;
}
?>