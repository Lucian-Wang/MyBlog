<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/Parsedown.php';
require_once __DIR__ . '/config.php';

$pdo = getPDO();
$site = $pdo->query('SELECT * FROM site_info WHERE id=1')->fetch();
// 获取当前主题名，默认default
$theme = $site['theme'] ?? 'default';

// 查询所有未隐藏的文章（按日期倒序）
$stmt = $pdo->query('SELECT id, title, date FROM articles WHERE hidden=0 ORDER BY date DESC');
$articles_all = $stmt->fetchAll();

// 按 年-月 分组归档
$archives = [];
foreach ($articles_all as $article) {
    $year_month = date('Y-m', strtotime($article['date']));
    $archives[$year_month][] = $article;
}

// 加载当前主题模板目录
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/tpl/{$theme}");
$twig = new \Twig\Environment($loader, ['cache' => false]);

echo $twig->render('archive.html', [
    'site' => $site,
    'archives' => $archives,
]);