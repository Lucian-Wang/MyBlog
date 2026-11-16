<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/Parsedown.php';
require_once __DIR__ . '/config.php';

$pdo = getPDO();
$site = $pdo->query('SELECT * FROM site_info WHERE id=1')->fetch();
// 获取当前主题名，默认default
$theme = $site['theme'] ?? 'default';

$page = max(1, intval($_GET['page'] ?? 1));
$pageSize = defined('ARTICLES_PER_PAGE') ? ARTICLES_PER_PAGE : 10;
$start = ($page - 1) * $pageSize;

// 只显示未隐藏的文章
$total = $pdo->query('SELECT COUNT(*) FROM articles WHERE hidden=0')->fetchColumn();
$pages = max(1, ceil($total / $pageSize));

// 1. 查询content
$stmt = $pdo->prepare('SELECT id, title, date, content FROM articles WHERE hidden=0 ORDER BY date DESC LIMIT ?, ?');
$stmt->bindValue(1, $start, PDO::PARAM_INT);
$stmt->bindValue(2, $pageSize, PDO::PARAM_INT);
$stmt->execute();
$articles_page = $stmt->fetchAll();

// 2. 生成摘要
$Parsedown = new Parsedown();
foreach ($articles_page as &$article) {
    $text = strip_tags($Parsedown->text($article['content'] ?? ''));
    $text = preg_replace('/\s+/', ' ', $text);
    $article['summary'] = mb_substr($text, 0, 90, 'utf-8') . (mb_strlen($text, 'utf-8') > 90 ? '...' : '');
}
unset($article);

// 加载当前主题模板目录
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/tpl/{$theme}");
$twig = new \Twig\Environment($loader, ['cache' => false]);

echo $twig->render('index.html', [
    'site' => $site,
    'articles' => $articles_page,
    'page' => $page,
    'pages' => $pages,
]);