<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Parsedown.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$pdo = getPDO();
$site = $pdo->query('SELECT * FROM site_info WHERE id=1')->fetch();
$theme = $site['theme'] ?? 'default';

$id = $_GET['id'] ?? '';
$id = intval($id);

if (!$id) {
    http_response_code(404);
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/tpl/{$theme}");
    $twig = new \Twig\Environment($loader, ['cache' => false]);
    echo $twig->render('404.html', ['site' => $site]);
    exit;
}

// 只允许访问未隐藏的文章
$stmt = $pdo->prepare('SELECT id, title, date, content FROM articles WHERE id = ? AND hidden = 0');
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/tpl/{$theme}");
    $twig = new \Twig\Environment($loader, ['cache' => false]);
    echo $twig->render('404.html', ['site' => $site]);
    exit;
}

$Parsedown = new Parsedown();
$article['content_html'] = $Parsedown->text($article['content']);

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/tpl/{$theme}");
$twig = new \Twig\Environment($loader, ['cache' => false]);
echo $twig->render('view.html', [
    'site' => $site,
    'article' => $article,
]);