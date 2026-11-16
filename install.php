<?php
// 博客系统安装向导

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$error = '';
$success = '';

// 步骤 1: 环境检测
if ($step === 1) {
    $checks = [
        'PHP 版本 >= 7.0' => version_compare(PHP_VERSION, '7.0.0', '>='),
        'PDO 扩展' => extension_loaded('pdo'),
        'PDO MySQL 扩展' => extension_loaded('pdo_mysql'),
        'config.php 可写或不存在' => (!file_exists(__DIR__ . '/config.php') || is_writable(__DIR__ . '/config.php')),
        'uploads/ 目录可写' => (is_dir(__DIR__ . '/uploads') && is_writable(__DIR__ . '/uploads')),
    ];
    
    if (!is_dir(__DIR__ . '/uploads')) {
        @mkdir(__DIR__ . '/uploads', 0755, true);
        $checks['uploads/ 目录可写'] = (is_dir(__DIR__ . '/uploads') && is_writable(__DIR__ . '/uploads'));
    }
}

// 步骤 2: 数据库配置
if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_port = intval($_POST['db_port'] ?? 3306);
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    
    try {
        $dsn = "mysql:host=$db_host;port=$db_port;charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$db_name`");
        
        $config_content = "<?php
define('DB_HOST', '$db_host');
define('DB_PORT', $db_port);
define('DB_NAME', '$db_name');
define('DB_USER', '$db_user');
define('DB_PASSWORD', '$db_pass');
define('ARTICLES_PER_PAGE', 10);
function getPDO() {
    static \$pdo = null;
    if (\$pdo === null) {
        \$dsn = 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4';
        \$options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        \$pdo = new PDO(\$dsn, DB_USER, DB_PASSWORD, \$options);
    }
    return \$pdo;
}
?>";
        
        file_put_contents(__DIR__ . '/config.php', $config_content);
        
        header('Location: ?step=3');
        exit;
        
    } catch (PDOException $e) {
        $error = '数据库连接失败: ' . $e->getMessage();
    }
}

// 步骤 3: 导入数据库
if ($step === 3) {
    if (file_exists(__DIR__ . '/config.php')) {
        require_once __DIR__ . '/config.php';
        try {
            $pdo = getPDO();
            $sql = file_get_contents(__DIR__ . '/install.sql');
            $pdo->exec($sql);
            $success = true;
        } catch (Exception $e) {
            $error = '导入失败: ' . $e->getMessage();
        }
    } else {
        $error = 'config.php 不存在，请先完成步骤 2';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>博客系统 - 安装向导</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .install-container {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            text-align: center;
            color: #667eea;
            margin-bottom: 30px;
            font-size: 1.8em;
        }
        .steps {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 10px;
        }
        .step-dot {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #64748b;
        }
        .step-dot.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        .step-dot.completed {
            background: #10b981;
            color: #fff;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.2s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            width: 100%;
            text-align: center;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102,126,234,0.4);
        }
        .check-item {
            padding: 12px 16px;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .check-item.success { color: #059669; }
        .check-item.error { color: #dc2626; }
        .check-icon {
            font-size: 1.2em;
            width: 24px;
            text-align: center;
        }
        .error-msg {
            background: #fef2f2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success-msg {
            text-align: center;
            padding: 20px;
        }
        .success-msg h2 {
            color: #059669;
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        .success-msg p {
            color: #64748b;
            margin-bottom: 20px;
        }
        .default-account {
            background: #eff6ff;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .default-account h3 {
            color: #1e40af;
            margin-bottom: 10px;
            font-size: 1em;
        }
        .default-account p {
            color: #374151;
            margin: 6px 0;
        }
        .default-account code {
            background: #dbeafe;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <h1>🚀 博客系统安装向导</h1>
        
        <div class="steps">
            <div class="step-dot <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">1</div>
            <div class="step-dot <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">2</div>
            <div class="step-dot <?php echo $step >= 3 ? 'completed' : ''; ?>">3</div>
        </div>

        <?php if ($step === 1): ?>
            <h2 style="margin-bottom:20px;color:#374151;font-size:1.2em;">环境检测</h2>
            <?php foreach ($checks as $name => $passed): ?>
                <div class="check-item <?php echo $passed ? 'success' : 'error'; ?>">
                    <span class="check-icon"><?php echo $passed ? '✓' : '✗'; ?></span>
                    <span><?php echo $name; ?></span>
                </div>
            <?php endforeach; ?>
            <form method="get" style="margin-top:20px;">
                <input type="hidden" name="step" value="2">
                <button type="submit" class="btn" <?php echo !in_array(false, $checks) ? '' : 'disabled style="opacity:0.5;cursor:not-allowed;"'; ?>>
                    下一步
                </button>
            </form>
        
        <?php elseif ($step === 2): ?>
            <h2 style="margin-bottom:20px;color:#374151;font-size:1.2em;">数据库配置</h2>
            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>数据库主机</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label>数据库端口</label>
                    <input type="number" name="db_port" value="3306" required>
                </div>
                <div class="form-group">
                    <label>数据库名</label>
                    <input type="text" name="db_name" required placeholder="请输入数据库名">
                </div>
                <div class="form-group">
                    <label>数据库用户名</label>
                    <input type="text" name="db_user" required placeholder="请输入用户名">
                </div>
                <div class="form-group">
                    <label>数据库密码</label>
                    <input type="password" name="db_pass" placeholder="请输入密码">
                </div>
                <button type="submit" class="btn">下一步</button>
            </form>
        
        <?php elseif ($step === 3): ?>
            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif ($success): ?>
                <div class="success-msg">
                    <h2>🎉 安装成功！</h2>
                    <p>博客系统已成功安装，请删除 install.php 和 install.sql 以确保安全。</p>
                    
                    <div class="default-account">
                        <h3>默认管理员账号</h3>
                        <p>用户名: <code>admin</code></p>
                        <p>密码: <code>admin123</code></p>
                        <p style="margin-top:10px;color:#dc2626;">⚠️ 请登录后立即修改密码！</p>
                    </div>
                    
                    <a href="/admin/" class="btn" style="display:inline-block;">前往后台</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
