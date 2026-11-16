<?php
session_start();
require_once '../config.php';
if (empty($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}
$pdo = getPDO();
$site = $pdo->query('SELECT * FROM site_info WHERE id=1')->fetch();

$themes = array_filter(scandir(__DIR__ . '/../tpl'), function($d) {
    return is_dir(__DIR__ . '/../tpl/' . $d) && !in_array($d, ['.', '..']);
});

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $footer = trim($_POST['footer'] ?? '');
    $theme = trim($_POST['theme'] ?? 'default');
    $stmt = $pdo->prepare('UPDATE site_info SET title=?, subtitle=?, footer=?, theme=? WHERE id=1');
    $stmt->execute([$title, $subtitle, $footer, $theme]);
    $site['title'] = $title;
    $site['subtitle'] = $subtitle;
    $site['footer'] = $footer;
    $site['theme'] = $theme;
    $msg = "保存成功！";
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>站点设置</title>
    <link rel="stylesheet" href="/assets/admin.css">
</head>
<body>
<div class="admin-container">
    <div class="admin-header">站点设置</div>
    <div class="admin-navbar">
        <a href="/" target="_blank">前台首页</a>
        <a href="manage.php">文章管理</a>
        <a href="write.php">写文章</a>
        <a href="images.php">图片管理</a>
        <a href="attachments.php">文件管理</a>
        <a href="settings.php">站点设置</a>
        <a href="logout.php">退出</a>
    </div>
    <div class="section-padding">
        <?php if (!empty($msg)) echo "<div class='success-msg'>✨ {$msg}</div>"; ?>
        <div class="settings-grid">
            <form method="post" class="admin-form" style="padding: 0;">
                <div class="settings-card">
                    <div class="card-header">
                        <div class="card-icon">🏷️</div>
                        <div>
                            <div class="card-title">基本信息</div>
                            <div class="card-desc">设置网站的标题和描述信息</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            网站标题
                            <span class="hint">· 显示在浏览器标签和页面顶部</span>
                        </label>
                        <input type="text" name="title" id="title" value="<?=htmlspecialchars($site['title'] ?? '')?>" class="settings-input" placeholder="请输入网站标题..." required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            网站副标题
                            <span class="hint">· 简短描述你的网站</span>
                        </label>
                        <input type="text" name="subtitle" id="subtitle" value="<?=htmlspecialchars($site['subtitle'] ?? '')?>" class="settings-input" placeholder="请输入网站副标题...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            页脚信息
                            <span class="hint">· 显示在页面底部，支持 HTML</span>
                        </label>
                        <input type="text" name="footer" id="footer" value="<?=htmlspecialchars($site['footer'] ?? '')?>" class="settings-input" placeholder="请输入页脚信息...">
                    </div>
                </div>
                <div class="settings-card" style="margin-top: 24px;">
                    <div class="card-header">
                        <div class="card-icon">🎨</div>
                        <div>
                            <div class="card-title">主题设置</div>
                            <div class="card-desc">选择网站的前台展示主题</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">当前主题</label>
                        <div class="theme-preview" id="themePreview">
                            <?php foreach ($themes as $t): ?>
                            <div class="theme-option <?= $site['theme'] == $t ? 'selected' : '' ?>" data-theme="<?=htmlspecialchars($t)?>">
                                <span class="theme-icon">📋</span>
                                <span class="theme-name"><?=htmlspecialchars($t)?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <select name="theme" id="theme" class="settings-select" style="margin-top: 16px;">
                            <?php foreach ($themes as $t): ?>
                            <option value="<?=htmlspecialchars($t)?>" <?=$site['theme']==$t?'selected':''?>><?=htmlspecialchars($t)?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-footer">
                    <button type="submit" class="btn-primary">
                        <span>💾</span>
                        <span>保存设置</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="admin-footer">
        &copy; <?=date('Y')?> 博客后台
    </div>
</div>
<script>
document.querySelectorAll('.theme-option').forEach(function(option) {
    option.addEventListener('click', function() {
        document.querySelectorAll('.theme-option').forEach(function(o) {
            o.classList.remove('selected');
        });
        this.classList.add('selected');
        document.getElementById('theme').value = this.getAttribute('data-theme');
    });
});
</script>
</body>
</html>
