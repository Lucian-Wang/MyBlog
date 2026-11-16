<?php
session_start();
require_once '../config.php';

if (empty($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}

$pdo = getPDO();

if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $row = $pdo->prepare('SELECT hidden FROM articles WHERE id=?');
    $row->execute([$id]);
    $cur = $row->fetch();
    if ($cur) {
        $newHidden = $cur['hidden'] ? 0 : 1;
        $stmt = $pdo->prepare('UPDATE articles SET hidden=? WHERE id=?');
        $stmt->execute([$newHidden, $id]);
        $pageParam = isset($_GET['page']) ? intval($_GET['page']) : 1;
        header("Location: manage.php?page={$pageParam}");
        exit;
    }
}

$page = max(1, intval($_GET['page'] ?? 1));
$pageSize = 10;
$total = $pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn();
$totalPages = ceil($total / $pageSize);

$stmt = $pdo->prepare('SELECT id, title, date, hidden FROM articles ORDER BY hidden ASC, date DESC LIMIT ?, ?');
$stmt->bindValue(1, ($page-1)*$pageSize, PDO::PARAM_INT);
$stmt->bindValue(2, $pageSize, PDO::PARAM_INT);
$stmt->execute();
$articlesPaged = $stmt->fetchAll();

$visibleArticles = [];
$hiddenArticles = [];
foreach ($articlesPaged as $a) {
    if ($a['hidden']) {
        $hiddenArticles[] = $a;
    } else {
        $visibleArticles[] = $a;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>文章管理</title>
    <link rel="stylesheet" href="/assets/admin.css">
</head>
<body>
<div class="admin-container">
    <div class="admin-header">文章管理</div>
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
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-number"><?= $total ?></div>
                <div class="stat-label">总文章数</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $page ?></div>
                <div class="stat-label">当前页</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $totalPages ?></div>
                <div class="stat-label">总页数</div>
            </div>
        </div>

        <?php if (count($visibleArticles) > 0): ?>
        <div class="list-section">
            <div class="section-header">
                <span class="section-title">已发布文章</span>
                <span class="section-count"><?= count($visibleArticles) ?></span>
            </div>
            <table class="list-table">
                <thead>
                    <tr>
                        <th style="width: 45%;">标题</th>
                        <th style="width: 18%;">日期</th>
                        <th style="width: 12%;">状态</th>
                        <th style="width: 25%;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($visibleArticles as $a): ?>
                    <tr>
                        <td>
                            <a href="/article/<?=urlencode($a['id'])?>" target="_blank" class="article-title-link">
                                <?=htmlspecialchars($a['title'])?>
                            </a>
                        </td>
                        <td><?=htmlspecialchars($a['date'])?></td>
                        <td><span class="status-badge visible">👁️ 已发布</span></td>
                        <td>
                            <div class="action-group">
                                <a href="write.php?edit=<?=urlencode($a['id'])?>" class="action-btn edit">✏️ 编辑</a>
                                <a href="/article/<?=urlencode($a['id'])?>" target="_blank" class="action-btn preview">👁️ 预览</a>
                                <a href="manage.php?toggle=<?=urlencode($a['id'])?>&page=<?=$page?>" class="action-btn toggle" onclick="return confirm('确定要隐藏这篇文章吗？')">🙈 隐藏</a>
                                <a href="delete.php?id=<?=urlencode($a['id'])?>" class="action-btn delete" onclick="return confirm('确认删除这篇文章？此操作不可恢复！')">🗑️ 删除</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if (count($hiddenArticles) > 0): ?>
        <div class="list-section">
            <div class="section-header">
                <span class="section-title">已隐藏文章</span>
                <span class="section-count"><?= count($hiddenArticles) ?></span>
            </div>
            <table class="list-table">
                <thead>
                    <tr>
                        <th style="width: 45%;">标题</th>
                        <th style="width: 18%;">日期</th>
                        <th style="width: 12%;">状态</th>
                        <th style="width: 25%;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($hiddenArticles as $a): ?>
                    <tr>
                        <td>
                            <a href="/article/<?=urlencode($a['id'])?>" target="_blank" class="article-title-link hidden">
                                <?=htmlspecialchars($a['title'])?>
                            </a>
                        </td>
                        <td><?=htmlspecialchars($a['date'])?></td>
                        <td><span class="status-badge hidden">👁️‍🗨️ 已隐藏</span></td>
                        <td>
                            <div class="action-group">
                                <a href="write.php?edit=<?=urlencode($a['id'])?>" class="action-btn edit">✏️ 编辑</a>
                                <a href="/article/<?=urlencode($a['id'])?>" target="_blank" class="action-btn preview">👁️ 预览</a>
                                <a href="manage.php?toggle=<?=urlencode($a['id'])?>&page=<?=$page?>" class="action-btn toggle" onclick="return confirm('确定要显示这篇文章吗？')">📢 显示</a>
                                <a href="delete.php?id=<?=urlencode($a['id'])?>" class="action-btn delete" onclick="return confirm('确认删除这篇文章？此操作不可恢复！')">🗑️ 删除</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if (count($articlesPaged) === 0): ?>
            <div class="empty-state">
                <div class="icon">📝</div>
                <div class="text">暂无文章，<a href="write.php" style="color: var(--primary); text-decoration: underline;">去写一篇</a>吧！</div>
            </div>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
                <a href="?page=<?=($page-1)?>">← 上一页</a>
            <?php endif; ?>
            <?php for($i=1; $i<=$totalPages; $i++): ?>
                <?php if($i == $page): ?>
                    <strong><?=$i?></strong>
                <?php else: ?>
                    <a href="?page=<?=$i?>"><?=$i?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if($page < $totalPages): ?>
                <a href="?page=<?=($page+1)?>">下一页 →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="admin-footer">
        &copy; <?=date('Y')?> 博客后台
    </div>
</div>
</body>
</html>
