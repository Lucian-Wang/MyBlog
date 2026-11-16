<?php
session_start();
require_once '../config.php';

$pdo = getPDO();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username=? LIMIT 1');
    $stmt->execute([$user]);
    $row = $stmt->fetch();
    if ($row && password_verify($pass, $row['password'])) {
        $_SESSION['admin_login'] = true;
        $_SESSION['admin_user'] = $row['username'];
        header("Location: manage.php");
        exit;
    } else {
        $error = "账号或密码错误";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>登录后台</title>
    <link rel="stylesheet" href="/assets/admin.css">
</head>
<body>
<div class="admin-container" style="max-width:460px; margin-top: 80px;">
    <div style="text-align: center; padding: 40px 32px 20px 32px;">
        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px; box-shadow: var(--shadow-lg);">
            <span style="color: white; font-size: 2em; font-weight: bold;">B</span>
        </div>
        <div class="admin-header" style="padding: 0; font-size: 1.8em;">后台登录</div>
    </div>
    <?php if (!empty($error)) echo "<div class='error-msg'>{$error}</div>"; ?>
    <form method="post" class="admin-form" style="padding-bottom: 32px;">
        <label for="user">账号</label>
        <input type="text" name="user" id="user" required placeholder="请输入账号">
        <label for="pass">密码</label>
        <input type="password" name="pass" id="pass" required placeholder="请输入密码">
        <button type="submit" style="width: 100%; margin-top: 32px; font-size: 1.05em;">登 录</button>
    </form>
</div>
</body>
</html>