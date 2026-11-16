<?php
// 数据库配置示例文件
// 请复制此文件为 config.php 并修改配置

define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASSWORD', 'your_database_password');

// 每页文章数量
define('ARTICLES_PER_PAGE', 10);

// 数据库连接函数（使用PDO）
function getPDO() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
    }
    return $pdo;
}
?>
